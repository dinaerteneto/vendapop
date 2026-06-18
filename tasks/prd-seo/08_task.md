# Task 08 — Script de pré-render da landing

**Status:** Concluído  
**Frente:** B  
**Dependências:** Task 02 (SEOHead criado), Task 03 (Landing com SEOHead)

## Objetivo

Criar o script TypeScript `scripts/prerender-landing.ts` que executa após o `vite build`, sobe um servidor HTTP local, usa Playwright para capturar o HTML estático completo da landing (com meta tags já injetadas pelo react-helmet-async) e sobrescreve o `dist/index.html`. O resultado é um HTML completamente indexável por crawlers sem necessidade de executar JavaScript.

## Contexto Técnico

- Playwright já está instalado no projeto como devDependency (`@playwright/test`)
- `tsx` precisa ser instalado como devDependency para executar scripts TypeScript diretamente
- O script usa `chromium` do Playwright (não `@playwright/test`) — importar de `playwright`
- `serve-handler` é dependência transitiva do Playwright — disponível sem instalação extra; se não disponível, instalar como devDependency
- O script deve ser tolerante a erros: se falhar, o build não deve ser silenciado — deve sair com código 1
- O comando `build:no-prerender` é útil para desenvolvimento local rápido

## Passos de Implementação

### 1. Instalar `tsx`

```bash
cd frontend
npm install -D tsx
```

### 2. Verificar se `serve-handler` está disponível

```bash
ls node_modules/serve-handler 2>/dev/null && echo "disponível" || echo "instalar"
```

Se não disponível:
```bash
npm install -D serve-handler @types/serve-handler
```

### 3. Criar `frontend/scripts/prerender-landing.ts`

```ts
import { createServer } from 'node:http'
import { writeFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { chromium } from 'playwright'

// serve-handler pode precisar de import diferente dependendo da versão
let handler: any
try {
  handler = (await import('serve-handler')).default
} catch {
  // fallback: serve arquivos estáticos manualmente
  const { readFileSync } = await import('node:fs')
  const { extname, join } = await import('node:path')
  const mimeTypes: Record<string, string> = {
    '.html': 'text/html',
    '.js': 'application/javascript',
    '.css': 'text/css',
    '.png': 'image/png',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
    '.json': 'application/json',
    '.woff2': 'font/woff2',
  }
  handler = (req: any, res: any, opts: any) => {
    const distDir = opts.public
    const urlPath = req.url === '/' ? '/index.html' : req.url.split('?')[0]
    const filePath = join(distDir, urlPath)
    try {
      const content = readFileSync(filePath)
      const ext = extname(filePath)
      res.writeHead(200, { 'Content-Type': mimeTypes[ext] || 'application/octet-stream' })
      res.end(content)
    } catch {
      // SPA fallback
      const html = readFileSync(join(distDir, 'index.html'))
      res.writeHead(200, { 'Content-Type': 'text/html' })
      res.end(html)
    }
  }
}

async function prerender() {
  const distDir = resolve(process.cwd(), 'dist')
  const port = 4173

  const server = createServer((req, res) => handler(req, res, { public: distDir }))
  await new Promise<void>((resolve) => server.listen(port, resolve))
  console.log(`Servidor de pré-render ouvindo em http://localhost:${port}`)

  try {
    const browser = await chromium.launch()
    const page = await browser.newPage()

    await page.goto(`http://localhost:${port}/`, { waitUntil: 'networkidle', timeout: 30_000 })

    // Aguarda o react-helmet-async injetar as meta tags OG
    await page.waitForSelector('meta[property="og:title"]', { timeout: 10_000 })

    const html = await page.evaluate(() => document.documentElement.outerHTML)
    await browser.close()

    writeFileSync(resolve(distDir, 'index.html'), `<!DOCTYPE html>\n${html}`, 'utf-8')
    console.log('✓ Landing pré-renderizada com sucesso em dist/index.html')
  } finally {
    await new Promise<void>((resolve) => server.close(() => resolve()))
  }
}

prerender().catch((err) => {
  console.error('✗ Falha no pré-render da landing:', err)
  process.exit(1)
})
```

### 4. Atualizar `frontend/package.json`

```json
"scripts": {
  "dev": "vite",
  "build": "vite build && tsx scripts/prerender-landing.ts",
  "build:no-prerender": "vite build",
  "typecheck": "tsc --noEmit",
  "lint": "eslint . --ext ts,tsx --report-unused-disable-directives --max-warnings 0",
  "preview": "vite preview",
  "test:e2e": "playwright test"
}
```

## Testes de Verificação

### Build local

```bash
cd frontend

# 1. Build completo com pré-render
npm run build
# Esperado: "✓ Landing pré-renderizada com sucesso em dist/index.html" no final

# 2. Verificar meta tags no HTML estático (SEM JavaScript)
grep 'og:title' dist/index.html
# Esperado: <meta property="og:title" content="VendaPop — Sua loja no WhatsApp"

grep 'og:image' dist/index.html
# Esperado: <meta property="og:image" content="https://vendapop.com.br/og-image.png"

grep 'og:description' dist/index.html
# Esperado: <meta property="og:description" content="Monte sua loja em 5 minutos..."

grep 'canonical' dist/index.html
# Esperado: <link rel="canonical" href="https://vendapop.com.br/">

# 3. Servir o dist e verificar com curl (simula crawler sem JS)
npm run preview &
sleep 2
curl -s http://localhost:4173/ | grep 'og:title'
# Esperado: meta tag presente no HTML bruto (sem JS)

# 4. Build sem pré-render ainda funciona
npm run build:no-prerender
# Esperado: build normal sem executar o script Playwright

# 5. Script falha graciosamente se dist/ não existe
tsx scripts/prerender-landing.ts
# Esperado: erro claro e exit code 1 (sem dist/)
```

### Verificação de indexabilidade (após deploy)

```bash
# Crawler recebe HTML completo sem JS
curl -s https://vendapop.com.br/ | grep 'og:title'
# Esperado: meta tag presente no HTML

# Google Rich Results Test
# https://search.google.com/test/rich-results?url=https://vendapop.com.br
```
