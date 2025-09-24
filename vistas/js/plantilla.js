if (typeof $ === 'undefined') {
    console.error("jQuery no está cargado correctamente.");
} else {
    $(document).ready(function() {
        console.log("jQuery cargado y listo.");
        
        // Inicializar SweetAlert2 Toast por defecto
        window.toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    });
}
