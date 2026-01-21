/**
 * =============================================================================
 * WEATHER DASHBOARD - MAIN JAVASCRIPT
 * =============================================================================
 * 
 * Handles all weather data fetching and UI updates using OpenWeatherMap API.
 * Features:
 * - City search with Enter key support
 * - Geolocation (browser location)
 * - 5-day forecast display
 * - Recent searches (localStorage)
 * - Dynamic background based on weather
 * 
 * @author  Afzal Khan
 * @version 1.0.0
 * @since   January 2026
 * =============================================================================
 */

// =============================================================================
// CONFIGURATION
// =============================================================================

/**
 * OpenWeatherMap API Configuration
 * IMPORTANT: Replace with your own API key for production use
 * Get free key at: https://openweathermap.org/api
 */
const CONFIG = {
    API_KEY: 'bd5e378503939ddaee76f12ad7a97608', // Demo key (limited requests)
    BASE_URL: 'https://api.openweathermap.org/data/2.5',
    ICON_URL: 'https://openweathermap.org/img/wn',
    UNITS: 'metric' // 'metric' for Celsius, 'imperial' for Fahrenheit
};

// =============================================================================
// DOM ELEMENTS
// =============================================================================

const elements = {
    // Search
    cityInput: document.getElementById('cityInput'),
    searchBtn: document.getElementById('searchBtn'),
    locationBtn: document.getElementById('locationBtn'),
    recentSearches: document.getElementById('recentSearches'),

    // States
    loading: document.getElementById('loading'),
    errorMessage: document.getElementById('errorMessage'),
    errorText: document.getElementById('errorText'),
    weatherContainer: document.getElementById('weatherContainer'),

    // Current Weather
    cityName: document.getElementById('cityName'),
    dateTime: document.getElementById('dateTime'),
    weatherIcon: document.getElementById('weatherIcon'),
    temperature: document.getElementById('temperature'),
    description: document.getElementById('description'),
    feelsLike: document.getElementById('feelsLike'),
    humidity: document.getElementById('humidity'),
    windSpeed: document.getElementById('windSpeed'),
    pressure: document.getElementById('pressure'),
    visibility: document.getElementById('visibility'),
    uvIndex: document.getElementById('uvIndex'),

    // Forecast
    forecastGrid: document.getElementById('forecastGrid'),

    // Sun times
    sunrise: document.getElementById('sunrise'),
    sunset: document.getElementById('sunset')
};

// =============================================================================
// STATE MANAGEMENT
// =============================================================================

/**
 * Recent searches stored in localStorage
 * Maximum 5 cities stored
 */
let recentCities = JSON.parse(localStorage.getItem('recentCities')) || [];

// =============================================================================
// EVENT LISTENERS
// =============================================================================

/**
 * Initialize event listeners when DOM is ready
 */
document.addEventListener('DOMContentLoaded', () => {
    // Search button click
    elements.searchBtn.addEventListener('click', handleSearch);

    // Enter key in search input
    elements.cityInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleSearch();
        }
    });

    // Geolocation button
    elements.locationBtn.addEventListener('click', handleGeolocation);

    // Display recent searches
    displayRecentSearches();

    // Load default city or last searched
    const lastCity = recentCities[0] || 'Hyderabad';
    fetchWeatherData(lastCity);
});

// =============================================================================
// SEARCH HANDLERS
// =============================================================================

/**
 * Handle city search from input
 */
function handleSearch() {
    const city = elements.cityInput.value.trim();
    if (city) {
        fetchWeatherData(city);
        elements.cityInput.value = '';
    }
}

/**
 * Handle geolocation request
 */
function handleGeolocation() {
    if (!navigator.geolocation) {
        showError('Geolocation is not supported by your browser');
        return;
    }

    showLoading();

    navigator.geolocation.getCurrentPosition(
        // Success callback
        (position) => {
            const { latitude, longitude } = position.coords;
            fetchWeatherByCoords(latitude, longitude);
        },
        // Error callback
        (error) => {
            hideLoading();
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    showError('Location permission denied. Please enable location access.');
                    break;
                case error.POSITION_UNAVAILABLE:
                    showError('Location information unavailable.');
                    break;
                case error.TIMEOUT:
                    showError('Location request timed out.');
                    break;
                default:
                    showError('An unknown error occurred.');
            }
        },
        // Options
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// =============================================================================
// API FUNCTIONS
// =============================================================================

/**
 * Fetch weather data for a city name
 * @param {string} city - City name to search
 */
async function fetchWeatherData(city) {
    showLoading();

    try {
        // Fetch current weather
        const currentUrl = `${CONFIG.BASE_URL}/weather?q=${encodeURIComponent(city)}&units=${CONFIG.UNITS}&appid=${CONFIG.API_KEY}`;
        const currentResponse = await fetch(currentUrl);

        if (!currentResponse.ok) {
            throw new Error(currentResponse.status === 404 ? 'City not found' : 'Failed to fetch weather data');
        }

        const currentData = await currentResponse.json();

        // Fetch 5-day forecast
        const forecastUrl = `${CONFIG.BASE_URL}/forecast?q=${encodeURIComponent(city)}&units=${CONFIG.UNITS}&appid=${CONFIG.API_KEY}`;
        const forecastResponse = await fetch(forecastUrl);
        const forecastData = await forecastResponse.json();

        // Update UI
        updateCurrentWeather(currentData);
        updateForecast(forecastData);
        updateBackground(currentData.weather[0].main);

        // Save to recent searches
        saveRecentCity(currentData.name);

        hideLoading();
        showWeather();

    } catch (error) {
        hideLoading();
        showError(error.message);
    }
}

/**
 * Fetch weather data by coordinates
 * @param {number} lat - Latitude
 * @param {number} lon - Longitude
 */
async function fetchWeatherByCoords(lat, lon) {
    try {
        // Fetch current weather
        const currentUrl = `${CONFIG.BASE_URL}/weather?lat=${lat}&lon=${lon}&units=${CONFIG.UNITS}&appid=${CONFIG.API_KEY}`;
        const currentResponse = await fetch(currentUrl);
        const currentData = await currentResponse.json();

        // Fetch 5-day forecast
        const forecastUrl = `${CONFIG.BASE_URL}/forecast?lat=${lat}&lon=${lon}&units=${CONFIG.UNITS}&appid=${CONFIG.API_KEY}`;
        const forecastResponse = await fetch(forecastUrl);
        const forecastData = await forecastResponse.json();

        // Update UI
        updateCurrentWeather(currentData);
        updateForecast(forecastData);
        updateBackground(currentData.weather[0].main);

        // Save to recent searches
        saveRecentCity(currentData.name);

        hideLoading();
        showWeather();

    } catch (error) {
        hideLoading();
        showError('Failed to fetch weather for your location');
    }
}

// =============================================================================
// UI UPDATE FUNCTIONS
// =============================================================================

/**
 * Update current weather display
 * @param {Object} data - Current weather API response
 */
function updateCurrentWeather(data) {
    // Location and date
    elements.cityName.textContent = `${data.name}, ${data.sys.country}`;
    elements.dateTime.textContent = formatDate(new Date());

    // Temperature and icon
    elements.weatherIcon.src = `${CONFIG.ICON_URL}/${data.weather[0].icon}@4x.png`;
    elements.weatherIcon.alt = data.weather[0].description;
    elements.temperature.textContent = Math.round(data.main.temp);
    elements.description.textContent = data.weather[0].description;

    // Details
    elements.feelsLike.textContent = `${Math.round(data.main.feels_like)}°C`;
    elements.humidity.textContent = `${data.main.humidity}%`;
    elements.windSpeed.textContent = `${Math.round(data.wind.speed * 3.6)} km/h`; // Convert m/s to km/h
    elements.pressure.textContent = `${data.main.pressure} hPa`;
    elements.visibility.textContent = `${(data.visibility / 1000).toFixed(1)} km`;
    elements.uvIndex.textContent = 'N/A'; // UV requires separate API call

    // Sunrise/Sunset
    elements.sunrise.textContent = formatTime(data.sys.sunrise, data.timezone);
    elements.sunset.textContent = formatTime(data.sys.sunset, data.timezone);
}

/**
 * Update 5-day forecast display
 * @param {Object} data - Forecast API response
 */
function updateForecast(data) {
    // Get one forecast per day (12:00 noon)
    const dailyForecasts = data.list.filter(item => item.dt_txt.includes('12:00:00'));

    let forecastHTML = '';

    dailyForecasts.slice(0, 5).forEach(day => {
        const date = new Date(day.dt * 1000);
        const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
        const icon = day.weather[0].icon;
        const tempMax = Math.round(day.main.temp_max);
        const tempMin = Math.round(day.main.temp_min);

        forecastHTML += `
            <div class="forecast-card">
                <div class="forecast-day">${dayName}</div>
                <img src="${CONFIG.ICON_URL}/${icon}@2x.png" alt="${day.weather[0].description}" class="forecast-icon">
                <div class="forecast-temp">${tempMax}°</div>
                <div class="forecast-temp-min">${tempMin}°</div>
            </div>
        `;
    });

    elements.forecastGrid.innerHTML = forecastHTML;
}

/**
 * Update background gradient based on weather condition
 * @param {string} condition - Weather condition (Clear, Clouds, Rain, etc.)
 */
function updateBackground(condition) {
    const hour = new Date().getHours();
    const isNight = hour < 6 || hour > 20;

    let gradient;

    if (isNight) {
        gradient = 'linear-gradient(135deg, #0f0c29 0%, #302b63 50%, #24243e 100%)';
    } else {
        switch (condition.toLowerCase()) {
            case 'clear':
                gradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                break;
            case 'clouds':
                gradient = 'linear-gradient(135deg, #606c88 0%, #3f4c6b 100%)';
                break;
            case 'rain':
            case 'drizzle':
                gradient = 'linear-gradient(135deg, #457fca 0%, #5691c8 100%)';
                break;
            case 'thunderstorm':
                gradient = 'linear-gradient(135deg, #373b44 0%, #4286f4 100%)';
                break;
            case 'snow':
                gradient = 'linear-gradient(135deg, #e6dada 0%, #274046 100%)';
                break;
            case 'mist':
            case 'fog':
            case 'haze':
                gradient = 'linear-gradient(135deg, #757f9a 0%, #d7dde8 100%)';
                break;
            default:
                gradient = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        }
    }

    document.body.style.background = gradient;
}

// =============================================================================
// UTILITY FUNCTIONS
// =============================================================================

/**
 * Format date to readable string
 * @param {Date} date - Date object
 * @returns {string} Formatted date string
 */
function formatDate(date) {
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

/**
 * Format Unix timestamp to time string
 * @param {number} timestamp - Unix timestamp
 * @param {number} timezone - Timezone offset in seconds
 * @returns {string} Formatted time string
 */
function formatTime(timestamp, timezone) {
    const date = new Date((timestamp + timezone) * 1000);
    return date.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
        timeZone: 'UTC'
    });
}

// =============================================================================
// STATE MANAGEMENT FUNCTIONS
// =============================================================================

/**
 * Show loading state
 */
function showLoading() {
    elements.loading.classList.add('show');
    elements.errorMessage.classList.remove('show');
    elements.weatherContainer.classList.remove('show');
}

/**
 * Hide loading state
 */
function hideLoading() {
    elements.loading.classList.remove('show');
}

/**
 * Show weather container
 */
function showWeather() {
    elements.weatherContainer.classList.add('show');
    elements.errorMessage.classList.remove('show');
}

/**
 * Show error message
 * @param {string} message - Error message to display
 */
function showError(message) {
    elements.errorText.textContent = message;
    elements.errorMessage.classList.add('show');
    elements.weatherContainer.classList.remove('show');
}

// =============================================================================
// RECENT SEARCHES
// =============================================================================

/**
 * Save city to recent searches
 * @param {string} city - City name
 */
function saveRecentCity(city) {
    // Remove if already exists
    recentCities = recentCities.filter(c => c.toLowerCase() !== city.toLowerCase());

    // Add to beginning
    recentCities.unshift(city);

    // Keep only 5 most recent
    recentCities = recentCities.slice(0, 5);

    // Save to localStorage
    localStorage.setItem('recentCities', JSON.stringify(recentCities));

    // Update display
    displayRecentSearches();
}

/**
 * Display recent searches as clickable buttons
 */
function displayRecentSearches() {
    if (recentCities.length === 0) {
        elements.recentSearches.innerHTML = '';
        return;
    }

    const html = recentCities.map(city =>
        `<button class="recent-city" onclick="fetchWeatherData('${city}')">${city}</button>`
    ).join('');

    elements.recentSearches.innerHTML = html;
}
