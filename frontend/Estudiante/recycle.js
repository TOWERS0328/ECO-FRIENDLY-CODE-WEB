// ===============================================
// CONFIG
// ===============================================
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

let residuos = [];
let carrito = [];

// Selector rápido
function qs(id) { return document.getElementById(id); }

// ===============================================
// CARGAR CATÁLOGO DE RESIDUOS
// ===============================================
async function cargarCatalogo() {
    try {
        const res = await fetch(API_BASE + "residuo.catalogo");
        const data = await res.json();

        residuos = Array.isArray(data) ? data : [];
        renderResiduos(residuos);
    } catch (err) {
        console.error("Error al cargar catálogo de residuos:", err);
        qs("contenedorResiduos").innerHTML = `<p>Error al cargar residuos.</p>`;
    }
}

// ===============================================
// RENDERIZAR TARJETAS
// ===============================================
function renderResiduos(lista) {
    const contenedor = qs("contenedorResiduos");

    if (!lista.length) {
        contenedor.innerHTML = `<p>No hay residuos disponibles.</p>`;
        return;
    }

    contenedor.innerHTML = lista.map(r => {
        const imgURL = r.imagen
            ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${r.imagen}`
            : "https://via.placeholder.com/200x150?text=Sin+Imagen";

        return `
            <div class="card">
                <img src="${imgURL}" alt="${r.nombre}" />
                <h3><i class="fi fi-rr-recycle"></i> ${r.nombre}</h3>
                <p>Tipo: ${r.tipo}</p>
                <span class="puntos">
                    <i class="fi fi-rr-star"></i> ${r.puntos} puntos
                </span>
                <button class="add-cart" onclick="agregarAlCarrito(${r.id_residuo})">
                    <i class="fi fi-rr-shopping-cart-add"></i> Agregar
                </button>
            </div>
        `;
    }).join('');
}

// ===============================================
// CARRITO
// ===============================================
function agregarAlCarrito(idResiduo) {
    const residuo = residuos.find(r => r.id_residuo == idResiduo);
    if (!residuo) return;

    carrito.push(residuo);
    qs("cart-count").textContent = carrito.length;
}

// ===============================================
// BUSCADOR
// ===============================================
qs("searchResiduo")?.addEventListener("keyup", () => {
    const q = qs("searchResiduo").value.trim().toLowerCase();

    if (!q) return renderResiduos(residuos);

    const filtrados = residuos.filter(r =>
        r.nombre.toLowerCase().includes(q) || r.tipo.toLowerCase().includes(q)
    );

    renderResiduos(filtrados);
});

// ===============================================
// INICIO
// ===============================================
document.addEventListener("DOMContentLoaded", () => {
    cargarCatalogo();
});
