const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

// ================================
// VARIABLES DEL MODAL (renombradas)
// ================================
const modalCanje = document.getElementById("modalCanje");
const btnCerrarModal = document.getElementById("btnCerrarModal");
const btnEntregarModal = document.getElementById("btnEntregarModal");
let canjeActualId = null;

// ================================
// CARGAR TODOS LOS CANJES PARA EL ASISTENTE
// ================================
async function cargarCanjes() {
    const tabla = document.getElementById("tabla-canjes").querySelector("tbody");
    if (!tabla) return;

    try {
        const res = await fetch(`${API_BASE}canje.listarCanjesAsistente`);
        if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
        const data = await res.json();

        if (data.status !== "success") throw new Error(data.message);

        renderTablaCanjes(data.canjes);
    } catch (error) {
        console.error("Error al cargar canjes:", error);
        tabla.innerHTML = `<tr><td colspan="7">Error al cargar los canjes</td></tr>`;
    }
}

// ================================
// RENDERIZAR TABLA DE CANJES
// ================================
function renderTablaCanjes(canjes) {
    const tbody = document.getElementById("tabla-canjes").querySelector("tbody");
    tbody.innerHTML = "";

    // Agrupar por id_canje
    const canjesAgrupados = {};
    canjes.forEach(item => {
        if (!canjesAgrupados[item.id_canje]) {
            canjesAgrupados[item.id_canje] = { ...item };
        }
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

// ================================
// ABRIR MODAL Y CARGAR DETALLES
// ================================
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

        // Datos generales
        document.getElementById("modal-estudiante").textContent = canje.nombre_estudiante + " " + canje.apellido_estudiante;
        document.getElementById("modal-cedula").textContent = canje.cedula;
        document.getElementById("modal-fecha").textContent = canje.fecha;
        document.getElementById("modal-estado").textContent = canje.estado;
        document.getElementById("modal-puntos").textContent = canje.puntos_usados;
        document.getElementById("modal-cantidad").textContent = canje.cantidad_total;

        // Lista de premios
        const listaPremios = canje.premios.map(p => `${p.nombre_premio} x${p.cantidad} (${p.puntos} pts)`).join("<br>");
        document.getElementById("modal-premio").innerHTML = listaPremios;

        modalCanje.style.display = "block";
    } catch (error) {
        console.error(error);
        alert("Error al cargar detalles del canje");
    }
}

// ================================
// CERRAR MODAL
// ================================
btnCerrarModal.addEventListener("click", () => {
    modalCanje.style.display = "none";
});

// ================================
// ENTREGAR CANJE
// ================================
btnEntregarModal.addEventListener("click", async () => {
    if (!canjeActualId) return;

    try {
        const formData = new FormData();
        formData.append("id_canje", canjeActualId);

        const res = await fetch(`${API_BASE}canje.entregarPremio`, {
            method: "POST",
            body: formData
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

// ================================
// BUSCADOR Y FILTRO
// ================================
function buscarPremios() {
    const input = document.getElementById("buscador").value.toLowerCase();
    const rows = document.querySelectorAll("#tabla-canjes tbody tr");

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

function filtrarTabla() {
    const filtro = document.getElementById("filtroEstado").value;
    const rows = document.querySelectorAll("#tabla-canjes tbody tr");

    rows.forEach(row => {
        const estado = row.cells[4].textContent.toLowerCase();
        if (filtro === "todos") {
            row.style.display = "";
        } else {
            row.style.display = (estado === filtro) ? "" : "none";
        }
    });
}

// ================================
// INICIALIZACIÓN
// ================================
document.addEventListener("DOMContentLoaded", cargarCanjes);
