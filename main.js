// frontend/js/main.js

// Ruta base del backend
const API_URL = "http://localhost/Eco-Friendly-Web/backend/estudiantes/getAll.php";

// Función para obtener estudiantes
async function cargarEstudiantes() {
  try {
    const response = await fetch(API_URL);
    const data = await response.json();

    console.log("Respuesta del servidor:", data);

    const contenedor = document.getElementById("lista-estudiantes");

    if (data && Array.isArray(data)) {
      contenedor.innerHTML = data
        .map(
          (est) => `
          <div class="card">
            <h3>${est.nombre}</h3>
            <p>Puntos: ${est.puntos}</p>
          </div>
        `
        )
        .join("");
    } else {
      contenedor.innerHTML = `<p>No hay estudiantes registrados.</p>`;
    }
  } catch (error) {
    console.error("Error al conectar con el backend:", error);
  }
}

// Ejecutar al cargar la página
document.addEventListener("DOMContentLoaded", cargarEstudiantes);
// frontend/js/main.js

document.addEventListener("DOMContentLoaded", async () => {
  try {
    const response = await fetch("http://localhost/Eco-Friendly-Web/backend/estudiantes/getAll.php");
    const data = await response.json();

    console.log("✅ Backend conectado correctamente:", data);
  } catch (error) {
    console.error("❌ Error al conectar con el backend:", error);
  }
});
