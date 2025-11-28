# Gallery Component - Usage Guide

## Overview
The Gallery component is a fully-featured wallpaper gallery with sorting, liking, and download functionality.

## Features
✅ **Sorting Controls**: Sort by Newest, Most Downloads, or Most Likes
✅ **Interactive Likes**: Click heart to toggle like state and update counter
✅ **Download Tracking**: Click download to increment download counter
✅ **Responsive Grid**: Adapts from 1 to 4 columns based on screen size
✅ **Hover Effects**: Smooth animations and download overlay on hover
✅ **Dark Mode Design**: Matches MTB Love design system (#0a1016, #0f1720)
✅ **Lucide Icons**: Heart (red/orange when active) and Download icons
✅ **Mock Data**: 8 sample MTB wallpapers included

## Installation

The component is already created at:
```
/frontend/src/components/Gallery.jsx
```

## Usage

### Option 1: Standalone Page
Create a new page that uses the Gallery component:

```jsx
// src/pages/GalleryPage.jsx
import React from 'react'
import Gallery from '../components/Gallery'

const GalleryPage = () => {
  return <Gallery />
}

export default GalleryPage
```

Then add to your router in `App.jsx`:
```jsx
import GalleryPage from './pages/GalleryPage'

// In your routes:
<Route path="/gallery" element={<GalleryPage />} />
```

### Option 2: Replace Existing Wallpapers Page
Replace the content in `/src/pages/Wallpapers.jsx`:

```jsx
import React from 'react'
import Gallery from '../components/Gallery'

const Wallpapers = () => {
  return <Gallery />
}

export default Wallpapers
```

### Option 3: Use as Component
Import and use anywhere in your app:

```jsx
import Gallery from './components/Gallery'

function MyPage() {
  return (
    <div>
      <h1>My Custom Page</h1>
      <Gallery />
    </div>
  )
}
```

## Customization

### Modify Mock Data
Edit the `INITIAL_WALLPAPERS` array in Gallery.jsx:

```jsx
const INITIAL_WALLPAPERS = [
  {
    id: 1,
    title: 'Your Title',
    imageSrc: 'your-image-url.jpg',
    likes: 100,
    downloads: 500,
    date: '2025-01-15'
  },
  // Add more wallpapers...
]
```

### Connect to Backend API
Replace the mock data with API calls:

```jsx
const Gallery = () => {
  const [wallpapers, setWallpapers] = useState([])

  useEffect(() => {
    const fetchWallpapers = async () => {
      const response = await fetch('/api/wallpapers')
      const data = await response.json()
      setWallpapers(data)
    }
    fetchWallpapers()
  }, [])

  // Rest of component...
}
```

## Component Props

The Gallery component accepts no props (standalone).

### Child Components:
- `SortButton`: Sorting control buttons
- `WallpaperCard`: Individual wallpaper card with image, stats, and actions

## Styling

Colors used (from tailwind.config.js):
- Background: `bg-dark` (#0a1016)
- Cards: `bg-dark-700` (#0f1720)
- Primary Action: `bg-orange` (#ed7f20)
- Hover: `bg-orange-light` (#ffb056)
- Text: `text-light` (#b1dde9)
- Accent: `text-blue` (#0268a8)

## Functionality

### Sorting
- **Newest**: Sorts by date (most recent first)
- **Most Downloads**: Sorts by download count (highest first)
- **Most Likes**: Sorts by like count (highest first)

### Interactions
- **Like**: Click heart icon to toggle. Updates state and counter immediately.
- **Download**: Click download button (appears on hover). Increments counter and logs to console.

### State Management
- `wallpapers`: Array of wallpaper objects
- `sortBy`: Current sort criteria ('newest', 'downloads', 'likes')
- `likedWallpapers`: Set of liked wallpaper IDs

## Browser Support
- Chrome, Firefox, Safari, Edge (latest versions)
- Requires React 18.2+
- Uses modern CSS (Grid, Flexbox, Transitions)

## Dependencies
- React (^18.2.0)
- lucide-react (^0.294.0)
- Tailwind CSS (^3.4.18)
