# Task 01 — Arquivos estáticos: robots.txt, favicons e manifest

**Status:** Pendente  
**Frente:** D  
**Dependências:** Nenhuma

## Objetivo

Criar o `robots.txt`, atualizar o `index.html` para referenciar os novos favicons reais do VendaPop (no lugar do `/vite.svg` padrão) e atualizar o `manifest.json` com nome, descrição e ícones corretos da plataforma.

Esta task não depende de nenhuma outra e pode ser mergeada de forma independente.

## Contexto Técnico

- `frontend/public/` é servido estaticamente pelo Vite e pelo Caddy em produção
- O `manifest.json` atual tem apenas `favicon.ico` de 64x64 e referencia "VendaPop" sem descrição
- O `index.html` aponta para `/vite.svg` (ícone padrão Vite)
- Os arquivos de assets visuais (favicons, og-image, ícones PWA) devem ser fornecidos pela equipe de design e colocados em `frontend/public/` antes de executar esta task

## Arquivos a Criar/Modificar

### `frontend/public/robots.txt` — CRIAR

```
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /superadmin/
Disallow: /convite/
Sitemap: https://vendapop.com.br/sitemap.xml
```

### `frontend/index.html` — MODIFICAR

Substituir:
```html
<link rel="icon" type="image/svg+xml" href="/vite.svg" />
```

Por:
```html
<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
<link rel="icon" type="image/x-icon" href="/favicon.ico" sizes="32x32" />
<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
```

### `frontend/public/manifest.json` — MODIFICAR

```json
{
  "name": "VendaPop",
  "short_name": "VendaPop",
  "description": "Sua loja online com pedidos direto no WhatsApp",
  "start_url": "/",
  "scope": "/",
  "display": "standalone",
  "theme_color": "#7c3aed",
  "background_color": "#ffffff",
  "icons": [
    { "src": "/favicon.ico", "sizes": "64x64 32x32 24x24 16x16", "type": "image/x-icon" },
    { "src": "/icon-192.png", "sizes": "192x192", "type": "image/png", "purpose": "any" },
    { "src": "/icon-512.png", "sizes": "512x512", "type": "image/png", "purpose": "any maskable" }
  ]
}
```

## Assets Necessários (fornecidos por design)

Colocar em `frontend/public/` antes de executar:
- `favicon.svg` — ícone SVG escalável
- `favicon.ico` — 32×32px
- `apple-touch-icon.png` — 180×180px
- `og-image.png` — 1200×630px (usado como fallback OG nas próximas tasks)
- `icon-192.png` — 192×192px
- `icon-512.png` — 512×512px

## Testes de Verificação

```bash
# 1. robots.txt acessível e com conteúdo correto
curl http://localhost:5173/robots.txt
# Esperado: User-agent: * com os Disallow configurados

# 2. manifest.json atualizado
curl http://localhost:5173/manifest.json | jq '.name, .description, .icons | length'
# Esperado: "VendaPop", "Sua loja online...", 3

# 3. Favicon não é mais o vite.svg
grep "vite.svg" frontend/index.html
# Esperado: nenhum resultado

# 4. Favicon carrega no browser
# Abrir http://localhost:5173 e verificar o ícone na aba do browser

# 5. Verificar que /admin/ está bloqueado para bots
grep "Disallow: /admin/" frontend/public/robots.txt
# Esperado: linha encontrada
```
