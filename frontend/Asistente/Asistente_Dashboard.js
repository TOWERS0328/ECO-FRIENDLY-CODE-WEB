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
    const imgHeader = document.querySelector("header .user");
    if (!imgHeader) return;

    // Verifica que el usuario y su perfil existan
    if (!usuario || !usuario.perfil) return;

    // Ruta de la foto del asistente o imagen por defecto
    const fotoPerfil = usuario.perfil.foto_perfil
        ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${usuario.perfil.foto_perfil}`
        : "../../Img/9434619.jpg";

    imgHeader.src = fotoPerfil;
}

// ===============================================
// ACTUALIZAR DATOS DEL DASHBOARD
// ===============================================
async function actualizarDashboard() {
    if (!usuario || !usuario.perfil?.id_asistente) return;

    try {
        const res = await fetch(`${API_BASE}asistente.resumenDashboard`);
        const data = await res.json();

        if (data.status !== "success" || !data.data) {
            console.warn("Error al obtener resumen del dashboard:", data);
            return;
        }

        const resumen = data.data;

        qs("residuosPendientes").textContent = resumen.residuos_pendientes ?? 0;
        qs("residuosValidados").textContent = resumen.residuos_validados ?? 0;
        qs("premiosPendientes").textContent = resumen.canjes_pendientes ?? 0;
        qs("premiosEntregados").textContent = resumen.canjes_entregados ?? 0;

    } catch (err) {
        console.error("Error actualizarDashboard:", err);
    }
}

// ===============================================
// CERRAR SESIÓN
// ===============================================
function cerrarSesion() {
    sessionStorage.removeItem("usuario");
    window.location.href = "../Login/login.html";
}

// ===============================================
// INICIALIZACIÓN
// ===============================================
document.addEventListener("DOMContentLoaded", () => {
    actualizarDashboard();
    actualizarImagenUsuario();

    // Botón de cerrar sesión
    const btnCerrar = document.querySelector(".btn-cerrar-sesion");
    if (btnCerrar) btnCerrar.addEventListener("click", cerrarSesion);
});
