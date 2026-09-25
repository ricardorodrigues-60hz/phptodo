document.addEventListener('DOMContentLoaded', () => {
    initSortableKanban();
});

function initSortableKanban() {
    const states = ['draft', 'todo', 'doing', 'done', 'trash'];
    states.forEach(state => {
        const col = document.getElementById(`column-${state}`);
        if (!col) return;

        new Sortable(col, {
            group: 'kanban',
            animation: 150,
            onEnd: async function (evt) {
                const itemEl = evt.item;
                const newState = evt.to.id.replace('column-', '');
                const oldState = evt.from.id.replace('column-', '');

                if (newState === oldState) return;
                const todoId = itemEl.getAttribute('data-id');

                try {
                    const res = await fetch('api.php?action=change_state', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: todoId, state: newState })
                    });
                    const data = await res.json();
                    if (data.success) {
                        itemEl.setAttribute('data-state', newState);
                        updateBadgeCounts();
                    } else {
                        evt.from.appendChild(itemEl);
                    }
                } catch (err) {
                    evt.from.appendChild(itemEl);
                }
            }
        });
    });
}

function updateBadgeCounts() {
    ['draft', 'todo', 'doing', 'done', 'trash'].forEach(state => {
        const col = document.getElementById(`column-${state}`);
        const badge = document.getElementById(`count-${state}`);
        if (col && badge) {
            badge.textContent = col.children.length;
        }
    });
}

function openCreateModal() {
    document.getElementById('todo-id').value = '';
    document.getElementById('todo-name').value = '';
    document.getElementById('todo-description').value = '';
    document.getElementById('todo-state').value = 'todo';
    document.getElementById('modal-todo-title').textContent = 'Nova Tarefa';
    document.getElementById('todo-modal').classList.remove('hidden');
}

function openEditModal(todo) {
    document.getElementById('todo-id').value = todo.id;
    document.getElementById('todo-name').value = todo.todo_name;
    document.getElementById('todo-description').value = todo.todo_description || '';
    document.getElementById('todo-state').value = todo.state;
    document.getElementById('modal-todo-title').textContent = 'Editar Tarefa';
    document.getElementById('todo-modal').classList.remove('hidden');
}

function closeTodoModal() {
    document.getElementById('todo-modal').classList.add('hidden');
}

async function handleTodoFormSubmit(e) {
    e.preventDefault();
    const id = document.getElementById('todo-id').value;
    const todo_name = document.getElementById('todo-name').value.trim();
    const todo_description = document.getElementById('todo-description').value.trim();
    const state = document.getElementById('todo-state').value;

    const action = id ? 'update_todo' : 'create_todo';
    const payload = id ? { id, todo_name, todo_description, state } : { todo_name, todo_description, state };

    try {
        const res = await fetch(`api.php?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Erro ao salvar tarefa.');
        }
    } catch (err) {
        console.error(err);
    }
}

async function deleteTodo(id) {
    if (!confirm('Mover tarefa para a lixeira?')) return;
    try {
        const res = await fetch('api.php?action=delete_todo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        }
    } catch (err) {
        console.error(err);
    }
}

async function changeTodoState(id, state) {
    try {
        const res = await fetch('api.php?action=change_state', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id, state })
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        }
    } catch (err) {
        console.error(err);
    }
}

async function emptyTrash() {
    if (!confirm('Esvaziar lixeira permanentemente?')) return;
    try {
        const res = await fetch('api.php?action=empty_trash', { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        }
    } catch (err) {
        console.error(err);
    }
}
