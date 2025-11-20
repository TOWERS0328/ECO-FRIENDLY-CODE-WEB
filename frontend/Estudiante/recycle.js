// recycle.js (CORREGIDO)
// ===============================================
// CONFIG
// ===============================================
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

// Datos del usuario (de la sesión)
const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario) {
    // si no hay sesión, redirigir al login
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
            // fallback: si backend devuelve objeto con propiedades distintas
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

    // Delegación/añadido de eventos: aseguramos que los botones existan
    contenedor.querySelectorAll(".add-cart").forEach(btn => {
        btn.removeEventListener("click", onClickAdd); // evita duplicados si se vuelve a renderizar
        btn.addEventListener("click", onClickAdd);
    });
}

function onClickAdd(e) {
    const id = e.currentTarget.dataset.id;
    agregarAlCarrito(Number(id));
}

// ===============================================
// AGREGAR AL CARRITO (backend) - envia JSON
// ===============================================
// ===============================================
// AGREGAR AL CARRITO (backend) - envia JSON
// ===============================================
async function agregarAlCarrito(idResiduo, cantidad = 1) {
    if (!usuario || !usuario.id_estudiante) {
        alert("Sesión no válida");
        return;
    }

    const payload = {
        id_estudiante: usuario.id_estudiante,
        id_residuo: idResiduo,
        cantidad: cantidad
    };

    try {
        // Usamos la ruta canasta.agregar (asegúrate de haberla creado en routes)
        const res = await fetch(`${API_BASE}canasta.agregar`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        console.log("Respuesta agregarCanasta:", data); // DEBUG importante

        if (data.status === "success") {
            // actualizar contador y cache local
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
// ACTUALIZAR CONTADOR DE LA CANASTA (backend -> UI)
// ===============================================
async function actualizarContadorCanasta() {
    if (!usuario || !usuario.id_estudiante) return;

    try {
        const url = `${API_BASE}canasta.listar&id_estudiante=${encodeURIComponent(usuario.id_estudiante)}`;
        console.log("Llamando a:", url); // DEBUG

        const res = await fetch(url);
        const data = await res.json();
        console.log("Respuesta contar canasta:", data); // DEBUG

        let totalCantidad = 0;

        if (data && data.status === "success" && Array.isArray(data.canasta)) {
            // 🔥 SUMA LA CANTIDAD REAL DE CADA RESIDUO
            data.canasta.forEach(item => {
                totalCantidad += Number(item.cantidad || 0);
            });

            carritoLocal = data.canasta;
        } else {
            carritoLocal = [];
        }

        // 🔥 Actualiza el contador visual
        const el = qs("cart-residuos-count");
        if (el) el.textContent = totalCantidad;

    } catch (err) {
        console.error("actualizarContadorCanasta:", err);
    }
}



// ===============================================
// ABRIR VISTA CANASTA (redirige a la página del carrito)
// ===============================================
function abrirCanastaView() {
    // redirige a la vista dedicada (cartRecycle.html)
    // Ajusta la ruta si la estructura de carpetas es distinta
    window.location.href = "cartRecycle.html";
}

// ===============================================
// BUSCADOR (filtra localmente)
// ===============================================
function activarBuscador() {
    const input = qs("searchResiduo");
    if (!input) return;
    input.addEventListener("input", () => {
        const q = input.value.trim().toLowerCase();
        if (!q) {
            renderResiduos(residuos);
            return;
        }
        const filtrados = residuos.filter(r =>
            (r.nombre || "").toString().toLowerCase().includes(q) ||
            (r.tipo || "").toString().toLowerCase().includes(q)
        );
        renderResiduos(filtrados);
    });

    const btn = qs("btnBuscarResiduo");
    if (btn) btn.addEventListener("click", () => {
        const q = input.value.trim().toLowerCase();
        if (!q) return renderResiduos(residuos);
        const filtrados = residuos.filter(r =>
            (r.nombre || "").toString().toLowerCase().includes(q) ||
            (r.tipo || "").toString().toLowerCase().includes(q)
        );
        renderResiduos(filtrados);
    });
}

// ===============================================
// Mostrar puntos del usuario (intenta usar sessionStorage)
// ===============================================
function actualizarUserPointsDisplay() {
    const el = qs("userPoints");
    if (!el) return;
    const pts = usuario?.puntos_acumulados ?? usuario?.puntos ?? 0;
    el.textContent = pts;
}

// ===============================================
// ESCAPE HTML helper (seguridad mínima)
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

// ===============================================
// INICIALIZACIÓN AL CARGAR LA PÁGINA
// ===============================================
document.addEventListener("DOMContentLoaded", () => {
    cargarCatalogo();
    activarBuscador();

    // comportamiento del icono carrito: buscar el wrapper exacto (#cart-residuos)
    const cartWrapper = qs("cart-residuos");
    if (cartWrapper) {
        cartWrapper.style.cursor = "pointer";
        cartWrapper.addEventListener("click", abrirCanastaView);
    } else {
        // si no existe el wrapper, intentar por el contador (fallback)
        const counter = qs("cart-residuos-count");
        if (counter && counter.parentElement) {
            counter.parentElement.style.cursor = "pointer";
            counter.parentElement.addEventListener("click", abrirCanastaView);
        }
    }
});
