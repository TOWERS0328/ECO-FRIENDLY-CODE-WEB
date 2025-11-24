const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

// =====================
// CARGAR ESTADÍSTICAS
// =====================
async function cargarEstadisticas() {
    try {
        const res = await fetch(`${API_BASE}dashboard.listar`);
        const data = await res.json();
        console.log(data);

        // Estadísticas principales
        document.getElementById("statStudents").textContent = data.total_estudiantes ?? "—";
        document.getElementById("statRecycle").textContent = data.total_reciclaje ?? "—";
        document.getElementById("statRewards").textContent = data.total_premios_entregados ?? "—";

        // Top carreras
        const careerContainer = document.getElementById("careerChart");
        careerContainer.innerHTML = "";
        if (Array.isArray(data.top_carreras)) {
            data.top_carreras.forEach(c => {
                const p = document.createElement("p");
                p.textContent = `${c.carrera}: ${c.total_reciclaje} reciclajes`;
                careerContainer.appendChild(p);
            });
        }

        // Top estudiantes
        const studentsContainer = document.getElementById("studentsChart");
        studentsContainer.innerHTML = "";
        if (Array.isArray(data.top_estudiantes)) {
            data.top_estudiantes.forEach(s => {
                const p = document.createElement("p");
                p.textContent = `${s.nombre} ${s.apellido}: ${s.total_reciclaje} reciclajes`;
                studentsContainer.appendChild(p);
            });
        }

    } catch (err) {
        console.error("Error cargando estadísticas:", err);
    }
}

// =====================
// MODAL FOTO USUARIO
// =====================
const navFoto = document.querySelector(".user");

function actualizarFotoUsuario() {
    const usuario = JSON.parse(sessionStorage.getItem("usuario"));
    if (!usuario || !usuario.perfil) return;
    const fotoUrl = usuario.perfil.foto_perfil
        ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${usuario.perfil.foto_perfil}`
        : "../Img/9434619.jpg";
    if (navFoto) navFoto.src = fotoUrl;
}

// =====================
// INICIALIZAR
// =====================
document.addEventListener("DOMContentLoaded", () => {
    actualizarFotoUsuario();

    // Modal
    const modal = document.getElementById("preview-modal");
    const modalImg = document.getElementById("preview-img");
    const closeModal = document.querySelector(".close-modal");

    if (navFoto && modal && modalImg && closeModal) {
        navFoto.addEventListener("click", () => {
            modal.style.display = "flex";
            modalImg.src = navFoto.src;
        });
        closeModal.addEventListener("click", () => {
            modal.style.display = "none";
        });
    }

    cargarEstadisticas();
});
