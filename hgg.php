<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Hotel Management System</title>
    <link rel="icon" type="image/avif" href="images/logo.avif">
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- AOS Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --gold-primary: #d4af37;
            --gold-secondary: #f4e4bc;
            --dark-gold: #b8941f;
            --deep-blue: #1a2a4a;
            --light-blue: #2c4270;
            --cream: #f8f6f0;
            --dark-charcoal: #2c2c2c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            height: 100vh;
        }

        /* Video Background */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .video-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, 
                rgba(26, 42, 74, 0.8), 
                rgba(44, 66, 112, 0.7),
                rgba(212, 175, 55, 0.3)
            );
            z-index: 1;
        }

        .video-background video {
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        /* Luxury Floating Elements */
        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 2;
        }

        .floating-star {
            position: absolute;
            color: var(--gold-primary);
            animation: luxuryFloat 20s infinite linear;
            opacity: 0.7;
        }

        @keyframes luxuryFloat {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.7;
            }
            90% {
                opacity: 0.7;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }

        /* Navigation */
        .navbar {
            background: rgba(26, 42, 74, 0.2);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(212, 175, 55, 0.3);
            position: relative;
            z-index: 1000;
            padding: 1.2rem 0;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 2.5rem;
            color: var(--gold-primary) !important;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: fadeInLeft 1s ease-out;
            display: flex;
            align-items: center;
        }

        .navbar-logo {
            width: 70px;
            height: 70px;
            margin-right: 10px;
        }

        .nav-link {
            color: white !important;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            position: relative;
            margin: 0 0.5rem;
        }

        .nav-link:hover {
            color: var(--gold-primary) !important;
            transform: translateY(-2px);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, var(--gold-primary), var(--gold-secondary));
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .navbar-nav {
            animation: fadeInRight 1s ease-out;
        }

        /* Hero Section */
        .hero {
            height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            position: relative;
            z-index: 10;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .hero-subtitle {
            font-size: 1.4rem;
            color: var(--gold-secondary);
            font-weight: 400;
            margin-bottom: 1rem;
            animation: fadeInUp 1s ease-out 0.2s both;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .hero-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 5.5rem;
            font-weight: 900;
            margin-bottom: 2rem;
            background: linear-gradient(45deg, #fff, var(--gold-primary), var(--gold-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeInUp 1s ease-out 0.4s both;
            text-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .hero-content p {
            font-size: 1.4rem;
            margin-bottom: 3rem;
            opacity: 0.9;
            line-height: 1.7;
            animation: fadeInUp 1s ease-out 0.6s both;
        }

        .cta-buttons {
            animation: fadeInUp 1s ease-out 0.8s both;
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-gold {
            background: linear-gradient(45deg, var(--gold-primary), var(--dark-gold));
            border: none;
            color: white;
            padding: 20px 50px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(212, 175, 55, 0.3);
        }

        .btn-gold:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(212, 175, 55, 0.4);
            color: white;
        }

        .btn-outline-gold {
            background: transparent;
            border: 2px solid var(--gold-primary);
            color: var(--gold-primary);
            padding: 18px 50px;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.4s ease;
        }

        .btn-outline-gold:hover {
            background: var(--gold-primary);
            color: white;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(212, 175, 55, 0.3);
        }

        /* Hotel Stats */
        .hotel-stats {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 4rem;
            animation: fadeInUp 1s ease-out 1s both;
        }

        .stat-item {
            text-align: center;
            color: white;
            padding: 1rem;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            min-width: 120px;
        }

        .stat-number {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--gold-primary);
            display: block;
            font-family: 'Playfair Display', serif;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 0.5rem;
        }

       
        .quick-access {
            position: fixed;
            top: 50%;
            right: 30px;
            transform: translateY(-50%);
            z-index: 500;
            animation: fadeInRight 1s ease-out 1.2s both;
        }

        .access-btn {
            display: block;
            width: 60px;
            height: 60px;
            background: rgba(212, 175, 55, 0.9);
            color: white;
            border: none;
            border-radius: 50%;
            margin-bottom: 15px;
            font-size: 1.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .access-btn:hover {
            transform: scale(1.1);
            background: var(--gold-primary);
            box-shadow: 0 10px 25px rgba(212, 175, 55, 0.4);
        }

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

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Glowing effect */
        .glow {
            animation: glow 3s ease-in-out infinite alternate;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 20px rgba(212, 175, 55, 0.5);
            }
            to {
                text-shadow: 0 0 30px rgba(212, 175, 55, 0.8), 0 0 40px rgba(212, 175, 55, 0.8);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 3.5rem;
            }
            
            .hero-content p {
                font-size: 1.2rem;
            }
            
            .cta-buttons {
                flex-direction: row;
                gap: 0.5rem;
            }

            .btn-gold, .btn-outline-gold {
                padding: 15px 30px;
                font-size: 1rem;
                width: auto;
            }

            .hotel-stats {
                flex-direction: column;
                gap: 1.5rem;
                bottom: 20px;
            }

            .quick-access {
                display: none;
            }

            .navbar-brand {
                font-size: 2rem;
            }

            .navbar-logo {
                width: 40px;
                height: 40px;
            }
        }

        /* Ripple Effect */
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.6);
            transform: scale(0);
            animation: rippleEffect 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes rippleEffect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Video Background -->
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <!-- Hotel luxury lobby/exterior video -->
            <source src="images/HGG Video.mp4" type="video/mp4">
        </video>
    </div>

    <!-- Floating Elements -->
    <div class="floating-elements" id="floatingElements"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/logo.avif" alt="Grand Luxe Logo" class="navbar-logo">
                <i class=" me-2"></i>Guardia HMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-subtitle">Guardia Ves 4.0</div>
                <h1 class="glow">Premium Hotel Management System</h1>
                
                <div class="cta-buttons">
                    <a href="restaurant.php" class="btn btn-gold">
                        <i class="fas fa-utensils me-2"></i>Restaurant
                    </a>
                    <a href="office_login.php" class="btn btn-gold">
                        <i class="fas fa-briefcase me-2"></i>Back Office
                    </a>
                    <a href="mainoffice.php" class="btn btn-gold">
                        <i class="fas fa-building me-2"></i>Main Office
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Access Panel -->
    

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS Animation JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
        // Create floating luxury elements
        function createFloatingElements() {
            const container = document.getElementById('floatingElements');
            const icons = ['★', '◆', '♦', '✦', '✧'];
            const elementCount = 25;

            for (let i = 0; i < elementCount; i++) {
                const element = document.createElement('div');
                element.classList.add('floating-star');
                element.innerHTML = icons[Math.floor(Math.random() * icons.length)];
                
                element.style.left = Math.random() * 100 + '%';
                element.style.fontSize = (Math.random() * 1 + 0.5) + 'rem';
                element.style.animationDuration = (Math.random() * 15 + 15) + 's';
                element.style.animationDelay = Math.random() * 20 + 's';
                
                container.appendChild(element);
            }
        }

        // Button click effects with ripple
        document.querySelectorAll('.btn, .access-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                // Ripple effect
                let ripple = document.createElement('span');
                let rect = this.getBoundingClientRect();
                let size = Math.max(rect.width, rect.height);
                let x = e.clientX - rect.left - size / 2;
                let y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Initialize everything when page loads
        window.addEventListener('load', () => {
            createFloatingElements();
            AOS.init();
        });

        // Navigation link hover effects
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('mouseenter', function() {
                this.style.textShadow = '0 0 10px rgba(212, 175, 55, 0.8)';
            });
            
            link.addEventListener('mouseleave', function() {
                this.style.textShadow = 'none';
            });
        });
    </script>
</body>
</html>