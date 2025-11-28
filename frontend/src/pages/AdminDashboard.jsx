import React, { useState, useEffect } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Lock, User, Download, ShoppingBag, Image, Package, BarChart3 } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'

const AdminDashboard = () => {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)
  const { isAuthenticated, user } = useAuth()
  const navigate = useNavigate()

  const API_URL = '/backend/api/index.php'

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/admin/login')
      return
    }

    loadStats()
  }, [isAuthenticated, navigate])

  const loadStats = async () => {
    setLoading(true)
    try {
      const formData = new FormData()
      formData.append('action', 'get_stats')

      const response = await axios.post(API_URL, formData)
      setStats(response.data)
    } catch (error) {
      console.error('Error loading stats:', error)
    } finally {
      setLoading(false)
    }
  }

  if (!isAuthenticated) {
    return null
  }

  return (
    <div className="max-w-7xl mx-auto px-4 py-16">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold text-white flex items-center gap-3">
            <Lock className="text-orange" />
            Admin Dashboard
          </h1>
          <p className="text-light/70 mt-1">
            Willkommen zurück, {user?.username}!
          </p>
        </div>
      </div>

      {/* Stats Cards */}
      {loading ? (
        <div className="text-center py-20">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange"></div>
        </div>
      ) : (
        <>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <StatCard
              icon={<User size={24} />}
              label="Total Follower"
              value={stats?.followers || '0'}
              color="blue"
            />
            <StatCard
              icon={<Download size={24} />}
              label="Downloads"
              value={stats?.downloads || '0'}
              color="orange"
            />
            <StatCard
              icon={<ShoppingBag size={24} />}
              label="Umsatz"
              value={stats?.revenue || '0 €'}
              color="green"
            />
            <StatCard
              icon={<BarChart3 size={24} />}
              label="Besucher"
              value={stats?.visitors || '0'}
              color="purple"
            />
          </div>

          {/* Quick Actions */}
          <div className="mb-12">
            <h2 className="text-2xl font-bold text-white mb-6">Schnellzugriff</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <ActionCard
                to="/admin/wallpapers"
                icon={<Image size={32} />}
                title="Wallpaper verwalten"
                description="Wallpapers hochladen, bearbeiten und löschen"
                count={stats?.total_wallpapers}
              />
              <ActionCard
                to="/admin/products"
                icon={<Package size={32} />}
                title="Produkte verwalten"
                description="Shop-Artikel pflegen und aktualisieren"
                count={stats?.total_products}
              />
            </div>
          </div>
        </>
      )}
    </div>
  )
}

const StatCard = ({ icon, label, value, color }) => {
  const colorClasses = {
    blue: 'text-blue',
    orange: 'text-orange',
    green: 'text-green-500',
    purple: 'text-purple-500'
  }

  return (
    <div className="bg-dark-700 border border-light/10 p-6 rounded-xl hover:border-orange/30 transition-all">
      <div className={`flex items-center gap-3 mb-2 ${colorClasses[color] || 'text-light'}/60`}>
        {icon}
        <span className="text-sm font-bold uppercase text-light/60">{label}</span>
      </div>
      <div className="text-3xl font-black text-white">{value}</div>
    </div>
  )
}

const ActionCard = ({ to, icon, title, description, count }) => (
  <Link
    to={to}
    className="bg-dark-700 border border-light/10 p-6 rounded-xl hover:border-orange/50 transition-all group"
  >
    <div className="flex items-start gap-4">
      <div className="p-3 bg-orange/10 rounded-lg text-orange group-hover:bg-orange group-hover:text-white transition-all">
        {icon}
      </div>
      <div className="flex-1">
        <h3 className="text-xl font-bold text-white mb-1 group-hover:text-orange transition-colors">
          {title}
        </h3>
        <p className="text-light/60 text-sm mb-2">{description}</p>
        {count !== undefined && (
          <div className="text-xs text-light/40">
            {count} Einträge
          </div>
        )}
      </div>
    </div>
  </Link>
)

export default AdminDashboard
