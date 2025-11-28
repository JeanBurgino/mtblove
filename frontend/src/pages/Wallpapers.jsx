import React, { useState, useEffect } from 'react'
import { Download, Image as ImageIcon, Heart } from 'lucide-react'
import axios from 'axios'

const Wallpapers = () => {
  const [wallpapers, setWallpapers] = useState([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState('all')
  const [sortBy, setSortBy] = useState('newest')
  const [likedWallpapers, setLikedWallpapers] = useState(new Set())

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

      // Update local state
      setWallpapers(wallpapers.map(w =>
        w.id === wallpaper.id
          ? { ...w, downloads: (parseInt(w.downloads) || 0) + 1 }
          : w
      ))
    } catch (error) {
      console.error('Download error:', error)
    }
  }

  const handleLike = async (wallpaper) => {
    const isLiked = likedWallpapers.has(wallpaper.id)
    const newLikedWallpapers = new Set(likedWallpapers)

    try {
      const formData = new FormData()
      formData.append('action', 'toggle_like')
      formData.append('id', wallpaper.id)
      formData.append('liked', !isLiked ? 'true' : 'false')

      const response = await axios.post(API_URL, formData)

      // Update liked state
      if (isLiked) {
        newLikedWallpapers.delete(wallpaper.id)
      } else {
        newLikedWallpapers.add(wallpaper.id)
      }
      setLikedWallpapers(newLikedWallpapers)

      // Update wallpapers with new like count
      setWallpapers(wallpapers.map(w =>
        w.id === wallpaper.id
          ? { ...w, likes: response.data.likes }
          : w
      ))
    } catch (error) {
      console.error('Like error:', error)
    }
  }

  const getSortedWallpapers = (wallpapersToSort) => {
    const sorted = [...wallpapersToSort]

    switch (sortBy) {
      case 'newest':
        return sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      case 'downloads':
        return sorted.sort((a, b) => (parseInt(b.downloads) || 0) - (parseInt(a.downloads) || 0))
      case 'likes':
        return sorted.sort((a, b) => (parseInt(b.likes) || 0) - (parseInt(a.likes) || 0))
      default:
        return sorted
    }
  }

  const filteredWallpapers = filter === 'all'
    ? wallpapers
    : wallpapers.filter(w => w.type === filter)

  const sortedWallpapers = getSortedWallpapers(filteredWallpapers)

  return (
    <div className="max-w-7xl mx-auto px-4 py-16">
      <div className="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
          <h1 className="text-4xl md:text-5xl font-black italic text-white mb-2">
            WALLPAPER GALLERY
          </h1>
          <p className="text-light/70">
            Free and premium wallpapers for your setup
          </p>
        </div>

        <div className="flex gap-2">
          <FilterButton
            active={filter === 'all'}
            onClick={() => setFilter('all')}
            label="All"
          />
          <FilterButton
            active={filter === 'free'}
            onClick={() => setFilter('free')}
            label="Free"
          />
          <FilterButton
            active={filter === 'premium'}
            onClick={() => setFilter('premium')}
            label="Premium"
          />
        </div>
      </div>

      {/* Sort Options */}
      <div className="flex flex-wrap gap-3 mb-8">
        <SortButton
          active={sortBy === 'newest'}
          onClick={() => setSortBy('newest')}
          label="Newest"
        />
        <SortButton
          active={sortBy === 'downloads'}
          onClick={() => setSortBy('downloads')}
          label="Most Downloads"
        />
        <SortButton
          active={sortBy === 'likes'}
          onClick={() => setSortBy('likes')}
          label="Most Likes"
        />
      </div>

      {loading ? (
        <div className="text-center py-20">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange"></div>
          <p className="mt-4 text-light/60">Loading wallpapers...</p>
        </div>
      ) : sortedWallpapers.length === 0 ? (
        <div className="text-center py-20">
          <p className="text-light/60">No wallpapers found</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {sortedWallpapers.map((wallpaper) => (
            <WallpaperCard
              key={wallpaper.id}
              wallpaper={wallpaper}
              isLiked={likedWallpapers.has(wallpaper.id)}
              onDownload={handleDownload}
              onLike={handleLike}
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

const SortButton = ({ active, onClick, label }) => (
  <button
    onClick={onClick}
    className={`px-5 py-2.5 rounded-lg font-bold text-sm transition-all ${
      active
        ? 'bg-orange text-white shadow-lg shadow-orange/30'
        : 'bg-dark-700 text-light/60 hover:text-white border border-light/10 hover:border-orange/30'
    }`}
  >
    {label}
  </button>
)

const WallpaperCard = ({ wallpaper, isLiked, onDownload, onLike }) => (
  <div className="group relative bg-dark-700 rounded-2xl overflow-hidden border border-light/10 hover:border-orange/50 transition-all hover:-translate-y-1">
    <div className="aspect-[3/4] bg-dark relative flex items-center justify-center">
      {wallpaper.file_path ? (
        <img
          src={wallpaper.file_path}
          alt={wallpaper.title}
          className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
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

      <div className="absolute inset-0 bg-gradient-to-t from-dark via-dark/80 to-transparent opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
        <button
          onClick={() => onDownload(wallpaper)}
          className="bg-orange hover:bg-orange-light text-white px-6 py-3 rounded-full font-bold flex items-center gap-2 transition-all hover:scale-105 shadow-lg shadow-orange/30"
        >
          <Download size={20} strokeWidth={3} /> Download
        </button>
      </div>
    </div>

    <div className="p-4 bg-dark-700">
      <h3 className="font-bold text-lg text-white mb-3 truncate">{wallpaper.title}</h3>
      <p className="text-light/50 text-sm mb-3">{wallpaper.style || 'Artwork'}</p>

      <div className="flex items-center justify-between">
        {/* Like Section */}
        <button
          onClick={() => onLike(wallpaper)}
          className="flex items-center gap-2 group/like transition-all"
        >
          <Heart
            size={20}
            className={`transition-all ${
              isLiked
                ? 'fill-orange text-orange'
                : 'text-light/50 group-hover/like:text-orange'
            }`}
          />
          <span className={`text-sm font-bold ${
            isLiked ? 'text-orange' : 'text-light/70'
          }`}>
            {wallpaper.likes || 0}
          </span>
        </button>

        {/* Download Section */}
        <div className="flex items-center gap-2">
          <Download
            size={20}
            className="text-blue"
          />
          <span className="text-sm font-bold text-light/70">
            {wallpaper.downloads || 0}
          </span>
        </div>
      </div>
    </div>
  </div>
)

export default Wallpapers
