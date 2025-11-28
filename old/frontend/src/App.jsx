import React, { useState } from 'react';
import {
  Menu, X, Instagram, Facebook,
  Download, ShoppingBag,
  RefreshCw, Zap, Image as ImageIcon,
  Heart, Share2, Bike, User, Lock, BarChart3, Settings
} from 'lucide-react';

const App = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const [activeTab, setActiveTab] = useState('home');
  const [excuse, setExcuse] = useState("Klick den Button für eine Ausrede!");

  // Datenbank für Ausreden
  const excuses = [
    "Der Reifendruck war 0.05 bar zu niedrig.",
    "Ein Eichhörnchen hat meine Line gekreuzt.",
    "Die Sonne stand im falschen Winkel zur Gabel.",
    "Mein Dämpfer war im Lock-Out Modus.",
    "Das war kein Sturz, das war ein taktischer Abstieg.",
    "Die Gravitation war heute besonders stark.",
    "Ich wollte nur die Bodenbeschaffenheit testen.",
    "Mein Lenker ist einfach zu schmal für diesen Trail.",
    "Das Bike ist neu, ich muss es erst einfahren."
  ];

  const generateExcuse = () => {
    const random = excuses[Math.floor(Math.random() * excuses.length)];
    setExcuse(random);
  };

  // Mock Data Artworks
  const artworks = [
    { id: 1, title: 'Skull Trail', style: 'Dark Art', type: 'free' },
    { id: 2, title: 'Neon Whip', style: 'Cyberpunk', type: 'premium' },
    { id: 3, title: 'Love the Ride', style: 'Watercolor', type: 'free' },
    { id: 4, title: 'Flow State', style: 'Abstract', type: 'premium' },
  ];

  // Mock Data Shop
  const products = [
    { id: 1, name: 'T-Shirt "MTB Love"', price: '29,99 €', tag: 'Bestseller' },
    { id: 2, name: 'Sticker Pack "Heartbeat"', price: '9,99 €', tag: 'New' },
    { id: 3, name: 'Poster "Trail Dreams"', price: '19,99 €', tag: null },
  ];

  const renderContent = () => {
    switch(activeTab) {
      case 'art':
        return <ArtSection artworks={artworks} />;
      case 'shop':
        return <ShopSection products={products} />;
      case 'admin':
        return <AdminSection />;
      default:
        return <HomeSection excuse={excuse} generateExcuse={generateExcuse} setActiveTab={setActiveTab} />;
    }
  };

  return (
    // Background using a very dark blue/slate to fit the new blue theme better than pure black
    <div className="min-h-screen bg-[#0a1016] text-white font-sans selection:bg-[#ed7f20] selection:text-white">
      {/* Navigation */}
      <nav className="fixed w-full z-50 bg-[#0a1016]/90 backdrop-blur-md border-b border-[#b1dde9]/10">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center cursor-pointer group" onClick={() => setActiveTab('home')}>
              {/* Logo */}
              <img
                src="/logo.jpg"
                alt="MTB Love Logo"
                className="h-12 w-auto mr-3 group-hover:scale-110 transition-transform"
              />
              <span className="text-2xl font-black italic tracking-tighter text-[#0268a8]">
                MTB <span className="text-[#ed7f20]" style={{ fontFamily: 'cursive' }}>Love</span>
              </span>
            </div>

            {/* Desktop Menu */}
            <div className="hidden md:flex items-center space-x-8">
              <NavButton label="Startseite & Tools" active={activeTab === 'home'} onClick={() => setActiveTab('home')} />
              <NavButton label="Wallpapers & Kunst" active={activeTab === 'art'} onClick={() => setActiveTab('art')} />
              <NavButton label="Merch Shop" active={activeTab === 'shop'} onClick={() => setActiveTab('shop')} />

              <div className="h-6 w-px bg-[#b1dde9]/20 mx-2"></div>

              <div className="flex items-center space-x-4">
                <div className="flex space-x-3 text-[#b1dde9]">
                  <Instagram size={20} className="hover:text-[#ed7f20] cursor-pointer transition-colors" />
                  <Facebook size={20} className="hover:text-[#0268a8] cursor-pointer transition-colors" />
                </div>

                <button
                  onClick={() => setActiveTab('admin')}
                  className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#0268a8]/10 hover:bg-[#0268a8]/20 border border-[#0268a8]/30 text-xs font-bold text-[#0268a8] transition-colors"
                >
                  <User size={14} />
                  <span>LOGIN</span>
                </button>
              </div>
            </div>

            {/* Mobile Menu Button */}
            <div className="md:hidden">
              <button onClick={() => setIsMenuOpen(!isMenuOpen)} className="text-[#b1dde9] p-2 hover:text-white">
                {isMenuOpen ? <X size={24} /> : <Menu size={24} />}
              </button>
            </div>
          </div>
        </div>

        {/* Mobile Menu */}
        {isMenuOpen && (
          <div className="md:hidden bg-[#0a1016] border-b border-[#b1dde9]/10">
            <div className="px-2 pt-2 pb-3 space-y-1">
              <MobileNavButton label="Startseite" onClick={() => { setActiveTab('home'); setIsMenuOpen(false); }} />
              <MobileNavButton label="Kunst & Wallpaper" onClick={() => { setActiveTab('art'); setIsMenuOpen(false); }} />
              <MobileNavButton label="Shop" onClick={() => { setActiveTab('shop'); setIsMenuOpen(false); }} />
              <div className="h-px bg-[#b1dde9]/10 my-2"></div>
              <MobileNavButton label="Admin Login" onClick={() => { setActiveTab('admin'); setIsMenuOpen(false); }} icon={<Lock size={16} />} />
            </div>
          </div>
        )}
      </nav>

      <main className="pt-16">
        {renderContent()}
      </main>

      {/* Footer */}
      <footer className="bg-[#020b12] border-t border-[#b1dde9]/10 py-12 mt-20">
        <div className="max-w-7xl mx-auto px-4 text-center text-[#b1dde9]/60 text-sm">
          <p>© 2025 MTB Love. Ride with Heart.</p>
        </div>
      </footer>
    </div>
  );
};

// --- Components ---

const AdminSection = () => (
  <div className="max-w-7xl mx-auto px-4 py-16">
    <div className="flex items-center justify-between mb-8">
      <div>
        <h2 className="text-3xl font-bold text-white flex items-center gap-3">
          <Lock className="text-[#ed7f20]" />
          Admin Center
        </h2>
        <p className="text-[#b1dde9]/70 mt-1">Willkommen zurück, Admin.</p>
      </div>
      <button className="bg-[#ed7f20] hover:bg-[#ffb056] text-white px-4 py-2 rounded-lg font-bold text-sm transition-colors shadow-lg">
        + Neuer Upload
      </button>
    </div>

    {/* Dashboard Stats */}
    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
      <div className="bg-[#0f1720] border border-[#b1dde9]/10 p-6 rounded-xl">
        <div className="flex items-center gap-3 mb-2 text-[#b1dde9]/60">
          <User size={20} />
          <span className="text-sm font-bold uppercase">Total Follower</span>
        </div>
        <div className="text-3xl font-black text-white">111.2k</div>
        <div className="text-green-500 text-xs font-bold mt-1 flex items-center gap-1">▲ 1.2% diese Woche</div>
      </div>
      <div className="bg-[#0f1720] border border-[#b1dde9]/10 p-6 rounded-xl">
        <div className="flex items-center gap-3 mb-2 text-[#b1dde9]/60">
          <Download size={20} />
          <span className="text-sm font-bold uppercase">Wallpaper Downloads</span>
        </div>
        <div className="text-3xl font-black text-white">4.8k</div>
        <div className="text-green-500 text-xs font-bold mt-1 flex items-center gap-1">▲ 12% heute</div>
      </div>
      <div className="bg-[#0f1720] border border-[#b1dde9]/10 p-6 rounded-xl">
        <div className="flex items-center gap-3 mb-2 text-[#b1dde9]/60">
          <ShoppingBag size={20} />
          <span className="text-sm font-bold uppercase">Shop Umsatz</span>
        </div>
        <div className="text-3xl font-black text-white">842,50 €</div>
        <div className="text-[#b1dde9]/40 text-xs font-bold mt-1">Letzte 30 Tage</div>
      </div>
    </div>

    {/* Quick Actions / Mock List */}
    <div className="bg-[#0f1720] border border-[#b1dde9]/10 rounded-xl overflow-hidden">
      <div className="px-6 py-4 border-b border-[#b1dde9]/10 flex items-center justify-between">
        <h3 className="font-bold text-white flex items-center gap-2">
          <Settings size={18} /> Letzte Aktivitäten
        </h3>
        <button className="text-[#0268a8] text-sm hover:text-white">Alle ansehen</button>
      </div>
      <div className="divide-y divide-[#b1dde9]/10">
        {[1, 2, 3].map((item) => (
          <div key={item} className="px-6 py-4 flex items-center justify-between hover:bg-[#0a1016] transition-colors">
            <div className="flex items-center gap-4">
              <div className="w-10 h-10 bg-[#0268a8]/20 rounded-lg flex items-center justify-center text-[#0268a8]">
                <BarChart3 size={20} />
              </div>
              <div>
                <p className="text-white font-medium">Neues Wallpaper hochgeladen</p>
                <p className="text-[#b1dde9]/50 text-xs">Vor {item * 2} Stunden</p>
              </div>
            </div>
            <span className="px-3 py-1 rounded-full bg-green-500/10 text-green-500 text-xs font-bold">
              Erledigt
            </span>
          </div>
        ))}
      </div>
    </div>
  </div>
);

const HomeSection = ({ excuse, generateExcuse, setActiveTab }) => (
  <>
    {/* Hero */}
    <div className="relative overflow-hidden py-24 lg:py-32 flex flex-col items-center text-center px-4">
      {/* Background Gradients using the new colors */}
      <div className="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-[#0268a8]/20 blur-[100px] rounded-full -z-10 pointer-events-none mix-blend-screen"></div>
      <div className="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-[#ffb056]/10 blur-[80px] rounded-full -z-10 pointer-events-none mix-blend-screen"></div>

      <div className="inline-flex items-center space-x-2 bg-[#0268a8]/10 border border-[#0268a8]/30 rounded-full px-4 py-1.5 mb-8">
         <Heart size={14} className="text-[#ed7f20] fill-[#ed7f20]" />
         <span className="text-[#b1dde9] text-xs font-bold tracking-wide uppercase">Community & Lifestyle</span>
      </div>

      <h1 className="text-5xl md:text-8xl font-black italic tracking-tighter mb-6 text-white">
        RIDE WITH <br/>
        <span className="text-transparent bg-clip-text bg-gradient-to-r from-[#ed7f20] via-[#ffb056] to-[#ed7f20]">PASSION</span>
      </h1>
      <p className="text-xl text-[#b1dde9]/80 max-w-2xl mb-10 font-light">
        Die Homebase für alle, die das Biken lieben. Memes, Kunst und Good Vibes only.
      </p>

      <div className="flex gap-4">
        <button
          onClick={() => setActiveTab('art')}
          className="px-8 py-4 bg-[#ed7f20] hover:bg-[#ffb056] text-white rounded-xl font-bold transition-all hover:scale-105 shadow-[0_0_20px_-5px_#ed7f20]"
        >
          Zur Galerie
        </button>
        <button
          onClick={() => setActiveTab('shop')}
          className="px-8 py-4 bg-[#0268a8]/10 hover:bg-[#0268a8]/20 border border-[#0268a8]/50 text-[#b1dde9] rounded-xl font-bold transition-all"
        >
          Zum Shop
        </button>
      </div>
    </div>

    {/* Interactive Tool: Excuse Generator */}
    <div className="max-w-4xl mx-auto px-4 py-12">
      <div className="bg-[#0f1720] rounded-3xl border border-[#0268a8]/30 p-8 md:p-12 text-center shadow-2xl shadow-[#0268a8]/10 relative overflow-hidden group">

        {/* Decorative circle */}
        <div className="absolute -top-24 -right-24 w-64 h-64 bg-[#ed7f20]/10 rounded-full blur-3xl group-hover:bg-[#ed7f20]/20 transition-all duration-700"></div>

        <div className="inline-flex items-center gap-2 text-[#ffb056] font-bold tracking-wide uppercase text-sm mb-4">
          <RefreshCw size={16} />
          <span>Beta Feature</span>
        </div>

        <h2 className="text-3xl md:text-4xl font-bold mb-8 text-white">Der MTB Ausreden-Generator</h2>

        <div className="bg-[#0a1016] rounded-xl p-8 mb-8 min-h-[120px] flex items-center justify-center border border-[#b1dde9]/10 relative">
          <div className="absolute top-2 left-2 text-[#0268a8]/20"><Zap size={24} /></div>
          <p className="text-2xl md:text-3xl font-serif italic text-[#b1dde9]">"{excuse}"</p>
        </div>

        <button
          onClick={generateExcuse}
          className="group relative inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-[#0268a8] to-[#025a90] rounded-xl font-bold text-lg text-white hover:brightness-110 transition-all active:scale-95 shadow-lg shadow-[#0268a8]/30"
        >
          <RefreshCw className="group-hover:rotate-180 transition-transform duration-500" size={20} />
          Neue Ausrede generieren
        </button>

        <p className="mt-4 text-sm text-[#b1dde9]/50">Perfekt für den Gruppen-Chat nach dem Crash.</p>
      </div>
    </div>
  </>
);

const ArtSection = ({ artworks }) => (
  <div className="max-w-7xl mx-auto px-4 py-16">
    <div className="flex flex-col md:flex-row justify-between items-end mb-12">
      <div>
        <h2 className="text-4xl font-black italic mb-2 text-white">WALLPAPER & KUNST</h2>
        <p className="text-[#b1dde9]/70">High-Res Downloads für Desktop & Mobile.</p>
      </div>
      <div className="flex gap-2 mt-4 md:mt-0">
        <FilterBadge label="Alle" active />
        <FilterBadge label="Free" />
        <FilterBadge label="Premium" />
      </div>
    </div>

    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      {artworks.map((art) => (
        <div key={art.id} className="group relative bg-[#0f1720] rounded-2xl overflow-hidden border border-[#b1dde9]/10 hover:border-[#ed7f20]/50 transition-all hover:-translate-y-1">
          <div className="aspect-[3/4] bg-[#0a1016] relative overflow-hidden">
            {/* Placeholder Image */}
            <div className="absolute inset-0 bg-gradient-to-br from-[#0a1016] to-[#1a2530] flex items-center justify-center group-hover:scale-105 transition-transform duration-500">
               <ImageIcon size={48} className="text-[#0268a8]/40" />
            </div>

            {/* Overlay Buttons */}
            <div className="absolute inset-0 bg-[#0268a8]/80 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-3 backdrop-blur-sm">
              <button className="bg-white text-[#0268a8] px-6 py-2 rounded-full font-bold flex items-center gap-2 hover:bg-[#b1dde9] shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                <Download size={18} /> Download
              </button>
            </div>

            {/* Badges */}
            <div className="absolute top-3 left-3">
              {art.type === 'premium' ? (
                <span className="bg-gradient-to-r from-[#ed7f20] to-[#ffb056] text-white text-xs font-bold px-2 py-1 rounded shadow-lg">GOLD</span>
              ) : (
                <span className="bg-[#0268a8] text-white text-xs font-bold px-2 py-1 rounded shadow-lg">FREE</span>
              )}
            </div>
          </div>

          <div className="p-4">
            <h3 className="font-bold text-lg mb-1 text-white">{art.title}</h3>
            <p className="text-[#b1dde9]/50 text-sm">{art.style}</p>
          </div>
        </div>
      ))}
    </div>
  </div>
);

const ShopSection = ({ products }) => (
  <div className="max-w-7xl mx-auto px-4 py-16">
    <div className="text-center max-w-2xl mx-auto mb-16">
      <h2 className="text-4xl font-black italic mb-4 text-white">DER SHOP</h2>
      <p className="text-[#b1dde9]/70">
        Zeig deine Liebe zum Sport. Supporte die Community.
      </p>
    </div>

    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
      {products.map((product) => (
        <div key={product.id} className="bg-[#0f1720] rounded-2xl p-4 border border-[#b1dde9]/10 hover:border-[#ed7f20]/50 transition-all group">
          <div className="aspect-square bg-[#0a1016] rounded-xl mb-4 relative flex items-center justify-center overflow-hidden">
            <div className="absolute inset-0 bg-[#0268a8]/5 group-hover:bg-[#0268a8]/10 transition-colors"></div>
            <ShoppingBag size={48} className="text-[#0268a8]/40 group-hover:scale-110 transition-transform duration-300" />
            {product.tag && (
              <div className="absolute top-3 right-3 bg-[#ed7f20] text-white text-xs font-bold px-3 py-1 rounded-full">
                {product.tag}
              </div>
            )}
          </div>
          <h3 className="text-xl font-bold mb-2 text-white">{product.name}</h3>
          <div className="flex items-center justify-between mt-4">
            <span className="text-2xl font-bold text-[#ed7f20]">{product.price}</span>
            <button className="p-3 bg-white text-[#0268a8] rounded-xl hover:bg-[#ffb056] hover:text-white transition-colors shadow-lg">
              <ShoppingBag size={20} />
            </button>
          </div>
        </div>
      ))}
    </div>
  </div>
);

// Helpers
const NavButton = ({ label, active, onClick }) => (
  <button
    onClick={onClick}
    className={`text-sm font-bold uppercase tracking-wide transition-colors ${
      active ? 'text-[#ed7f20]' : 'text-[#b1dde9]/60 hover:text-white'
    }`}
  >
    {label}
  </button>
);

const MobileNavButton = ({ label, onClick, icon }) => (
  <button
    onClick={onClick}
    className="flex items-center gap-3 w-full text-left px-3 py-3 rounded-lg text-base font-medium text-[#b1dde9] hover:bg-[#0268a8]/20 hover:text-white"
  >
    {icon && <span>{icon}</span>}
    <span>{label}</span>
  </button>
);

const FilterBadge = ({ label, active }) => (
  <span className={`px-4 py-1.5 rounded-full text-sm font-medium border cursor-pointer transition-all ${
    active
    ? 'bg-[#ed7f20] border-[#ed7f20] text-white shadow-lg shadow-[#ed7f20]/20'
    : 'bg-transparent border-[#b1dde9]/20 text-[#b1dde9]/60 hover:border-[#b1dde9]/50 hover:text-white'
  }`}>
    {label}
  </span>
);

export default App;
