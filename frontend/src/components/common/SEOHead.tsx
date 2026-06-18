import { Helmet } from 'react-helmet-async'

const APP_URL = import.meta.env.VITE_API_BASE_URL?.replace('/api', '') ?? 'https://vendapop.com.br'

interface SEOHeadProps {
  title: string
  description?: string
  image?: string
  path?: string
  type?: 'website' | 'product'
  noIndex?: boolean
}

export function SEOHead({
  title,
  description,
  image,
  path = '',
  type = 'website',
  noIndex = false,
}: SEOHeadProps) {
  const fullUrl = `${APP_URL}${path}`
  const ogImage = image ?? `${APP_URL}/og-image.png`

  return (
    <Helmet prioritizeSeoTags>
      <title>{title}</title>
      {description && <meta name="description" content={description} />}
      {noIndex && <meta name="robots" content="noindex, nofollow" />}

      {/* Open Graph */}
      <meta property="og:title" content={title} />
      {description && <meta property="og:description" content={description} />}
      <meta property="og:image" content={ogImage} />
      <meta property="og:url" content={fullUrl} />
      <meta property="og:type" content={type} />
      <meta property="og:site_name" content="VendaPop" />

      {/* Twitter Card */}
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content={title} />
      {description && <meta name="twitter:description" content={description} />}
      <meta name="twitter:image" content={ogImage} />

      {/* Canonical */}
      {!noIndex && <link rel="canonical" href={fullUrl} />}
    </Helmet>
  )
}
