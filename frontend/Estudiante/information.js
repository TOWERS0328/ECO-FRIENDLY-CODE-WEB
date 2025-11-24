const usuario = JSON.parse(sessionStorage.getItem("usuario"));
if (!usuario || !usuario.perfil?.id_estudiante || usuario.rol !== "estudiante") {
    window.location.href = "../formularios/login.html";
}

const API = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

const profilePreview = document.getElementById("profile-preview");
const profileFile = document.getElementById("profile-file");
const profileBtn = document.getElementById("profile-btn");

const cedulaInput = document.getElementById("cedula");
const nameInput = document.getElementById("name");
const lastnameInput = document.getElementById("lastname");
const careerInput = document.getElementById("career");
const genderInput = document.getElementById("gender");
const emailInput = document.getElementById("email");

const form = document.querySelector("form");
const pointsBox = document.querySelector(".points-box .points");

async function cargarPerfil() {
    try {
        const res = await fetch(`${API}estudiante.obtener&id_estudiante=${usuario.perfil.id_estudiante}`);
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

        if (profilePreview) {
            profilePreview.src = u.foto_perfil
                ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${u.foto_perfil}`
                : "../Img/9434619.jpg";
        }

    } catch (e) {
        console.error("Error cargarPerfil:", e);
    }
}

if (profileBtn && profileFile && profilePreview) {
    profileBtn.addEventListener("click", () => profileFile.click());

    profileFile.addEventListener("change", () => {
        const f = profileFile.files[0];
        if (f) profilePreview.src = URL.createObjectURL(f);
    });
}

if (form) {
    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        await actualizarDatos();
    });
}

async function actualizarDatos() {
    if (!usuario?.perfil?.id_estudiante) return;

    const fd = new FormData();
    fd.append("id_estudiante", usuario.perfil.id_estudiante);

    fd.append("nombre", nameInput.value.trim());
    fd.append("apellido", lastnameInput.value.trim());
    fd.append("cedula", cedulaInput.value.trim());
    fd.append("carrera", careerInput.value.trim());
    fd.append("genero", genderInput.value.trim());
    fd.append("correo", emailInput.value.trim());

    if (profileFile?.files.length > 0) {
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

function actualizarImagenUsuarioHeader() {
    const imgHeader = document.getElementById("header-avatar");
    if (!imgHeader) return;

    if (!usuario || !usuario.perfil) return;

    const fotoPerfil = usuario.perfil.foto_perfil
        ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${usuario.perfil.foto_perfil}`
        : "../Img/9434619.jpg";

    imgHeader.src = fotoPerfil;
}

document.addEventListener("DOMContentLoaded", () => {
    cargarPerfil();
    actualizarImagenUsuarioHeader();
});
