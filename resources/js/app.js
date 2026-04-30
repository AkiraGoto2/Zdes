import './bootstrap';
import Alpine from 'alpinejs';
import mask from '@alpinejs/mask';
Alpine.plugin(mask);
window.Alpine = Alpine;
Alpine.start();

// import 'leaflet/dist/leaflet.css';
// import L from 'leaflet';

// const map = L.map('map').setView([54.6872, 25.2797], 13); // Вильнюс

// L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
//     attribution: '&copy; OpenStreetMap'
// }).addTo(map);

