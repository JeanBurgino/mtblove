import React, { useState, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { ArrowLeft, Plus, Edit, Trash2, Package } from 'lucide-react'
import { useAuth } from '../context/AuthContext'
import axios from 'axios'

const AdminProducts = () => {
  const [products, setProducts] = useState([])
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

    loadProducts()
  }, [isAuthenticated, navigate])

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

  const handleDelete = async (id) => {
    if (!confirm('Produkt wirklich löschen?')) return

    try {
      const formData = new FormData()
      formData.append('action', 'delete_product')
      formData.append('id', id)

      await axios.post(API_URL, formData)
      loadProducts()
      alert('Produkt gelöscht')
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
            <h1 className="text-3xl font-bold text-white">Produkt Verwaltung</h1>
            <p className="text-light/70 mt-1">{products.length} Produkte</p>
          </div>
        </div>

        <button
          onClick={() => setShowAddModal(true)}
          className="flex items-center gap-2 bg-orange hover:bg-orange-light text-white px-4 py-2 rounded-lg font-bold transition-colors"
        >
          <Plus size={20} />
          Neues Produkt
        </button>
      </div>

      {loading ? (
        <div className="text-center py-20">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-orange"></div>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {products.map((product) => (
            <ProductCard
              key={product.id}
              product={product}
              onDelete={handleDelete}
            />
          ))}
        </div>
      )}

      {showAddModal && (
        <AddProductModal
          onClose={() => setShowAddModal(false)}
          onSuccess={() => {
            setShowAddModal(false)
            loadProducts()
          }}
        />
      )}
    </div>
  )
}

const ProductCard = ({ product, onDelete }) => (
  <div className="bg-dark-700 rounded-xl border border-light/10 overflow-hidden">
    <div className="aspect-square bg-dark flex items-center justify-center">
      <Package size={48} className="text-blue/40" />
    </div>
    <div className="p-4">
      <h3 className="font-bold text-white mb-1">{product.name}</h3>
      <p className="text-orange text-lg font-bold mb-2">
        {product.price_formatted || `${product.price} ${product.currency}`}
      </p>
      <div className="flex items-center gap-2 mb-4">
        {product.tag && (
          <span className="text-xs px-2 py-1 rounded bg-orange/20 text-orange">
            {product.tag}
          </span>
        )}
        <span className="text-xs text-light/40">Lager: {product.stock_quantity}</span>
      </div>
      <div className="flex gap-2">
        <button className="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-blue/10 hover:bg-blue/20 border border-blue/30 text-blue rounded-lg text-sm font-medium transition-colors">
          <Edit size={16} /> Bearbeiten
        </button>
        <button
          onClick={() => onDelete(product.id)}
          className="flex items-center justify-center gap-2 px-3 py-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 text-red-500 rounded-lg text-sm font-medium transition-colors"
        >
          <Trash2 size={16} />
        </button>
      </div>
    </div>
  </div>
)

const AddProductModal = ({ onClose, onSuccess }) => {
  const [name, setName] = useState('')
  const [description, setDescription] = useState('')
  const [price, setPrice] = useState('')
  const [category, setCategory] = useState('Bekleidung')
  const [tag, setTag] = useState('')
  const [stock, setStock] = useState(0)
  const [file, setFile] = useState(null)
  const [loading, setLoading] = useState(false)

  const API_URL = '/backend/api/index.php'

  const handleSubmit = async (e) => {
    e.preventDefault()
    setLoading(true)

    try {
      const formData = new FormData()
      formData.append('action', 'add_product')
      formData.append('name', name)
      formData.append('description', description)
      formData.append('price', price)
      formData.append('category', category)
      formData.append('tag', tag)
      formData.append('stock_quantity', stock)
      if (file) {
        formData.append('image', file)
      }

      await axios.post(API_URL, formData)
      alert('Produkt hinzugefügt')
      onSuccess()
    } catch (error) {
      console.error('Add product error:', error)
      alert('Fehler beim Hinzufügen')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-dark/80 backdrop-blur-sm p-4">
      <div className="bg-dark-700 border border-light/10 rounded-2xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
        <h2 className="text-2xl font-bold text-white mb-6">Neues Produkt</h2>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm text-light mb-1">Name</label>
            <input
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
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
            <label className="block text-sm text-light mb-1">Preis (EUR)</label>
            <input
              type="number"
              step="0.01"
              value={price}
              onChange={(e) => setPrice(e.target.value)}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white focus:border-orange outline-none"
              required
            />
          </div>
          <div>
            <label className="block text-sm text-light mb-1">Kategorie</label>
            <select
              value={category}
              onChange={(e) => setCategory(e.target.value)}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white focus:border-orange outline-none"
            >
              <option value="Bekleidung">Bekleidung</option>
              <option value="Accessoires">Accessoires</option>
              <option value="Druck">Druck</option>
              <option value="Sonstiges">Sonstiges</option>
            </select>
          </div>
          <div>
            <label className="block text-sm text-light mb-1">Tag (optional)</label>
            <input
              type="text"
              value={tag}
              onChange={(e) => setTag(e.target.value)}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white focus:border-orange outline-none"
              placeholder="z.B. New, Bestseller"
            />
          </div>
          <div>
            <label className="block text-sm text-light mb-1">Lagerbestand</label>
            <input
              type="number"
              value={stock}
              onChange={(e) => setStock(e.target.value)}
              className="w-full bg-dark border border-light/20 rounded-lg p-3 text-white focus:border-orange outline-none"
            />
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

export default AdminProducts
