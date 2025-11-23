// cartRecycle.js (CORREGIDO)
// =========================================
// CONFIGURACIÓN
// =========================================
const API = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

// Usuario en sesión
const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario || !usuario.perfil?.id_estudiante || usuario.rol !== "estudiante") {
    // Redirigir al login si no hay sesión válida
    window.location.href = "../Login/login.html";
}

function qs(id) {
    return document.getElementById(id);
}

let itemsCarrito = []; // Contendrá los items de la canasta

// =========================================
// CARGAR CANASTA
// =========================================
async function cargarCarrito() {
    if (!usuario?.perfil?.id_estudiante) return;

    try {
        const res = await fetch(`${API}canasta.listar&id_estudiante=${usuario.perfil.id_estudiante}`);
        const data = await res.json();

        if (data.status === "success") {
            itemsCarrito = data.canasta;
            renderCarrito();
        } else {
            qs("cart-items").innerHTML = "<p>No hay items en la canasta.</p>";
        }
    } catch (e) {
        console.error("Error cargarCarrito:", e);
    }
}

// =========================================
// RENDERIZAR CARRITO EN HTML
// =========================================
function renderCarrito() {
    const cont = qs("cart-items");
    if (!itemsCarrito.length) {
        cont.innerHTML = "<p>Tu canasta está vacía.</p>";
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
            <div class="item-carrito" data-id="${item.id_residuo}">
                <div class="imagen">
                    <img src="${img}" alt="${item.nombre}">
                </div>
                
                <div class="info">
                    <h3>${item.nombre}</h3>
                    <p>Tipo: ${item.tipo}</p>
                    <p>Puntos por unidad: ${item.puntos_unitarios}</p>
                </div>

                <div class="cantidad">
                    <button class="btn-menos" data-id="${item.id_residuo}">-</button>
                    <span>${item.cantidad}</span>
                    <button class="btn-mas" data-id="${item.id_residuo}">+</button>
                </div>

                <div class="total">
                    <p>Total: <strong>${puntosTotal} pts</strong></p>
                </div>

                <button class="btn-eliminar" data-id="${item.id_residuo}">🗑</button>
            </div>
        `;
    });

    cont.innerHTML = html;
    qs("totalPuntos").textContent = `${totalGeneral} pts`;

    activarBotones();
}

// =========================================
// ACTIVAR BOTONES DINÁMICOS
// =========================================
function activarBotones() {
    document.querySelectorAll(".btn-mas").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;
            actualizarCantidad(id, +1);
        });
    });

    document.querySelectorAll(".btn-menos").forEach(btn => {
        btn.addEventListener("click", () => {
            const id = btn.dataset.id;
            actualizarCantidad(id, -1);
        });
    });

    document.querySelectorAll(".btn-eliminar").forEach(btn => {
        btn.addEventListener("click", () => {
            eliminarItem(btn.dataset.id);
        });
    });
}

// =========================================
// ACTUALIZAR CANTIDAD
// =========================================
async function actualizarCantidad(id_residuo, cambio) {
    const item = itemsCarrito.find(i => i.id_residuo == id_residuo);
    if (!item || !usuario?.perfil?.id_estudiante) return;

    const nuevaCantidad = item.cantidad + cambio;
    if (nuevaCantidad < 1) return eliminarItem(id_residuo);

    const payload = {
        id_estudiante: usuario.perfil.id_estudiante,
        id_residuo,
        cantidad: nuevaCantidad
    };

    try {
        const res = await fetch(`${API}canasta.actualizar`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.status === "success") {
            item.cantidad = nuevaCantidad;
            renderCarrito();
            actualizarContadorCanasta();
        } else {
            alert(data.message || "No se pudo actualizar la cantidad");
        }
    } catch (e) {
        console.error("actualizarCantidad error:", e);
    }
}

// =========================================
// ELIMINAR ITEM
// =========================================
async function eliminarItem(id_residuo) {
    if (!usuario?.perfil?.id_estudiante) return;

    const payload = {
        id_estudiante: usuario.perfil.id_estudiante,
        id_residuo
    };

    try {
        const res = await fetch(`${API}canasta.eliminarItem`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.status === "success") {
            itemsCarrito = itemsCarrito.filter(i => i.id_residuo != id_residuo);
            renderCarrito();
            actualizarContadorCanasta();
        } else {
            alert(data.message || "Error eliminando item");
        }
    } catch (e) {
        console.error("eliminarItem error:", e);
    }
}

// =========================================
// ACTUALIZAR CONTADOR DE LA CANASTA
// =========================================
async function actualizarContadorCanasta() {
    if (!usuario?.perfil?.id_estudiante) return;

    try {
        const res = await fetch(`${API}canasta.listar&id_estudiante=${usuario.perfil.id_estudiante}`);
        const data = await res.json();

        const el = qs("cart-count");
        if (data.status === "success" && Array.isArray(data.canasta)) {
            let totalCantidad = data.canasta.reduce((acc, item) => acc + Number(item.cantidad), 0);
            if (el) el.textContent = `Items en la canasta: ${totalCantidad}`;
        } else if (el) {
            el.textContent = "Items en la canasta: 0";
        }
    } catch (err) {
        console.error("actualizarContadorCarrito:", err);
    }
}

// =========================================
// FINALIZAR ACOPIO
// =========================================
async function finalizarAcopio() {
    if (!itemsCarrito.length) {
        alert("Tu canasta está vacía");
        return;
    }

    if (!usuario?.perfil?.id_estudiante) return;

    const formData = new FormData();
    formData.append("id_estudiante", usuario.perfil.id_estudiante);

    try {
        const res = await fetch(`${API}canasta.finalizar`, {
            method: "POST",
            body: formData
        });
        const data = await res.json();

        if (data.status === "success") {
            alert("Acopio realizado correctamente 🎉");
            window.location.href = "recycle.html";
        } else {
            alert(data.message || "Error al finalizar acopio");
        }
    } catch (e) {
        console.error("finalizarAcopio error:", e);
    }
}

// =========================================
// INICIO
// =========================================
document.addEventListener("DOMContentLoaded", () => {
    cargarCarrito();
    actualizarContadorCanasta();

    const btnFinalizar = qs("btnFinalizarAcopio");
    if (btnFinalizar) btnFinalizar.addEventListener("click", finalizarAcopio);
});
