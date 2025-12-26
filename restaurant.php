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

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            gap: 30px;
            padding: 20px;
            position: relative;
            z-index: 10;
            flex-wrap: wrap;
        }

        /* Card base styles */
        .login-card {
            width: 350px;
            height: 480px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 30px;
            box-shadow: 
                0 25px 50px rgba(0,0,0,0.2),
                0 0 0 1px rgba(255,255,255,0.3),
                inset 0 1px 0 rgba(255,255,255,0.4);
            position: relative;
            overflow: hidden;
            transition: all 0.6s cubic-bezier(0.23, 1, 0.320, 1);
            transform-style: preserve-3d;
        }

        /* Main Kitchen Card */
        .main-kitchen-card {
            border: 2px solid rgba(255, 138, 0, 0.3);
        }

        .main-kitchen-card:hover {
            transform: scale(1.02);
            box-shadow: 
                0 0 50px rgba(255, 138, 0, 0.6),
                0 0 100px rgba(255, 138, 0, 0.4),
                0 35px 70px rgba(255, 138, 0, 0.3),
                inset 0 0 30px rgba(255, 138, 0, 0.1);
            border-color: rgba(255, 138, 0, 0.8);
        }

        .main-kitchen-card .card-overlay {
            background: linear-gradient(135deg, 
                rgba(255, 138, 0, 0.1) 0%, 
                rgba(255, 87, 34, 0.1) 100%);
            animation: gradientShift 3s ease-in-out infinite;
        }

        .main-kitchen-card .card-decoration {
            background: linear-gradient(135deg, #ff8a00, #ff5733);
            animation: decorationSpin 10s linear infinite;
        }

        /* HGG Bar & Restaurant Card */
        .hgg-bar-card {
            border: 2px solid rgba(46, 204, 113, 0.3);
        }

        .hgg-bar-card:hover {
            transform: scale(1.02);
            box-shadow: 
                0 0 50px rgba(46, 204, 113, 0.6),
                0 0 100px rgba(46, 204, 113, 0.4),
                0 35px 70px rgba(46, 204, 113, 0.3),
                inset 0 0 30px rgba(46, 204, 113, 0.1);
            border-color: rgba(46, 204, 113, 0.8);
        }

        .hgg-bar-card .card-overlay {
            background: linear-gradient(135deg, 
                rgba(46, 204, 113, 0.1) 0%, 
                rgba(39, 174, 96, 0.1) 100%);
            animation: gradientShift 3s ease-in-out infinite reverse;
        }

        .hgg-bar-card .card-decoration {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            animation: decorationSpin 10s linear infinite reverse;
        }

        /* Sky Restaurant Card */
        .sky-restaurant-card {
            border: 2px solid rgba(52, 152, 219, 0.3);
        }

        .sky-restaurant-card:hover {
            transform: scale(1.02);
            box-shadow: 
                0 0 50px rgba(52, 152, 219, 0.6),
                0 0 100px rgba(52, 152, 219, 0.4),
                0 35px 70px rgba(52, 152, 219, 0.3),
                inset 0 0 30px rgba(52, 152, 219, 0.1);
            border-color: rgba(52, 152, 219, 0.8);
        }

        .sky-restaurant-card .card-overlay {
            background: linear-gradient(135deg, 
                rgba(52, 152, 219, 0.1) 0%, 
                rgba(41, 128, 185, 0.1) 100%);
            animation: gradientShift 3s ease-in-out infinite;
        }

        .sky-restaurant-card .card-decoration {
            background: linear-gradient(135deg, #3498db, #2980b9);
            animation: decorationSpin 10s linear infinite;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 0.1; transform: rotate(0deg) scale(1); }
            50% { opacity: 0.3; transform: rotate(180deg) scale(1.1); }
        }

        .card-title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        .main-kitchen-card .card-title {
            animation: titlePulse 2s ease-in-out infinite;
        }

        .hgg-bar-card .card-title {
            animation: titleGlow 2.5s ease-in-out infinite;
        }

        .sky-restaurant-card .card-title {
            animation: titlePulse 2.2s ease-in-out infinite;
        }

        @keyframes titlePulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes titleGlow {
            0%, 100% { 
                text-shadow: 0 0 10px rgba(46, 204, 113, 0.3);
                transform: translateY(0px);
            }
            50% { 
                text-shadow: 0 0 20px rgba(46, 204, 113, 0.6);
                transform: translateY(-2px);
            }
        }

        .card-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
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
            font-size: 15px;
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
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
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

        /* Responsive design */
        @media (max-width: 1200px) {
            .container {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .login-card {
                width: 320px;
                height: 460px;
                padding: 25px;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                gap: 20px;
            }
            
            .login-card {
                width: 90%;
                max-width: 350px;
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

    <div class="container">
        <!-- Main Kitchen Login Card -->
        <div class="login-card main-kitchen-card">
            <div class="card-overlay"></div>
            <div class="card-decoration"></div>
            <h2 class="card-title">Main Kitchen</h2>
            <p class="card-subtitle">Culinary Operations</p>
            
            <form id="mainKitchenForm">
                <div class="form-group">
                    <input type="text" class="form-input" id="kitchenUsername" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" id="kitchenPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Access Main Kitchen</button>
            </form>
        </div>

        <!-- HGG Bar & Restaurant Login Card -->
        <div class="login-card hgg-bar-card">
            <div class="card-overlay"></div>
            <div class="card-decoration"></div>
            <h2 class="card-title">HGG Bar & Restaurant</h2>
            <p class="card-subtitle">Dining & Beverage Services</p>
            
            <form id="hggBarForm">
                <div class="form-group">
                    <input type="text" class="form-input" id="hggUsername" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" id="hggPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Access HGG Bar</button>
            </form>
        </div>

        <!-- Sky Restaurant Login Card -->
        <div class="login-card sky-restaurant-card">
            <div class="card-overlay"></div>
            <div class="card-decoration"></div>
            <h2 class="card-title">Sky Restaurant</h2>
            <p class="card-subtitle">Rooftop Dining Experience</p>
            
            <form id="skyRestaurantForm">
                <div class="form-group">
                    <input type="text" class="form-input" id="skyUsername" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-input" id="skyPassword" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Access Sky Restaurant</button>
            </form>
        </div>
    </div>

    <script>
        // Manual credentials (not from database)
        const credentials = {
            mainKitchen: {
                username: "sachith",
                password: "123456"
            },
            hggBar: {
                username: "sachith",
                password: "123456"
            },
            skyRestaurant: {
                username: "sachith",
                password: "123456"
            }
        };

        // Main Kitchen Form Handler
        document.getElementById('mainKitchenForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('kitchenUsername').value;
            const password = document.getElementById('kitchenPassword').value;
            const card = document.querySelector('.main-kitchen-card');
            
            if (username === credentials.mainKitchen.username && password === credentials.mainKitchen.password) {
                card.classList.add('success');
                showSuccessMessage(card, 'Access Granted! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = 'Mainkitchen_login.php';
                }, 1500);
            } else {
                showErrorMessage(card, 'Invalid credentials. Please try again.');
            }
        });

        // HGG Bar & Restaurant Form Handler
        document.getElementById('hggBarForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('hggUsername').value;
            const password = document.getElementById('hggPassword').value;
            const card = document.querySelector('.hgg-bar-card');
            
            if (username === credentials.hggBar.username && password === credentials.hggBar.password) {
                card.classList.add('success');
                showSuccessMessage(card, 'Access Granted! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = 'Hgg_restaurant_login.php';
                }, 1500);
            } else {
                showErrorMessage(card, 'Invalid credentials. Please try again.');
            }
        });

        // Sky Restaurant Form Handler
        document.getElementById('skyRestaurantForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('skyUsername').value;
            const password = document.getElementById('skyPassword').value;
            const card = document.querySelector('.sky-restaurant-card');
            
            if (username === credentials.skyRestaurant.username && password === credentials.skyRestaurant.password) {
                card.classList.add('success');
                showSuccessMessage(card, 'Access Granted! Redirecting...');
                
                setTimeout(() => {
                    window.location.href = 'sky_login.php';
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