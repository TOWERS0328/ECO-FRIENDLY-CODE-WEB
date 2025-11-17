console.log("signup.js cargado correctamente");

const form = document.getElementById("signup-form");
const mensajeBox = document.getElementById("mensaje");

const campos = {
    "cedula": "id",
    "nombre": "name",
    "apellido": "lastname",
    "genero": "gender",
    "correo": "email",
    "contrasena": "password",
    "confirmar": "confirm-password",
    "carrera": "career"
};

// --- Limpiar errores ---
function limpiarErrores() {
    document.querySelectorAll(".input-error").forEach(el => el.classList.remove("input-error"));
    document.querySelectorAll(".msg-error").forEach(el => el.remove());
    mensajeBox.innerHTML = "";
}

// --- Mostrar error debajo del input ---
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

// --- Consumir backend ---
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
form.addEventListener("submit", async function(e) {
    e.preventDefault();
    limpiarErrores();

    // Tomar datos del formulario
    const data = {
        cedula: document.getElementById("id").value.trim(),
        nombre: document.getElementById("name").value.trim(),
        apellido: document.getElementById("lastname").value.trim(),
        genero: document.getElementById("gender").value,
        carrera: document.getElementById("career").value,
        correo: document.getElementById("email").value.trim(),
        contrasena: document.getElementById("password").value,
        confirmar: document.getElementById("confirm-password").value,
    };

    // Enviar al backend
    const json = await registrarEstudiante(data);

    if (json.status === "error") {
        if (json.field) {
            mostrarError(json.field, json.message);

            if (json.field === "contrasena") {
                mostrarError("confirmar", json.message);
            }
        } else {
            mensajeBox.innerHTML = `<p style="color:red; font-weight:bold;">${json.message}</p>`;
            mensajeBox.scrollIntoView({ behavior: "smooth" });
        }
    } else {
        mensajeBox.innerHTML = `<p style="color:green; font-weight:bold;">${json.message}</p>`;
        mensajeBox.scrollIntoView({ behavior: "smooth" });

        // Redirección con delay
        setTimeout(() => {
            window.location.href = "login.html";
        }, 2000);
    }
});
