const apiKey = 'dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y'; 
const map = tt.map({
    key: apiKey,
    container: 'map',
    center: coordenadas.length ? [parseFloat(coordenadas[0].lng), parseFloat(coordenadas[0].lat)] : [-99.19, 19.425],
    zoom: 13
});
map.addControl(new tt.NavigationControl());
coordenadas.forEach(punto => {
    if (punto.lat && punto.lng) {
        const markerElement = document.createElement('div');
        markerElement.className = 'custom-marker';
        markerElement.textContent = punto.id;
        new tt.Marker({ element: markerElement })
            .setLngLat([parseFloat(punto.lng), parseFloat(punto.lat)])
            .addTo(map);
    }
});