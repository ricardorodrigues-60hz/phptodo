let timerInterval = null;
let secondsRemaining = 25 * 60;
let isRunning = false;
let mode = 'work';
let activeTodoId = null;
let activeSessionId = null;

const WORK_TIME = 25 * 60;
const BREAK_TIME = 5 * 60;

document.addEventListener('DOMContentLoaded', () => {
    initPomodoro();
});

function initPomodoro() {
    updateTimerDisplay();

    document.getElementById('timer-start-btn')?.addEventListener('click', startTimer);
    document.getElementById('timer-pause-btn')?.addEventListener('click', pauseTimer);
    document.getElementById('timer-reset-btn')?.addEventListener('click', resetTimer);
    document.getElementById('pomodoro-minimize-btn')?.addEventListener('click', () => {
        document.querySelector('.pomodoro-body')?.classList.toggle('hidden');
    });

    fetchPomodoroStats();
}

function updateTimerDisplay() {
    const m = Math.floor(secondsRemaining / 60);
    const s = secondsRemaining % 60;
    const formatted = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

    const display = document.getElementById('timer-display');
    if (display) display.textContent = formatted;
}

async function startTimer() {
    if (isRunning) return;

    if (mode === 'work' && !activeSessionId) {
        try {
            const res = await fetch('api.php?action=start_pomodoro', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ todo_id: activeTodoId })
            });
            const data = await res.json();
            if (data.data && data.data.id) {
                activeSessionId = data.data.id;
            }
        } catch (err) {
            console.error(err);
        }
    }

    isRunning = true;
    document.getElementById('timer-start-btn')?.classList.add('hidden');
    document.getElementById('timer-pause-btn')?.classList.remove('hidden');

    timerInterval = setInterval(() => {
        if (secondsRemaining > 0) {
            secondsRemaining--;
            updateTimerDisplay();
        } else {
            handleTimerComplete();
        }
    }, 1000);
}

function pauseTimer() {
    if (!isRunning) return;
    isRunning = false;
    clearInterval(timerInterval);
    document.getElementById('timer-start-btn')?.classList.remove('hidden');
    document.getElementById('timer-pause-btn')?.classList.add('hidden');
}

function resetTimer() {
    pauseTimer();
    secondsRemaining = mode === 'work' ? WORK_TIME : BREAK_TIME;
    activeSessionId = null;
    updateTimerDisplay();
}

async function handleTimerComplete() {
    pauseTimer();

    if (mode === 'work') {
        if (activeSessionId) {
            try {
                await fetch('api.php?action=complete_pomodoro', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ session_id: activeSessionId })
                });
                fetchPomodoroStats();
            } catch (err) {
                console.error(err);
            }
        }

        mode = 'break';
        secondsRemaining = BREAK_TIME;
        const badge = document.getElementById('timer-mode-badge');
        if (badge) {
            badge.textContent = 'Descanso';
            badge.className = 'timer-mode-badge break';
        }
    } else {
        mode = 'work';
        secondsRemaining = WORK_TIME;
        activeSessionId = null;
        const badge = document.getElementById('timer-mode-badge');
        if (badge) {
            badge.textContent = 'Trabalho';
            badge.className = 'timer-mode-badge work';
        }
    }

    updateTimerDisplay();
}

function setFocusTodo(id, name) {
    activeTodoId = id;
    const taskDisplay = document.getElementById('pomodoro-focused-task');
    const taskName = document.getElementById('focus-task-name');
    if (taskDisplay && taskName) {
        taskName.textContent = name;
        taskDisplay.classList.remove('hidden');
    }
}

function clearFocusTodo() {
    activeTodoId = null;
    document.getElementById('pomodoro-focused-task')?.classList.add('hidden');
}

async function fetchPomodoroStats() {
    try {
        const res = await fetch('api.php?action=pomodoro_stats');
        const data = await res.json();
        if (data.data) {
            document.getElementById('pomodoro-completed-count').textContent = data.data.completed_sessions;
            document.getElementById('stats-total-minutes').textContent = data.data.total_focus_minutes;
            document.getElementById('stats-total-sessions').textContent = data.data.completed_sessions;
        }
    } catch (err) {
        console.error(err);
    }
}

async function togglePomodoroHistory() {
    const modal = document.getElementById('pomodoro-history-modal');
    if (!modal) return;

    if (modal.classList.contains('hidden')) {
        modal.classList.remove('hidden');
        await loadPomodoroHistory();
    } else {
        modal.classList.add('hidden');
    }
}

async function loadPomodoroHistory() {
    try {
        const res = await fetch('api.php?action=pomodoro_history');
        const data = await res.json();
        const list = document.getElementById('pomodoro-history-list');
        if (!list) return;

        if (data.data && data.data.length > 0) {
            list.innerHTML = data.data.map(s => `
                <li>
                    <span>25 min ${s.todo_name ? `(${s.todo_name})` : ''}</span>
                    <small>${new Date(s.ended_at).toLocaleString('pt-BR')}</small>
                </li>
            `).join('');
        } else {
            list.innerHTML = '<li class="empty-msg">Nenhuma sessao registrada.</li>';
        }
    } catch (err) {
        console.error(err);
    }
}
