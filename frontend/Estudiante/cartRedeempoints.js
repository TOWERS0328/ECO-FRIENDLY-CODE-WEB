// ============================
// CONFIGURACIÓN
// ============================
const API = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";
const usuario = JSON.parse(sessionStorage.getItem("usuario"));

// Validación de sesión y rol estudiante
if (!usuario || !usuario.perfil?.id_estudiante || usuario.rol !== "estudiante") {
    window.location.href = "../Login/login.html";
}

function qs(id) { return document.getElementById(id); }

let itemsCarrito = [];
let puntosEstudiante = 0; // puntos reales del estudiante

// ============================
// CARGAR PUNTOS DEL ESTUDIANTE
// ============================
async function cargarPuntosEstudiante() {
    const idEstudiante = usuario.perfil.id_estudiante;
    try {
        const res = await fetch(`${API}estudiante.obtener&id_estudiante=${idEstudiante}`);
        const data = await res.json();

        if (data.status === "success") {
            puntosEstudiante = Number(data.data.puntos_acumulados || 0);
            qs("puntosEstudiante").textContent = `${puntosEstudiante} pts`;
            // actualizar sessionStorage
            usuario.perfil.puntos_acumulados = puntosEstudiante;
            sessionStorage.setItem("usuario", JSON.stringify(usuario));
        } else {
            console.warn("No se pudieron obtener los puntos del estudiante");
        }
    } catch (err) {
        console.error("Error cargarPuntosEstudiante:", err);
    }
}

// ============================
// CARGAR CARRITO DE PREMIOS
// ============================
async function cargarCarrito() {
    const idEstudiante = usuario.perfil.id_estudiante;
    try {
        const res = await fetch(`${API}canje.listar&id_estudiante=${idEstudiante}`);
        const data = await res.json();

        if (data.status === "success") {
            itemsCarrito = data.carrito;
            renderCarrito();
        } else {
            qs("cart-items").innerHTML = "<p>Tu carrito está vacío.</p>";
            qs("totalPuntos").textContent = "0 pts";
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
    let totalCarrito = 0;

    itemsCarrito.forEach(item => {
        const img = item.imagen
            ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${item.imagen}`
            : "https://via.placeholder.com/120";

        const puntosTotal = item.puntos_unitarios * item.cantidad;
        totalCarrito += puntosTotal;

        html += `
            <div class="item-carrito" data-id="${item.id_premio}">
                <div class="imagen">
                    <img src="${img}" alt="${item.nombre}">
                </div>
                
                <div class="info">
                    <h3>${item.nombre}</h3>
                    <p>Puntos por unidad: ${item.puntos_unitarios}</p>
                    <p>Stock disponible: ${item.stock}</p>
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
    qs("totalPuntos").textContent = `${totalCarrito} pts`;

    activarBotones();
}

// ============================
// ACTIVAR BOTONES DEL CARRITO
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

    if (nuevaCantidad > item.stock) {
        alert(`No hay suficiente stock para "${item.nombre}". Disponible: ${item.stock}`);
        return;
    }

    const payload = {
        id_estudiante: usuario.perfil.id_estudiante,
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
        } else {
            alert(data.message || "No se pudo actualizar la cantidad");
        }
    } catch (e) {
        console.error("actualizarCantidad error:", e);
    }
}

// ============================
// ELIMINAR ITEM
// ============================
async function eliminarItem(id_premio) {
    const payload = { id_estudiante: usuario.perfil.id_estudiante, id_premio };

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
        } else alert(data.message || "Error eliminando item");
    } catch (e) {
        console.error("eliminarItem error:", e);
    }
}

// ============================
// FINALIZAR CANJE
// ============================
async function finalizarCanje() {
    if (!itemsCarrito.length) {
        alert("Tu carrito está vacío");
        return;
    }

    const totalCarrito = itemsCarrito.reduce((acc, item) => acc + item.puntos_unitarios * item.cantidad, 0);
    if (totalCarrito > puntosEstudiante) {
        alert("No tienes suficientes puntos para este canje");
        return;
    }

    try {
        const res = await fetch(`${API}canje.finalizar`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_estudiante: usuario.perfil.id_estudiante })
        });

        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } 
        catch (e) {
            console.error("Respuesta del servidor no es JSON:", text);
            alert("Error interno del servidor. Revisa la consola.");
            return;
        }

        if (data.status === "success") {
            alert(`¡Canje realizado! Puntos usados: ${data.puntos_usados}`);
            itemsCarrito = [];
            await cargarPuntosEstudiante();
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
    cargarPuntosEstudiante();
    cargarCarrito();
    const btnFinalizar = qs("btnFinalizarCanje");
    if (btnFinalizar) btnFinalizar.addEventListener("click", finalizarCanje);
});
