const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route=";

let estudiantes = [];
let asistentes = [];

function qs(id) { return document.getElementById(id); }

async function cargarEstudiantes() {
  try {
    const res = await fetch(API_BASE + "estudiante.listar");
    if (!res.ok) throw new Error("HTTP " + res.status);
    const data = await res.json();
    estudiantes = Array.isArray(data) ? data : [];
    renderTablaEstudiantes(estudiantes);
  } catch (err) {
    console.error("Error al cargar estudiantes:", err);
    qs('resultadoTabla').innerHTML = `<tr><td colspan="8" class="empty">Error cargando estudiantes.</td></tr>`;
  }
}

function renderTablaEstudiantes(lista) {
  const tabla = qs('resultadoTabla');
  if (!lista || !lista.length) {
    tabla.innerHTML = `<tr><td colspan="8" class="empty">No hay resultados.</td></tr>`;
    return;
  }

  tabla.innerHTML = lista.map(e => `
    <tr data-id="${e.id_estudiante}">
      <td>${e.id_estudiante}</td>
      <td>${e.cedula}</td>
      <td>${e.nombre} ${e.apellido}</td>
      <td>${e.genero}</td>
      <td>${e.carrera}</td>
      <td>${e.correo}</td>
      <td>${e.puntos_acumulados ?? 0}</td>
      <td style="display:flex; gap:6px;">
        <button class="btn small btn-edit">
          <i class="fi fi-rr-pencil"></i> Editar
        </button>
      </td>
    </tr>
  `).join('');

  // Asignar evento a todos los botones de editar
  tabla.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const tr = e.target.closest('tr');
      const id_estudiante = Number(tr.dataset.id);
      abrirModalEditar(id_estudiante);
    });
  });
}


qs('btnBuscar')?.addEventListener('click', () => {
  const q = qs('searchId').value.trim().toLowerCase();
  if (!q) return renderTablaEstudiantes(estudiantes);
  const filtered = estudiantes.filter(e =>
    (e.cedula && e.cedula.toLowerCase().includes(q)) ||
    ((e.nombre + " " + e.apellido).toLowerCase().includes(q))
  );
  renderTablaEstudiantes(filtered);
});

qs('searchId')?.addEventListener('keyup', (ev) => {
  if (ev.key === 'Enter') qs('btnBuscar').click();
});

// ---------- MODAL ESTUDIANTES ----------
qs('btnNuevo')?.addEventListener('click', abrirModalRegistrar);

function abrirModalRegistrar() {
  qs('modalRegistrar').style.display = 'flex';
  qs('formRegistrar').reset();
}

function cerrarModalRegistrar() {
  qs('modalRegistrar').style.display = 'none';
}

qs('formRegistrar')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = {
    cedula: qs('regCedula').value.trim(),
    nombre: qs('regName').value.trim(),
    apellido: qs('regLastname').value.trim(),
    genero: qs('regGender').value,
    carrera: qs('regCareer').value,
    correo: qs('regEmail').value.trim(),
    contrasena: qs('regPassword').value,
    confirmar: qs('regConfirm').value
  };

  if (!payload.cedula || !payload.nombre || !payload.apellido) return alert("Completa los campos obligatorios.");
  if (payload.contrasena !== payload.confirmar) return alert("Las contraseñas no coinciden.");

  try {
    const res = await fetch(API_BASE + "estudiante.registrar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (json.status === "success") {
      alert(json.message || "Registrado");
      cerrarModalRegistrar();
      await cargarEstudiantes();
    } else {
      alert(json.message || "Error al registrar");
    }
  } catch (err) {
    console.error(err);
    alert("Error comunicándose con el servidor.");
  }
});

function abrirModalEditar(id_estudiante) {
  console.log("Abrir modal con ID:", id_estudiante); // 🔹 debug
  const est = estudiantes.find(x => Number(x.id_estudiante) === id_estudiante);
  if (!est) return alert("Estudiante no encontrado");

  const form = qs('formEditar');
  form.dataset.editId = id_estudiante;

  qs('editId').value = est.id_estudiante;
  qs('editCedula').value = est.cedula || '';
  qs('editName').value = est.nombre || '';
  qs('editLastname').value = est.apellido || '';
  qs('editGender').value = est.genero || '';
  qs('editCareer').value = est.carrera || '';
  qs('editEmail').value = est.correo || '';

  qs('modalEditar').style.display = 'flex';

  console.log("Dataset formEditar:", form.dataset.editId); // 🔹 debug
}

function cerrarModalEditar() {
  qs('modalEditar').style.display = 'none';
  delete qs('formEditar').dataset.editId;
}

qs('formEditar')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id_estudiante = Number(qs('formEditar').dataset.editId);
  if (!id_estudiante) return alert("ID inválido");

  const formData = new FormData();
  formData.append('id_estudiante', id_estudiante);
  formData.append('nombre', qs('editName').value.trim());
  formData.append('apellido', qs('editLastname').value.trim());
  formData.append('genero', qs('editGender').value);
  formData.append('cedula', qs('editCedula').value.trim());
  formData.append('carrera', qs('editCareer').value);
  formData.append('correo', qs('editEmail').value.trim());

  try {
    const res = await fetch(API_BASE + "estudiante.actualizar", {
      method: "POST",
      body: formData
    });
    const json = await res.json();
    if (json.status === "success") {
      alert(json.message || "Actualizado");
      cerrarModalEditar();
      await cargarEstudiantes();
    } else {
      alert(json.message || "Error al actualizar");
    }
  } catch (err) {
    console.error(err);
    alert("Error comunicándose con el servidor.");
  }
});


qs('btnRestablecer')?.addEventListener('click', async () => {
  const id_estudiante = Number(qs('formEditar').dataset.editId);
  if (!id_estudiante) return alert("ID inválido");
  if (!confirm("¿Generar y asignar una contraseña temporal para este estudiante?")) return;

  try {
    const res = await fetch(API_BASE + "estudiante.restablecer", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_estudiante })
    });
    const json = await res.json();
    if (json.status === "success") {
      alert("Contraseña temporal generada: " + json.temp_password);
    } else {
      alert(json.message || "Error al restablecer");
    }
  } catch (err) {
    console.error(err);
    alert("Error comunicándose con el servidor.");
  }
});

// ===================== ASISTENTES =====================
async function cargarAsistentes() {
  try {
    const res = await fetch(API_BASE + "asistente.listar");
    if (!res.ok) throw new Error("HTTP " + res.status);
    const data = await res.json();
    asistentes = Array.isArray(data) ? data : [];
    renderTablaAsistentes(asistentes);
  } catch (err) {
    console.error("Error al cargar asistentes:", err);
    qs('resultadoTablaAyudantes').innerHTML = `<tr><td colspan="6" class="empty">Error cargando asistentes.</td></tr>`;
  }
}

function renderTablaAsistentes(lista) {
  const tabla = qs('resultadoTablaAyudantes');
  if (!lista || !lista.length) {
    tabla.innerHTML = `<tr><td colspan="7" class="empty">No hay resultados.</td></tr>`;
    return;
  }
  tabla.innerHTML = lista.map(a => `
    <tr>
      <td>${a.id_asistente}</td>
      <td>${a.cedula}</td>
      <td>${a.nombre} ${a.apellido}</td>
      <td>${a.genero}</td>
      <td>${a.area}</td>
      <td>${a.correo || ''}</td>
      <td style="display:flex; gap:6px;">
        <button class="btn small" onclick='abrirModalEditarAsistente(${a.id_asistente})'>
          <i class="fi fi-rr-pencil"></i> Editar
        </button>
      </td>
    </tr>
  `).join('');
}


qs('btnNuevoAyudante')?.addEventListener('click', abrirModalRegistrarAsistente);
function abrirModalRegistrarAsistente() {
  qs('modalRegistrarAsistente').style.display = 'flex';
  qs('formRegistrarAsistente').reset();
}
function cerrarModalRegistrarAsistente() {
  qs('modalRegistrarAsistente').style.display = 'none';
}
qs('formRegistrarAsistente')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const payload = {
    cedula: qs('regCedulaA').value.trim(),
    nombre: qs('regNameA').value.trim(),
    apellido: qs('regLastnameA').value.trim(),
    genero: qs('regGenderA').value,
    area: qs('regAreaA').value,
    correo: qs('regEmailA').value.trim(),
    contrasena: qs('regPasswordA').value,
    confirmar: qs('regConfirmA').value
  };

  if (!payload.cedula || !payload.nombre || !payload.area) return alert("Completa los campos obligatorios.");
  if (payload.contrasena !== payload.confirmar) return alert("Las contraseñas no coinciden.");

  try {
    const res = await fetch(API_BASE + "asistente.registrar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (json.status === "success") {
      alert(json.message || "Registrado");
      cerrarModalRegistrarAsistente();
      await cargarAsistentes();
    } else {
      alert(json.message || "Error al registrar");
    }
  } catch (err) {
    console.error(err);
    alert("Error comunicándose con el servidor.");
  }
});

function abrirModalEditarAsistente(id_asistente) {
  const a = asistentes.find(x => Number(x.id_asistente) === Number(id_asistente));
  if (!a) return alert("Asistente no encontrado");

  qs('modalEditarAsistente').style.display = 'flex';
  qs('formEditarAsistente').dataset.editId = id_asistente;

  qs('editIdA').value = a.id_asistente;
  qs('editCedulaA').value = a.cedula || '';
  qs('editNameA').value = a.nombre || '';
  qs('editLastnameA').value = a.apellido || '';
  qs('editGenderA').value = a.genero || '';
  qs('editAreaA').value = a.area || '';
  qs('editEmailA').value = a.correo || '';
}

function cerrarModalEditarAsistente() {
  qs('modalEditarAsistente').style.display = 'none';
  delete qs('formEditarAsistente').dataset.editId;
}

qs('formEditarAsistente')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const id_asistente = Number(qs('formEditarAsistente').dataset.editId);
  if (!id_asistente) return alert("ID inválido");

  const payload = {
    id_asistente,
    cedula: qs('editCedulaA').value.trim(),
    nombre: qs('editNameA').value.trim(),
    apellido: qs('editLastnameA').value.trim(),
    genero: qs('editGenderA').value,
    area: qs('editAreaA').value,
    correo: qs('editEmailA').value.trim()
  };

  try {
    const res = await fetch(API_BASE + "asistente.actualizar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
    });
    const json = await res.json();
    if (json.status === "success") {
      alert(json.message || "Actualizado");
      cerrarModalEditarAsistente();
      await cargarAsistentes();
    } else {
      alert(json.message || "Error al actualizar");
    }
  } catch (err) {
    console.error(err);
    alert("Error comunicándose con el servidor.");
  }
});

qs('btnRestablecerA')?.addEventListener('click', async () => {
  const id_asistente = Number(qs('formEditarAsistente').dataset.editId);
  if (!id_asistente) return alert("ID inválido");
  if (!confirm("¿Generar y asignar una contraseña temporal para este asistente?")) return;

  try {
    const res = await fetch(API_BASE + "asistente.restablecer", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id_asistente })
    });
    const json = await res.json();
    if (json.status === "success") {
      alert("Contraseña temporal generada: " + json.temp_password);
    } else {
      alert(json.message || "Error al restablecer");
    }
  } catch (err) {
    console.error(err);
    alert("Error comunicándose con el servidor.");
  }
});

// ===================== INICIAL =====================
document.addEventListener('DOMContentLoaded', () => {
  cargarEstudiantes();
  cargarAsistentes();
});
