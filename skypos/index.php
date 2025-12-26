<?php
session_start();
require_once 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guardia POS - Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #f59e0b;
            --bg-primary: #0f0f23;
            --bg-secondary: #1a1a2e;
            --glass: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #a1a1aa;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg-primary);
            overflow: hidden;
            position: relative;
        }

        /* Animated Background */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            background: linear-gradient(45deg, #0f0f23, #1a1a2e, #16213e);
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(236, 72, 153, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 60%, rgba(245, 158, 11, 0.2) 0%, transparent 50%);
            animation: floatingBg 20s ease-in-out infinite;
        }

        @keyframes floatingBg {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg) scale(1); }
            33% { transform: translate(-45%, -55%) rotate(120deg) scale(1.1); }
            66% { transform: translate(-55%, -45%) rotate(240deg) scale(0.9); }
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }

        .particle {
            position: absolute;
            width: 2px;
            height: 2px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        /* Main container */
        .login-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: flex;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 
                0 25px 50px -12px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(255, 255, 255, 0.05),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            animation: containerFloat 6s ease-in-out infinite;
            width: 900px;
            max-width: 95vw;
            min-height: 600px;
        }

        @keyframes containerFloat {
            0%, 100% { transform: translate(-50%, -50%) translateY(0px); }
            50% { transform: translate(-50%, -50%) translateY(-10px); }
        }

        /* Left side - Logo section */
        .login-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: 
                radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 70%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            animation: shimmer 8s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: translate(-50%, -50%) rotate(0deg); }
            50% { transform: translate(-50%, -50%) rotate(180deg); }
        }

        .logo-container {
            position: relative;
            z-index: 2;
        }

        .user-avatar {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 3px solid rgba(255, 255, 255, 0.2);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            position: relative;
            animation: avatarPulse 3s ease-in-out infinite;
            overflow: hidden;
        }

        @keyframes avatarPulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 0 0 0 20px rgba(255, 255, 255, 0);
            }
        }

        .user-avatar::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(
                from 0deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            animation: rotate 4s linear infinite;
        }

        @keyframes rotate {
            to { transform: rotate(360deg); }
        }

        .user-icon {
            width: 80px;
            height: 80px;
            color: white;
            z-index: 1;
            position: relative;
        }

        .brand-text {
            text-align: center;
            color: white;
            z-index: 2;
            position: relative;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(45deg, #ffffff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: textShine 2s ease-in-out infinite;
        }

        @keyframes textShine {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.2); }
        }

        .brand-subtitle {
            font-size: 1.1rem;
            font-weight: 300;
            opacity: 0.9;
            letter-spacing: 2px;
        }

        /* Right side - Form */
        .login-right {
            flex: 1;
            padding: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(255, 255, 255, 0.02);
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 24px;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .input-container {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 
                0 0 0 4px rgba(99, 102, 241, 0.1),
                0 8px 32px rgba(99, 102, 241, 0.2);
            transform: translateY(-2px);
        }

        .form-input::placeholder {
            color: var(--text-secondary);
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            font-size: 1.2rem;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 12px;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 
                0 12px 40px rgba(99, 102, 241, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.1);
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        /* Messages */
        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid;
            animation: messageSlide 0.5s ease-out;
        }

        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .success-message {
            background: rgba(34, 197, 94, 0.1);
            border-color: rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        /* Loading state */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .loading .login-btn {
            background: linear-gradient(135deg, #6b7280, #4b5563);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 90vw;
                min-height: 80vh;
            }
            
            .login-left {
                min-height: 200px;
                flex: none;
            }
            
            .user-avatar {
                width: 120px;
                height: 120px;
                margin-bottom: 20px;
            }
            
            .user-icon {
                width: 50px;
                height: 50px;
            }
            
            .brand-title {
                font-size: 1.8rem;
            }
            
            .login-right {
                padding: 40px 30px;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
        }

        /* Focus animations */
        .form-group {
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- Animated background -->
    <div class="bg-animation"></div>
    
    <!-- Floating particles -->
    <div class="particles" id="particles"></div>

    <div class="login-container">
        <div class="login-left">
            <div class="logo-container">
                <div class="user-avatar">
                    <svg class="user-icon" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                </div>
                <div class="brand-text">
                    <div class="brand-title">Guardia POS</div>
                    <div class="brand-subtitle">POINT OF SALE</div>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <div class="form-header">
                <h1 class="form-title">Welcome Back</h1>
                <p class="form-subtitle">Please sign in to your account</p>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="message error-message"><?php echo htmlspecialchars($_SESSION['error']); ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="message success-message"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <form method="POST" id="loginForm" action="login_process.php">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-container">
                        <input type="text" id="username" name="username" class="form-input" 
                               placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-container">
                        <input type="password" id="password" name="password" class="form-input" 
                               placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            👁️
                        </button>
                    </div>
                </div>
                
                <button type="submit" name="login" class="login-btn">
                    Sign In
                </button>
            </form>
        </div>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (15 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        }

        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.innerHTML = '🙈';
            } else {
                passwordInput.type = 'password';
                toggleBtn.innerHTML = '👁️';
            }
        }

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            document.body.classList.add('loading');
        });

        // Enhanced input animations
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });

            // Add typing animation
            input.addEventListener('input', function() {
                if (this.value.length > 0) {
                    this.style.borderColor = 'var(--primary)';
                } else {
                    this.style.borderColor = 'rgba(255, 255, 255, 0.1)';
                }
            });
        });

        // Initialize particles on page load
        window.addEventListener('load', createParticles);

        // Cursor trail effect
        let mouseX = 0, mouseY = 0;
        let trailElements = [];

        document.addEventListener('mousemove', (e) => {
            mouseX = e.clientX;
            mouseY = e.clientY;
            
            // Create cursor trail
            if (Math.random() > 0.8) {
                const trail = document.createElement('div');
                trail.style.position = 'fixed';
                trail.style.left = mouseX + 'px';
                trail.style.top = mouseY + 'px';
                trail.style.width = '4px';
                trail.style.height = '4px';
                trail.style.background = 'rgba(99, 102, 241, 0.6)';
                trail.style.borderRadius = '50%';
                trail.style.pointerEvents = 'none';
                trail.style.zIndex = '9999';
                trail.style.animation = 'trailFade 1s ease-out forwards';
                document.body.appendChild(trail);
                
                setTimeout(() => {
                    if (trail.parentNode) {
                        trail.parentNode.removeChild(trail);
                    }
                }, 1000);
            }
        });

        // Add trail fade animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes trailFade {
                to {
                    opacity: 0;
                    transform: scale(0);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>