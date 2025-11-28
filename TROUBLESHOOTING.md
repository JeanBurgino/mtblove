# Troubleshooting: Like Section Not Visible

## ✅ Code Verification

The like section **IS** implemented in the code:
- **File**: `frontend/src/pages/Wallpapers.jsx`
- **Lines**: 244-263 (Like button with Heart icon)
- **Backend API**: `backend/api/wallpapers.php:249-284` (toggleLike function)
- **API Router**: `backend/api/index.php:75-78` (toggle_like action)

## 🔍 Diagnostic Steps

### Step 1: Test the API

Open this file in your browser:
```
http://localhost/test-api.html
```

Run all three tests:
1. **Test 1**: Verify API returns wallpapers
2. **Test 2**: Check if likes field exists in data
3. **Test 3**: Test the toggle_like endpoint

### Step 2: Check Frontend Dev Server

If you're running a development server (Vite), you need to restart it:

```bash
cd frontend
npm install  # If dependencies aren't installed
npm run dev  # Start dev server
```

Then open: `http://localhost:5173/wallpapers` (or whatever port Vite shows)

### Step 3: Hard Refresh Browser

Clear your browser cache:
- **Chrome/Edge**: `Ctrl + Shift + R` (Windows) or `Cmd + Shift + R` (Mac)
- **Firefox**: `Ctrl + F5` or `Cmd + Shift + R`

### Step 4: Check Browser Console

1. Open browser DevTools (`F12`)
2. Go to **Console** tab
3. Look for any JavaScript errors
4. Go to **Network** tab and check if API calls are successful

### Step 5: Verify Database

Check if the wallpapers table has the likes column:

```sql
DESCRIBE wallpapers;
```

Should show:
```
likes | int unsigned | YES | | 0 |
```

### Step 6: Check Wallpapers Data

Make sure there's data in the database:

```sql
SELECT id, title, likes, downloads FROM wallpapers LIMIT 5;
```

## 🐛 Common Issues & Solutions

### Issue 1: "likes" field is null or undefined
**Solution**: The database has the field, but might need data:

```sql
UPDATE wallpapers SET likes = 0 WHERE likes IS NULL;
UPDATE wallpapers SET downloads = 0 WHERE downloads IS NULL;
```

### Issue 2: Frontend not updating
**Solution**:
- Stop the dev server (`Ctrl+C`)
- Delete `.vite` cache: `rm -rf frontend/.vite`
- Restart: `npm run dev`

### Issue 3: API not found (404)
**Solution**: Check that the API path is correct:
- API file should be at: `/backend/api/index.php`
- Frontend calls: `/backend/api/index.php`

### Issue 4: CORS errors
**Solution**: Check `backend/config.php` line 38:
```php
define('CORS_ALLOWED_ORIGINS', '*');
```

## 📋 What Should Be Visible

On each wallpaper card, you should see:

```
┌─────────────────────────┐
│                         │
│   [Wallpaper Image]     │
│                         │
└─────────────────────────┘
  Title of Wallpaper
  Style/Category

  ❤️ 0          ⬇️ 0
  (Like)      (Downloads)
```

- **Heart icon**: Clickable, turns orange when liked
- **Like counter**: Number next to heart
- **Download icon**: Blue arrow down
- **Download counter**: Number next to arrow

## 🔧 Force Component Reload

If nothing works, try this:

1. Add a console.log to verify component is rendering:

```jsx
// In WallpaperCard component, add this line:
console.log('Wallpaper:', wallpaper.id, 'Likes:', wallpaper.likes);
```

2. Rebuild the component:
```bash
cd frontend
npm run build
```

## 📞 Still Not Working?

Check these files have the latest code:
- `backend/api/wallpapers.php` - Should have `toggleLike()` function
- `backend/api/index.php` - Should have `case 'toggle_like':`
- `frontend/src/pages/Wallpapers.jsx` - Should import `Heart` from lucide-react

If the issue persists, check:
1. Browser console for errors
2. Network tab for failed API calls
3. Database connection
4. File permissions on backend files
