# Weather Dashboard

A modern, responsive weather dashboard with real-time data from OpenWeatherMap API.

## 🌟 Features

- **City Search** - Search any city worldwide with Enter key support
- **Geolocation** - Auto-detect your location with one click
- **Current Weather** - Temperature, humidity, wind, pressure, visibility
- **5-Day Forecast** - Visual forecast cards with icons
- **Sunrise/Sunset** - Display local sun times
- **Recent Searches** - Quick access to previously searched cities
- **Dynamic Backgrounds** - Theme changes based on weather conditions

## 🚀 Quick Start

1. Open `index.html` in any modern browser
2. Search for a city or use geolocation
3. Weather data displays automatically

**Note**: Uses a demo API key with limited requests. For production, get your free key at [OpenWeatherMap](https://openweathermap.org/api).

## 📁 Project Structure

```
02-weather-dashboard/
├── index.html      # Main HTML page
├── css/
│   └── style.css   # Glassmorphism styles
├── js/
│   └── app.js      # API logic & UI updates
└── README.md       # This file
```

## 🔧 Configuration

Edit `js/app.js` to change:

```javascript
const CONFIG = {
    API_KEY: 'your-api-key-here',  // Get free key from OpenWeatherMap
    UNITS: 'metric'                 // 'metric' (°C) or 'imperial' (°F)
};
```

## 🎨 Design

- **Glassmorphism** - Frosted glass effect with backdrop blur
- **Dynamic gradients** - Background changes with weather
- **Fully responsive** - Works on mobile, tablet, desktop

## 📝 Tech Stack

- HTML5, CSS3, JavaScript (ES6+)
- OpenWeatherMap API (Free tier)
- Font Awesome 6.5
- Google Fonts (Inter)

---

Built by **Afzal Khan** | January 2026
