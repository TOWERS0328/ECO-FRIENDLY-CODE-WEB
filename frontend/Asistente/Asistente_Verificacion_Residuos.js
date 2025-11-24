// ================================
// VALIDACIÓN DE SESIÓN
// ================================
const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario || !usuario.perfil?.id_asistente || usuario.rol !== "asistente") {
    window.location.href = "../Login/login.html";
}

// ================================
// CONFIGURACIÓN
// ================================
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

// ================================
// VARIABLES DEL MODAL
// ================================
const modalAcopio = document.getElementById("modalAcopio");
const btnCerrarModalAcopio = document.getElementById("btnCerrarModalAcopio");
const btnValidarModal = document.getElementById("btnValidarModal");
const btnRechazarModal = document.getElementById("btnRechazarModal");
let acopioActualId = null;
let acopiosCache = [];

// ================================
// ACTUALIZAR FOTO DEL HEADER
// ================================
function actualizarFotoHeader() {
    const navFoto = document.getElementById("headerFoto");
    console.log("navFoto:", navFoto);
    console.log("usuario:", usuario);

    if (!navFoto || !usuario || !usuario.perfil) return;

    const fotoUrl = usuario.perfil.foto_perfil
        ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${usuario.perfil.foto_perfil}`
        : "../../Img/9434619.jpg";

    console.log("fotoUrl:", fotoUrl);
    navFoto.src = fotoUrl;
}


// ================================
// CARGAR ACOPIOS
// ================================
async function cargarAcopios() {
    const tabla = document.querySelector("#tabla-acopios tbody");
    if (!tabla) return;

    try {
        const res = await fetch(`${API_BASE}acopio.listarAcopiosAsistente`);
        const data = await res.json();

        if (data.status !== "success") throw new Error(data.message);

        acopiosCache = data.acopios;
        renderTablaAcopios(acopiosCache);

    } catch (error) {
        console.error("Error al cargar acopios:", error);
        tabla.innerHTML = `<tr><td colspan="7">Error al cargar los acopios</td></tr>`;
    }
}

// ================================
// RENDERIZAR TABLA DE ACOPIOS
// ================================
function renderTablaAcopios(acopios) {
    const tbody = document.querySelector("#tabla-acopios tbody");
    tbody.innerHTML = "";

    acopios.forEach(acopio => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${acopio.id_acopio}</td>
            <td>${acopio.nombre_estudiante} ${acopio.apellido_estudiante}</td>
            <td>${acopio.cedula}</td>
            <td>${acopio.puntos_totales}</td>
            <td>${acopio.fecha}</td>
            <td>${acopio.estado}</td>
            <td><button class="btn-detalles" onclick="abrirModal(${acopio.id_acopio})">Detalles</button></td>
        `;
        tbody.appendChild(tr);
    });
}

// ================================
// ABRIR MODAL
// ================================
function abrirModal(id_acopio) {
    acopioActualId = id_acopio;

    const acopio = acopiosCache.find(a => a.id_acopio == id_acopio);
    if (!acopio) return alert("Acopio no encontrado");

    document.getElementById("modal-estudiante").textContent = `${acopio.nombre_estudiante} ${acopio.apellido_estudiante}`;
    document.getElementById("modal-cedula").textContent = acopio.cedula;
    document.getElementById("modal-fecha").textContent = acopio.fecha;
    document.getElementById("modal-estado").textContent = acopio.estado;
    document.getElementById("modal-puntos").textContent = acopio.puntos_totales;

    document.getElementById("modal-residuos").innerHTML = acopio.residuos
        .map(r => `${r.tipo} - ${r.nombre_residuo} (${r.cantidad} uds)`)
        .join("<br>");

    modalAcopio.style.display = "flex";
}

// ================================
// CERRAR MODAL
// ================================
btnCerrarModalAcopio.addEventListener("click", () => {
    modalAcopio.style.display = "none";
});

// ================================
// VALIDAR / RECHAZAR ACOPIO
// ================================
btnValidarModal.addEventListener("click", async () => {
    if (!acopioActualId) return;

    try {
        const res = await fetch(`${API_BASE}acopio.actualizarEstado`, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({id_acopio: acopioActualId, estado: "validado"})
        });
        const data = await res.json();
        if (data.status === "success") {
            alert("Acopio validado correctamente");
            modalAcopio.style.display = "none";
            cargarAcopios();
        } else alert("Error al validar acopio: " + data.message);
    } catch (err) { console.error(err); alert("Error de red"); }
});

btnRechazarModal.addEventListener("click", async () => {
    if (!acopioActualId) return;

    try {
        const res = await fetch(`${API_BASE}acopio.actualizarEstado`, {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({id_acopio: acopioActualId, estado: "rechazado"})
        });
        const data = await res.json();
        if (data.status === "success") {
            alert("Acopio rechazado correctamente");
            modalAcopio.style.display = "none";
            cargarAcopios();
        } else alert("Error al rechazar acopio: " + data.message);
    } catch (err) { console.error(err); alert("Error de red"); }
});

// ================================
// BUSCADOR
// ================================
function buscarEnTabla() {
    const input = document.getElementById("buscador").value.toLowerCase();
    document.querySelectorAll("#tabla-acopios tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(input) ? "" : "none";
    });
}

// ================================
// FILTRAR POR ESTADO
// ================================
function filtrarTabla() {
    const filtro = document.getElementById("filtroEstado").value.toLowerCase();
    document.querySelectorAll("#tabla-acopios tbody tr").forEach(row => {
        const estado = row.cells[5].textContent.toLowerCase();
        row.style.display = (filtro === "todos" || estado === filtro) ? "" : "none";
    });
}

// ================================
// INICIALIZACIÓN
// ================================
document.addEventListener("DOMContentLoaded", () => {
    actualizarFotoHeader(); // ✅ actualiza la foto del header
    cargarAcopios();

    const btnCerrar = document.querySelector(".btn-cerrar-sesion");
    if (btnCerrar) btnCerrar.addEventListener("click", () => {
        sessionStorage.removeItem("usuario");
        window.location.href = "../Login/login.html";
    });
});
