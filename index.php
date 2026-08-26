<?php

declare(strict_types=1);

session_start();
require __DIR__ . '/connection/db.php';

$message = "";
$statusType = "";
$activeTab = 'login';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $activeTab = $action === 'signup' ? 'signup' : 'login';

    if (!hash_equals($_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
        $message = 'Your session expired. Please refresh the page and try again.';
        $statusType = 'error';
    } elseif ($action === 'signup') {
        $username = trim($_POST["reg_username"] ?? '');
        $email = strtolower(trim($_POST["reg_email"] ?? ''));
        $password = $_POST["reg_password"] ?? '';

        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
            $message = 'Username must be 3-30 characters and contain only letters, numbers, or underscores.';
            $statusType = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $statusType = 'error';
        } elseif (strlen($password) < 8 || strlen($password) > 72) {
            $message = 'Password must be between 8 and 72 characters.';
            $statusType = 'error';
        } else {
            $statement = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
            $statement->execute(['username' => $username, 'email' => $email]);

            if ($statement->fetch()) {
                $message = 'That username or email is already registered.';
                $statusType = 'warning';
            } else {
                $statement = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)');
                $statement->execute([
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $message = 'Account created successfully! You can now log in.';
                $statusType = 'success';
                $activeTab = 'login';
            }
        }
    } elseif ($action === 'login') {
        $username = trim($_POST["login_username"] ?? '');
        $password = $_POST["login_password"] ?? '';

        if ($username === '' || $password === '') {
            $message = 'Username and password are required.';
            $statusType = "error";
        } else {
            $statement = $pdo->prepare('SELECT username, password_hash FROM users WHERE username = :username LIMIT 1');
            $statement->execute(['username' => $username]);
            $user = $statement->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['logged_in_user'] = $user['username'];
                $message = 'Welcome back, ' . $user['username'] . '!';
                $statusType = "success";
            } else {
                $message = "Invalid username or password.";
                $statusType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizBattle</title>
    
    
    <link rel="stylesheet" href="style.css">
    
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="auth-container">
    <!-- Tab Switcher -->
    <div class="tabs">
        <button class="tab-btn active" id="loginTabBtn" onclick="switchTab('login')">Login</button>
        <button class="tab-btn" id="signupTabBtn" onclick="switchTab('signup')">Sign Up</button>
    </div>

    
    <form method="POST" id="loginForm" class="auth-form active">
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        
        <div class="form-group">
            <label for="login_username">Username</label>
            <input type="text" name="login_username" id="login_username" class="form-control" placeholder="Enter your username">
        </div>

        <div class="form-group">
            <label for="login_password">Password</label>
            <input type="password" name="login_password" id="login_password" class="form-control" placeholder="••••••••">
        </div>

        <button type="submit" class="submit-btn">Sign In</button>
    </form>

    
    <form method="POST" id="signupForm" class="auth-form">
        <input type="hidden" name="action" value="signup">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        
        <div class="form-group">
            <label for="reg_username">Username</label>
            <input type="text" name="reg_username" id="reg_username" class="form-control" placeholder="Choose a username">
        </div>

        <div class="form-group">
            <label for="reg_email">Email Address</label>
            <input type="email" name="reg_email" id="reg_email" class="form-control" placeholder="name@example.com">
        </div>

        <div class="form-group">
            <label for="reg_password">Password</label>
            <input type="password" name="reg_password" id="reg_password" class="form-control" placeholder="••••••••">
        </div>

        <button type="submit" class="submit-btn">Create Account</button>
    </form>
</div>

<script>

function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));

    if (tab === 'login') {
        document.getElementById('loginTabBtn').classList.add('active');
        document.getElementById('loginForm').classList.add('active');
    } else {
        document.getElementById('signupTabBtn').classList.add('active');
        document.getElementById('signupForm').classList.add('active');
    }
}


document.getElementById('loginForm').addEventListener('submit', function(e) {
    let u = document.getElementById('login_username').value.trim();
    let p = document.getElementById('login_password').value.trim();

    if (u === '' || p === '') {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Missing Fields',
            text: 'Please enter both username and password.',
            background: '#1e1b4b',
            color: '#fff',
            confirmButtonColor: '#6366f1'
        });
    }
});

document.getElementById('signupForm').addEventListener('submit', function(e) {
    let u = document.getElementById('reg_username').value.trim();
    let eMail = document.getElementById('reg_email').value.trim();
    let p = document.getElementById('reg_password').value.trim();

    if (u === '' || eMail === '' || p === '') {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Incomplete Registration',
            text: 'Please fill out all fields to create an account.',
            background: '#1e1b4b',
            color: '#fff',
            confirmButtonColor: '#6366f1'
        });
    }
});


<?php if (!empty($message)): ?>
    Swal.fire({
        icon: <?= json_encode($statusType) ?>,
        title: <?= json_encode(ucfirst($statusType)) ?>,
        text: <?= json_encode($message) ?>,
        background: '#1e1b4b',
        color: '#fff',
        confirmButtonColor: '#6366f1'
    });
<?php endif; ?>

<?php if ($activeTab === 'signup'): ?>
switchTab('signup');
<?php endif; ?>
</script>

</body>
</html>