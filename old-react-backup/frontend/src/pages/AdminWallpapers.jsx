import React, { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { ArrowLeft, Plus, Edit, Trash2, Image as ImageIcon } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'

const AdminWallpapers = () => {
  const [wallpapers, setWallpapers] = useState([])
  const [loading, setLoading] = useState(true)
  const [showAddModal, setShowAddModal] = useState(false)

  const { isAuthenticated } = useAuth()
  const navigate = useNavigate()

  const API_URL = '/backend/api/index.php'

  useEffect(() => {
    if (!isAuthenticated) {
      navigate('/admin/login')
      return
    }

    loadWallpapers()
  }, [isAuthenticated, navigate])

  const loadWallpapers = async () => {
    setLoading(true)
    try {
      const formData = new FormData()
      formData.append('action', 'get_wallpapers')

      const response = await axios.post(API_URL, formData)
      setWallpapers(response.data || [])
    } catch (error) {
      console.error('Error loading wallpapers:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async (id) => {
    if (!confirm('Wallpaper wirklich löschen?')) return

    try {
      const formData = new FormData()
      formData.append('action', 'delete_wallpaper')
      formData.append('id', id)

      await axios.post(API_URL, formData)
      loadWallpapers()
      alert('Wallpaper gelöscht')
    } catch (error) {
      console.error('Delete error:', error)
      alert('Fehler beim Löschen')
    }
  }

  if (!isAuthenticated) {
    return null
  }

  return (
    <div className="max-w-7xl mx-auto px-4 py-16">
      <div className="flex items-center justify-between mb-8">
        <div className="flex items-center gap-4">
          <button
            onClick={() => navigate('/admin')}
            className="p-2 hover:bg-dark-700 rounded-lg transition-colors"
          >
            <ArrowLeft className="text-light" size={24} />
          </button>
          <div>
            <h1 className="text-3xl font-bold text-white">Wallpaper Verwaltung</h1>
            <p className="text-light/70 mt-1">{wallpapers.length} Wallpapers</p>
          </div>
        </div>

        <button
          onClick={() => setShowAddModal(true)}
          className="flex items-center gap-2 bg-orange hover:bg-orange-light text-white px-4 py-2 rounded-lg font-bold transition-colors"
        >
          <Plus size={20} />
          Neues Wallpaper
        </button>
      </div>

      {loading ? (
        <div className="text-center py-20">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange"></div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {wallpapers.map((wallpaper) => (
            <WallpaperCard
              key={wallpaper.id}
              wallpaper={wallpaper}
              onDelete={handleDelete}
            />
          ))}
        </div>
      )}

      {showAddModal && (
        <AddWallpaperModal
          onClose={() => setShowAddModal(false)}
          onSuccess={() => {
            setShowAddModal(false)
            loadWallpapers()
          }}
        />
      )}
    </div>
  )
}

const WallpaperCard = ({ wallpaper, onDelete }) => (
  <div className="bg-dark-700 rounded-xl border border-light/10 overflow-hidden">
    <div className="aspect-video bg-dark flex items-center justify-center">
      <ImageIcon size={48} className="text-blue/40" />
    </div>
    <div className="p-4">
      <h3 className="font-bold text-white mb-1">{wallpaper.title}</h3>
      <p className="text-light/60 text-sm mb-2">{wallpaper.style}</p>
      <div className="flex items-center gap-2 mb-4">
        <span className={`text-xs px-2 py-1 rounded ${wallpaper.type === 'premium' ? 'bg-orange/20 text-orange' : 'bg-blue/20 text-blue'}`}>
          {wallpaper.type}
        </span>
        <span className="text-xs text-light/40">{wallpaper.downloads || 0} Downloads</span>
      </div>
      <div className="flex gap-2">
        <button className="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-blue/10 hover:bg-blue/20 border border-blue/30 text-blue rounded-lg text-sm font-medium transition-colors">
          <Edit size={16} /> Bearbeiten
        </button>
        <button
          onClick={() => onDelete(wallpaper.id)}
          className="flex items-center justify-center gap-2 px-3 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-500 rounded-lg text-sm font-medium transition-colors"
        >
          <Trash2 size={16} />
        </button>
      </div>
    </div>
  </div>
)

const AddWallpaperModal = ({ onClose, onSuccess }) => {
  const [title, setTitle] = useState('')
  const [description, setDescription] = useState('')
  const [style, setStyle] = useState('')
  const [type, setType] = useState('free')
  const [file, setFile] = useState(null)
  const [loading, setLoading] = useState(false)

  const API_URL = '/backend/api/index.php'

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)

    try {
      const formData = new FormData()
      formData.append('action', 'add_wallpaper')
      formData.append('title', title)
      formData.append('description', description)
      formData.append('style', style)
      formData.append('type', type)
      if (file) {
        formData.append('image', file)
      }

      await axios.post(API_URL, formData)
      alert('Wallpaper hinzugefügt')
      onSuccess()
    } catch (error) {
      console.error('Add wallpaper error:', error)
      alert('Fehler beim Hinzufügen')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-dark/80 backdrop-blur-sm p-4">
      <div className="bg-dark-700 border border-light/10 rounded-2xl w-full max-w-md p-6">
        <h2 className="text-2xl font-bold text-white mb-6">Neues Wallpaper</h2>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm text-light mb-1">Titel</label>
            <input
              type="text"
              value={title}
              onChange={(e) => setTitle(e.target.value)}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white focus:border-orange outline-none"
              required
            />
          </div>
          <div>
            <label className="block text-sm text-light mb-1">Beschreibung</label>
            <textarea
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white focus:border-orange outline-none"
              rows="3"
            />
          </div>
          <div>
            <label className="block text-sm text-light mb-1">Stil</label>
            <input
              type="text"
              value={style}
              onChange={(e) => setStyle(e.target.value)}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white focus:border-orange outline-none"
              placeholder="z.B. Dark Art, Cyberpunk"
            />
          </div>
          <div>
            <label className="block text-sm text-light mb-1">Typ</label>
            <select
              value={type}
              onChange={(e) => setType(e.target.value)}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white focus:border-orange outline-none"
            >
              <option value="free">Kostenlos</option>
              <option value="premium">Premium</option>
            </select>
          </div>
          <div>
            <label className="block text-sm text-light mb-1">Bild hochladen</label>
            <input
              type="file"
              accept="image/*"
              onChange={(e) => setFile(e.target.files[0])}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-orange file:text-white"
            />
          </div>
          <div className="flex gap-3">
            <button
              type="button"
              onClick={onClose}
              className="flex-1 px-4 py-3 bg-dark-800 text-light rounded-lg font-bold hover:bg-dark transition-colors"
            >
              Abbrechen
            </button>
            <button
              type="submit"
              disabled={loading}
              className="flex-1 px-4 py-3 bg-orange hover:bg-orange-light text-white rounded-lg font-bold transition-colors disabled:opacity-50"
            >
              {loading ? 'Lädt...' : 'Hinzufügen'}
            </button>
          </div>
        </form>
      </div>
    </div>
  )
}

export default AdminWallpapers
