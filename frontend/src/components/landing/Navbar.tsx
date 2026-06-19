import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'

const links = [
  { label: 'Como funciona', href: '#como-funciona' },
  { label: 'Planos', href: '#planos' },
  { label: 'Cases', href: '#cases' },
]

const Navbar: React.FC = () => {
  const [scrolled, setScrolled] = useState(false)

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40)
    window.addEventListener('scroll', onScroll, { passive: true })
    return () => window.removeEventListener('scroll', onScroll)
  }, [])

  const scrollTo = (href: string) => {
    const el = document.querySelector(href)
    if (el) el.scrollIntoView({ behavior: 'smooth' })
  }

  return (
    <nav
      className={`fixed top-0 left-0 right-0 z-50 transition-all duration-200 ${
        scrolled
          ? 'bg-white/95 backdrop-blur shadow-sm border-b border-gray-100'
          : 'bg-transparent'
      }`}
    >
      <div className="container mx-auto px-4 h-14 flex items-center justify-between">
        <Link to="/" className="text-lg font-bold text-purple-700 shrink-0">
          VendaPop
        </Link>

        <div className="hidden md:flex items-center gap-6">
          {links.map((l) => (
            <button
              key={l.href}
              onClick={() => scrollTo(l.href)}
              className="text-sm text-gray-500 hover:text-purple-700 transition"
            >
              {l.label}
            </button>
          ))}
        </div>

        <div className="flex items-center gap-3">
          <a
            href="#waitlist"
            onClick={(e) => {
              e.preventDefault()
              scrollTo('#waitlist')
            }}
            className="hidden sm:inline-block px-4 py-1.5 text-sm font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 transition"
          >
            Quero meu convite
          </a>
          <Link
            to="/admin/login"
            className="text-sm text-gray-500 hover:text-purple-700 transition"
          >
            Entrar
          </Link>
        </div>
      </div>
    </nav>
  )
}

export default Navbar
