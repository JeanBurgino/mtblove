# MTB Love Frontend

Moderne React-Anwendung für die MTB Love Website mit Tailwind CSS und Lucide React Icons.

## 🚀 Installation

```bash
npm install
```

## 🎨 Logo hinzufügen

Die App erwartet ein Logo-Bild im `public/`-Verzeichnis:

1. Speichere dein Logo als `logo.jpg` im Ordner `frontend/public/`
2. Oder aktualisiere den Bildpfad in `src/App.jsx` (Zeile 70):
   ```jsx
   <img src="/logo.jpg" alt="MTB Love Logo" />
   ```

## 🛠️ Entwicklung

Starte den Entwicklungsserver:

```bash
npm run dev
```

Die App läuft dann auf `http://localhost:5173`

## 📦 Production Build

Erstelle einen optimierten Production Build:

```bash
npm run build
```

Die Build-Dateien werden im `dist/`-Ordner gespeichert.

## 🌐 Deployment

Um die React-App in das bestehende PHP-Projekt zu integrieren:

1. **Build erstellen:**
   ```bash
   npm run build
   ```

2. **Dateien kopieren:**
   Kopiere die Inhalte von `dist/` in ein öffentlich zugängliches Verzeichnis deines Webservers.

3. **Integration mit PHP:**
   - Option A: Ersetze `app/public/index.php` durch die `dist/index.html`
   - Option B: Erstelle ein Subdomain oder Verzeichnis für die React-App

## 🎨 Features

- ✨ Moderne, responsive UI mit Tailwind CSS
- 🎨 Wallpaper & Kunst Gallery
- 🛍️ Merch Shop Section
- 🎲 MTB Ausreden-Generator (Interactive Tool)
- 📊 Admin Dashboard mit Analytics
- 📱 Mobile-First Design

## 🔧 Technologie-Stack

- **React** - UI Library
- **Vite** - Build Tool & Dev Server
- **Tailwind CSS** - Utility-First CSS Framework
- **Lucide React** - Icon Library
- **PostCSS** - CSS Processing

## 📝 Hinweise

- Das Logo wird derzeit als `/logo.jpg` referenziert
- Farben können in den Tailwind-Klassen angepasst werden
- Die Admin-Section ist aktuell nur ein Mock-Up (keine Backend-Integration)
