import React, { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { Heart, RefreshCw, Zap } from 'lucide-react'
import axios from 'axios'

const Home = () => {
  const [excuse, setExcuse] = useState("Klick den Button für eine Ausrede!")
  const [loading, setLoading] = useState(false)
  const navigate = useNavigate()

  const API_URL = '/backend/api/index.php'

  const generateExcuse = async () => {
    setLoading(true)
    try {
      const formData = new FormData()
      formData.append('action', 'get_random_excuse')

      const response = await axios.post(API_URL, formData)
      if (response.data.text) {
        setExcuse(response.data.text)
      }
    } catch (error) {
      console.error('Error fetching excuse:', error)
      setExcuse('Fehler beim Laden der Ausrede!')
    } finally {
      setLoading(false)
    }
  }

  return (
    <>
      {/* Hero Section */}
      <div className="relative overflow-hidden py-24 lg:py-32 flex flex-col items-center text-center px-4">
        <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-blue/20 blur-[100px] rounded-full -z-10 pointer-events-none mix-blend-screen"></div>
        <div className="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-orange/10 blur-[80px] rounded-full -z-10 pointer-events-none mix-blend-screen"></div>

        <div className="inline-flex items-center space-x-2 bg-blue/10 border border-blue/30 rounded-full px-4 py-1.5 mb-8">
          <Heart size={14} className="text-orange fill-orange" />
          <span className="text-light text-xs font-bold tracking-wide uppercase">Community & Lifestyle</span>
        </div>

        <h1 className="text-5xl md:text-8xl font-black italic tracking-tighter mb-6 text-white">
          RIDE WITH <br />
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-orange via-orange-light to-orange">
            PASSION
          </span>
        </h1>

        <p className="text-xl text-light/80 max-w-2xl mb-10 font-light">
          Die Homebase für alle, die das Biken lieben. Memes, Kunst und Good Vibes only.
        </p>

        <div className="flex gap-4 flex-wrap justify-center">
          <button
            onClick={() => navigate('/wallpapers')}
            className="px-8 py-4 bg-orange hover:bg-orange-light text-white rounded-xl font-bold transition-all hover:scale-105 shadow-[0_0_20px_-5px] shadow-orange"
          >
            Zur Galerie
          </button>
          <button
            onClick={() => navigate('/shop')}
            className="px-8 py-4 bg-blue/10 hover:bg-blue/20 border border-blue/50 text-light rounded-xl font-bold transition-all"
          >
            Zum Shop
          </button>
        </div>
      </div>

      {/* MTB Ausreden-Generator */}
      <div className="max-w-4xl mx-auto px-4 py-12">
        <div className="bg-dark-700 rounded-3xl border border-blue/30 p-8 md:p-12 text-center shadow-2xl relative overflow-hidden group">
          <div className="absolute -top-24 -right-24 w-64 h-64 bg-orange/10 rounded-full blur-3xl group-hover:bg-orange/20 transition-all duration-700"></div>

          <h2 className="text-3xl md:text-4xl font-bold mb-8 text-white">
            Der MTB Ausreden-Generator
          </h2>

          <div className="bg-dark rounded-xl p-8 mb-8 min-h-[120px] flex items-center justify-center border border-light/10 relative">
            <div className="absolute top-2 left-2 text-blue/20">
              <Zap size={24} />
            </div>
            <p className="text-2xl md:text-3xl font-serif italic text-light">
              "{excuse}"
            </p>
          </div>

          <button
            onClick={generateExcuse}
            disabled={loading}
            className="group relative inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue to-blue-dark rounded-xl font-bold text-lg text-white hover:brightness-110 transition-all active:scale-95 shadow-lg shadow-blue/30 disabled:opacity-50"
          >
            <RefreshCw
              className={`${loading ? 'animate-spin' : 'group-hover:rotate-180'} transition-transform duration-500`}
              size={20}
            />
            {loading ? 'Lädt...' : 'Neue Ausrede generieren'}
          </button>
        </div>
      </div>

      {/* Features */}
      <div className="max-w-7xl mx-auto px-4 py-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <FeatureCard
            title="Wallpapers"
            description="Kostenlose und Premium Wallpapers für dein Setup"
            icon="🎨"
          />
          <FeatureCard
            title="Shop"
            description="Merch, Sticker und mehr für MTB-Enthusiasten"
            icon="🛒"
          />
          <FeatureCard
            title="Community"
            description="Teile deine Leidenschaft mit Gleichgesinnten"
            icon="❤️"
          />
        </div>
      </div>
    </>
  )
}

const FeatureCard = ({ title, description, icon }) => (
  <div className="bg-dark-700 border border-light/10 rounded-2xl p-6 hover:border-orange/50 transition-all group">
    <div className="text-5xl mb-4 group-hover:scale-110 transition-transform">{icon}</div>
    <h3 className="text-xl font-bold mb-2 text-white">{title}</h3>
    <p className="text-light/70">{description}</p>
  </div>
)

export default Home
