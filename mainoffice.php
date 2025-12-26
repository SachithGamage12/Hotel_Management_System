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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.15);
        }

        .stores-purchasing-card {
            border-left-color: #f1c40f;
        }

        .logistics-card {
            border-left-color: #34495e;
        }

        .inventory-card {
            border-left-color: #e74c3c;
        }

        .accounts-card {
            border-left-color: #1abc9c;
        }

        .audit-card {
            border-left-color: #9b59b6;
        }

        .card-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #333;
        }

        .card-subtitle {
            color: #999;
            font-size: 13px;
            margin-bottom: 25px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .input-wrapper {
            position: relative;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            background: #f9f9f9;
            transition: border-color 0.3s ease, background 0.3s ease;
            outline: none;
        }

        .form-input:focus {
            border-color: #667eea;
            background: #fff;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            color: #999;
            transition: color 0.3s ease;
            padding: 5px;
        }

        .toggle-password:hover {
            color: #667eea;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-btn:hover {
            opacity: 0.9;
        }

        .login-btn:active {
            transform: scale(0.98);
        }

        .message {
            margin-bottom: 15px;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-align: center;
            display: none;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            display: block;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            display: block;
        }

        .admin-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            background: white;
            color: #333;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .admin-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 15px;
            }

            .login-card {
                padding: 25px;
            }

            .card-title {
                font-size: 20px;
            }

            .admin-button {
                padding: 8px 16px;
                font-size: 12px;
                top: 15px;
                right: 15px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .container {
                padding: 10px;
            }

            .login-card {
                padding: 20px;
            }

            .card-title {
                font-size: 18px;
            }

            .form-input {
                font-size: 16px;
                padding: 14px 12px;
            }
        }
    </style>
</head>
<body>
    <button class="admin-button" onclick="window.location.href='admin_login.php'">Admin</button>

    <div class="container">
        <!-- Stores & Purchasing -->
        <div class="login-card stores-purchasing-card">
            <h2 class="card-title">Stores & Purchasing</h2>
            <p class="card-subtitle">Procurement Management</p>
            <form id="storesPurchasingForm">
                <div class="message" id="storesMessage"></div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-input password-input" placeholder="Enter password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
                    </div>
                </div>
                <button type="submit" class="login-btn">Access</button>
            </form>
        </div>

        <!-- Logistics -->
        <div class="login-card logistics-card">
            <h2 class="card-title">Logistics</h2>
            <p class="card-subtitle">Supply Chain Operations</p>
            <form id="logisticsForm">
                <div class="message" id="logisticsMessage"></div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-input password-input" placeholder="Enter password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
                    </div>
                </div>
                <button type="submit" class="login-btn">Access</button>
            </form>
        </div>

        <!-- Inventory -->
        <div class="login-card inventory-card">
            <h2 class="card-title">Inventory</h2>
            <p class="card-subtitle">Stock Management</p>
            <form id="inventoryForm">
                <div class="message" id="inventoryMessage"></div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-input password-input" placeholder="Enter password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
                    </div>
                </div>
                <button type="submit" class="login-btn">Access</button>
            </form>
        </div>

        <!-- Accounts -->
        <div class="login-card accounts-card">
            <h2 class="card-title">Accounts</h2>
            <p class="card-subtitle">Financial Management</p>
            <form id="accountsForm">
                <div class="message" id="accountsMessage"></div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-input password-input" placeholder="Enter password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
                    </div>
                </div>
                <button type="submit" class="login-btn">Access</button>
            </form>
        </div>

        <!-- Audit -->
        <div class="login-card audit-card">
            <h2 class="card-title">Audit</h2>
            <p class="card-subtitle">Compliance & Oversight</p>
            <form id="auditForm">
                <div class="message" id="auditMessage"></div>
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-input password-input" placeholder="Enter password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword(this)">👁️</button>
                    </div>
                </div>
                <button type="submit" class="login-btn">Access</button>
            </form>
        </div>
    </div>

    <script>
        const credentials = {
            storesPurchasing: { username: "Hggstores", password: "Stores123@@" },
            logistics: { username: "Hgglogistics", password: "Logistics123@" },
            inventory: { username: "sachith", password: "123456" },
            accounts: { username: "sachith", password: "123456" },
            audit: { username: "sachith", password: "123456" }
        };

        const formConfigs = {
            storesPurchasingForm: { credentials: credentials.storesPurchasing, redirect: 'stores_login.php', messageId: 'storesMessage' },
            logisticsForm: { credentials: credentials.logistics, redirect: 'logistic_login.php', messageId: 'logisticsMessage' },
            inventoryForm: { credentials: credentials.inventory, redirect: 'inventory_login.php', messageId: 'inventoryMessage' },
            accountsForm: { credentials: credentials.accounts, redirect: 'accounts_login.php', messageId: 'accountsMessage' },
            auditForm: { credentials: credentials.audit, redirect: 'audit_login.php', messageId: 'auditMessage' }
        };

        Object.entries(formConfigs).forEach(([formId, config]) => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const inputs = form.querySelectorAll('.form-input');
                    const username = inputs[0].value;
                    const password = inputs[1].value;
                    const messageDiv = document.getElementById(config.messageId);

                    if (username === config.credentials.username && password === config.credentials.password) {
                        messageDiv.textContent = 'Access Granted! Redirecting...';
                        messageDiv.className = 'message success';
                        setTimeout(() => {
                            window.location.href = config.redirect;
                        }, 1500);
                    } else {
                        messageDiv.textContent = 'Invalid credentials. Please try again.';
                        messageDiv.className = 'message error';
                        setTimeout(() => {
                            messageDiv.className = 'message';
                        }, 4000);
                    }
                });
            }
        });

        function togglePassword(button) {
            const input = button.parentElement.querySelector('.password-input');
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.textContent = isPassword ? '🙈' : '👁️';
        }
    </script>
</body>
</html>