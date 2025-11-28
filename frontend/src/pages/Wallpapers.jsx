import React, { useState, useEffect } from 'react'
import { Download, Image as ImageIcon } from 'lucide-react'
import axios from 'axios'

const Wallpapers = () => {
  const [wallpapers, setWallpapers] = useState([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState('all')

  const API_URL = '/backend/api/index.php'

  useEffect(() => {
    loadWallpapers()
  }, [filter])

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

  const handleDownload = async (wallpaper) => {
    try {
      const formData = new FormData()
      formData.append('action', 'increment_download')
      formData.append('id', wallpaper.id)

      await axios.post(API_URL, formData)
      alert(`Download gestartet: ${wallpaper.title}`)
    } catch (error) {
      console.error('Download error:', error)
    }
  }

  const filteredWallpapers = filter === 'all'
    ? wallpapers
    : wallpapers.filter(w => w.type === filter)

  return (
    <div className="max-w-7xl mx-auto px-4 py-16">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
        <div>
          <h1 className="text-4xl md:text-5xl font-black italic text-white mb-2">
            WALLPAPER & KUNST
          </h1>
          <p className="text-light/70">
            Kostenlose und Premium Wallpapers für dein Setup
          </p>
        </div>

        <div className="flex gap-2">
          <FilterButton
            active={filter === 'all'}
            onClick={() => setFilter('all')}
            label="Alle"
          />
          <FilterButton
            active={filter === 'free'}
            onClick={() => setFilter('free')}
            label="Kostenlos"
          />
          <FilterButton
            active={filter === 'premium'}
            onClick={() => setFilter('premium')}
            label="Premium"
          />
        </div>
      </div>

      {loading ? (
        <div className="text-center py-20">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange"></div>
          <p className="mt-4 text-light/60">Lade Wallpapers...</p>
        </div>
      ) : filteredWallpapers.length === 0 ? (
        <div className="text-center py-20">
          <p className="text-light/60">Keine Wallpapers gefunden</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {filteredWallpapers.map((wallpaper) => (
            <WallpaperCard
              key={wallpaper.id}
              wallpaper={wallpaper}
              onDownload={handleDownload}
            />
          ))}
        </div>
      )}
    </div>
  )
}

const FilterButton = ({ active, onClick, label }) => (
  <button
    onClick={onClick}
    className={`px-4 py-2 rounded-lg font-bold text-sm transition-all ${
      active
        ? 'bg-orange text-white'
        : 'bg-dark-700 text-light/60 hover:text-white border border-light/10'
    }`}
  >
    {label}
  </button>
)

const WallpaperCard = ({ wallpaper, onDownload }) => (
  <div className="group relative bg-dark-700 rounded-2xl overflow-hidden border border-light/10 hover:border-orange/50 transition-all hover:-translate-y-1">
    <div className="aspect-[3/4] bg-dark relative flex items-center justify-center">
      {wallpaper.file_path ? (
        <img
          src={wallpaper.file_path}
          alt={wallpaper.title}
          className="w-full h-full object-cover"
          onError={(e) => {
            e.target.style.display = 'none'
            e.target.parentElement.querySelector('.fallback-icon').style.display = 'flex'
          }}
        />
      ) : null}
      <div className="fallback-icon absolute inset-0 flex items-center justify-center">
        <ImageIcon size={48} className="text-blue/40" />
      </div>

      {wallpaper.type === 'premium' && (
        <div className="absolute top-3 right-3 bg-orange text-white text-xs font-bold px-3 py-1 rounded-full">
          Premium
        </div>
      )}

      <div className="absolute inset-0 bg-blue/80 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
        <button
          onClick={() => onDownload(wallpaper)}
          className="bg-white text-blue px-6 py-2 rounded-full font-bold flex items-center gap-2 hover:bg-light"
        >
          <Download size={18} /> Download
        </button>
      </div>
    </div>

    <div className="p-4">
      <h3 className="font-bold text-lg text-white mb-1">{wallpaper.title}</h3>
      <p className="text-light/50 text-sm">{wallpaper.style || 'Artwork'}</p>
      <div className="mt-2 text-xs text-light/40">
        {wallpaper.downloads || 0} Downloads
      </div>
    </div>
  </div>
)

export default Wallpapers
