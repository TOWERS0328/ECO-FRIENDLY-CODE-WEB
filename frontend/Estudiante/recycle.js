// recycle.js (CORREGIDO)
// ===============================================
// CONFIG
// ===============================================
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

// Datos del usuario (de la sesión)
const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario || !usuario.perfil?.id_estudiante || usuario.rol !== "estudiante") {
    // si no hay sesión o no es estudiante, redirigir al login
    window.location.href = "../Login/login.html";
}

// Variables globales
let residuos = [];    // catálogo
let carritoLocal = []; // datos de la canasta obtenidos del backend

// ======================
// UTIL - selector rápido
// ======================
function qs(id) { return document.getElementById(id); }

// ===============================================
// CARGAR CATÁLOGO (desde backend) y render inicial
// ===============================================
async function cargarCatalogo() {
    try {
        const res = await fetch(`${API_BASE}residuo.catalogo`);
        const data = await res.json();

        // Soportar distintos formatos de respuesta
        if (Array.isArray(data)) {
            residuos = data;
        } else if (data && data.status === "success" && Array.isArray(data.residuos)) {
            residuos = data.residuos;
        } else if (data && Array.isArray(data.data)) {
            residuos = data.data;
        } else {
            residuos = Array.isArray(data) ? data : (data.residuos || data.data || []);
        }

        renderResiduos(residuos);
        await actualizarContadorCanasta();
        actualizarUserPointsDisplay();
    } catch (err) {
        console.error("Error cargarCatalogo:", err);
        const cont = qs("contenedorResiduos");
        if (cont) cont.innerHTML = `<p>Error al cargar residuos.</p>`;
    }
}

// ===============================================
// RENDERIZAR TARJETAS DEL CATÁLOGO
// ===============================================
function renderResiduos(lista) {
    const contenedor = qs("contenedorResiduos");
    if (!contenedor) return;

    if (!lista || !lista.length) {
        contenedor.innerHTML = `<p>No hay residuos disponibles.</p>`;
        return;
    }

    contenedor.innerHTML = lista.map(r => {
        const imgURL = r.imagen
            ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${r.imagen}`
            : "https://via.placeholder.com/200x150?text=Sin+Imagen";

        return `
            <div class="card" data-id="${r.id_residuo}">
                <img src="${imgURL}" alt="${escapeHtml(r.nombre)}" />
                <h3><i class="fi fi-rr-recycle"></i> ${escapeHtml(r.nombre)}</h3>
                <p>Tipo: ${escapeHtml(r.tipo)}</p>
                <span class="puntos"><i class="fi fi-rr-star"></i> ${r.puntos} puntos</span>
                <button class="add-cart" data-id="${r.id_residuo}">
                    <i class="fi fi-rr-shopping-cart-add"></i> Agregar
                </button>
            </div>
        `;
    }).join('');

    contenedor.querySelectorAll(".add-cart").forEach(btn => {
        btn.removeEventListener("click", onClickAdd);
        btn.addEventListener("click", onClickAdd);
    });
}

function onClickAdd(e) {
    const id = e.currentTarget.dataset.id;
    agregarAlCarrito(Number(id));
}

// ===============================================
// AGREGAR AL CARRITO (backend)
// ===============================================
async function agregarAlCarrito(idResiduo, cantidad = 1) {
    if (!usuario || !usuario.perfil?.id_estudiante) {
        alert("Sesión no válida");
        return;
    }

    const payload = {
        id_estudiante: usuario.perfil.id_estudiante,
        id_residuo: idResiduo,
        cantidad: cantidad
    };

    try {
        const res = await fetch(`${API_BASE}canasta.agregar`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        console.log("Respuesta agregarCanasta:", data);

        if (data.status === "success") {
            await actualizarContadorCanasta();
            alert("Residuo agregado a la canasta ✅");
        } else {
            alert(data.message || "No se pudo agregar el residuo");
            console.warn("agregarAlCarrito:", data);
        }
    } catch (err) {
        console.error("agregarAlCarrito error:", err);
        alert("Error al agregar residuo");
    }
}

// ===============================================
// ACTUALIZAR CONTADOR DE LA CANASTA
// ===============================================
async function actualizarContadorCanasta() {
    if (!usuario || !usuario.perfil?.id_estudiante) return;

    try {
        const url = `${API_BASE}canasta.listar&id_estudiante=${encodeURIComponent(usuario.perfil.id_estudiante)}`;
        const res = await fetch(url);
        const data = await res.json();

        let totalCantidad = 0;
        if (data && data.status === "success" && Array.isArray(data.canasta)) {
            data.canasta.forEach(item => totalCantidad += Number(item.cantidad || 0));
            carritoLocal = data.canasta;
        } else {
            carritoLocal = [];
        }

        const el = qs("cart-residuos-count");
        if (el) el.textContent = totalCantidad;

    } catch (err) {
        console.error("actualizarContadorCanasta:", err);
    }
}

// ===============================================
// ABRIR VISTA CANASTA
// ===============================================
function abrirCanastaView() {
    window.location.href = "cartRecycle.html";
}

// ===============================================
// BUSCADOR
// ===============================================
function activarBuscador() {
    const input = qs("searchResiduo");
    if (!input) return;

    const filtrar = () => {
        const q = input.value.trim().toLowerCase();
        if (!q) return renderResiduos(residuos);
        const filtrados = residuos.filter(r =>
            (r.nombre || "").toLowerCase().includes(q) ||
            (r.tipo || "").toLowerCase().includes(q)
        );
        renderResiduos(filtrados);
    };

    input.addEventListener("input", filtrar);
    const btn = qs("btnBuscarResiduo");
    if (btn) btn.addEventListener("click", filtrar);
}

// ===============================================
// Mostrar puntos del usuario
// ===============================================
function actualizarUserPointsDisplay() {
    const el = qs("userPoints");
    if (!el) return;
    const pts = usuario?.perfil?.puntos_acumulados ?? 0;
    el.textContent = pts;
}

// ===============================================
// ESCAPE HTML helper
// ===============================================
function escapeHtml(str) {
    if (!str && str !== 0) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function actualizarImagenUsuario() {
    const imgHeader = document.querySelector("header .user");
    if (!imgHeader) return;

    // Verifica que el usuario y su perfil existan
    if (!usuario || !usuario.perfil) return;

    // Si tiene foto guardada, usa la ruta completa; si no, la imagen por defecto
    const fotoPerfil = usuario.perfil.foto_perfil
        ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${usuario.perfil.foto_perfil}`
        : "../Img/9434619.jpg";

    imgHeader.src = fotoPerfil;
}

// ===============================================
// INICIALIZACIÓN
// ===============================================
document.addEventListener("DOMContentLoaded", () => {
    cargarCatalogo();
    activarBuscador();
    actualizarImagenUsuario();

    const cartWrapper = qs("cart-residuos");
    if (cartWrapper) {
        cartWrapper.style.cursor = "pointer";
        cartWrapper.addEventListener("click", abrirCanastaView);
    } else {
        const counter = qs("cart-residuos-count");
        if (counter && counter.parentElement) {
            counter.parentElement.style.cursor = "pointer";
            counter.parentElement.addEventListener("click", abrirCanastaView);
        }
    }
});
