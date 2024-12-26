<?php
require_once 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha_token = $_POST['smart-token'] ?? '';
    if (empty($login)) {
        $errors[] = "Введите email или телефон";
    }
    if (empty($password)) {
        $errors[] = "Введите пароль";
    }
    if (empty($captcha_token)) {
        $errors[] = "Пожалуйста, подтвердите, что вы не робот";
    }

    if (empty($errors)) {
        // Проверка капчи
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://smartcaptcha.yandexcloud.net/validate',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'secret' => 'ysc2_e8Un8J2Ova9TmJfeEUAHZI8N36xndfO45PpRH3YN6a6dd8ad',
                'token' => $captcha_token
            ])
        ]); 
        $response = curl_exec($ch);
        $captcha_status = json_decode($response, true);
        curl_close($ch);

        if (!isset($captcha_status['status']) || $captcha_status['status'] !== 'ok') {
            $errors[] = "Ошибка проверки капчи";
        } else {
            try {
                $db = getDBConnection();
                $stmt = $db->prepare("SELECT * FROM users WHERE email = ? OR phone = ?");
                $stmt->execute([$login, $login]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    header('Location: profile.php');
                    exit;
                } else {
                    $errors[] = "Неверный логин или пароль";
                }
            } catch (PDOException $e) {
                $errors[] = "Ошибка при входе в систему";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход в систему</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js" defer></script>
</head>
<body>
    <h2>Вход в систему</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="login">Email или телефон:</label>
            <input type="text" id="login" name="login" value="<?php echo htmlspecialchars($login ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div id="captcha-container" class="form-group">
            <div
                id="smartcaptcha"
                class="smart-captcha"
                data-sitekey="ysc1_e8Un8J2Ova9TmJfeEUAHw1W2jA2XracWXoE6tihW7d5018aa"
                data-callback="onCaptchaSuccess"
            ></div>
        </div>
        
        <button type="submit">Войти</button>
    </form>

    <div class="links">
        <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        <p><a href="index.php">На главную</a></p>
    </div>

    <script>
        function onCaptchaSuccess(token) {
            document.getElementById('smart-token').value = token;
        }
    </script>
</body>
</html>