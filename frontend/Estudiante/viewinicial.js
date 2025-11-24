const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario || !usuario.perfil?.id_estudiante || usuario.rol !== "estudiante") {
   window.location.href = "../formularios/login.html";
}

const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";
const navFoto = document.getElementById("navFoto");

function actualizarFotoUsuario() {
    if (!usuario || !usuario.perfil) return;
    const fotoUrl = usuario.perfil.foto_perfil
        ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${usuario.perfil.foto_perfil}`
        : "../Img/9434619.jpg";
    if (navFoto) navFoto.src = fotoUrl;
}


async function cargarAcopios() {
    const tabla = document.querySelector("#tabla-acopios tbody");
    if (!tabla) return;

    try {
        const res = await fetch(`${API_BASE}acopio.listarAcopiosEstudiante&id_estudiante=${usuario.perfil.id_estudiante}`);
        const data = await res.json();

        if (data.status !== "success") throw new Error(data.message || "Error al cargar acopios");

        // Usar array vacío si no viene definido
        const acopios = data.acopios || [];

        tabla.innerHTML = "";
        acopios.forEach(a => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${a.fecha}</td>
                <td>${a.nombre_residuo}</td>
                <td>${a.tipo}</td>
                <td>${a.cantidad}</td>
                <td>${a.puntos_ganados}</td>
                <td>${a.estado}</td>
            `;
            tabla.appendChild(tr);
        });

        if(acopios.length === 0){
            tabla.innerHTML = `<tr><td colspan="6" class="empty">No hay acopios registrados</td></tr>`;
        }

    } catch (error) {
        console.error(error);
        tabla.innerHTML = `<tr><td colspan="6">Error cargando acopios</td></tr>`;
    }
}

async function cargarCanjes() {
    const tabla = document.querySelector("#tabla-canjear tbody");
    if (!tabla) return;

    try {
        const res = await fetch(`${API_BASE}canje.listarCanjesEstudiante&id_estudiante=${usuario.perfil.id_estudiante}`);
        const data = await res.json();

        if (data.status !== "success") throw new Error(data.message || "Error al cargar canjes");

        // Usar array vacío si no viene definido
        const canjes = data.historial || [];

        tabla.innerHTML = "";
        canjes.forEach(c => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${c.fecha}</td>
                <td>${c.nombre_premio}</td>
                <td>${c.cantidad}</td>
                <td>${c.puntos_gastados}</td>
                <td>${c.estado}</td>
            `;
            tabla.appendChild(tr);
        });

        if(canjes.length === 0){
            tabla.innerHTML = `<tr><td colspan="5" class="empty">No hay canjes registrados</td></tr>`;
        }

    } catch (error) {
        console.error(error);
        tabla.innerHTML = `<tr><td colspan="5">Error cargando canjes</td></tr>`;
    }
}


document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("preview-modal");
    const modalImg = document.getElementById("preview-img");
    const closeModal = document.querySelector(".close-modal");

    if (navFoto && modal && modalImg && closeModal) {
        navFoto.addEventListener("click", () => {
            modal.style.display = "flex";
            modalImg.src = navFoto.src;
        });

        closeModal.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    actualizarFotoUsuario();
    cargarAcopios();
    cargarCanjes();
});
