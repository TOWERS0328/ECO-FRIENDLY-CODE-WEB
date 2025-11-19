// ===============================================
// CONFIG
// ===============================================
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

let premios = [];
let carrito = [];

function qs(id) { return document.getElementById(id); }

// ===============================================
// CARGAR CATÁLOGO DEL BACKEND
// ===============================================
async function cargarCatalogo() {
    try {
        const res = await fetch(API_BASE + "premio.catalogo");
        const data = await res.json();

        // Filtrar por seguridad: solo premios con stock > 0
        premios = (Array.isArray(data) ? data : []).filter(p => p.stock > 0);

        renderPremios(premios);
    } catch (err) {
        console.error("Error al cargar catálogo:", err);
    }
}

// ===============================================
// RENDERIZAR TARJETAS
// ===============================================
function renderPremios(lista) {
    const contenedor = qs("contenedorPremios");

    if (!lista.length) {
        contenedor.innerHTML = `<p>No hay premios disponibles.</p>`;
        return;
    }

    contenedor.innerHTML = lista.map(p => {
        const imgURL = p.imagen
            ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${p.imagen}`
            : "https://via.placeholder.com/200x150?text=Sin+Imagen";

        return `
            <div class="card">
                <img src="${imgURL}" alt="${p.nombre}" />

                <h3><i class="fi fi-rr-gift"></i> ${p.nombre}</h3>

                <span class="puntos">
                    <i class="fi fi-rr-star"></i> ${p.puntos_requeridos} puntos
                </span>

                <span class="stock">
                    <i class="fi fi-rr-database"></i> Stock: ${p.stock}
                </span>

                <button class="add-cart" onclick="agregarAlCarrito(${p.id_premio})">
                    <i class="fi fi-rr-shopping-cart-add"></i> Agregar
                </button>
            </div>
        `;
    }).join('');
}

// ===============================================
// CARRITO
// ===============================================
function agregarAlCarrito(idPremio) {
    const premio = premios.find(p => p.id_premio == idPremio);
    if (!premio) return;

    carrito.push(premio);
    qs("cart-count").textContent = carrito.length;
}

// ===============================================
// BUSCADOR
// ===============================================
qs("searchPremio")?.addEventListener("keyup", () => {
    const q = qs("searchPremio").value.trim().toLowerCase();

    if (!q) return renderPremios(premios);

    const filtrados = premios.filter(p =>
        p.nombre.toLowerCase().includes(q)
    );

    renderPremios(filtrados);
});

// ===============================================
// INICIO
// ===============================================
document.addEventListener("DOMContentLoaded", () => {
    cargarCatalogo();
});
