<?php
session_start();

$message = "";
$statusType = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    
    if ($action === 'signup') {
        $username = trim($_POST["reg_username"] ?? '');
        $email    = trim($_POST["reg_email"] ?? '');
        $password = trim($_POST["reg_password"] ?? '');

        if (empty($username) || empty($email) || empty($password)) {
            $message = "Please complete all fields to sign up!";
            $statusType = "error";
        } elseif (isset($_SESSION['registered_users'][$username])) {
            $message = "Username already exists! Please choose another.";
            $statusType = "warning";
        } else {
            $_SESSION['registered_users'][$username] = password_hash($password, PASSWORD_BCRYPT);
            $message = "Account created successfully! You can now log in.";
            $statusType = "success";
        }
    }

    if ($action === 'login') {
        $username = trim($_POST["login_username"] ?? '');
        $password = trim($_POST["login_password"] ?? '');

        if (empty($username) || empty($password)) {
            $message = "Username and Password are required!";
            $statusType = "error";
        } else {
            $users = $_SESSION['registered_users'];
            if (isset($users[$username]) && password_verify($password, $users[$username])) {
                $_SESSION['logged_in_user'] = $username;
                $message = "Welcome back, " . htmlspecialchars($username) . "!";
                $statusType = "success";
            } else {
                $message = "Invalid username or password!";
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
        icon: '<?= $statusType ?>',
        title: '<?= ucfirst($statusType) ?>',
        text: '<?= $message ?>',
        background: '#1e1b4b',
        color: '#fff',
        confirmButtonColor: '#6366f1'
    });
<?php endif; ?>
</script>

</body>
</html>