// ============================
// CONFIGURACIÓN
// ============================
const API = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";
const usuario = JSON.parse(sessionStorage.getItem("usuario"));

if (!usuario) window.location.href = "../Login/login.html";

function qs(id) { return document.getElementById(id); }

let itemsCarrito = [];

// ============================
// CARGAR CARRITO DE PREMIOS
// ============================
async function cargarCarrito() {
    try {
        const res = await fetch(`${API}canje.listar&id_estudiante=${usuario.id_estudiante}`);
        const data = await res.json();

        if (data.status === "success") {
            itemsCarrito = data.carrito;
            renderCarrito();
        } else {
            qs("cart-items").innerHTML = "<p>No hay premios en el carrito.</p>";
        }
    } catch (e) {
        console.error("Error cargarCarrito:", e);
    }
}

// ============================
// RENDERIZAR CARRITO
// ============================
function renderCarrito() {
    const cont = qs("cart-items");
    if (!itemsCarrito.length) {
        cont.innerHTML = "<p>Tu carrito está vacío.</p>";
        qs("totalPuntos").textContent = "0 pts";
        return;
    }

    let html = "";
    let totalGeneral = 0;

    itemsCarrito.forEach(item => {
        const img = item.imagen
            ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${item.imagen}`
            : "https://via.placeholder.com/120";

        const puntosTotal = item.puntos_unitarios * item.cantidad;
        totalGeneral += puntosTotal;

        html += `
            <div class="item-carrito" data-id="${item.id_premio}">
                <div class="imagen">
                    <img src="${img}" alt="${item.nombre}">
                </div>
                
                <div class="info">
                    <h3>${item.nombre}</h3>
                    <p>Puntos por unidad: ${item.puntos_unitarios}</p>
                    <p>Subtotal: ${puntosTotal} pts</p>
                </div>

                <div class="cantidad">
                    <button class="btn-menos" data-id="${item.id_premio}">-</button>
                    <span>${item.cantidad}</span>
                    <button class="btn-mas" data-id="${item.id_premio}">+</button>
                </div>

                <button class="btn-eliminar" data-id="${item.id_premio}">🗑</button>
            </div>
        `;
    });

    cont.innerHTML = html;
    qs("totalPuntos").textContent = `${totalGeneral} pts`;

    activarBotones();
}

// ============================
// ACTIVAR BOTONES
// ============================
function activarBotones() {
    // Aumentar cantidad
    document.querySelectorAll(".btn-mas").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;
            actualizarCantidad(id, +1);
        });
    });

    // Disminuir cantidad
    document.querySelectorAll(".btn-menos").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;
            actualizarCantidad(id, -1);
        });
    });

    // Eliminar item
    document.querySelectorAll(".btn-eliminar").forEach(btn => {
        btn.addEventListener("click", () => {
            eliminarItem(btn.dataset.id);
        });
    });
}

// ============================
// ACTUALIZAR CANTIDAD
// ============================
async function actualizarCantidad(id_premio, cambio) {
    const item = itemsCarrito.find(i => i.id_premio == id_premio);
    if (!item) return;

    const nuevaCantidad = item.cantidad + cambio;
    if (nuevaCantidad < 1) return eliminarItem(id_premio);

    const payload = {
        id_estudiante: usuario.id_estudiante,
        id_premio: id_premio,
        cantidad: nuevaCantidad
    };

    try {
        const res = await fetch(`${API}canje.actualizar`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.status === "success") {
            item.cantidad = nuevaCantidad;
            renderCarrito();
        } else alert("No se pudo actualizar la cantidad");
    } catch (e) { console.error("actualizarCantidad error:", e); }
}

// ============================
// ELIMINAR ITEM
// ============================
async function eliminarItem(id_premio) {
    const payload = { id_estudiante: usuario.id_estudiante, id_premio };

    try {
        const res = await fetch(`${API}canje.eliminarItem`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.status === "success") {
            itemsCarrito = itemsCarrito.filter(i => i.id_premio != id_premio);
            renderCarrito();
        } else alert("Error eliminando item");
    } catch (e) { console.error("eliminarItem error:", e); }
}

// ============================
// FINALIZAR CANJE
// ============================
async function finalizarCanje() {
    if (!itemsCarrito.length) {
        alert("Tu carrito está vacío");
        return;
    }

    try {
        const res = await fetch(`${API}canje.finalizar`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_estudiante: usuario.id_estudiante })
        });

        // Verificar si la respuesta es JSON válida
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("Respuesta del servidor no es JSON:", text);
            alert("Error interno del servidor. Revisa la consola.");
            return;
        }

        if (data.status === "success") {
            alert(`¡Canje realizado! Puntos usados: ${data.puntos_usados}`);
            itemsCarrito = [];
            renderCarrito();
        } else {
            alert(data.message || "Error al finalizar canje");
        }

    } catch (e) {
        console.error("finalizarCanje error:", e);
        alert("No se pudo conectar con el servidor");
    }
}



// ============================
// INICIALIZACIÓN
// ============================
document.addEventListener("DOMContentLoaded", () => {
    cargarCarrito();
    const btnFinalizar = qs("btnFinalizarCanje");
    if (btnFinalizar) btnFinalizar.addEventListener("click", finalizarCanje);
});
