import { createServer } from 'node:http'
import { readFileSync, writeFileSync } from 'node:fs'
import { extname, join, resolve } from 'node:path'
import { chromium } from 'playwright'

const mimeTypes: Record<string, string> = {
  '.html': 'text/html',
  '.js': 'application/javascript',
  '.mjs': 'application/javascript',
  '.css': 'text/css',
  '.png': 'image/png',
  '.svg': 'image/svg+xml',
  '.ico': 'image/x-icon',
  '.json': 'application/json',
  '.woff2': 'font/woff2',
}

function staticHandler(req: any, res: any, opts: { public: string }) {
  const distDir = opts.public
  const urlPath = req.url === '/' ? '/index.html' : req.url.split('?')[0]
  const filePath = join(distDir, urlPath)
  try {
    const content = readFileSync(filePath)
    const ext = extname(filePath)
    res.writeHead(200, { 'Content-Type': mimeTypes[ext] || 'application/octet-stream' })
    res.end(content)
  } catch {
    const html = readFileSync(join(distDir, 'index.html'))
    res.writeHead(200, { 'Content-Type': 'text/html' })
    res.end(html)
  }
}

async function prerender() {
  const distDir = resolve(process.cwd(), 'dist')
  const port = 4173

  const server = createServer((req, res) => staticHandler(req, res, { public: distDir }))
  await new Promise<void>((done) => server.listen(port, done))

  try {
    const browser = await chromium.launch({
      args: ['--no-sandbox', '--disable-setuid-sandbox'],
    })
    const page = await browser.newPage()

    await page.goto(`http://localhost:${port}/`, { waitUntil: 'load', timeout: 30_000 })
    await page.waitForSelector('meta[property="og:title"]', { timeout: 10_000 })

    const html = await page.evaluate(() => document.documentElement.outerHTML)
    await browser.close()

    writeFileSync(resolve(distDir, 'index.html'), `<!DOCTYPE html>\n${html}`, 'utf-8')
    console.log('✓ Landing pré-renderizada com sucesso em dist/index.html')
  } finally {
    await new Promise<void>((done) => server.close(() => done()))
  }
}

prerender().catch((err) => {
  console.error('✗ Falha no pré-render da landing:', err)
  process.exit(1)
})
