
let sprintSeleccionado = null;
let categoriaActual    = null;
let itemAEliminar      = null;

const CATEGORIAS = [
    { key: 'logro',       label: 'Logros',       icon: '✦' },
    { key: 'impedimento', label: 'Impedimentos',  icon: '✗' },
    { key: 'accion',      label: 'Acciones',      icon: '→' },
    { key: 'comentario',  label: 'Comentarios',   icon: '◎' },
    { key: 'otro',        label: 'Otros',         icon: '○' },
];


document.getElementById('select-sprint').addEventListener('change', async function () {
    sprintSeleccionado = this.value;
    if (!sprintSeleccionado) {
        document.getElementById('tablero-board').innerHTML =
            '<div class="board-empty-state"><p>Selecciona un sprint para ver o registrar su retrospectiva</p></div>';
        document.getElementById('acciones-anteriores-section').classList.add('hidden');
        return;
    }
    await cargarTablero(sprintSeleccionado);
    await cargarAccionesAnteriores(sprintSeleccionado);
});

async function cargarTablero(sprintId) {
    const board = document.getElementById('tablero-board');
    board.innerHTML = '';

    try {
        const res   = await fetch(`${API}/retro-items/sprint/${sprintId}`);
        let items   = [];

        if (res.ok) {
            items = await res.json();
            if (!Array.isArray(items)) items = [];
        }

        const principales = CATEGORIAS.slice(0, 3);
        principales.forEach(cat => {
            const col = crearColumna(cat, items.filter(i => i.categoria === cat.key), sprintId);
            board.appendChild(col);
        });

        
        const extra = document.createElement('div');
        extra.className = 'tablero-board-extra';
        CATEGORIAS.slice(3).forEach(cat => {
            const col = crearColumna(cat, items.filter(i => i.categoria === cat.key), sprintId);
            extra.appendChild(col);
        });
        board.appendChild(extra);

    } catch {
        board.innerHTML = '<div class="board-empty-state"><p>Error al cargar el tablero.</p></div>';
        mostrarToast('Error al cargar el tablero', 'error');
    }
}

function crearColumna(cat, items, sprintId) {
    const col = document.createElement('div');
    col.className = `board-col col-${cat.key}`;
    col.innerHTML = `
        <div class="board-col-header">
            <span class="board-col-title">
                <span class="board-col-icon">${cat.icon}</span> ${cat.label}
            </span>
            <button class="btn-add-item" title="Agregar ${cat.label}" onclick="abrirModalItem('${cat.key}', ${sprintId})">+</button>
        </div>
        <div class="board-col-items" id="col-items-${cat.key}">
            ${items.length
                ? items.map(renderItemCard).join('')
                : '<div class="empty-col">Sin registros aún</div>'
            }
        </div>
    `;
    return col;
}

function renderItemCard(item) {
    let badgeHtml = '';
    if (item.categoria === 'accion') {
        if (item.cumplida === null || item.cumplida === undefined) {
            badgeHtml = '<span class="item-cumplida-badge badge-pendiente">Pendiente</span>';
        } else if (item.cumplida == 1 || item.cumplida === true) {
            badgeHtml = '<span class="item-cumplida-badge badge-cumplida">✓ Cumplida</span>';
        } else {
            badgeHtml = '<span class="item-cumplida-badge badge-no">✗ No cumplida</span>';
        }
    }

    return `
        <div class="retro-item-card item-${item.categoria}">
            <p class="retro-item-desc">${item.descripcion}</p>
            <div class="retro-item-footer">
                ${badgeHtml}
                <div class="item-actions">
                    <button class="btn-icon edit" title="Editar" onclick="editarItem(${item.id})">✎</button>
                    <button class="btn-icon delete" title="Eliminar" onclick="confirmarEliminarItem(${item.id})">✕</button>
                </div>
            </div>
        </div>
    `;
}

async function cargarAccionesAnteriores(sprintId) {
    const section = document.getElementById('acciones-anteriores-section');
    const lista   = document.getElementById('lista-acciones-anteriores');

    try {
        // Obtener todos los sprints para encontrar el anterior
        const resSprints = await fetch(`${API}/sprints`);
        const sprints    = await resSprints.json();
        const idx        = sprints.findIndex(s => s.id == sprintId);

        if (idx <= 0) {
            section.classList.add('hidden');
            return;
        }

        const sprintAnterior = sprints[idx - 1];
        const resItems = await fetch(`${API}/retro-items/sprint/${sprintAnterior.id}/accion`);

        if (!resItems.ok) { section.classList.add('hidden'); return; }

        const acciones = await resItems.json();
        if (!acciones.length) { section.classList.add('hidden'); return; }

        lista.innerHTML = acciones.map(a => `
            <div class="accion-anterior-card">
                <p class="accion-anterior-desc">${a.descripcion}</p>
                <p class="accion-anterior-meta">Sprint anterior: ${sprintAnterior.nombre}</p>
            </div>
        `).join('');

        section.classList.remove('hidden');
    } catch {
        section.classList.add('hidden');
    }
}

function abrirModalItem(categoria, sprintId) {
    categoriaActual = categoria;
    document.getElementById('modal-item-titulo').textContent = 'Nuevo Registro';
    document.getElementById('item-id').value = '';
    document.getElementById('item-categoria').value = categoria;
    document.getElementById('item-descripcion').value = '';
    document.getElementById('item-fecha-revision').value = '';
    document.querySelectorAll('input[name="cumplida"]').forEach(r => { r.checked = r.value === ''; });
    toggleCamposAccion(categoria);
    abrirModal('modal-item');
}

async function editarItem(id) {
    try {
        const res  = await fetch(`${API}/retro-items/${id}`);
        const item = await res.json();

        document.getElementById('modal-item-titulo').textContent = 'Editar Registro';
        document.getElementById('item-id').value              = item.id;
        document.getElementById('item-categoria').value       = item.categoria;
        document.getElementById('item-descripcion').value     = item.descripcion;
        document.getElementById('item-fecha-revision').value  = item.fecha_revision || '';

        const val = item.cumplida === null ? '' : (item.cumplida ? '1' : '0');
        document.querySelectorAll('input[name="cumplida"]').forEach(r => {
            r.checked = r.value === val;
        });

        toggleCamposAccion(item.categoria);
        abrirModal('modal-item');
    } catch {
        mostrarToast('Error al cargar el item', 'error');
    }
}

document.getElementById('item-categoria').addEventListener('change', function () {
    toggleCamposAccion(this.value);
});

function toggleCamposAccion(categoria) {
    const mostrar = categoria === 'accion';
    document.getElementById('grupo-cumplida').style.display       = mostrar ? 'block' : 'none';
    document.getElementById('grupo-fecha-revision').style.display = mostrar ? 'block' : 'none';
}

document.getElementById('btn-guardar-item').addEventListener('click', async () => {
    const id          = document.getElementById('item-id').value;
    const categoria   = document.getElementById('item-categoria').value;
    const descripcion = document.getElementById('item-descripcion').value.trim();
    const fechaRev    = document.getElementById('item-fecha-revision').value;
    const cumplida    = document.querySelector('input[name="cumplida"]:checked')?.value;

    if (!descripcion) {
        mostrarToast('La descripción es obligatoria', 'error');
        return;
    }

    const data = {
        sprint_id:      sprintSeleccionado,
        categoria,
        descripcion,
        fecha_revision: fechaRev || null,
        cumplida:       cumplida === '' ? null : (cumplida === '1' ? true : false)
    };

    try {
        const url    = id ? `${API}/retro-items/${id}` : `${API}/retro-items`;
        const method = id ? 'PUT' : 'POST';
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        if (!res.ok) throw new Error();
        cerrarModal('modal-item');
        mostrarToast(id ? 'Registro actualizado ✓' : 'Registro creado ✓');
        cargarTablero(sprintSeleccionado);
    } catch {
        mostrarToast('Error al guardar el registro', 'error');
    }
});


function confirmarEliminarItem(id) {
    itemAEliminar = id;
    document.getElementById('confirmar-mensaje').textContent =
        '¿Estás seguro de que deseas eliminar este registro de la retrospectiva?';
    abrirModal('modal-confirmar');
}


document.getElementById('btn-confirmar-eliminar').addEventListener('click', async () => {
    if (itemAEliminar) {
        try {
            const res = await fetch(`${API}/retro-items/${itemAEliminar}`, { method: 'DELETE' });
            if (!res.ok) throw new Error();
            cerrarModal('modal-confirmar');
            mostrarToast('Registro eliminado ✓');
            cargarTablero(sprintSeleccionado);
        } catch {
            mostrarToast('Error al eliminar el registro', 'error');
        } finally {
            itemAEliminar = null;
        }
    }
}, true);