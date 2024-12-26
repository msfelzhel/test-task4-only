<?php
require_once 'config.php';


if (!isAuthenticated()) {
    header('Location: index.php');
    exit;
}

$db = getDBConnection();
$errors = [];
$success = false;


$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';


    if (empty($name)) $errors[] = "Имя обязательно для заполнения";
    if (empty($phone)) $errors[] = "Телефон обязателен для заполнения";
    if (empty($email)) $errors[] = "Email обязателен для заполнения";

    if (!preg_match("/^\+?[0-9]{10,12}$/", $phone)) {
        $errors[] = "Неверный формат телефона";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Неверный формат email";
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE (email = ? OR phone = ?) AND id != ?");
    $stmt->execute([$email, $phone, $_SESSION['user_id']]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Email или телефон уже используются другим пользователем";
    }

    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $errors[] = "Новые пароли не совпадают";
        }
    }

    if (empty($errors)) {
        try {
            if (!empty($new_password)) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $email, $hashedPassword, $_SESSION['user_id']]);
            } else {
                $stmt = $db->prepare("UPDATE users SET name = ?, phone = ?, email = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $email, $_SESSION['user_id']]);
            }
            $success = true;
            
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $errors[] = "Ошибка при обновлении данных: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>Профиль пользователя</h2>

    <?php if ($success): ?>
        <p class="success">Данные успешно обновлены!</p>
    <?php endif; ?>

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
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>

        <div>
            <label>Телефон:</label>
            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>

        <div>
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>

        <div>
            <label>Новый пароль (оставьте пустым, если не хотите менять):</label>
            <input type="password" name="new_password">
        </div>

        <div>
            <label>Подтверждение нового пароля:</label>
            <input type="password" name="confirm_password">
        </div>

        <button type="submit">Сохранить изменения</button>
    </form>

    <p><a href="logout.php">Выйти</a></p>
</body>
</html>