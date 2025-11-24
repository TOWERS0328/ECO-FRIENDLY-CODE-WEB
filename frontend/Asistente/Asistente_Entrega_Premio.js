// ===============================================
// CONFIGURACIÓN
// ===============================================
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

// Obtener datos del asistente desde sessionStorage
const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario || !usuario.perfil?.id_asistente || usuario.rol !== "asistente") {
    window.location.href = "../Login/login.html"; // redirige si no hay sesión válida
}

// ===============================================
// UTIL - selector rápido
// ===============================================
function qs(id) { return document.getElementById(id); }

// ===============================================
// ACTUALIZAR IMAGEN DEL HEADER
// ===============================================
function actualizarImagenUsuario() {
    const imgHeader = document.getElementById("header-avatar");
    if (!imgHeader) return;

    // Verifica que el usuario y su perfil existan
    if (!usuario || !usuario.perfil) return;

    const fotoPerfil = usuario.perfil.foto_perfil
        ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${usuario.perfil.foto_perfil}`
        : "../../Img/9434619.jpg";

    imgHeader.src = fotoPerfil;
}


// ===============================================
// CERRAR SESIÓN
// ===============================================
function cerrarSesion() {
    sessionStorage.removeItem("usuario");
    window.location.href = "../Login/login.html";
}

// ===============================================
// VARIABLES DEL MODAL
// ===============================================
const modalCanje = qs("modalCanje");
const btnCerrarModal = qs("btnCerrarModal");
const btnEntregarModal = qs("btnEntregarModal");
let canjeActualId = null;

// ===============================================
// CARGAR TODOS LOS CANJES
// ===============================================
async function cargarCanjes() {
    const tbody = document.querySelector("#tabla-canjes tbody");
    if (!tbody) return;

    try {
        const res = await fetch(`${API_BASE}canje.listarCanjesAsistente`);
        const data = await res.json();

        if (data.status !== "success") throw new Error(data.message);

        renderTablaCanjes(data.canjes || []);
    } catch (error) {
        console.error("Error al cargar canjes:", error);
        tbody.innerHTML = `<tr><td colspan="7">Error al cargar los canjes</td></tr>`;
    }
}

// ===============================================
// RENDERIZAR TABLA DE CANJES
// ===============================================
function renderTablaCanjes(canjes) {
    const tbody = document.querySelector("#tabla-canjes tbody");
    tbody.innerHTML = "";

    const canjesAgrupados = {};
    canjes.forEach(item => {
        if (!canjesAgrupados[item.id_canje]) {
            canjesAgrupados[item.id_canje] = { ...item, premios: [] };
        }
        canjesAgrupados[item.id_canje].premios.push({
            nombre_premio: item.nombre_premio,
            cantidad: item.cantidad,
            puntos: item.puntos_gastados
        });
    });

    Object.values(canjesAgrupados).forEach(canje => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${canje.id_canje}</td>
            <td>${canje.nombre_estudiante} ${canje.apellido_estudiante}</td>
            <td>${canje.puntos_usados}</td>
            <td>${canje.fecha}</td>
            <td>${canje.estado}</td>
            <td>
                <button class="btn-detalles" onclick="abrirModal(${canje.id_canje})">Detalles</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// ===============================================
// ABRIR MODAL Y CARGAR DETALLES
// ===============================================
async function abrirModal(id_canje) {
    canjeActualId = id_canje;

    try {
        const res = await fetch(`${API_BASE}canje.detalles&id_canje=${id_canje}`);
        const data = await res.json();

        if (data.status !== "success") {
            alert("Error al obtener detalles: " + data.message);
            return;
        }

        const canje = data.detalles;

        qs("modal-estudiante").textContent = canje.nombre_estudiante + " " + canje.apellido_estudiante;
        qs("modal-cedula").textContent = canje.cedula;
        qs("modal-fecha").textContent = canje.fecha;
        qs("modal-estado").textContent = canje.estado;
        qs("modal-puntos").textContent = canje.puntos_usados;
        qs("modal-cantidad").textContent = canje.premios.reduce((acc, p) => acc + p.cantidad, 0);

        qs("modal-premio").innerHTML = canje.premios
            .map(p => `${p.nombre_premio} x${p.cantidad} (${p.puntos} pts)`)
            .join("<br>");

        modalCanje.style.display = "flex";
    } catch (error) {
        console.error(error);
        alert("Error al cargar detalles del canje");
    }
}

// ===============================================
// CERRAR MODAL
// ===============================================
btnCerrarModal.addEventListener("click", () => {
    modalCanje.style.display = "none";
});

// ===============================================
// ENTREGAR CANJE
// ===============================================
btnEntregarModal.addEventListener("click", async () => {
    if (!canjeActualId) return;

    try {
        const res = await fetch(`${API_BASE}canje.entregarPremio`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_canje: canjeActualId })
        });
        const data = await res.json();

        if (data.status === "success") {
            alert("Canje entregado correctamente");
            modalCanje.style.display = "none";
            cargarCanjes();
        } else {
            alert("Error al entregar canje: " + data.message);
        }
    } catch (error) {
        console.error(error);
        alert("Error de red al entregar canje");
    }
});

// ===============================================
// BUSCADOR Y FILTRO
// ===============================================
function buscarPremios() {
    const input = qs("buscador").value.toLowerCase();
    const rows = document.querySelectorAll("#tabla-canjes tbody tr");
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(input) ? "" : "none";
    });
}

function filtrarTabla() {
    const filtro = qs("filtroEstado").value;
    const rows = document.querySelectorAll("#tabla-canjes tbody tr");
    rows.forEach(row => {
        const estado = row.cells[4].textContent.toLowerCase();
        row.style.display = (filtro === "todos" || estado === filtro) ? "" : "none";
    });
}

// ===============================================
// INICIALIZACIÓN
// ===============================================
document.addEventListener("DOMContentLoaded", () => {
    actualizarImagenUsuario();
    cargarCanjes();

    const btnCerrar = document.querySelector(".btn-cerrar-sesion");
    if (btnCerrar) btnCerrar.addEventListener("click", cerrarSesion);
});
