// ═══════════════════════════════════════════
//  sprints.js — CRUD de sprints
// ═══════════════════════════════════════════

let sprintAEliminar = null;

// ── Cargar y renderizar sprints ──
async function cargarSprints() {
    const lista = document.getElementById('lista-sprints');
    lista.innerHTML = '<div class="loading-state">Cargando sprints...</div>';
    try {
        const res = await fetch(`${API}/sprints`);
        const sprints = await res.json();

        if (!sprints.length) {
            lista.innerHTML = '<div class="loading-state">No hay sprints registrados aún. ¡Crea el primero!</div>';
            return;
        }

        lista.innerHTML = '';
        sprints.forEach(sprint => {
            lista.appendChild(renderSprintCard(sprint));
        });
    } catch {
        lista.innerHTML = '<div class="loading-state">Error al conectar con el servidor.</div>';
        mostrarToast('Error al cargar los sprints', 'error');
    }
}

function renderSprintCard(sprint) {
    const card = document.createElement('div');
    card.className = 'sprint-card';
    card.innerHTML = `
        <div class="sprint-card-header">
            <span class="sprint-nombre">${sprint.nombre}</span>
            <div class="sprint-actions">
                <button class="btn-icon edit" title="Editar" onclick="editarSprint(${sprint.id})">✎</button>
                <button class="btn-icon delete" title="Eliminar" onclick="confirmarEliminarSprint(${sprint.id}, '${sprint.nombre}')">✕</button>
            </div>
        </div>
        <div class="sprint-fechas">
            <span class="sprint-fecha"><span>Inicio</span> ${formatFecha(sprint.fecha_inicio)}</span>
            <span class="sprint-fecha"><span>Fin</span> ${formatFecha(sprint.fecha_fin)}</span>
        </div>
        <button class="sprint-ver-retro" onclick="irATablero(${sprint.id})">
            Ver tablero de retrospectiva →
        </button>
    `;
    return card;
}

function formatFecha(fecha) {
    if (!fecha) return '—';
    const [y, m, d] = fecha.split('-');
    return `${d}/${m}/${y}`;
}

// ── Ir al tablero desde la card ──
function irATablero(sprintId) {
    document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.querySelector('[data-view="tablero"]').classList.add('active');
    document.getElementById('view-tablero').classList.add('active');

    cargarSelectSprints().then(() => {
        const select = document.getElementById('select-sprint');
        select.value = sprintId;
        select.dispatchEvent(new Event('change'));
    });
}

// ── Abrir modal nuevo sprint ──
document.getElementById('btn-nuevo-sprint').addEventListener('click', () => {
    document.getElementById('modal-sprint-titulo').textContent = 'Nuevo Sprint';
    document.getElementById('sprint-id').value = '';
    document.getElementById('sprint-nombre').value = '';
    document.getElementById('sprint-inicio').value = '';
    document.getElementById('sprint-fin').value = '';
    abrirModal('modal-sprint');
});

// ── Editar sprint ──
async function editarSprint(id) {
    try {
        const res = await fetch(`${API}/sprints/${id}`);
        const sprint = await res.json();
        document.getElementById('modal-sprint-titulo').textContent = 'Editar Sprint';
        document.getElementById('sprint-id').value = sprint.id;
        document.getElementById('sprint-nombre').value = sprint.nombre;
        document.getElementById('sprint-inicio').value = sprint.fecha_inicio;
        document.getElementById('sprint-fin').value = sprint.fecha_fin;
        abrirModal('modal-sprint');
    } catch {
        mostrarToast('Error al cargar el sprint', 'error');
    }
}

// ── Guardar (crear o editar) ──
document.getElementById('btn-guardar-sprint').addEventListener('click', async () => {
    const id     = document.getElementById('sprint-id').value;
    const nombre = document.getElementById('sprint-nombre').value.trim();
    const inicio = document.getElementById('sprint-inicio').value;
    const fin    = document.getElementById('sprint-fin').value;

    if (!nombre || !inicio || !fin) {
        mostrarToast('Todos los campos son obligatorios', 'error');
        return;
    }

    const data = { nombre, fecha_inicio: inicio, fecha_fin: fin };

    try {
        const url    = id ? `${API}/sprints/${id}` : `${API}/sprints`;
        const method = id ? 'PUT' : 'POST';
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (!res.ok) throw new Error();
        cerrarModal('modal-sprint');
        mostrarToast(id ? 'Sprint actualizado ✓' : 'Sprint creado ✓');
        cargarSprints();
    } catch {
        mostrarToast('Error al guardar el sprint', 'error');
    }
});

// ── Confirmar eliminar ──
function confirmarEliminarSprint(id, nombre) {
    sprintAEliminar = id;
    document.getElementById('confirmar-mensaje').textContent =
        `¿Eliminar el sprint "${nombre}"? Se eliminarán también todos sus items de retrospectiva.`;
    abrirModal('modal-confirmar');
}

document.getElementById('btn-confirmar-eliminar').addEventListener('click', async () => {
    if (!sprintAEliminar) return;
    try {
        const res = await fetch(`${API}/sprints/${sprintAEliminar}`, { method: 'DELETE' });
        if (!res.ok) throw new Error();
        cerrarModal('modal-confirmar');
        mostrarToast('Sprint eliminado ✓');
        cargarSprints();
    } catch {
        mostrarToast('Error al eliminar el sprint', 'error');
    } finally {
        sprintAEliminar = null;
    }
});

// ── Init ──
cargarSprints();