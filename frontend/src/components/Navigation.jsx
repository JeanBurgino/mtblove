import React, { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Menu, X, Instagram, Facebook, Music2 as TikTok, User, LogOut } from 'lucide-react'
import { useAuth } from '../context/AuthContext'

const Navigation = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false)
  const { isAuthenticated, logout, user } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  return (
    <nav className="fixed w-full z-50 bg-dark-800/90 backdrop-blur-md border-b border-blue/10">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <Link to="/" className="flex items-center cursor-pointer group">
            <span className="text-2xl font-black italic tracking-tighter text-blue">
              MTB <span className="text-orange font-cursive">Love</span>
            </span>
          </Link>

          {/* Desktop Navigation */}
          <div className="hidden md:flex items-center space-x-8">
            <NavLink to="/" label="Home" />
            <NavLink to="/wallpapers" label="Gallery" />
            <NavLink to="/shop" label="Shop" />

            <div className="h-6 w-px bg-light/20 mx-2"></div>

            {/* Social Links */}
            <div className="flex space-x-3 text-light">
              <Facebook size={20} className="hover:text-blue cursor-pointer transition-colors" />
              <Instagram size={20} className="hover:text-orange cursor-pointer transition-colors" />
              <TikTok size={20} className="hover:text-light cursor-pointer transition-colors" />
            </div>

            {/* Auth Button */}
            {isAuthenticated ? (
              <div className="flex items-center gap-3">
                <Link to="/admin" className="text-sm text-light/80 hover:text-white">
                  Admin ({user?.username})
                </Link>
                <button
                  onClick={handleLogout}
                  className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-xs font-bold text-red-500 transition-colors"
                >
                  <LogOut size={14} />
                  <span>LOGOUT</span>
                </button>
              </div>
            ) : (
              <Link
                to="/admin/login"
                className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue/10 hover:bg-blue/20 border border-blue/30 text-xs font-bold text-blue transition-colors"
              >
                <User size={14} />
                <span>LOGIN</span>
              </Link>
            )}
          </div>

          {/* Mobile Menu Button */}
          <div className="md:hidden">
            <button
              onClick={() => setIsMenuOpen(!isMenuOpen)}
              className="text-light p-2 hover:text-white"
            >
              {isMenuOpen ? <X size={24} /> : <Menu size={24} />}
            </button>
          </div>
        </div>
      </div>

      {/* Mobile Menu */}
      {isMenuOpen && (
        <div className="md:hidden bg-dark-800 border-b border-light/10">
          <div className="px-2 pt-2 pb-3 space-y-1">
            <MobileNavLink to="/" label="Home" onClick={() => setIsMenuOpen(false)} />
            <MobileNavLink to="/wallpapers" label="Gallery" onClick={() => setIsMenuOpen(false)} />
            <MobileNavLink to="/shop" label="Shop" onClick={() => setIsMenuOpen(false)} />
            <div className="h-px bg-light/10 my-2"></div>
            {isAuthenticated ? (
              <>
                <MobileNavLink to="/admin" label="Admin Dashboard" onClick={() => setIsMenuOpen(false)} />
                <button
                  onClick={() => {
                    handleLogout()
                    setIsMenuOpen(false)
                  }}
                  className="w-full text-left px-3 py-3 rounded-lg text-base font-medium text-red-500 hover:bg-red-500/20"
                >
                  Logout
                </button>
              </>
            ) : (
              <MobileNavLink to="/admin/login" label="Login" onClick={() => setIsMenuOpen(false)} />
            )}
          </div>
        </div>
      )}
    </nav>
  )
}

const NavLink = ({ to, label }) => (
  <Link
    to={to}
    className="text-sm font-bold uppercase tracking-wide text-light/60 hover:text-white transition-colors"
  >
    {label}
  </Link>
)

const MobileNavLink = ({ to, label, onClick }) => (
  <Link
    to={to}
    onClick={onClick}
    className="block px-3 py-3 rounded-lg text-base font-medium text-light hover:bg-blue/20 hover:text-white"
  >
    {label}
  </Link>
)

export default Navigation
