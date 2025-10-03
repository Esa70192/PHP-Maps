function mostrarNombre(input) {
    const nombre = input.files.length > 0 ? input.files[0].name : 'Ningún archivo seleccionado';
    document.getElementById('nombreArchivo').textContent = nombre;
}