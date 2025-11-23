/* ===========================
   TOGGLE SIDEBAR (PC/TABLET/MÓVIL)
=========================== */
const menuButton = document.querySelector(".menu-container");
const sidebar = document.getElementById("sidebar");
const mainContent = document.querySelector("main");

/* ===========================
   AJUSTE SEGÚN ANCHO DE PANTALLA
=========================== */
function checkScreenSize() {
    const width = window.innerWidth;

    if (width >= 992) {
        // PC y Laptops → sidebar en modo iconos (colapsado por defecto)
        sidebar.classList.remove("expand", "show");
        mainContent.classList.remove("menu-toggle");
        menuButton.classList.remove("active");
    } 
    else if (width >= 768) {
        // Tablets medianas → sidebar expandido fijo
        sidebar.classList.add("expand");
        sidebar.classList.remove("show");
        mainContent.classList.add("menu-toggle");
        menuButton.classList.remove("active");
    } 
    else {
        // Móviles → sidebar oculto
        sidebar.classList.remove("expand", "show");
        mainContent.classList.remove("menu-toggle");
        menuButton.classList.remove("active");
    }
}

// Ejecutar al cargar y al redimensionar
checkScreenSize();
window.addEventListener("resize", checkScreenSize);

/* ===========================
   CLICK DEL MENÚ HAMBURGUESA
=========================== */
menuButton.addEventListener("click", () => {
    const width = window.innerWidth;

    if (width >= 992) {
        // PC/Laptop → toggle entre iconos y expandido
        sidebar.classList.toggle("expand");
        mainContent.classList.toggle("menu-toggle");
    } 
    else if (width >= 768) {
        // Tablets → no hacer nada (ya está fijo expandido)
        return;
    } 
    else {
        // Móviles → mostrar/ocultar panel lateral
        sidebar.classList.toggle("show");
    }

    menuButton.classList.toggle("active");
});

/* ===========================
   CERRAR SIDEBAR AL HACER CLICK FUERA (SOLO MÓVILES)
=========================== */
document.addEventListener("click", (e) => {
    if (window.innerWidth < 768) {
        // Si el sidebar está abierto y el click no es dentro del sidebar ni del botón menú
        if (
            sidebar.classList.contains("show") &&
            !sidebar.contains(e.target) &&
            !menuButton.contains(e.target)
        ) {
            sidebar.classList.remove("show");
            menuButton.classList.remove("active");
        }
    }
});

/* ===========================
   PREVIEW DE IMAGEN USER
=========================== */
const userImg = document.querySelector(".user");
const modal = document.getElementById("preview-modal");
const modalImg = document.getElementById("preview-img");
const closeModal = document.querySelector(".close-modal");

// Abrir modal
userImg?.addEventListener("click", () => {
    modal.style.display = "block";
    modalImg.src = userImg.src;
});

// Cerrar modal con la X
closeModal?.addEventListener("click", () => {
    modal.style.display = "none";
});

// Cerrar modal haciendo clic fuera de la imagen
modal?.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});