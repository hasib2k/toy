<?php
session_start();

// If already logged in, redirect to dashboard
if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Simple hardcoded admin credentials (in production, use database)
    $ADMIN_USER = getenv('ADMIN_USER') ?: 'admin';
    $ADMIN_PASS = getenv('ADMIN_PASS') ?: 'admin123';
    
    if ($username === $ADMIN_USER && $password === $ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        $_SESSION['login_time'] = time();
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Babu Toys</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            overflow: hidden;
        }
        
        /* Animated Background */
        .bg-animation {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }
        
        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 80% 20%, rgba(14, 165, 233, 0.15) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(168, 85, 247, 0.1) 0%, transparent 50%);
            animation: bgMove 30s ease-in-out infinite;
        }
        
        @keyframes bgMove {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-5%, -5%) scale(1.1); }
        }
        
        /* Floating Shapes */
        .shapes {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(14, 165, 233, 0.05) 100%);
            animation: float 20s ease-in-out infinite;
        }
        
        .shape-1 { width: 300px; height: 300px; top: -100px; right: -100px; animation-delay: 0s; }
        .shape-2 { width: 200px; height: 200px; bottom: -50px; left: -50px; animation-delay: -5s; }
        .shape-3 { width: 150px; height: 150px; top: 50%; left: 20%; animation-delay: -10s; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
        
        /* Left Panel - Branding */
        .brand-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            z-index: 2;
        }
        
        .brand-content {
            max-width: 480px;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 56px;
        }
        
        .logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1 0%, #0ea5e9 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.3);
        }
        
        .logo-icon svg {
            width: 28px;
            height: 28px;
            color: #fff;
        }
        
        .logo-text {
            color: #fff;
        }
        
        .logo-text h1 {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .logo-text p {
            font-size: 12px;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 4px;
        }
        
        .brand-headline {
            font-size: 52px;
            font-weight: 800;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 24px;
            letter-spacing: -2px;
        }
        
        .brand-headline span {
            background: linear-gradient(135deg, #818cf8 0%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .brand-description {
            font-size: 17px;
            color: rgba(255,255,255,0.6);
            line-height: 1.7;
            margin-bottom: 48px;
        }
        
        .feature-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 16px;
            color: rgba(255,255,255,0.8);
            font-size: 15px;
        }
        
        .feature-icon {
            width: 44px;
            height: 44px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .feature-icon svg {
            width: 20px;
            height: 20px;
            color: #818cf8;
        }
        
        /* Right Panel - Login Form */
        .login-panel {
            width: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            position: relative;
            z-index: 2;
        }
        
        .login-card {
            width: 100%;
            max-width: 380px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 36px;
        }
        
        .login-header-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(14, 165, 233, 0.2) 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        
        .login-header-icon svg {
            width: 32px;
            height: 32px;
            color: #818cf8;
        }
        
        .login-header h2 {
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: rgba(255,255,255,0.5);
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255,255,255,0.7);
            margin-bottom: 10px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.4);
            pointer-events: none;
        }
        
        .input-icon svg {
            width: 18px;
            height: 18px;
        }
        
        .form-input {
            width: 100%;
            padding: 16px 16px 16px 50px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            font-size: 15px;
            font-family: inherit;
            color: #fff;
            transition: all 0.3s ease;
        }
        
        .form-input::placeholder {
            color: rgba(255,255,255,0.3);
        }
        
        .form-input:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.08);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.4);
            cursor: pointer;
            transition: color 0.2s;
            padding: 4px;
        }
        
        .password-toggle:hover {
            color: rgba(255,255,255,0.8);
        }
        
        .password-toggle svg {
            width: 18px;
            height: 18px;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .error-message svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login svg {
            width: 18px;
            height: 18px;
        }
        
        .demo-credentials {
            margin-top: 28px;
            padding: 16px;
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.15);
            border-radius: 12px;
            text-align: center;
        }
        
        .demo-credentials h4 {
            color: #fcd34d;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        
        .demo-credentials h4 svg {
            width: 14px;
            height: 14px;
        }
        
        .demo-credentials p {
            color: rgba(255,255,255,0.7);
            font-size: 13px;
        }
        
        .demo-credentials code {
            background: rgba(255,255,255,0.1);
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'SF Mono', Monaco, monospace;
            font-size: 12px;
        }
        
        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 28px;
        }
        
        .login-footer a {
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        
        .login-footer a:hover {
            color: #818cf8;
        }
        
        .login-footer a svg {
            width: 16px;
            height: 16px;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .brand-panel {
                display: none;
            }
            
            .login-panel {
                width: 100%;
                padding: 40px 20px;
            }
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }
            
            .login-header h2 {
                font-size: 22px;
            }
            
            .form-input {
                padding: 14px 14px 14px 46px;
            }
        }
    </style>
</head>
<body>
    <div class="bg-animation"></div>
    <div class="shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>
    
    <!-- Left Panel - Branding -->
    <div class="brand-panel">
        <div class="brand-content">
            <div class="brand-logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <div class="logo-text">
                    <h1>Babu Toys</h1>
                    <p>Admin Panel</p>
                </div>
            </div>
            
            <h2 class="brand-headline">
                Manage your store<br>with <span>precision</span>
            </h2>
            
            <p class="brand-description">
                A powerful admin dashboard to manage orders, track inventory, and grow your business efficiently.
            </p>
            
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                    </div>
                    <span>Real-time order management</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                    <span>Comprehensive analytics</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <span>Secure authentication</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Panel - Login Form -->
    <div class="login-panel">
        <div class="login-card">
            <div class="login-header">
                <div class="login-header-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h2>Welcome Back</h2>
                <p>Sign in to access the admin panel</p>
            </div>
            
            <?php if ($error): ?>
            <div class="error-message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input"
                            placeholder="Enter your username"
                            required
                            autocomplete="username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeOffIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    Sign In
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>
            
            <div class="demo-credentials">
                <h4>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    Demo Credentials
                </h4>
                <p>Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>admin123</code></p>
            </div>
            
            <div class="login-footer">
                <a href="../">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back to Store
                </a>
            </div>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            
            if (input.type === 'password') {
                input.type = 'text';
                eyeIcon.style.display = 'none';
                eyeOffIcon.style.display = 'block';
            } else {
                input.type = 'password';
                eyeIcon.style.display = 'block';
                eyeOffIcon.style.display = 'none';
            }
        }
        
        // Auto-focus username field
        document.getElementById('username').focus();
    </script>
</body>
</html>
