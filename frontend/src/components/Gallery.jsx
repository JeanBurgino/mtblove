import React, { useState } from 'react'
import { Heart, Download, ArrowDown } from 'lucide-react'

// Mock data for wallpapers
const INITIAL_WALLPAPERS = [
  {
    id: 1,
    title: 'Mountain Trail Sunset',
    imageSrc: 'https://images.unsplash.com/photo-1544191696-102dbdaeeaa0?w=800&q=80',
    likes: 342,
    downloads: 1287,
    date: '2025-01-15'
  },
  {
    id: 2,
    title: 'Downhill Action',
    imageSrc: 'https://images.unsplash.com/photo-1541625602330-2277a4c46182?w=800&q=80',
    likes: 589,
    downloads: 2156,
    date: '2025-01-12'
  },
  {
    id: 3,
    title: 'Forest Jump',
    imageSrc: 'https://images.unsplash.com/photo-1559080464-18e6c9c93c69?w=800&q=80',
    likes: 423,
    downloads: 1543,
    date: '2025-01-10'
  },
  {
    id: 4,
    title: 'Epic Mountain View',
    imageSrc: 'https://images.unsplash.com/photo-1576858574144-9ae1ebcf5ae5?w=800&q=80',
    likes: 756,
    downloads: 2890,
    date: '2025-01-08'
  },
  {
    id: 5,
    title: 'Trail Riding',
    imageSrc: 'https://images.unsplash.com/photo-1511994714008-b6d68a8b32a2?w=800&q=80',
    likes: 234,
    downloads: 987,
    date: '2025-01-05'
  },
  {
    id: 6,
    title: 'Bike Park Session',
    imageSrc: 'https://images.unsplash.com/photo-1532298229144-0ec0c57515c7?w=800&q=80',
    likes: 512,
    downloads: 1876,
    date: '2025-01-03'
  },
  {
    id: 7,
    title: 'Muddy Adventures',
    imageSrc: 'https://images.unsplash.com/photo-1505870281309-76e94e938e9f?w=800&q=80',
    likes: 678,
    downloads: 2345,
    date: '2024-12-28'
  },
  {
    id: 8,
    title: 'Sunrise Ride',
    imageSrc: 'https://images.unsplash.com/photo-1571068316344-75bc76f77890?w=800&q=80',
    likes: 891,
    downloads: 3124,
    date: '2024-12-25'
  }
]

const Gallery = () => {
  const [wallpapers, setWallpapers] = useState(INITIAL_WALLPAPERS)
  const [sortBy, setSortBy] = useState('newest')
  const [likedWallpapers, setLikedWallpapers] = useState(new Set())

  // Sort wallpapers based on selected criteria
  const getSortedWallpapers = () => {
    const sorted = [...wallpapers]

    switch (sortBy) {
      case 'newest':
        return sorted.sort((a, b) => new Date(b.date) - new Date(a.date))
      case 'downloads':
        return sorted.sort((a, b) => b.downloads - a.downloads)
      case 'likes':
        return sorted.sort((a, b) => b.likes - a.likes)
      default:
        return sorted
    }
  }

  // Handle like toggle
  const handleLike = (wallpaperId) => {
    const newLikedWallpapers = new Set(likedWallpapers)
    const isLiked = likedWallpapers.has(wallpaperId)

    if (isLiked) {
      newLikedWallpapers.delete(wallpaperId)
    } else {
      newLikedWallpapers.add(wallpaperId)
    }

    setLikedWallpapers(newLikedWallpapers)

    // Update likes count
    setWallpapers(wallpapers.map(w =>
      w.id === wallpaperId
        ? { ...w, likes: w.likes + (isLiked ? -1 : 1) }
        : w
    ))
  }

  // Handle download
  const handleDownload = (wallpaperId) => {
    setWallpapers(wallpapers.map(w =>
      w.id === wallpaperId
        ? { ...w, downloads: w.downloads + 1 }
        : w
    ))

    // Simulate download action
    const wallpaper = wallpapers.find(w => w.id === wallpaperId)
    console.log(`Downloading: ${wallpaper.title}`)
  }

  const sortedWallpapers = getSortedWallpapers()

  return (
    <div className="max-w-7xl mx-auto px-4 py-16">
      {/* Header Section */}
      <div className="mb-12">
        <h1 className="text-4xl md:text-5xl font-black italic text-white mb-2">
          WALLPAPER GALLERY
        </h1>
        <p className="text-light/70">
          High-quality MTB wallpapers for your desktop and mobile
        </p>
      </div>

      {/* Sorting Controls */}
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

      {/* Wallpaper Grid */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        {sortedWallpapers.map((wallpaper) => (
          <WallpaperCard
            key={wallpaper.id}
            wallpaper={wallpaper}
            isLiked={likedWallpapers.has(wallpaper.id)}
            onLike={handleLike}
            onDownload={handleDownload}
          />
        ))}
      </div>
    </div>
  )
}

// Sort Button Component
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

// Wallpaper Card Component
const WallpaperCard = ({ wallpaper, isLiked, onLike, onDownload }) => {
  const [isHovered, setIsHovered] = useState(false)

  return (
    <div
      className="group relative bg-dark-700 rounded-2xl overflow-hidden border border-light/10 hover:border-orange/50 transition-all hover:-translate-y-1"
      onMouseEnter={() => setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {/* Image Container */}
      <div className="aspect-[3/4] bg-dark relative overflow-hidden">
        <img
          src={wallpaper.imageSrc}
          alt={wallpaper.title}
          className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
        />

        {/* Download Overlay */}
        <div
          className={`absolute inset-0 bg-gradient-to-t from-dark via-dark/80 to-transparent transition-opacity duration-300 ${
            isHovered ? 'opacity-100' : 'opacity-0'
          }`}
        >
          <div className="absolute inset-0 flex items-center justify-center">
            <button
              onClick={() => onDownload(wallpaper.id)}
              className="bg-orange hover:bg-orange-light text-white px-6 py-3 rounded-full font-bold flex items-center gap-2 transition-all hover:scale-105 shadow-lg shadow-orange/30"
            >
              <ArrowDown size={20} strokeWidth={3} />
              Download
            </button>
          </div>
        </div>
      </div>

      {/* Stats Footer */}
      <div className="p-4 bg-dark-700">
        <h3 className="font-bold text-lg text-white mb-3 truncate">
          {wallpaper.title}
        </h3>

        <div className="flex items-center justify-between">
          {/* Like Section */}
          <button
            onClick={() => onLike(wallpaper.id)}
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
              {wallpaper.likes}
            </span>
          </button>

          {/* Download Section */}
          <div className="flex items-center gap-2">
            <Download
              size={20}
              className="text-blue"
            />
            <span className="text-sm font-bold text-light/70">
              {wallpaper.downloads}
            </span>
          </div>
        </div>
      </div>
    </div>
  )
}

export default Gallery
