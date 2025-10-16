// Paso 1: Mapa de colores por tipo
const apiKey = 'dnFFEblgizXhxa7tXsNLdLT3cA7IKR0Y'; 
const map = tt.map({
    key: apiKey,
    container: 'map',
    center: coordenadas.length ? [parseFloat(coordenadas[0].lng), parseFloat(coordenadas[0].lat)] : [-99.19, 19.425],
    zoom: 13
});
map.addControl(new tt.NavigationControl());

console.log(coordenadas);

const tipoColores = {};
const coloresUsados = new Set();

// Función para generar un color aleatorio (sin repetir)
function generarColorAleatorio() {
    let color;
    do {
        color = '#' + Math.floor(Math.random() * 16777215).toString(16).padStart(6, '0');
    } while (coloresUsados.has(color));
    coloresUsados.add(color);
    return color;
}

coordenadas.forEach(punto => {
    if (punto.lat && punto.lng) {
        console.log('Tipo:', punto.tipo);
        // Paso 2: Asignar color si el tipo aún no tiene uno
        if (!tipoColores[punto.tipo]) {
            tipoColores[punto.tipo] = generarColorAleatorio();
        }

        // Paso 3: Crear el marcador
        const markerElement = document.createElement('div');
        markerElement.className = `custom-marker`; // Solo clase base
        markerElement.textContent = punto.id;

        // Aplicar color de fondo directamente desde JS
        markerElement.style.backgroundColor = tipoColores[punto.tipo];

        new tt.Marker({ element: markerElement })
            .setLngLat([parseFloat(punto.lng), parseFloat(punto.lat)])
            .addTo(map);
    }
});
