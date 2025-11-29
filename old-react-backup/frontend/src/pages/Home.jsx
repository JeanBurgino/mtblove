import React, { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Heart, Sparkles } from 'lucide-react'

const Home = () => {
  const [ideas, setIdeas] = useState([
    "Gravity always wins",
    "Eat, Sleep, Ride",
    "Mud is my makeup"
  ])
  const [newIdea, setNewIdea] = useState('')
  const navigate = useNavigate()

  const handleSubmitIdea = (e) => {
    e.preventDefault()
    if (newIdea.trim()) {
      setIdeas([newIdea, ...ideas])
      setNewIdea('')
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
          <span className="text-light text-xs font-bold tracking-wide uppercase">MTB Community Page</span>
        </div>

        <h1 className="text-5xl md:text-8xl font-black italic tracking-tighter mb-6 text-white">
          RIDE WITH <br />
          <span className="text-transparent bg-clip-text bg-gradient-to-r from-orange via-orange-light to-orange">
            PASSION
          </span>
        </h1>

        <p className="text-xl text-light/80 max-w-2xl mb-10 font-light">
          The home base for everyone who loves biking. Memes, art, and good vibes only.
        </p>

        <div className="flex gap-4 flex-wrap justify-center">
          <a
            href="/wallpapers-standalone.html"
            className="px-8 py-4 bg-orange hover:bg-orange-light text-white rounded-xl font-bold transition-all hover:scale-105 shadow-[0_0_20px_-5px] shadow-orange inline-block"
          >
            Gallery
          </a>
          <button
            onClick={() => navigate('/shop')}
            className="px-8 py-4 bg-blue/10 hover:bg-blue/20 border border-blue/50 text-light rounded-xl font-bold transition-all"
          >
            Shop
          </button>
        </div>
      </div>

      {/* T-Shirt Idea Board */}
      <div className="max-w-4xl mx-auto px-4 py-12">
        <div className="bg-dark-700 rounded-3xl border border-blue/30 p-8 md:p-12 text-center shadow-2xl relative overflow-hidden group">
          <div className="absolute -top-24 -right-24 w-64 h-64 bg-orange/10 rounded-full blur-3xl group-hover:bg-orange/20 transition-all duration-700"></div>

          <h2 className="text-3xl md:text-4xl font-bold mb-4 text-white">
            T-Shirt Idea Board
          </h2>

          <p className="text-light/70 mb-8">
            Share your ideas for funny or cool MTB T-shirt slogans
          </p>

          {/* Input Form */}
          <form onSubmit={handleSubmitIdea} className="mb-12">
            <div className="flex flex-col sm:flex-row gap-3">
              <input
                type="text"
                value={newIdea}
                onChange={(e) => setNewIdea(e.target.value)}
                placeholder="Your awesome slogan idea..."
                className="flex-1 px-6 py-4 bg-dark border border-light/20 rounded-xl text-white placeholder-light/40 transition-colors"
                style={{ outline: 'none' }}
                onFocus={(e) => e.target.style.borderColor = 'var(--color-orange)'}
                onBlur={(e) => e.target.style.borderColor = 'rgba(177, 221, 233, 0.2)'}
              />
              <button
                type="submit"
                className="px-8 py-4 bg-gradient-to-r from-blue to-blue-dark hover:brightness-110 text-white rounded-xl font-bold transition-all shadow-lg shadow-blue/30 flex items-center justify-center gap-2"
              >
                <Sparkles size={20} />
                Submit Idea
              </button>
            </div>
          </form>

          {/* Recent Ideas List */}
          <div className="text-left">
            <h3 className="text-2xl font-semibold mb-6 text-white">
              Recent Ideas
            </h3>
            <div className="space-y-3">
              {ideas.map((idea, index) => (
                <div
                  key={index}
                  className="bg-dark border border-light/10 rounded-xl px-6 py-4 hover:border-orange/50 transition-all group"
                >
                  <p className="text-lg text-light/90 group-hover:text-white transition-colors">
                    "{idea}"
                  </p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Features */}
      <div className="max-w-7xl mx-auto px-4 py-16">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <FeatureCard
            title="Gallery"
            description="Free and premium wallpapers for your setup"
            icon="🎨"
          />
          <FeatureCard
            title="Shop"
            description="Merch, stickers, and more for MTB enthusiasts"
            icon="🛒"
          />
          <FeatureCard
            title="Community"
            description="Share your passion with like-minded riders"
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
