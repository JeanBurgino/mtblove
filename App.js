import React, { useState } from 'react';
import { Facebook, Instagram, Music2 as TikTok, Menu, X } from 'lucide-react';

function App() {
  const [ideas, setIdeas] = useState([
    "Gravity always wins",
    "Eat, Sleep, Ride",
    "Mud is my makeup"
  ]);
  const [newIdea, setNewIdea] = useState('');
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const handleSubmitIdea = (e) => {
    e.preventDefault();
    if (newIdea.trim()) {
      setIdeas([newIdea, ...ideas]);
      setNewIdea('');
    }
  };

  return (
    <div className="min-h-screen bg-[#0a1016] text-white">
      {/* Header / Navigation - Sticky */}
      <header className="sticky top-0 z-50 bg-[#0a1016]/95 backdrop-blur-sm border-b border-gray-800">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16 sm:h-20">
            {/* Logo & Brand */}
            <div className="flex items-center space-x-3">
              <img
                src="logo.jpg"
                alt="MTB Love Logo"
                className="h-10 w-10 sm:h-12 sm:w-12 rounded-full object-cover"
              />
              <h1 className="text-xl sm:text-2xl font-bold bg-gradient-to-r from-[#0268a8] to-[#ed7f20] bg-clip-text text-transparent">
                MTB Love
              </h1>
            </div>

            {/* Desktop Navigation */}
            <nav className="hidden md:flex items-center space-x-8">
              <a href="#gallery" className="text-gray-300 hover:text-[#0268a8] transition-colors">
                Gallery
              </a>
              <a href="#shop" className="text-gray-300 hover:text-[#ed7f20] transition-colors">
                Shop
              </a>
              <a href="#login" className="text-gray-300 hover:text-white transition-colors">
                Login
              </a>

              {/* Social Icons */}
              <div className="flex items-center space-x-4 ml-4 pl-4 border-l border-gray-700">
                <a href="#facebook" className="text-gray-400 hover:text-[#0268a8] transition-colors">
                  <Facebook size={20} />
                </a>
                <a href="#instagram" className="text-gray-400 hover:text-[#ed7f20] transition-colors">
                  <Instagram size={20} />
                </a>
                <a href="#tiktok" className="text-gray-400 hover:text-white transition-colors">
                  <TikTok size={20} />
                </a>
              </div>
            </nav>

            {/* Mobile Menu Button */}
            <button
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              className="md:hidden text-gray-300 hover:text-white transition-colors"
            >
              {mobileMenuOpen ? <X size={24} /> : <Menu size={24} />}
            </button>
          </div>

          {/* Mobile Menu */}
          {mobileMenuOpen && (
            <div className="md:hidden py-4 border-t border-gray-800">
              <nav className="flex flex-col space-y-4">
                <a href="#gallery" className="text-gray-300 hover:text-[#0268a8] transition-colors">
                  Gallery
                </a>
                <a href="#shop" className="text-gray-300 hover:text-[#ed7f20] transition-colors">
                  Shop
                </a>
                <a href="#login" className="text-gray-300 hover:text-white transition-colors">
                  Login
                </a>

                {/* Social Icons Mobile */}
                <div className="flex items-center space-x-6 pt-4 border-t border-gray-800">
                  <a href="#facebook" className="text-gray-400 hover:text-[#0268a8] transition-colors">
                    <Facebook size={20} />
                  </a>
                  <a href="#instagram" className="text-gray-400 hover:text-[#ed7f20] transition-colors">
                    <Instagram size={20} />
                  </a>
                  <a href="#tiktok" className="text-gray-400 hover:text-white transition-colors">
                    <TikTok size={20} />
                  </a>
                </div>
              </nav>
            </div>
          )}
        </div>
      </header>

      {/* Hero Section */}
      <section className="relative py-20 sm:py-32 lg:py-40">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <div className="text-center max-w-4xl mx-auto">
            {/* Headline */}
            <h2 className="text-5xl sm:text-6xl lg:text-7xl font-black mb-4 sm:mb-6 leading-tight">
              <span className="bg-gradient-to-r from-[#0268a8] via-white to-[#ed7f20] bg-clip-text text-transparent">
                RIDE WITH PASSION
              </span>
            </h2>

            {/* Subtitle */}
            <p className="text-xl sm:text-2xl text-gray-400 mb-10 sm:mb-12">
              MTB Community Page
            </p>

            {/* CTA Buttons */}
            <div className="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center items-center">
              <a
                href="#gallery"
                className="w-full sm:w-auto px-8 sm:px-12 py-4 bg-[#0268a8] hover:bg-[#0268a8]/80 text-white font-bold text-lg rounded-lg transition-all transform hover:scale-105 shadow-lg hover:shadow-[#0268a8]/50"
              >
                Gallery
              </a>
              <a
                href="#shop"
                className="w-full sm:w-auto px-8 sm:px-12 py-4 bg-[#ed7f20] hover:bg-[#ed7f20]/80 text-white font-bold text-lg rounded-lg transition-all transform hover:scale-105 shadow-lg hover:shadow-[#ed7f20]/50"
              >
                Shop
              </a>
            </div>
          </div>
        </div>

        {/* Decorative Elements */}
        <div className="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
          <div className="absolute top-1/4 left-10 w-72 h-72 bg-[#0268a8]/10 rounded-full blur-3xl"></div>
          <div className="absolute bottom-1/4 right-10 w-96 h-96 bg-[#ed7f20]/10 rounded-full blur-3xl"></div>
        </div>
      </section>

      {/* T-Shirt Idea Board Section */}
      <section className="py-16 sm:py-20 bg-gradient-to-b from-[#0a1016] to-[#0f1620]">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
          {/* Section Header */}
          <div className="text-center mb-10 sm:mb-12">
            <h3 className="text-3xl sm:text-4xl font-bold mb-3 bg-gradient-to-r from-[#0268a8] to-[#ed7f20] bg-clip-text text-transparent">
              T-Shirt Idea Board
            </h3>
            <p className="text-gray-400 text-lg">
              Share your ideas for funny or cool MTB T-shirt slogans
            </p>
          </div>

          {/* Input Form */}
          <form onSubmit={handleSubmitIdea} className="mb-12">
            <div className="flex flex-col sm:flex-row gap-3">
              <input
                type="text"
                value={newIdea}
                onChange={(e) => setNewIdea(e.target.value)}
                placeholder="Your awesome slogan idea..."
                className="flex-1 px-6 py-4 bg-[#1a2332] border-2 border-gray-700 rounded-lg focus:border-[#0268a8] focus:outline-none text-white placeholder-gray-500 transition-colors"
              />
              <button
                type="submit"
                className="px-8 py-4 bg-gradient-to-r from-[#0268a8] to-[#ed7f20] hover:from-[#0268a8]/80 hover:to-[#ed7f20]/80 text-white font-bold rounded-lg transition-all transform hover:scale-105 shadow-lg"
              >
                Submit Idea
              </button>
            </div>
          </form>

          {/* Recent Ideas List */}
          <div>
            <h4 className="text-xl sm:text-2xl font-semibold mb-6 text-gray-300">
              Recent Ideas
            </h4>
            <div className="space-y-3">
              {ideas.map((idea, index) => (
                <div
                  key={index}
                  className="bg-[#1a2332] border border-gray-700 rounded-lg px-6 py-4 hover:border-[#0268a8] transition-colors group"
                >
                  <p className="text-lg text-gray-300 group-hover:text-white transition-colors">
                    "{idea}"
                  </p>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-8 border-t border-gray-800">
        <div className="container mx-auto px-4 sm:px-6 lg:px-8">
          <p className="text-center text-gray-500">
            © 2024 MTB Love - Ride with Passion
          </p>
        </div>
      </footer>
    </div>
  );
}

export default App;
