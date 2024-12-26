<?php
require_once 'config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($name)) $errors[] = "Имя обязательно для заполнения";
    if (empty($phone)) $errors[] = "Телефон обязателен для заполнения";
    if (empty($email)) $errors[] = "Email обязателен для заполнения";
    if (empty($password)) $errors[] = "Пароль обязателен для заполнения";
    
    if (!preg_match("/^\+?[0-9]{10,12}$/", $phone)) {
        $errors[] = "Неверный формат телефона";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Неверный формат email";
    }
    
    if ($password !== $password_confirm) {
        $errors[] = "Пароли не совпадают";
    }

    if (empty($errors)) {
        $db = getDBConnection();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Пользователь с таким email или телефоном уже существует";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
            
            try {
                $stmt->execute([$name, $phone, $email, $hashedPassword]);
                $success = true;
            } catch (PDOException $e) {
                $errors[] = "Ошибка при регистрации: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Регистрация</title>
    <style>
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>Регистрация</h2>
    
    <?php if ($success): ?>
        <p class="success">Регистрация успешна! <a href="login.php">Войти</a></p>
    <?php else: ?>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div>
                <label>Имя:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>">
            </div>
            
            <div>
                <label>Телефон:</label>
                <input type="tel" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
            </div>
            
            <div>
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
            </div>
            
            <div>
                <label>Пароль:</label>
                <input type="password" name="password">
            </div>
            
            <div>
                <label>Подтверждение пароля:</label>
                <input type="password" name="password_confirm">
            </div>
            
            <button type="submit">Зарегистрироваться</button>
        </form>
    <?php endif; ?>
</body>
</html>