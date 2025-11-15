console.log("signup.js cargado correctamente");

const form = document.getElementById("signup-form");
const mensajeBox = document.getElementById("mensaje");

const campos = {
    "cedula": "id",
    "nombre": "name",
    "apellido": "lastname",
    "correo": "email",
    "contrasena": "password",
    "confirmar": "confirm-password",
    "programa": "career"
};

// --- Función para limpiar errores visuales ---
function limpiarErrores() {
    document.querySelectorAll(".input-error").forEach(el => el.classList.remove("input-error"));
    document.querySelectorAll(".msg-error").forEach(el => el.remove());
    mensajeBox.innerHTML = "";
}

// --- Función para mostrar un error en un input ---
function mostrarError(campo, mensaje) {
    const input = document.getElementById(campos[campo]);
    if (!input) return;

    input.classList.add("input-error");

    const errorText = document.createElement("p");
    errorText.classList.add("msg-error");
    errorText.style.color = "red";
    errorText.style.marginTop = "5px";
    errorText.textContent = mensaje;

    input.insertAdjacentElement("afterend", errorText);

    input.focus();
    input.scrollIntoView({ behavior: "smooth", block: "center" });
}

// --- Función que hace el fetch al backend ---
async function registrarEstudiante(data) {
    try {
        const res = await fetch("http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=estudiante.registrar", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });

        return await res.json();
    } catch (err) {
        return { status: "error", field: null, message: "Error inesperado en el servidor." };
    }
}

// --- Evento submit ---
form.addEventListener("submit", async function (e) {
    e.preventDefault();
    limpiarErrores();

    // --- Tomar datos ---
    const data = {
        cedula: document.getElementById("id").value.trim(),
        nombre: document.getElementById("name").value.trim(),
        apellido: document.getElementById("lastname").value.trim(),
        genero: document.getElementById("gender").value,
        programa: document.getElementById("career").value,
        correo: document.getElementById("email").value.trim(),
        contrasena: document.getElementById("password").value,
        confirmar: document.getElementById("confirm-password").value,
    };

    // --- Llamar al backend ---
    const json = await registrarEstudiante(data);

    if (json.status === "error") {
        // Si hay campo específico, mostrar en ese input
        if (json.field) {
            mostrarError(json.field, json.message);

            // Manejar caso especial: contraseñas no coinciden
            if (json.field === "contrasena") {
                mostrarError("confirmar", json.message);
            }
        } else {
            // Mensaje general
            mensajeBox.innerHTML = `<p style="color:red; font-weight:bold;">${json.message}</p>`;
            mensajeBox.scrollIntoView({ behavior: "smooth" });
        }
    } else {
        // Éxito
        mensajeBox.innerHTML = `<p style="color:green; font-weight:bold;">${json.message}</p>`;
        mensajeBox.scrollIntoView({ behavior: "smooth" });
        setTimeout(() => {
    window.location.href = "login.html"; // o la ruta correcta de tu login
}, 2000);
    }
});
