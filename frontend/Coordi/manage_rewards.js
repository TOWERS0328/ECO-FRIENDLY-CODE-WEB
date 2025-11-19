// ==========================================================
//  CONFIG
// ==========================================================
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

let premios = [];
let empresas = [];

// Utilidad corta para obtener elementos por ID
function qs(id) { return document.getElementById(id); }

// ==========================================================
//  CARGAR EMPRESAS
// ==========================================================
async function cargarEmpresas() {
  try {
    const res = await fetch(API_BASE + "empresa.listar");
    const data = await res.json();

    empresas = Array.isArray(data) ? data : [];
    renderTablaEmpresas();
    renderSelectEmpresas();
  } catch (err) {
    console.error("Error al cargar empresas:", err);
    const tbody = qs("tablaEmpresas").querySelector("tbody");
    tbody.innerHTML = `<tr><td colspan="6" class="empty">Error cargando empresas.</td></tr>`;
  }
}

function renderTablaEmpresas() {
  const tbody = qs("tablaEmpresas").querySelector("tbody");

  if (!empresas.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="empty">No hay empresas.</td></tr>`;
    return;
  }

  tbody.innerHTML = empresas.map(e => {
    // normalizar campos para evitar desbordes si vienen null/undefined
    const id = e.id_empresa ?? "";
    const nit = e.nit ?? "";
    const nombre = e.nombre ?? "";
    const contacto = e.contacto ?? "";
    const estado = (e.estado ?? "activo");

    return `
      <tr>
        <td>${id}</td>
        <td>${nit}</td>
        <td>${nombre}</td>
        <td>${contacto}</td>
        <td>${estado}</td>
        <td style="display:flex; gap:6px;">
          <button class="btn small green" onclick="abrirModalEditarEmpresa(${id})">Editar</button>
        </td>
      </tr>
    `;
  }).join('');
}

function renderSelectEmpresas() {
  const selReg = qs("regEmpresa");
  const selEdit = qs("editEmpresa");

  // si faltan selects (por seguridad), salir
  if (!selReg || !selEdit) return;

  [selReg, selEdit].forEach(sel => {
    sel.innerHTML = `<option value="">Seleccione empresa</option>`;
    empresas.forEach(emp => {
      sel.innerHTML += `<option value="${emp.id_empresa}">${emp.nombre}</option>`;
    });
  });
}

// ==========================================================
//  EMPRESAS ACTIVAS PARA PREMIOS (devuelve data)
 // ==========================================================
async function cargarEmpresasActivasParaPremio() {
  try {
    const res = await fetch(API_BASE + "empresa.listarActivas");
    const data = await res.json();

    const selectReg = qs("regEmpresa");
    const selectEdit = qs("editEmpresa");

    if (selectReg) {
      selectReg.innerHTML = "<option value=''>Seleccione empresa</option>";
      data.forEach(e => {
        selectReg.innerHTML += `<option value="${e.id_empresa}">${e.nombre}</option>`;
      });
    }

    if (selectEdit) {
      selectEdit.innerHTML = "<option value=''>Seleccione empresa</option>";
      data.forEach(e => {
        selectEdit.innerHTML += `<option value="${e.id_empresa}">${e.nombre}</option>`;
      });
    }

    return data;
  } catch (err) {
    console.error("Error al cargar empresas activas:", err);
    return [];
  }
}

// ==========================================================
//  CARGAR PREMIOS
// ==========================================================
async function cargarPremios() {
  try {
    const res = await fetch(API_BASE + "premio.listar");
    const data = await res.json();

    premios = Array.isArray(data) ? data : [];
    renderTablaPremios(premios);
  } catch (err) {
    console.error("Error al cargar premios:", err);
    qs("resultadoTabla").innerHTML = `<tr><td colspan="8" class="empty">Error cargando premios.</td></tr>`;
  }
}

function renderTablaPremios(lista) {
  const tabla = qs("resultadoTabla");

  if (!lista || !lista.length) {
    tabla.innerHTML = `<tr><td colspan="8" class="empty">No hay resultados.</td></tr>`;
    return;
  }

  tabla.innerHTML = lista.map(p => {
    // Construir URL absoluta de la imagen
    const imagenUrl = p.imagen 
      ? `http://localhost/ECO-FRIENDLY-CODE-WEB/backend/${p.imagen}`
      : null;

    return `
      <tr>
        <td>${p.id_premio}</td>
        <td>${p.codigo}</td>
        <td>${p.nombre}</td>
        <td>${p.puntos_requeridos}</td>
        <td>${p.stock}</td>
        <td>${p.empresa ?? ""}</td>
        <td>${p.estado ?? ""}</td>
        <td style="display:flex; gap:6px; align-items:center;">
          ${imagenUrl
            ? `<img src="${imagenUrl}" alt="${p.nombre}" style="width:50px; height:auto; border-radius:4px; margin-right:6px;">`
            : `<span style="font-size:12px; color:#888;">No imagen</span>`}
            </td>
            <td>
          <button class="btn small green" onclick="abrirModalEditarPremio(${p.id_premio})">Editar</button>
        </td>
      </tr>
    `;
  }).join('');
}


// ==========================================================
//  BUSQUEDA DE PREMIOS
// ==========================================================
qs("btnBuscar")?.addEventListener("click", () => {
  const q = qs("searchId").value.trim().toLowerCase();
  if (!q) return renderTablaPremios(premios);

  const filtered = premios.filter(p =>
    (p.codigo || "").toLowerCase().includes(q) ||
    (p.nombre || "").toLowerCase().includes(q)
  );
  renderTablaPremios(filtered);
});

qs("searchId")?.addEventListener("keyup", ev => {
  if (ev.key === "Enter") qs("btnBuscar").click();
});

// ==========================================================
//  MODALES DE PREMIOS
// ==========================================================
qs("btnNuevo")?.addEventListener("click", async () => {
  qs("modalRegistrar").style.display = "flex";
  qs("formRegistrar").reset();
  // cargar sólo empresas activas para crear premio
  await cargarEmpresasActivasParaPremio();
});

function cerrarModalRegistrarPremio() {
  qs("modalRegistrar").style.display = "none";
}

// abrir modal editar premio: carga empresas activas y selecciona la que tenga el premio (si está activa)
async function abrirModalEditarPremio(id) {
  const premio = premios.find(p => Number(p.id_premio) === Number(id));
  if (!premio) return alert("Premio no encontrado");

  const form = qs("formEditar");
  form.dataset.editId = premio.id_premio;

  qs("editId").value = premio.id_premio;
  qs("editCodigo").value = premio.codigo ?? "";
  qs("editNombre").value = premio.nombre ?? "";
  qs("editPuntos").value = premio.puntos_requeridos ?? "";
  qs("editStock").value = premio.stock ?? "";
  qs("editImagen").value = "";

  // cargar empresas activas
  const activos = await cargarEmpresasActivasParaPremio();
  const select = qs("editEmpresa");
  if (select) select.value = premio.id_empresaP ?? "";

  // mostrar preview de la imagen
  const container = qs("editImagen").parentElement.querySelector(".preview-container");
  container.innerHTML = "";
  if (premio.imagen) {
    const imgPreview = document.createElement("img");
    imgPreview.src = premio.imagen;
    imgPreview.alt = premio.nombre;
    imgPreview.style.width = "80px";
    imgPreview.style.display = "block";
    container.appendChild(imgPreview);
  }

  qs("modalEditar").style.display = "flex";
}


function cerrarModalEditarPremio() {
  qs("modalEditar").style.display = "none";
  delete qs("formEditar").dataset.editId;
  const container = qs("editImagen").parentElement.querySelector(".preview-container");
  container.innerHTML = "";
}

// ==========================================================
//  MODALES DE EMPRESAS
// ==========================================================
qs("btnNuevaEmpresa")?.addEventListener("click", () => {
  qs("modalEmpresa").style.display = "flex";
  qs("formEmpresa").reset();
});

function cerrarModalEmpresa() {
  qs("modalEmpresa").style.display = "none";
}

function abrirModalEditarEmpresa(id) {
  const emp = empresas.find(x => Number(x.id_empresa) === Number(id));
  if (!emp) return alert("Empresa no encontrada");

  qs("editEmpId").value = emp.id_empresa ?? "";
  qs("editEmpNIT").value = emp.nit ?? "";
  qs("editEmpNombre").value = emp.nombre ?? "";
  qs("editEmpLogo").value = emp.logo ?? "";
  qs("editEmpContacto").value = emp.contacto ?? "";
  qs("editEmpEstado").value = emp.estado ?? "activo";

  qs("modalEditarEmpresa").style.display = "flex";
}

function cerrarModalEditarEmpresa() {
  qs("modalEditarEmpresa").style.display = "none";
}

// ==========================================================
//  FORMULARIOS - REGISTRAR PREMIO
// ==========================================================
qs("formRegistrar").addEventListener("submit", async e => {
  e.preventDefault();

  const formData = new FormData();
  formData.append("nombre", qs("regNombre").value.trim());
  formData.append("puntos_requeridos", qs("regPuntos").value.trim());
  formData.append("stock", qs("regStock").value.trim());
  formData.append("id_empresaP", qs("regEmpresa").value);

  const imagenFile = qs("regImagen").files[0];
  if (imagenFile) formData.append("imagen", imagenFile);

  try {
    const res = await fetch(API_BASE + "premio.crear", {
      method: "POST",
      body: formData // NO JSON, usamos FormData
    });
    const json = await res.json();

    alert(json.message || "Registrado");

    if (json.status === "success") {
      cerrarModalRegistrarPremio();
      await cargarPremios();
    }
  } catch (err) {
    console.error(err);
    alert("Error con el servidor");
  }
});


// ==========================================================
//  FORMULARIOS - EDITAR PREMIO
// ==========================================================
qs("formEditar").addEventListener("submit", async e => {
  e.preventDefault();

  const formData = new FormData();
  formData.append("id_premio", qs("editId").value);
  formData.append("codigo", qs("editCodigo").value.trim());
  formData.append("nombre", qs("editNombre").value.trim());
  formData.append("puntos_requeridos", qs("editPuntos").value.trim());
  formData.append("stock", qs("editStock").value.trim());
  formData.append("id_empresaP", qs("editEmpresa").value);

  const imagenFile = qs("editImagen").files[0];
  if (imagenFile) formData.append("imagen", imagenFile);

  try {
    const res = await fetch(API_BASE + "premio.actualizar", {
      method: "POST",
      body: formData
    });
    const json = await res.json();

    alert(json.message || "Actualizado");

    if (json.status === "success") {
      cerrarModalEditarPremio();
      await cargarPremios();
    }
  } catch (err) {
    console.error(err);
    alert("Error con el servidor");
  }
});


// ==========================================================
//  FORMULARIOS - REGISTRAR EMPRESA
// ==========================================================
qs("formEmpresa").addEventListener("submit", async e => {
  e.preventDefault();

  const payload = {
    nit: qs("empNIT").value.trim(),
    nombre: qs("empNombre").value.trim(),
    logo: qs("empLogo").value.trim(),
    contacto: qs("empContacto").value.trim()
  };

  try {
    const res = await fetch(API_BASE + "empresa.registrar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    alert(json.message || "Registrado");

    if (json.status === "success") {
      cerrarModalEmpresa();
      // refrescar lista completa y también el select de empresas activas
      await cargarEmpresas();
      await cargarEmpresasActivasParaPremio();
    }

  } catch (err) {
    console.error(err);
    alert("Error con el servidor");
  }
});

// ==========================================================
//  FORMULARIOS - EDITAR EMPRESA
// ==========================================================
qs("formEditarEmpresa").addEventListener("submit", async e => {
  e.preventDefault();

  const payload = {
    id_empresa: qs("editEmpId").value,
    nit: qs("editEmpNIT").value.trim(),
    nombre: qs("editEmpNombre").value.trim(),
    logo: qs("editEmpLogo").value.trim(),
    contacto: qs("editEmpContacto").value.trim(),
    estado: qs("editEmpEstado").value
  };

  try {
    const res = await fetch(API_BASE + "empresa.actualizar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    alert(json.message || "Actualizado");

    if (json.status === "success") {
      cerrarModalEditarEmpresa();
      await cargarEmpresas();
      await cargarEmpresasActivasParaPremio(); // actualizar selects de premios
    }

  } catch (err) {
    console.error(err);
    alert("Error con el servidor");
  }
});

// ==========================================================
//  INICIAL
// ==========================================================
document.addEventListener("DOMContentLoaded", () => {
  cargarEmpresas();
  cargarPremios();
  // cargar también las activas para que los selects empiecen bien
  cargarEmpresasActivasParaPremio();
});



