const API = 'http://127.0.0.1:8000';

function abrirModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function cerrarModal(id) {
    document.getElementById(id).classList.add('hidden');
}


document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => cerrarModal(btn.dataset.modal));
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) cerrarModal(overlay.id);
    });
});

// ── Toast ──
function mostrarToast(mensaje, tipo = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = mensaje;
    toast.className = `toast ${tipo}`;
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const view = btn.dataset.view;
        document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(`view-${view}`).classList.add('active');

        if (view === 'tablero') cargarSelectSprints();
    });
});


async function cargarSelectSprints() {
    const select = document.getElementById('select-sprint');
    try {
        const res = await fetch(`${API}/sprints`);
        const sprints = await res.json();
        select.innerHTML = '<option value="">— Selecciona un Sprint —</option>';
        sprints.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.nombre;
            select.appendChild(opt);
        });
    } catch {
        mostrarToast('Error al cargar los sprints', 'error');
    }
}