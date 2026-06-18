#!/bin/bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DEPLOY_DIR="$ROOT_DIR/deploy"
COMPOSE_FILE="$DEPLOY_DIR/docker-compose.prod.yml"
ENV_FILE="$DEPLOY_DIR/.env.production"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'

log()  { echo -e "${GREEN}[✓]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }
err()  { echo -e "${RED}[✗]${NC} $1"; exit 1; }
info() { echo -e "${CYAN}[i]${NC} $1"; }

echo ""
echo -e "${CYAN}╔══════════════════════════════════════════╗${NC}"
echo -e "${CYAN}║     VendaPop — Deploy v1.12.0            ║${NC}"
echo -e "${CYAN}╚══════════════════════════════════════════╝${NC}"
echo ""

# ---------------------------------------------------------
# 1. Pré-requisitos
# ---------------------------------------------------------
command -v docker >/dev/null 2>&1 || err "Docker não instalado."
command -v docker compose >/dev/null 2>&1 || err "Docker Compose não instalado."
[ -f "$ENV_FILE" ] || err ".env.production não encontrado em $ENV_FILE"

# Verifica rede externa 'web' (necessária para o reverse proxy)
if ! docker network inspect web >/dev/null 2>&1; then
    warn "Rede 'web' não existe. Criando..."
    docker network create web
    log "Rede 'web' criada"
fi

# ---------------------------------------------------------
# 2. Pull do git
# ---------------------------------------------------------
info "Atualizando código..."
cd "$ROOT_DIR"
git checkout main
git pull origin main

# Verifica tag se especificada
if [ -n "${TAG:-}" ]; then
    info "Checkout da tag $TAG..."
    git checkout "$TAG"
fi

log "Código atualizado (branch: $(git branch --show-current))"
echo ""

# ---------------------------------------------------------
# 3. Build das imagens
# ---------------------------------------------------------
info "Construindo imagens Docker..."
cd "$DEPLOY_DIR"
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" build \
    --build-arg "VITE_API_BASE_URL=https://api.${DOMAIN:-vendapop.com.br}/api" \
    --build-arg "VITE_RECAPTCHA_SITE_KEY=${RECAPTCHA_SITE_KEY:-}"

log "Imagens construídas"
echo ""

# ---------------------------------------------------------
# 4. Rodar migrations
# ---------------------------------------------------------
info "Rodando migrations..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm backend php artisan migrate --force
log "Migrations concluídas"
echo ""

# ---------------------------------------------------------
# 5. Criar symlink de storage (se não existir)
# ---------------------------------------------------------
info "Verificando storage symlink..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm backend sh -c \
    "if [ ! -L public/storage ]; then php artisan storage:link; fi" || warn "Storage symlink já existe ou não foi possível criar"
echo ""

# ---------------------------------------------------------
# 6. Cache do Laravel
# ---------------------------------------------------------
info "Otimizando cache..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm backend php artisan config:cache
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm backend php artisan route:cache
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" run --rm backend php artisan view:cache
log "Cache otimizado"
echo ""

# ---------------------------------------------------------
# 7. Subir serviços (rolling update)
# ---------------------------------------------------------
info "Subindo serviços..."
docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" up -d --remove-orphans

echo ""

# ---------------------------------------------------------
# 8. Healthcheck
# ---------------------------------------------------------
info "Aguardando serviços iniciarem..."
sleep 5

# Verifica se containers estão rodando
FAILED=$(docker compose -f "$COMPOSE_FILE" ps --format json | python3 -c "
import sys, json
for line in sys.stdin:
    c = json.loads(line)
    if c.get('State') != 'running':
        print(c.get('Name','unknown'))
" 2>/dev/null || echo "")

if [ -n "$FAILED" ]; then
    warn "Containers com problema: $FAILED"
else
    log "Todos os containers rodando"
fi

echo ""
docker compose -f "$COMPOSE_FILE" ps
echo ""

# ---------------------------------------------------------
# 9. Limpeza de imagens antigas
# ---------------------------------------------------------
info "Limpando imagens antigas (3 dias)..."
docker image prune -af --filter "until=72h" 2>/dev/null || warn "Limpeza de imagens ignorada"
echo ""

# ---------------------------------------------------------
# 10. Conclusão
# ---------------------------------------------------------
echo -e "${GREEN}╔══════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║     Deploy concluído! v1.12.0            ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════╝${NC}"
echo ""
info "Frontend: https://${DOMAIN:-vendapop.com.br}"
info "API:      https://api.${DOMAIN:-vendapop.com.br}/api"
echo ""
