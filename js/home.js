// Eliminar amigo

function eliminar(form) {
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
    });

    swalWithBootstrapButtons.fire({
        title: "¿Eliminar amigo?",
        text: "Dejaréis de ser amigos.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "No, cancelar",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();   // ahora sí se envía a amistad.php
        }
        // Si cancela, no hacemos nada: el amigo se queda
    });

    return false;   // frena el envío normal mientras el usuario decide
}
