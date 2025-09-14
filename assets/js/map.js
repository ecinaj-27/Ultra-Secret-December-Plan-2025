// Map functionality for the Us page

let map;
let markers = [];

function initializeMap(locations) {
    // Initialize map centered on a default location (you can change this)
    map = L.map('map').setView([40.7128, -74.0060], 10);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add markers for each location
    locations.forEach(location => {
        if (location.latitude && location.longitude) {
            const marker = L.marker([location.latitude, location.longitude])
                .addTo(map)
                .bindPopup(`
                    <div class="map-popup">
                        <h3>${location.name}</h3>
                        ${location.description ? `<p>${location.description}</p>` : ''}
                        ${location.visit_date ? `<p><i class="fas fa-calendar"></i> ${formatDate(location.visit_date)}</p>` : ''}
                    </div>
                `);
            
            markers.push(marker);
        }
    });
    
    // Fit map to show all markers
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
    
    // Add click handlers to location cards
    document.querySelectorAll('.location-card').forEach(card => {
        card.addEventListener('click', function() {
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 15);
                
                // Find and open the corresponding marker popup
                markers.forEach(marker => {
                    const markerLat = marker.getLatLng().lat;
                    const markerLng = marker.getLatLng().lng;
                    
                    if (Math.abs(markerLat - lat) < 0.001 && Math.abs(markerLng - lng) < 0.001) {
                        marker.openPopup();
                    }
                });
            }
        });
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

// Add custom CSS for map popup
const mapStyle = document.createElement('style');
mapStyle.textContent = `
    .map-popup {
        text-align: center;
        min-width: 200px;
    }
    
    .map-popup h3 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 1.1rem;
    }
    
    .map-popup p {
        margin: 5px 0;
        color: #666;
        font-size: 0.9rem;
    }
    
    .map-popup i {
        margin-right: 5px;
        color: #ff6b6b;
    }
    
    .leaflet-popup-content-wrapper {
        border-radius: 10px;
    }
    
    .leaflet-popup-tip {
        background: white;
    }
`;
document.head.appendChild(mapStyle);
