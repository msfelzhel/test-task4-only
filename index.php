<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Главная страница</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Добро пожаловать</h1>
    
    <?php if (isAuthenticated()): ?>
        <p>Вы авторизованы! <a href="profile.php">Перейти в профиль</a></p>
        <p><a href="logout.php">Выйти</a></p>
    <?php else: ?>
        <p><a href="login.php">Войти</a> или <a href="register.php">Зарегистрироваться</a></p>
    <?php endif; ?>
</body>
</html>