function toggleAuthBox(e) {
    if (e) e.preventDefault();
    document.getElementById('login-box').classList.toggle('hidden');
    document.getElementById('register-box').classList.toggle('hidden');
}

async function handleLogin(e) {
    e.preventDefault();
    const login = document.getElementById('login-input').value.trim();
    const password = document.getElementById('password-input').value.trim();

    try {
        const res = await fetch('api.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ login, password })
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            alert(data.message || 'Erro ao realizar login.');
        }
    } catch (err) {
        console.error(err);
        alert('Erro de conexao.');
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const name = document.getElementById('reg-name').value.trim();
    const lastname = document.getElementById('reg-lastname').value.trim();
    const username = document.getElementById('reg-username').value.trim();
    const email = document.getElementById('reg-email').value.trim();
    const password = document.getElementById('reg-password').value.trim();

    try {
        const res = await fetch('api.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, lastname, username, email, password })
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = 'index.php';
        } else {
            alert(data.message || 'Erro ao realizar cadastro.');
        }
    } catch (err) {
        console.error(err);
        alert('Erro de conexao.');
    }
}

async function handleLogout() {
    try {
        await fetch('api.php?action=logout');
        window.location.href = 'login.php';
    } catch (err) {
        window.location.href = 'login.php';
    }
}
