// ============================
// CONFIG
// ============================
const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario) window.location.href = "../Login/login.html";

const API = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";


// ============================
// ELEMENTOS DEL DOM
// ============================
const profilePreview = document.getElementById("profile-preview");
const profileFile = document.getElementById("profile-file");
const profileBtn = document.getElementById("profile-btn");

const cedulaInput = document.getElementById("cedula");
const nameInput = document.getElementById("name");
const lastnameInput = document.getElementById("lastname");
const careerInput = document.getElementById("career");
const genderInput = document.getElementById("gender");
const emailInput = document.getElementById("email");

const passwordNueva = document.getElementById("password");
const passwordConfirm = document.getElementById("confirm-password");

const form = document.querySelector("form");
const pointsBox = document.querySelector(".points-box .points");


// ============================
// 1. CARGAR PERFIL REAL
// ============================
async function cargarPerfil() {
    try {
        // --- AHORA SÍ USAMOS UNA RUTA CORRECTA ---
        const res = await fetch(`${API}estudiante.obtener&id_estudiante=${usuario.id_estudiante}`);
        const data = await res.json();

        if (!data || data.status !== "success") {
            alert("No se encontró el estudiante");
            return;
        }

        const u = data.data;

        cedulaInput.value = u.cedula || "";
        nameInput.value = u.nombre || "";
        lastnameInput.value = u.apellido || "";
        careerInput.value = u.carrera || "";
        genderInput.value = u.genero || "";
        emailInput.value = u.correo || "";

        pointsBox.textContent = u.puntos_acumulados || 0;

        profilePreview.src = u.foto_perfil
            ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${u.foto_perfil}`
            : "../Img/9434619.jpg";

    } catch (e) {
        console.error("Error cargarPerfil:", e);
    }
}


// ============================
// 2. PREVIEW FOTO
// ============================
profileBtn.addEventListener("click", () => profileFile.click());

profileFile.addEventListener("change", () => {
    const f = profileFile.files[0];
    if (f) profilePreview.src = URL.createObjectURL(f);
});


// ============================
// 3. SUBMIT DEL FORM
// ============================
form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const quiereCambiarContra =
        passwordNueva.value.trim() !== "" ||
        passwordConfirm.value.trim() !== "";

    if (quiereCambiarContra) {
        const ok = await actualizarPassword();
        if (!ok) return;
    }

    await actualizarDatos();
});


// ============================
// 4. ACTUALIZAR DATOS GENERALES
// ============================
async function actualizarDatos() {

    const fd = new FormData();
    fd.append("id_estudiante", usuario.id_estudiante);

    fd.append("nombre", nameInput.value.trim());
    fd.append("apellido", lastnameInput.value.trim());
    fd.append("cedula", cedulaInput.value.trim());
    fd.append("carrera", careerInput.value.trim());
    fd.append("genero", genderInput.value.trim());
    fd.append("correo", emailInput.value.trim());

    if (profileFile.files.length > 0) {
        fd.append("foto", profileFile.files[0]);
    }

    try {
        const res = await fetch(`${API}estudiante.actualizar`, {
            method: "POST",
            body: fd
        });

        const data = await res.json();
        alert(data.message);

    } catch (e) {
        console.error("Error actualizarDatos:", e);
        alert("Error en el servidor");
    }
}


// ============================
// 5. ACTUALIZAR CONTRASEÑA
// ============================
async function actualizarPassword() {

    if (passwordNueva.value.trim() === "" || passwordConfirm.value.trim() === "") {
        alert("Debes llenar los dos campos de contraseña");
        return false;
    }

    if (passwordNueva.value !== passwordConfirm.value) {
        alert("Las contraseñas no coinciden");
        return false;
    }

    const payload = {
        id_estudiante: usuario.id_estudiante,
        nueva: passwordNueva.value,
        confirmar: passwordConfirm.value
    };

    try {
        const res = await fetch(`${API}estudiante.actualizarPassword`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        alert(data.message);

        return data.status === "success";

    } catch (e) {
        console.error("Error actualizarPassword:", e);
        alert("Error en el servidor");
        return false;
    }
}


// ============================
// INICIO
// ============================
document.addEventListener("DOMContentLoaded", cargarPerfil);
