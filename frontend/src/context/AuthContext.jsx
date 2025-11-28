import React, { createContext, useContext, useState, useEffect } from 'react'
import axios from 'axios'

const AuthContext = createContext()

export const useAuth = () => {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider')
  }
  return context
}

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null)
  const [token, setToken] = useState(localStorage.getItem('mtblove_token'))
  const [loading, setLoading] = useState(true)

  const API_URL = '/backend/api/index.php'

  useEffect(() => {
    if (token) {
      checkAuth()
    } else {
      setLoading(false)
    }
  }, [token])

  const checkAuth = async () => {
    try {
      const formData = new FormData()
      formData.append('action', 'check_auth')
      formData.append('token', token)

      const response = await axios.post(API_URL, formData)

      if (response.data.authenticated) {
        setUser(response.data.user)
      } else {
        logout()
      }
    } catch (error) {
      console.error('Auth check failed:', error)
      logout()
    } finally {
      setLoading(false)
    }
  }

  const login = async (username, password) => {
    try {
      const formData = new FormData()
      formData.append('action', 'login')
      formData.append('user', username)
      formData.append('pass', password)

      const response = await axios.post(API_URL, formData)

      if (response.data.success) {
        const { token, user } = response.data
        setToken(token)
        setUser(user)
        localStorage.setItem('mtblove_token', token)
        return { success: true }
      } else {
        return { success: false, message: 'Login fehlgeschlagen' }
      }
    } catch (error) {
      console.error('Login error:', error)
      return { success: false, message: error.response?.data?.error || 'Login fehlgeschlagen' }
    }
  }

  const logout = async () => {
    try {
      if (token) {
        const formData = new FormData()
        formData.append('action', 'logout')
        formData.append('token', token)
        await axios.post(API_URL, formData)
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      setUser(null)
      setToken(null)
      localStorage.removeItem('mtblove_token')
    }
  }

  const value = {
    user,
    token,
    loading,
    login,
    logout,
    isAuthenticated: !!user
  }

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  )
}
