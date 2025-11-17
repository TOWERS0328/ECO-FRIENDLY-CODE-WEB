const form = document.getElementById("login-form");
const message = document.getElementById("login-message");

form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const correo = document.getElementById("email").value;
    const contrasena = document.getElementById("password").value;

    const response = await fetch("http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=actor.login", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo, contrasena })
    });

    const data = await response.json();

    if (data.status === "success") {
        message.textContent = "¡Bienvenido!";
        // Redirigir según rol
        switch(data.rol) {
            case "estudiante":
                window.location.href = "../Estudiante/viewinicial.html";
                break;
            case "coordinador":
                window.location.href = "../Coordi/coordinator_dashboard.html";
                break;
            case "entidad":
                window.location.href = "../Entidad/dashboard.html";
                break;
        }
    } else {
        message.textContent = data.message;
    }
});

