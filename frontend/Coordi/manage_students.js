// manage_students.js
const API_BASE = "http://localhost/ECO-FRIENDLY-CODE-WEB/backend/index.php?route="; // AJUSTA según tu proyecto

let estudiantes = [];

// ---------- UTIL ----------
function qs(id) { return document.getElementById(id); }

// ---------- CARGAR ----------
async function cargarEstudiantes() {
  try {
    const res = await fetch(API_BASE + "estudiante.listar");
    if (!res.ok) throw new Error("HTTP " + res.status);
    const data = await res.json();
    estudiantes = Array.isArray(data) ? data : [];
    renderTabla(estudiantes);
  } catch (err) {
    console.error("Error al cargar estudiantes:", err);
    qs('resultadoTabla').innerHTML = `<tr><td colspan="8" class="empty">Error cargando estudiantes.</td></tr>`;
  }
}

function renderTabla(lista) {
  const tabla = qs('resultadoTabla');
  if (!lista || !lista.length) {
    tabla.innerHTML = `<tr><td colspan="8" class="empty">No hay resultados.</td></tr>`;
    return;
  }
  tabla.innerHTML = lista.map(e => `
    <tr>
      <td>${e.id_estudiante}</td>
      <td>${e.cedula}</td>
      <td>${e.nombre} ${e.apellido}</td>
      <td>${e.genero}</td>
      <td>${e.carrera}</td>
      <td>${e.correo}</td>
      <td>${e.puntos_acumulados ?? 0}</td>
      <td style="display:flex; gap:6px;">
        <button class="btn small" onclick='abrirModalEditar(${e.id_estudiante})'>
          <i class="fi fi-rr-pencil"></i> Editar
        </button>
      </td>
    </tr>
  `).join('');
}

// ---------- BUSCAR ----------
qs('btnBuscar')?.addEventListener('click', () => {
  const q = qs('searchId').value.trim().toLowerCase();
  if (!q) return renderTabla(estudiantes);
  const filtered = estudiantes.filter(e =>
    (e.cedula && e.cedula.toLowerCase().includes(q)) ||
    ((e.nombre + " " + e.apellido).toLowerCase().includes(q))
  );
  renderTabla(filtered);
});

// Enter en input de búsqueda
qs('searchId')?.addEventListener('keyup', (ev) => {
  if (ev.key === 'Enter') qs('btnBuscar').click();
});

// ---------- MODAL REGISTRAR ----------
qs('btnNuevo')?.addEventListener('click', abrirModalRegistrar);

function abrirModalRegistrar() {
  qs('modalRegistrar').style.display = 'flex';
  qs('formRegistrar').reset();
}

function cerrarModalRegistrar() {
  qs('modalRegistrar').style.display = 'none';
}

// Registrar envio
qs('formRegistrar').addEventListener('submit', async (e) => {
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

  if (!payload.cedula || !payload.nombre || !payload.apellido) {
    return alert("Completa los campos obligatorios.");
  }
  if (payload.contrasena !== payload.confirmar) {
    return alert("Las contraseñas no coinciden.");
  }

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

// ---------- MODAL EDITAR ----------
function abrirModalEditar(id_estudiante) {
  const est = estudiantes.find(x => Number(x.id_estudiante) === Number(id_estudiante));
  if (!est) return alert("Estudiante no encontrado");

  qs('modalEditar').style.display = 'flex';
  qs('formEditar').dataset.editId = id_estudiante;

  qs('editId').value = est.id_estudiante;
  qs('editCedula').value = est.cedula || '';
  qs('editName').value = est.nombre || '';
  qs('editLastname').value = est.apellido || '';
  qs('editGender').value = est.genero || '';
  qs('editCareer').value = est.carrera || '';
  qs('editEmail').value = est.correo || '';
}

function cerrarModalEditar() {
  qs('modalEditar').style.display = 'none';
  delete qs('formEditar').dataset.editId;
}

// Guardar cambios (editar)
qs('formEditar').addEventListener('submit', async (e) => {
  e.preventDefault();
  const id_estudiante = Number(qs('formEditar').dataset.editId);
  if (!id_estudiante) return alert("ID inválido");

  const payload = {
    id_estudiante: id_estudiante,
    nombre: qs('editName').value.trim(),
    apellido: qs('editLastname').value.trim(),
    genero: qs('editGender').value,
    cedula: qs('editCedula').value.trim(),
    carrera: qs('editCareer').value,
    correo: qs('editEmail').value.trim()
  };

  try {
    const res = await fetch(API_BASE + "estudiante.actualizar", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload)
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

// ---------- RESTABLECER CONTRASEÑA (botón dentro del modal editar) ----------
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
      // opcional: mostrar en la UI de forma temporal o copiar al portapapeles
    } else {
      alert(json.message || "Error al restablecer");
    }
  } catch (err) {
    console.error(err);
    alert("Error comunicándose con el servidor.");
  }
});

// ---------- INICIAL ----------
document.addEventListener('DOMContentLoaded', () => {
  cargarEstudiantes();
});


