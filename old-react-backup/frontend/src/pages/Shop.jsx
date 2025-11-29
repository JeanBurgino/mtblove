import React, { useState, useEffect } from 'react'
import { ShoppingBag } from 'lucide-react'
import axios from 'axios'

const Shop = () => {
  const [products, setProducts] = useState([])
  const [loading, setLoading] = useState(true)

  const API_URL = '/backend/api/index.php'

  useEffect(() => {
    loadProducts()
  }, [])

  const loadProducts = async () => {
    setLoading(true)
    try {
      const formData = new FormData()
      formData.append('action', 'get_products')

      const response = await axios.post(API_URL, formData)
      setProducts(response.data || [])
    } catch (error) {
      console.error('Error loading products:', error)
    } finally {
      setLoading(false)
    }
  }

  const handleAddToCart = (product) => {
    alert(`"${product.name}" wurde zum Warenkorb hinzugefügt!`)
  }

  return (
    <div className="max-w-7xl mx-auto px-4 py-16">
      <div className="text-center mb-16">
        <h1 className="text-4xl md:text-5xl font-black italic mb-4 text-white">
          DER SHOP
        </h1>
        <p className="text-light/70 max-w-2xl mx-auto">
          Hochwertige Merch-Artikel für MTB-Enthusiasten. Von Shirts bis Sticker.
        </p>
      </div>

      {loading ? (
        <div className="text-center py-20">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange"></div>
          <p className="mt-4 text-light/60">Lade Produkte...</p>
        </div>
      ) : products.length === 0 ? (
        <div className="text-center py-20">
          <p className="text-light/60">Keine Produkte verfügbar</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {products.map((product) => (
            <ProductCard
              key={product.id}
              product={product}
              onAddToCart={handleAddToCart}
            />
          ))}
        </div>
      )}
    </div>
  )
}

const ProductCard = ({ product, onAddToCart }) => (
  <div className="bg-dark-700 rounded-2xl p-4 border border-light/10 hover:border-orange/50 transition-all group">
    <div className="aspect-square bg-dark rounded-xl mb-4 relative flex items-center justify-center overflow-hidden">
      {product.image_path ? (
        <img
          src={product.image_path}
          alt={product.name}
          className="w-full h-full object-cover"
          onError={(e) => {
            e.target.style.display = 'none'
            e.target.parentElement.querySelector('.fallback-icon').style.display = 'flex'
          }}
        />
      ) : null}
      <div className="fallback-icon absolute inset-0 flex items-center justify-center">
        <ShoppingBag size={48} className="text-blue/40 group-hover:scale-110 transition-transform duration-300" />
      </div>

      {product.tag && (
        <div className="absolute top-3 right-3 bg-orange text-white text-xs font-bold px-3 py-1 rounded-full">
          {product.tag}
        </div>
      )}

      {product.stock_quantity === 0 && (
        <div className="absolute inset-0 bg-dark/80 flex items-center justify-center">
          <span className="text-red-500 font-bold">Ausverkauft</span>
        </div>
      )}
    </div>

    <h3 className="text-xl font-bold mb-2 text-white">{product.name}</h3>
    {product.description && (
      <p className="text-light/60 text-sm mb-4 line-clamp-2">{product.description}</p>
    )}

    <div className="flex items-center justify-between mt-4">
      <span className="text-2xl font-bold text-orange">
        {product.price_formatted || `${product.price} ${product.currency}`}
      </span>
      <button
        onClick={() => onAddToCart(product)}
        disabled={product.stock_quantity === 0}
        className="p-3 bg-white text-blue rounded-xl hover:bg-orange-light hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <ShoppingBag size={20} />
      </button>
    </div>

    {product.stock_quantity > 0 && product.stock_quantity < 10 && (
      <div className="mt-2 text-xs text-orange">
        Nur noch {product.stock_quantity} auf Lager
      </div>
    )}
  </div>
)

export default Shop
