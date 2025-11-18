const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

let residuos = [];

// Utilidad corta
function qs(id) { return document.getElementById(id); }

// ==========================================================
// CARGAR RESIDUOS
// ==========================================================
async function cargarResiduos() {
  try {
    const res = await fetch(API_BASE + "residuo.listar");
    const data = await res.json();
    residuos = Array.isArray(data) ? data : [];
    renderTablaResiduos(residuos);
  } catch (err) {
    console.error(err);
    qs("residuoTable").innerHTML = `<tr><td colspan="8">Error al cargar residuos</td></tr>`;
  }
}

function renderTablaResiduos(lista) {
  const tbody = qs("residuoTable");

  if (!lista.length) {
    tbody.innerHTML = `<tr><td colspan="8">No hay residuos registrados</td></tr>`;
    return;
  }

  tbody.innerHTML = lista.map(r => {
    const imagenUrl = r.imagen ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${r.imagen}` : null;
    return `
      <tr>
        <td>${r.id_residuo}</td>
        <td>${r.codigo}</td>
        <td>${r.tipo}</td>
        <td>${r.nombre}</td>
        <td>${r.puntos}</td>
        <td>${r.estado}</td>
        <td>${imagenUrl ? `<img src="${imagenUrl}" class="img-mini">` : "-"}</td>
        <td>
          <button class="btn small green" onclick="abrirModalEditarPorId(${r.id_residuo})">Editar</button>
        </td>
      </tr>
    `;
  }).join('');
}

// ==========================================================
// MODALES
// ==========================================================
function abrirModalRegistrarResiduo() {
  qs("modalRegistrarResiduo").style.display = "flex";
  qs("formRegistrarResiduo").reset();
}

function cerrarModalRegistrarResiduo() {
  qs("modalRegistrarResiduo").style.display = "none";
}

function abrirModalEditarPorId(id) {
  const residuo = residuos.find(r => Number(r.id_residuo) === Number(id));
  if (!residuo) return alert("Residuo no encontrado");

  abrirModalEditar(residuo);
}

function abrirModalEditar(residuo) {
  const modalEdit = qs("modalEditarResiduo");
  const previewContainer = modalEdit.querySelector(".preview-container");

  // Mostrar modal
  modalEdit.style.display = "flex";

  // Llenar campos
  qs("editId").value = residuo.id_residuo;
  qs("editTipo").value = residuo.tipo ?? "";
  qs("editNombre").value = residuo.nombre ?? "";
  qs("editPuntos").value = residuo.puntos ?? "";
  qs("editEstado").value = residuo.estado ?? "";

  // Preview de imagen
  previewContainer.innerHTML = "";
  if (residuo.imagen) {
    const img = document.createElement("img");
    img.src = `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${residuo.imagen}`;
    img.classList.add("img-mini");
    previewContainer.appendChild(img);
  }
}

function cerrarModalEditarResiduo() {
  const modalEdit = qs("modalEditarResiduo");
  modalEdit.style.display = "none";
  modalEdit.querySelector(".preview-container").innerHTML = "";
}

// ==========================================================
// FORMULARIO REGISTRAR
// ==========================================================
qs("formRegistrarResiduo").addEventListener("submit", async e => {
  e.preventDefault();
  const formData = new FormData();
  formData.append("tipo", qs("regTipo").value);
  formData.append("nombre", qs("regNombre").value);
  formData.append("puntos", qs("regPuntos").value);
  formData.append("estado", qs("regEstado").value);
  const file = qs("regImagen").files[0];
  if (file) formData.append("imagen", file);

  try {
    const res = await fetch(API_BASE + "residuo.crear", { method: "POST", body: formData });
    const data = await res.json();
    alert(data.message);
    if (data.status === "success") {
      cerrarModalRegistrarResiduo();
      cargarResiduos();
    }
  } catch (err) {
    console.error(err);
    alert("Error al registrar residuo");
  }
});

// ==========================================================
// FORMULARIO EDITAR
// ==========================================================
qs("formEditarResiduo").addEventListener("submit", async e => {
  e.preventDefault();
  const formData = new FormData();
  formData.append("id_residuo", qs("editId").value);
  formData.append("tipo", qs("editTipo").value);
  formData.append("nombre", qs("editNombre").value);
  formData.append("puntos", qs("editPuntos").value);
  formData.append("estado", qs("editEstado").value);
  const file = qs("editImagen").files[0];
  if (file) formData.append("imagen", file);

  try {
    const res = await fetch(API_BASE + "residuo.actualizar", { method: "POST", body: formData });
    const data = await res.json();
    alert(data.message);
    if (data.status === "success") {
      cerrarModalEditarResiduo();
      cargarResiduos();
    }
  } catch (err) {
    console.error(err);
    alert("Error al actualizar residuo");
  }
});

// ==========================================================
// EVENTOS INICIALES
// ==========================================================
qs("btnNuevoResiduo")?.addEventListener("click", abrirModalRegistrarResiduo);

qs("btnBuscarResiduo")?.addEventListener("click", () => {
  const q = qs("searchInput").value.trim().toLowerCase();
  if (!q) return renderTablaResiduos(residuos);

  const filtered = residuos.filter(r =>
    (r.nombre ?? "").toLowerCase().includes(q) ||
    (r.tipo ?? "").toLowerCase().includes(q)
  );
  renderTablaResiduos(filtered);
});

qs("searchInput")?.addEventListener("keyup", ev => {
  if (ev.key === "Enter") qs("btnBuscarResiduo").click();
});

// ==========================================================
// CERRAR MODALES CON X
// ==========================================================
document.querySelectorAll(".modal .close-btn").forEach(btn => {
  btn.addEventListener("click", e => {
    const modal = e.target.closest(".modal");
    if (modal.id === "modalRegistrarResiduo") cerrarModalRegistrarResiduo();
    if (modal.id === "modalEditarResiduo") cerrarModalEditarResiduo();
  });
});

// ==========================================================
// INICIAL
// ==========================================================
document.addEventListener("DOMContentLoaded", cargarResiduos);
