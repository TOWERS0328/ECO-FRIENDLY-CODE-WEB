const form = document.getElementById("login-form");
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
      // Guardar sesión del usuario
      const perfil = data.usuario;

      // Unificar id_usuario para todos los roles
      const usuarioData = {
        id_usuario: perfil.id_usuario || perfil.id_estudiante || perfil.id_coordinador || perfil.id_asistente || perfil.id_entidad,
        correo: perfil.correo,
        rol: data.rol,  // tomarlo del campo rol que viene en la respuesta
        perfil: perfil
      };

      sessionStorage.setItem("usuario", JSON.stringify(usuarioData));

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
        case "asistente":
          window.location.href = "../Asistente/Asistente_Dashboard.html";
          break;
        default:
          showPopupError("Rol no reconocido");
      }
    } else {
      showPopupError(data.message || "Correo o contraseña incorrectos");
    }
  } catch (err) {
    console.error(err);
    showPopupError("Error al conectar con el servidor");
  }
});
