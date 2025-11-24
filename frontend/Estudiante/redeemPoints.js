
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario || !usuario.perfil?.id_estudiante) {
 window.location.href = "../formularios/login.html";
}

let premios = [];

function qs(id) { return document.getElementById(id); }

async function init() {
    await cargarCatalogo();
    await actualizarContadorCarrito();
    await actualizarPuntosUsuario();
}

async function cargarCatalogo() {
    try {
        const res = await fetch(`${API_BASE}premio.catalogo`);
        const json = await res.json();
        premios = Array.isArray(json) ? json : (json.data || []);
        renderPremios(premios);
    } catch (err) {
        console.error("Error cargarCatalogo:", err);
        const cont = qs("contenedorPremios");
        if (cont) cont.innerHTML = "<p>Error al cargar premios</p>";
    }
}

function renderPremios(lista) {
    const contenedor = qs("contenedorPremios");
    if (!contenedor) return;

    if (!lista || lista.length === 0) {
        contenedor.innerHTML = "<p>No hay premios disponibles.</p>";
        return;
    }

    contenedor.innerHTML = lista.map(p => {
        const imgURL = p.imagen 
            ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${p.imagen}` 
            : "https://via.placeholder.com/200x150?text=Sin+Imagen";

        const puntosText = (p.puntos_requeridos !== undefined) 
            ? `<i class="fi fi-rr-badge-check" style="color:#00b050;"></i> ${p.puntos_requeridos} pts` 
            : "— pts";

        const stockHTML = (p.stock !== undefined) 
            ? `<span class="stock"><i class="fi fi-rr-box-alt"></i> Stock: <span>${p.stock}</span></span>` 
            : "";

        const empresaHTML = p.empresa ? `<span class="empresa"><i class="fi fi-rr-briefcase"></i> ${escapeHtml(p.empresa)}</span>` : "";

        return `
            <div class="card">
                <img src="${imgURL}" alt="${escapeHtml(p.nombre || 'Premio')}">
                <h3>${escapeHtml(p.nombre || '')}</h3>
                <span class="puntos">${puntosText}</span>
                ${stockHTML}
                ${empresaHTML}
                <button class="add-cart" data-id="${p.id_premio}">
                    <i class="fi fi-rr-shopping-cart-add"></i> Agregar
                </button>
            </div>
        `;
    }).join('');

    contenedor.querySelectorAll(".add-cart").forEach(btn => {
        btn.addEventListener("click", () => agregarAlCarrito(Number(btn.dataset.id)));
    });
}

function escapeHtml(s) {
    if (s === null || s === undefined) return "";
    return String(s)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

async function agregarAlCarrito(idPremio) {
    const idEstudiante = usuario.perfil?.id_estudiante;
    if (!idEstudiante) return;

    const payload = { id_estudiante: idEstudiante, id_premio: idPremio, cantidad: 1 };

    try {
        const res = await fetch(`${API_BASE}canje.agregar`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        console.log("Agregar al carrito:", data);

        if (data.status === "success") {
            await actualizarContadorCarrito();
        } else {
            console.warn(data.message || "No se pudo agregar al carrito");
        }
    } catch (err) {
        console.error("Error agregarAlCarrito:", err);
    }
}


async function actualizarContadorCarrito() {
    const idEstudiante = usuario.perfil?.id_estudiante;
    if (!idEstudiante) return;

    try {
        const res = await fetch(`${API_BASE}canje.listar&id_estudiante=${encodeURIComponent(idEstudiante)}`);
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Respuesta no JSON en canje.listar:", text);
            return;
        }

        let total = 0;
        if (data && data.status === "success" && Array.isArray(data.carrito)) {
            total = data.carrito.reduce((acc, item) => acc + Number(item.cantidad || 0), 0);
        } else if (Array.isArray(data)) {
            total = data.reduce((acc, item) => acc + Number(item.cantidad || 0), 0);
        }

        const el = qs("cart-residuos-count");
        if (el) el.textContent = total;
    } catch (err) {
        console.error("Error actualizarContadorCarrito:", err);
    }
}

async function actualizarPuntosUsuario() {
    const idEstudiante = usuario.perfil?.id_estudiante;
    if (!idEstudiante) return;

    try {
        const res = await fetch(`${API_BASE}estudiante.obtener&id_estudiante=${encodeURIComponent(idEstudiante)}`);
        const json = await res.json();

        let puntos = 0;
        if (json && json.status === "success" && json.data) {
            puntos = Number(json.data.puntos_acumulados || 0);
            // Actualizar sessionStorage
            usuario.perfil.puntos_acumulados = puntos;
            sessionStorage.setItem("usuario", JSON.stringify(usuario));
        } else if (json && json.puntos_acumulados !== undefined) {
            puntos = Number(json.puntos_acumulados || 0);
            usuario.perfil.puntos_acumulados = puntos;
            sessionStorage.setItem("usuario", JSON.stringify(usuario));
        }

        const el = qs("userPoints");
        if (el) el.textContent = puntos;
    } catch (err) {
        console.error("Error actualizarPuntosUsuario:", err);
    }
}

function buscarPremios() {
    const filtro = (qs("searchPremio")?.value || "").trim().toLowerCase();
    const filtrados = premios.filter(p => (p.nombre || "").toLowerCase().includes(filtro));
    renderPremios(filtrados);
}

function abrirCarritoView() {
    window.location.href = "cartRedeempoints.html";
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

document.addEventListener("DOMContentLoaded", () => {
    init();
    actualizarImagenUsuario();
    const cart = qs("cart-residuos");
    if (cart) cart.addEventListener("click", abrirCarritoView);

    const btnBuscar = qs("btnBuscarPremio");
    if (btnBuscar) btnBuscar.addEventListener("click", buscarPremios);

    const inputBuscar = qs("searchPremio");
    if (inputBuscar) inputBuscar.addEventListener("keyup", (e) => {
        if (e.key === "Enter") buscarPremios();
    });
});
