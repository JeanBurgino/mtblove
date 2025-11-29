import React from 'react'
import { Heart, Download, Star, Bike, Mountain } from 'lucide-react'

/**
 * Test component to verify lucide-react icons are rendering
 *
 * Usage: Import this component and add it to your page to test icons
 *
 * import IconTest from './components/IconTest'
 * <IconTest />
 */
const IconTest = () => {
  return (
    <div style={{
      padding: '20px',
      background: '#0f1720',
      border: '2px solid #ed7f20',
      borderRadius: '10px',
      margin: '20px',
      color: '#b1dde9'
    }}>
      <h2 style={{ color: '#ed7f20', marginBottom: '15px' }}>🔧 Icon Test</h2>

      <div style={{ display: 'flex', gap: '20px', flexWrap: 'wrap', marginBottom: '15px' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <Heart size={24} className="text-orange fill-orange" style={{ color: '#ed7f20', fill: '#ed7f20' }} />
          <span>Heart (Orange)</span>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <Download size={24} className="text-blue" style={{ color: '#0268a8' }} />
          <span>Download (Blue)</span>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <Star size={24} className="text-light" style={{ color: '#b1dde9' }} />
          <span>Star (Light)</span>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <Bike size={24} style={{ color: '#ed7f20' }} />
          <span>Bike</span>
        </div>

        <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
          <Mountain size={24} style={{ color: '#0268a8' }} />
          <span>Mountain</span>
        </div>
      </div>

      <p style={{ fontSize: '12px', opacity: 0.7 }}>
        ✓ If you can see icons above, lucide-react is working correctly!<br/>
        ✗ If you only see text, there's an issue with lucide-react loading.
      </p>
    </div>
  )
}

export default IconTest
