const form = document.getElementById("login-form");
const message = document.getElementById("login-message");
const popup = document.getElementById("popup-error");

function showPopupError(text) {
  popup.textContent = text;
  popup.style.display = "block";

  setTimeout(() => {
    popup.style.display = "none";
  }, 3000);
}

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const correo = document.getElementById("email").value;
  const contrasena = document.getElementById("password").value;

  try {
    const response = await fetch(
      "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=actor.login",
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ correo, contrasena }),
      }
    );

    const data = await response.json();
    console.log("Respuesta del backend:", data);

    if (data.status === "success") {
      // Guardar en sessionStorage el perfil completo
      sessionStorage.setItem(
        "usuario",
        JSON.stringify({
          id_estudiante: data.usuario.id_estudiante, // desde perfil
          nombre: data.usuario.nombre,
          rol: data.rol,
          puntos: data.usuario.puntos_acumulados,
        })
      );

      // Redirigir según rol
      switch (data.rol) {
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
    }
  } catch (err) {
    console.error(err);
    showPopupError("Error al conectar con el servidor");
  }
});
