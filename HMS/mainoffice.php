<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        /* Animated background with hotel theme and colorful effects */
        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                url('https://images.unsplash.com/photo-1517840901100-8179e20d81d8?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover,
                linear-gradient(45deg, 
                    #ff6b6b 0%, 
                    #4ecdc4 25%, 
                    #45b7d1 50%, 
                    #96ceb4 75%, 
                    #feca57 100%
                ),
                radial-gradient(circle at 20% 30%, rgba(255, 107, 107, 0.4) 0%, transparent 60%),
                radial-gradient(circle at 80% 20%, rgba(78, 205, 196, 0.4) 0%, transparent 60%),
                radial-gradient(circle at 60% 80%, rgba(69, 183, 209, 0.4) 0%, transparent 60%),
                radial-gradient(circle at 30% 70%, rgba(150, 206, 180, 0.4) 0%, transparent 60%),
                radial-gradient(circle at 70% 40%, rgba(254, 202, 87, 0.4) 0%, transparent 60%);
            background-size: cover, 400% 400%, 800px 800px, 600px 600px, 700px 700px, 500px 500px, 900px 900px;
            background-blend-mode: overlay;
            animation: 
                backgroundShift 8s ease-in-out infinite,
                colorPulse 6s ease-in-out infinite,
                gradientMove 12s linear infinite;
            opacity: 0.7;
        }

        @keyframes backgroundShift {
            0%, 100% { 
                background-position: center center, 0% 50%, 0% 0%, 100% 0%, 0% 100%, 100% 100%, 50% 50%;
            }
            25% { 
                background-position: center center, 50% 0%, 25% 25%, 75% 25%, 25% 75%, 75% 75%, 25% 25%;
            }
            50% { 
                background-position: center center, 100% 50%, 50% 50%, 50% 50%, 50% 50%, 50% 50%, 0% 100%;
            }
            75% { 
                background-position: center center, 50% 100%, 75% 75%, 25% 75%, 75% 25%, 25% 25%, 75% 75%;
            }
        }

        @keyframes colorPulse {
            0%, 100% { 
                filter: brightness(1) saturate(1) hue-rotate(0deg);
            }
            33% { 
                filter: brightness(1.2) saturate(1.3) hue-rotate(60deg);
            }
            66% { 
                filter: brightness(0.9) saturate(1.5) hue-rotate(-30deg);
            }
        }

        @keyframes gradientMove {
            0% { 
                filter: hue-rotate(0deg) contrast(1);
            }
            25% { 
                filter: hue-rotate(90deg) contrast(1.1);
            }
            50% { 
                filter: hue-rotate(180deg) contrast(0.9);
            }
            75% { 
                filter: hue-rotate(270deg) contrast(1.2);
            }
            100% { 
                filter: hue-rotate(360deg) contrast(1);
            }
        }

        /* Floating particles with colorful glowing effects */
        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: colorfulFloat 8s ease-in-out infinite;
            box-shadow: 0 0 20px currentColor;
        }

        .particle:nth-child(1) { 
            width: 8px; height: 8px; left: 10%; 
            background: radial-gradient(circle, #ff6b6b, #ff8e53);
            animation-delay: 0s; 
            box-shadow: 0 0 30px #ff6b6b, 0 0 60px #ff6b6b;
        }
        .particle:nth-child(2) { 
            width: 12px; height: 12px; left: 20%; 
            background: radial-gradient(circle, #4ecdc4, #44a08d);
            animation-delay: 1s; 
            box-shadow: 0 0 30px #4ecdc4, 0 0 60px #4ecdc4;
        }
        .particle:nth-child(3) { 
            width: 6px; height: 6px; left: 30%; 
            background: radial-gradient(circle, #45b7d1, #96c93d);
            animation-delay: 2s; 
            box-shadow: 0 0 30px #45b7d1, 0 0 60px #45b7d1;
        }
        .particle:nth-child(4) { 
            width: 10px; height: 10px; left: 70%; 
            background: radial-gradient(circle, #96ceb4, #ffecd2);
            animation-delay: 0.5s; 
            box-shadow: 0 0 30px #96ceb4, 0 0 60px #96ceb4;
        }
        .particle:nth-child(5) { 
            width: 8px; height: 8px; left: 80%; 
            background: radial-gradient(circle, #feca57, #ff9ff3);
            animation-delay: 1.5s; 
            box-shadow: 0 0 30px #feca57, 0 0 60px #feca57;
        }
        .particle:nth-child(6) { 
            width: 14px; height: 14px; left: 90%; 
            background: radial-gradient(circle, #ff9ff3, #54a0ff);
            animation-delay: 2.5s; 
            box-shadow: 0 0 30px #ff9ff3, 0 0 60px #ff9ff3;
        }

        @keyframes colorfulFloat {
            0%, 100% { 
                transform: translateY(100vh) rotate(0deg) scale(0.5); 
                opacity: 0; 
                filter: hue-rotate(0deg) brightness(1);
            }
            10% { 
                opacity: 1; 
                transform: translateY(90vh) rotate(36deg) scale(0.8);
                filter: hue-rotate(60deg) brightness(1.5);
            }
            50% { 
                opacity: 1; 
                transform: translateY(50vh) rotate(180deg) scale(1.2);
                filter: hue-rotate(180deg) brightness(2);
            }
            90% { 
                opacity: 1; 
                transform: translateY(10vh) rotate(324deg) scale(0.9);
                filter: hue-rotate(300deg) brightness(1.2);
            }
            100% { 
                transform: translateY(-10vh) rotate(360deg) scale(0.3); 
                opacity: 0; 
                filter: hue-rotate(360deg) brightness(0.5);
            }
        }

        /* Container for horizontal layout */
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            gap: 15px;
            padding: 20px;
            position: relative;
            z-index: 10;
            flex-wrap: nowrap;
            overflow-x: auto;
            scroll-behavior: smooth;
        }

        /* Card base styles */
        .login-card {
            width: 280px;
            height: 460px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 20px;
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.2),
                0 0 0 1px rgba(255,255,255,0.3),
                inset 0 1px 0 rgba(255,255,255,0.4);
            position: relative;
            overflow: hidden;
            transition: all 0.6s cubic-bezier(0.23, 1, 0.320, 1);
            transform-style: preserve-3d;
            flex: 0 0 auto;
        }

        /* Stores & Purchasing Card */
        .stores-purchasing-card {
            border: 2px solid rgba(241, 196, 15, 0.3);
        }

        .stores-purchasing-card:hover {
            transform: scale(1.02);
            box-shadow: 
                0 0 50px rgba(241, 196, 15, 0.6),
                0 0 100px rgba(241, 196, 15, 0.4),
                0 35px 70px rgba(241, 196, 15, 0.3),
                inset 0 0 30px rgba(241, 196, 15, 0.1);
            border-color: rgba(241, 196, 15, 0.8);
        }

        .stores-purchasing-card .card-overlay {
            background: linear-gradient(135deg, 
                rgba(241, 196, 15, 0.1) 0%, 
                rgba(243, 156, 18, 0.1) 100%);
            animation: gradientShift 3s ease-in-out infinite;
        }

        .stores-purchasing-card .card-decoration {
            background: linear-gradient(135deg, #f1c40f, #f39c12);
            animation: decorationSpin 10s linear infinite;
        }

        /* Logistics Card */
        .logistics-card {
            border: 2px solid rgba(52, 73, 94, 0.3);
        }

        .logistics-card:hover {
            transform: scale(1.02);
            box-shadow: 
                0 0 50px rgba(52, 73, 94, 0.6),
                0 0 100px rgba(52, 73, 94, 0.4),
                0 35px 70px rgba(52, 73, 94, 0.3),
                inset 0 0 30px rgba(52, 73, 94, 0.1);
            border-color: rgba(52, 73, 94, 0.8);
        }

        .logistics-card .card-overlay {
            background: linear-gradient(135deg, 
                rgba(52, 73, 94, 0.1) 0%, 
                rgba(44, 62, 80, 0.1) 100%);
            animation: gradientShift 3s ease-in-out infinite reverse;
        }

        .logistics-card .card-decoration {
            background: linear-gradient(135deg, #34495e, #2c3e50);
            animation: decorationSpin 10s linear infinite reverse;
        }

        /* Inventory Card */
        .inventory-card {
            border: 2px solid rgba(231, 76, 60, 0.3);
        }

        .inventory-card:hover {
            transform: scale(1.02);
            box-shadow: 
                0 0 50px rgba(231, 76, 60, 0.6),
                0 0 100px rgba(231, 76, 60, 0.4),
                0 35px 70px rgba(231, 76, 60, 0.3),
                inset 0 0 30px rgba(231, 76, 60, 0.1);
            border-color: rgba(231, 76, 60, 0.8);
        }

        .inventory-card .card-overlay {
            background: linear-gradient(135deg, 
                rgba(231, 76, 60, 0.1) 0%, 
                rgba(192, 57, 43, 0.1) 100%);
            animation: gradientShift 3s ease-in-out infinite;
        }

        .inventory-card .card-decoration {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            animation: decorationSpin 10s linear infinite;
        }

        /* Accounts Card */
        .accounts-card {
            border: 2px solid rgba(26, 188, 156, 0.3);
        }

        .accounts-card:hover {
            transform: scale(1.02);
            box-shadow: 
                0 0 50px rgba(26, 188, 156, 0.6),
                0 0 100px rgba(26, 188, 156, 0.4),
                0 35px 70px rgba(26, 188, 156, 0.3),
                inset 0 0 30px rgba(26, 188, 156, 0.1);
            border-color: rgba(26, 188, 156, 0.8);
        }

        .accounts-card .card-overlay {
            background: linear-gradient(135deg, 
                rgba(26, 188, 156, 0.1) 0%, 
                rgba(22, 160, 133, 0.1) 100%);
            animation: gradientShift 3s ease-in-out infinite reverse;
        }

        .accounts-card .card-decoration {
            background: linear-gradient(135deg, #1abc9c, #16a085);
            animation: decorationSpin 10s linear infinite reverse;
        }

        /* Audit Card */
        .audit-card {
            border: 2px solid rgba(155, 89, 182, 0.3);
        }

        .audit-card:hover {
            transform: scale(1.02);
            box-shadow: 
                0 0 50px rgba(155, 89, 182, 0.6),
                0 0 100px rgba(155, 89, 182, 0.4),
                0 35px 70px rgba(155, 89, 182, 0.3),
                inset 0 0 30px rgba(155, 89, 182, 0.1);
            border-color: rgba(155, 89, 182, 0.8);
        }

        .audit-card .card-overlay {
            background: linear-gradient(135deg, 
                rgba(155, 89, 182, 0.1) 0%, 
                rgba(142, 68, 173, 0.1) 100%);
            animation: gradientShift 3s ease-in-out infinite;
        }

        .audit-card .card-decoration {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
            animation: decorationSpin 10s linear infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 0.1; transform: rotate(0deg) scale(1); }
            50% { opacity: 0.3; transform: rotate(180deg) scale(1.1); }
        }

        .card-title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        .stores-purchasing-card .card-title {
            animation: titlePulse 2s ease-in-out infinite;
        }

        .logistics-card .card-title {
            animation: titleGlow 2.5s ease-in-out infinite;
        }

        .inventory-card .card-title {
            animation: titlePulse 2.2s ease-in-out infinite;
        }

        .accounts-card .card-title {
            animation: titleGlow 2.3s ease-in-out infinite;
        }

        .audit-card .card-title {
            animation: titlePulse 2.1s ease-in-out infinite;
        }

        @keyframes titlePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes titleGlow {
            0%, 100% { 
                text-shadow: 0 0 10px rgba(155, 89, 182, 0.3);
                transform: translateY(0px);
            }
            50% { 
                text-shadow: 0 0 20px rgba(155, 89, 182, 0.6);
                transform: translateY(-2px);
            }
        }

        .card-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 25px;
            font-size: 13px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 12px 18px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            outline: none;
        }

        .form-input:focus {
            border-color: #667eea;
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }

        .form-input::placeholder {
            color: #999;
            transition: all 0.3s ease;
        }

        .form-input:focus::placeholder {
            opacity: 0;
            transform: translateY(-10px);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .login-btn:hover:before {
            left: 100%;
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        /* Success animation */
        @keyframes successBounce {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .success {
            animation: successBounce 0.6s ease-in-out;
        }

        /* Error shake animation */
        @keyframes errorShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error {
            animation: errorShake 0.5s ease-in-out;
            border-color: #ff6b6b !important;
        }

        /* Decorative elements */
        .card-decoration {
            position: absolute;
            top: -50px;
            right: -50px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            opacity: 0.1;
        }

        @keyframes decorationSpin {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(180deg) scale(1.2); }
            100% { transform: rotate(360deg) scale(1); }
        }

        /* Admin button styles */
        .admin-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 20;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-transform: uppercase;
            background: linear-gradient(45deg, #ffffff, #e0e0e0);
            color: #333;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            animation: colorToggle 4s infinite;
        }

        @keyframes colorToggle {
            0%, 100% {
                background: linear-gradient(45deg, #ffffff, #e0e0e0);
                color: #333;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }
            50% {
                background: linear-gradient(45deg, #333333, #4a4a4a);
                color: #ffffff;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            }
        }

        .admin-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
        }

        .admin-button:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        /* Responsive design */
        @media (max-width: 1400px) {
            .container {
                gap: 10px;
            }
            
            .login-card {
                width: 260px;
                height: 440px;
                padding: 15px;
            }
        }

        @media (max-width: 900px) {
            .container {
                justify-content: flex-start;
                padding: 20px 10px;
            }
            
            .login-card {
                width: 240px;
                height: 420px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="background">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <button class="admin-button" onclick="window.location.href='admin_login.php'">Admin</button>

    <div class="container">
        <!-- Stores & Purchasing Login Card -->
        <div class="login-card stores-purchasing-card">
            <div class="card-overlay"></div>
            <div class="card-decoration"></div>
            <h2 class="card-title">Stores & Purchasing</h2>
            <p class="card-subtitle">Procurement Management</p>
            
            <form id="storesPurchasingForm">
                <div class="form-group">
                    <input type="text" class="form-input" id="storesUsername" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" id="storesPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Access Stores & Purchasing</button>
            </form>
        </div>

        <!-- Logistics Login Card -->
        <div class="login-card logistics-card">
            <div class="card-overlay"></div>
            <div class="card-decoration"></div>
            <h2 class="card-title">Logistics</h2>
            <p class="card-subtitle">Supply Chain Operations</p>
            
            <form id="logisticsForm">
                <div class="form-group">
                    <input type="text" class="form-input" id="logisticsUsername" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" id="logisticsPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Access Logistics</button>
            </form>
        </div>

        <!-- Inventory Login Card -->
        <div class="login-card inventory-card">
            <div class="card-overlay"></div>
            <div class="card-decoration"></div>
            <h2 class="card-title">Inventory</h2>
            <p class="card-subtitle">Stock Management</p>
            
            <form id="inventoryForm">
                <div class="form-group">
                    <input type="text" class="form-input" id="inventoryUsername" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" id="inventoryPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Access Inventory</button>
            </form>
        </div>

        <!-- Accounts Login Card -->
        <div class="login-card accounts-card">
            <div class="card-overlay"></div>
            <div class="card-decoration"></div>
            <h2 class="card-title">Accounts</h2>
            <p class="card-subtitle">Financial Management</p>
            
            <form id="accountsForm">
                <div class="form-group">
                    <input type="text" class="form-input" id="accountsUsername" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" id="accountsPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Access Accounts</button>
            </form>
        </div>

        <!-- Audit Login Card -->
        <div class="login-card audit-card">
            <div class="card-overlay"></div>
            <div class="card-decoration"></div>
            <h2 class="card-title">Audit</h2>
            <p class="card-subtitle">Compliance & Oversight</p>
            
            <form id="auditForm">
                <div class="form-group">
                    <input type="text" class="form-input" id="auditUsername" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" id="auditPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Access Audit</button>
            </form>
        </div>
    </div>

    <script>
        // Manual credentials (not from database)
        const credentials = {
            storesPurchasing: {
                username: "sachith",
                password: "123456"
            },
            logistics: {
                username: "sachith",
                password: "123456"
            },
            inventory: {
                username: "sachith",
                password: "123456"
            },
            accounts: {
                username: "sachith",
                password: "123456"
            },
            audit: {
                username: "sachith",
                password: "123456"
            }
        };

        // Stores & Purchasing Form Handler
        document.getElementById('storesPurchasingForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('storesUsername').value;
            const password = document.getElementById('storesPassword').value;
            const card = document.querySelector('.stores-purchasing-card');
            
            if (username === credentials.storesPurchasing.username && password === credentials.storesPurchasing.password) {
                card.classList.add('success');
                showSuccessMessage(card, 'Access Granted! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = 'stores_login.php';
                }, 1500);
            } else {
                showErrorMessage(card, 'Invalid credentials. Please try again.');
            }
        });

        // Logistics Form Handler
        document.getElementById('logisticsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('logisticsUsername').value;
            const password = document.getElementById('logisticsPassword').value;
            const card = document.querySelector('.logistics-card');
            
            if (username === credentials.logistics.username && password === credentials.logistics.password) {
                card.classList.add('success');
                showSuccessMessage(card, 'Access Granted! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = 'logistic_login.php';
                }, 1500);
            } else {
                showErrorMessage(card, 'Invalid credentials. Please try again.');
            }
        });

        // Inventory Form Handler
        document.getElementById('inventoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('inventoryUsername').value;
            const password = document.getElementById('inventoryPassword').value;
            const card = document.querySelector('.inventory-card');
            
            if (username === credentials.inventory.username && password === credentials.inventory.password) {
                card.classList.add('success');
                showSuccessMessage(card, 'Access Granted! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = 'inventory_login.php';
                }, 1500);
            } else {
                showErrorMessage(card, 'Invalid credentials. Please try again.');
            }
        });

        // Accounts Form Handler
        document.getElementById('accountsForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('accountsUsername').value;
            const password = document.getElementById('accountsPassword').value;
            const card = document.querySelector('.accounts-card');
            
            if (username === credentials.accounts.username && password === credentials.accounts.password) {
                card.classList.add('success');
                showSuccessMessage(card, 'Access Granted! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = 'accounts_login.php';
                }, 1500);
            } else {
                showErrorMessage(card, 'Invalid credentials. Please try again.');
            }
        });

        
        // Audit Form Handler
        document.getElementById('auditForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('auditUsername').value;
            const password = document.getElementById('auditPassword').value;
            const card = document.querySelector('.audit-card');
            
            if (username === credentials.audit.username && password === credentials.audit.password) {
                card.classList.add('success');
                showSuccessMessage(card, 'Access Granted! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = 'audit_login.php';
                }, 1500);
            } else {
                showErrorMessage(card, 'Invalid credentials. Please try again.');
            }
        });

        function showSuccessMessage(card, message) {
            const successDiv = document.createElement('div');
            successDiv.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: linear-gradient(135deg, #4CAF50, #45a049);
                color: white;
                padding: 15px 25px;
                border-radius: 10px;
                font-weight: 600;
                z-index: 1000;
                animation: fadeInOut 3s ease-in-out;
            `;
            successDiv.textContent = message;
            
            card.appendChild(successDiv);
            
            setTimeout(() => {
                card.removeChild(successDiv);
                card.classList.remove('success');
            }, 3000);
        }

        function showErrorMessage(card, message) {
            const inputs = card.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.classList.add('error');
                setTimeout(() => {
                    input.classList.remove('error');
                }, 500);
            });

            const errorDiv = document.createElement('div');
            errorDiv.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: linear-gradient(135deg, #ff6b6b, #ff5252);
                color: white;
                padding: 15px 25px;
                border-radius: 10px;
                font-weight: 600;
                z-index: 1000;
                animation: fadeInOut 3s ease-in-out;
            `;
            errorDiv.textContent = message;
            
            card.appendChild(errorDiv);
            
            setTimeout(() => {
                card.removeChild(errorDiv);
            }, 3000);
        }

        // Add CSS for fade in/out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInOut {
                0% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
                20%, 80% { opacity: 1; transform: translate(-50%, -50%) scale(1); }
                100% { opacity: 0; transform: translate(-50%, -50%) scale(0.8); }
            }
        `;
        document.head.appendChild(style);

        // Enhanced particle animation with random movement
        function createRandomParticles() {
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                const randomDelay = Math.random() * 3;
                const randomDuration = 4 + Math.random() * 4;
                particle.style.animationDelay = randomDelay + 's';
                particle.style.animationDuration = randomDuration + 's';
            });
        }

        // Initialize enhanced animations
        createRandomParticles();
        
        // Recreate particles periodically for continuous effect
        setInterval(createRandomParticles, 8000);
    </script>
</body>
</html>