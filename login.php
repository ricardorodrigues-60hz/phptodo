<?php
require_once __DIR__ . '/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Cadastro - TodoPomodoro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div id="toast-container" class="toast-container"></div>

    <div class="auth-container">
        <!-- Formulario Login -->
        <div id="login-box" class="auth-card">
            <div class="auth-header">
                <h2>Login</h2>
                <p>Acesse suas tarefas e Pomodoro</p>
            </div>
            <form onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label for="login-input">Email ou Usuario</label>
                    <input type="text" id="login-input" class="form-control" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password-input">Senha</label>
                    <input type="password" id="password-input" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Entrar</button>
            </form>
            <div class="auth-footer">
                <p>Nao tem uma conta? <a href="#" onclick="toggleAuthBox(event)">Cadastre-se</a></p>
            </div>
        </div>

        <!-- Formulario Cadastro -->
        <div id="register-box" class="auth-card hidden">
            <div class="auth-header">
                <h2>Criar Conta</h2>
                <p>Cadastre-se no sistema</p>
            </div>
            <form onsubmit="handleRegister(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label for="reg-name">Nome</label>
                        <input type="text" id="reg-name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="reg-lastname">Sobrenome</label>
                        <input type="text" id="reg-lastname" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reg-username">Nome de Usuario</label>
                    <input type="text" id="reg-username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="reg-email">E-mail</label>
                    <input type="email" id="reg-email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="reg-password">Senha (minimo 6 caracteres)</label>
                    <input type="password" id="reg-password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
            </form>
            <div class="auth-footer">
                <p>Ja tem conta? <a href="#" onclick="toggleAuthBox(event)">Faca Login</a></p>
            </div>
        </div>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>
