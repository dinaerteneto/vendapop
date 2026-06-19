#!/bin/bash
# Gera um convite público e formata mensagem pronta para WhatsApp
# Uso: bash scripts/convite-publico.sh [slots] [horas]
# Ex:   bash scripts/convite-publico.sh 10 168   (10 vagas, 7 dias)

set -euo pipefail
cd "$(dirname "$0")/.."

SLOTS="${1:-10}"
HOURS="${2:-168}"

echo "🔐 Gerando convite público (${SLOTS} vagas, ${HOURS}h)..."

OUTPUT=$(docker compose exec -T backend php artisan invite:create public --slots="$SLOTS" --hours="$HOURS" 2>/dev/null)

# Extrai a URL do output
URL=$(echo "$OUTPUT" | grep -oP 'URL:\s+\K\S+')
CODE=$(echo "$OUTPUT" | grep -oP 'Code:\s+\K\S+')

if [ -z "$URL" ]; then
  echo "❌ Erro ao gerar convite."
  echo "$OUTPUT"
  exit 1
fi

echo ""
echo "✅ Convite gerado!"
echo ""
echo "════════════════════════════════════════════"
echo "  📋 MENSAGEM PARA WHATSAPP"
echo "════════════════════════════════════════════"
echo ""
cat <<MSG
🚀 **Monte sua loja online em 5 minutos — de graça.**

Criei uma ferramenta que transforma seu catálogo do Instagram numa loja de verdade. A cliente escolhe tamanho, cor, monta o carrinho e o pedido chega **organizado no seu WhatsApp**.

🆓 **Plano Grátis**: até 6 produtos, sem pagar nada.
🎁 **Convidadas ganham 90 dias de Básico grátis** (30 produtos, PIX, sem anúncios).

👉 ${URL}

*Vagas limitadas — só funciona pra quem usar esse link.*
MSG
echo ""
echo "════════════════════════════════════════════"
echo ""
echo "📊 Detalhes:"
echo "   Código: ${CODE}"
echo "   Vagas:  ${SLOTS}"
echo "   Expira: ${HOURS}h"
echo "   Status: ativo"
