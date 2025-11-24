// ============================
// VALIDACIÓN DE SESIÓN
// ============================
const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario || !usuario.perfil?.id_asistente || usuario.rol !== "asistente") {
    window.location.href = "../formularios/login.html"; // redirige si no hay sesión válida
}

// ============================
// CONFIGURACIÓN
// ============================
const API = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

const perfilFoto = document.getElementById("perfilFoto");
const navFoto = document.getElementById("navFoto"); // Navbar
const inputFoto = document.getElementById("inputFoto");
const btnCambiarFoto = document.getElementById("btnCambiarFoto");

const perfilCedula = document.getElementById("perfilCedula");
const perfilNombre = document.getElementById("perfilNombre");
const perfilApellido = document.getElementById("perfilApellido");
const perfilCorreo = document.getElementById("perfilCorreo");
const perfilGenero = document.getElementById("perfilGenero");
const perfilArea = document.getElementById("perfilArea");

const btnActualizar = document.getElementById("btn-actualizar");

// ============================
// Cargar perfil
// ============================
async function cargarPerfil() {
    try {
        const res = await fetch(`${API}asistente.obtenerPerfil&id_asistente=${usuario.perfil.id_asistente}`);
        const data = await res.json();
        if (data.status !== "success") return alert(data.message);

        const a = data.data;
        perfilCedula.value = a.cedula || "";
        perfilNombre.value = a.nombre || "";
        perfilApellido.value = a.apellido || "";
        perfilCorreo.value = a.correo || "";
        perfilGenero.value = a.genero || "";
        perfilArea.value = a.area || "";

        const fotoUrl = a.foto_perfil 
            ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${a.foto_perfil}` 
            : "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/uploads/asistentes/default.png";

        perfilFoto.src = fotoUrl;
        navFoto.src = fotoUrl; // actualizar navbar también

    } catch (e) {
        console.error(e);
        alert("Error cargando perfil");
    }
}

// ============================
// Cambiar foto preview
// ============================
btnCambiarFoto.addEventListener("click", () => inputFoto.click());
inputFoto.addEventListener("change", () => {
    const file = inputFoto.files[0];
    if (file) {
        const url = URL.createObjectURL(file);
        perfilFoto.src = url;
        navFoto.src = url; // actualizar navbar también
    }
});

// ============================
// Actualizar perfil
// ============================
btnActualizar.addEventListener("click", async () => {
    const fd = new FormData();
    fd.append("id_asistente", usuario.perfil.id_asistente);
    fd.append("cedula", perfilCedula.value.trim());
    fd.append("nombre", perfilNombre.value.trim());
    fd.append("apellido", perfilApellido.value.trim());
    fd.append("genero", perfilGenero.value.trim());
    fd.append("area", perfilArea.value.trim());
    fd.append("correo", perfilCorreo.value.trim());

    if (inputFoto.files.length > 0) fd.append("foto", inputFoto.files[0]);

    try {
        const res = await fetch(`${API}asistente.actualizarPerfilAsistente`, {
            method: "POST",
            body: fd
        });
        const data = await res.json();
        alert(data.message);
        if (data.status === "success") {
            cargarPerfil(); // recarga foto y datos actualizados
        }
    } catch (e) {
        console.error(e);
        alert("Error al actualizar perfil");
    }
});

// ============================
// Inicialización
// ============================
document.addEventListener("DOMContentLoaded", () => {
    cargarPerfil();

    const btnCerrar = document.querySelector(".btn-cerrar-sesion");
    if (btnCerrar) btnCerrar.addEventListener("click", () => {
        sessionStorage.removeItem("usuario");
        window.location.href = "../Login/login.html";
    });
});
