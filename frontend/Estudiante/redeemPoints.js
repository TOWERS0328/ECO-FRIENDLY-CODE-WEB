// redeemPoints.js


const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";
const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario) window.location.href = "../Login/login.html";

let premios = [];

function qs(id) { return document.getElementById(id); }

// ============================
// Cargar catálogo de premios
// ============================
async function cargarCatalogo() {
    try {
        const res = await fetch(`${API_BASE}premio.catalogo`);
        const data = await res.json();
        premios = Array.isArray(data) ? data : (data.data || []);
        renderPremios(premios);
        actualizarContadorCarrito();
        mostrarPuntosUsuario();
    } catch (err) {
        console.error("Error cargarCatalogo:", err);
        qs("contenedorPremios").innerHTML = "<p>Error al cargar premios</p>";
    }
}

// ============================
// Render de tarjetas de premios
// ============================
function renderPremios(lista) {
    const contenedor = qs("contenedorPremios");
    if (!lista.length) {
        contenedor.innerHTML = "<p>No hay premios disponibles.</p>";
        return;
    }

    contenedor.innerHTML = lista.map(p => {
        const imgURL = p.imagen ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${p.imagen}` : "https://via.placeholder.com/200x150?text=Sin+Imagen";
        return `
            <div class="card">
                <img src="${imgURL}" alt="${p.nombre}">
                <h3>${p.nombre}</h3>
                <span class="puntos">${p.puntos} pts</span>
                <span class="stock">Stock: ${p.stock}</span>
                <button class="add-cart" data-id="${p.id_premio}">
                    <i class="fi fi-rr-shopping-cart-add"></i> Agregar
                </button>
            </div>
        `;
    }).join('');

    // Agregar eventos a los botones
    contenedor.querySelectorAll(".add-cart").forEach(btn => {
        btn.addEventListener("click", () => agregarAlCarrito(Number(btn.dataset.id)));
    });
}

// ============================
// Agregar premio al carrito
// ============================
async function agregarAlCarrito(idPremio) {
    if (!usuario?.id_estudiante) return alert("Sesión no válida");

    const payload = { id_estudiante: usuario.id_estudiante, id_premio: idPremio, cantidad: 1 };

    try {
        const res = await fetch(`${API_BASE}canje.agregar`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        console.log("Agregar al carrito:", data);

        if (data.status === "success") {
            alert("Premio agregado ✅");
            actualizarContadorCarrito();
        } else {
            alert(data.message || "No se pudo agregar al carrito");
        }
    } catch (err) {
        console.error("Error agregarAlCarrito:", err);
        alert("Error al agregar premio");
    }
}

// ============================
// Actualizar contador de carrito
// ============================
async function actualizarContadorCarrito() {
    if (!usuario?.id_estudiante) return;

    try {
        const res = await fetch(`${API_BASE}canje.listar&id_estudiante=${encodeURIComponent(usuario.id_estudiante)}`);
        const text = await res.text(); // <-- obtener texto crudo
        console.log("Respuesta cruda:", text);

        const data = JSON.parse(text); // intentar parsear
        console.log("listarCarrito:", data);

        let total = 0;
        if (data.status === "success") {
            total = data.carrito.reduce((acc, item) => acc + Number(item.cantidad || 0), 0);
        }

        const el = qs("cart-residuos-count");
        if (el) el.textContent = total;
    } catch (err) {
        console.error("Error actualizarContadorCarrito:", err);
    }
}


// ============================
// Mostrar puntos del usuario
// ============================
function mostrarPuntosUsuario() {
    const el = qs("userPoints");
    if (el) el.textContent = usuario?.puntos ?? 0;
}

// ============================
// Buscar premios
// ============================
function buscarPremios() {
    const filtro = qs("searchPremio").value.trim().toLowerCase();
    const filtrados = premios.filter(p => p.nombre.toLowerCase().includes(filtro));
    renderPremios(filtrados);
}

// ============================
// Abrir vista carrito
// ============================
function abrirCarritoView() {
    window.location.href = "cartRedeempoints.html"; // ajusta según tu estructura
}

// ============================
// Inicialización
// ============================
document.addEventListener("DOMContentLoaded", () => {
    cargarCatalogo();

    const cart = qs("cart-residuos");
    if (cart) cart.addEventListener("click", abrirCarritoView);

    const btnBuscar = qs("btnBuscarPremio");
    if (btnBuscar) btnBuscar.addEventListener("click", buscarPremios);

    const inputBuscar = qs("searchPremio");
    if (inputBuscar) inputBuscar.addEventListener("keyup", (e) => {
        if (e.key === "Enter") buscarPremios();
    });
});
