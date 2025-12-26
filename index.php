<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management System - Christmas Edition</title>
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
            --christmas-red: #c41e3a;
            --christmas-green: #0f7a3e;
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

        /* Snowfall Effect */
        .snowflake {
            position: fixed;
            top: -10px;
            z-index: 9999;
            user-select: none;
            pointer-events: none;
            animation: fall linear infinite;
            color: white;
            text-shadow: 0 0 5px rgba(255, 255, 255, 0.8);
        }

        @keyframes fall {
            to {
                transform: translateY(100vh);
            }
        }

        /* Neon Merry Christmas Banner */
        .christmas-banner {
            position: fixed;
            top: 100px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            text-align: center;
            animation: bannerSlideDown 1.5s ease-out;
        }

        @keyframes bannerSlideDown {
            from {
                top: -200px;
                opacity: 0;
            }
            to {
                top: 100px;
                opacity: 1;
            }
        }

        .neon-text {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 5px;
            animation: neonGlow 1.5s ease-in-out infinite alternate;
            text-shadow: 
                0 0 10px #fff,
                0 0 20px #fff,
                0 0 30px var(--christmas-red),
                0 0 40px var(--christmas-red),
                0 0 50px var(--christmas-red),
                0 0 60px var(--christmas-red),
                0 0 70px var(--christmas-red);
            color: #fff;
        }

        @keyframes neonGlow {
            from {
                text-shadow: 
                    0 0 10px #fff,
                    0 0 20px #fff,
                    0 0 30px var(--christmas-red),
                    0 0 40px var(--christmas-red),
                    0 0 50px var(--christmas-red);
            }
            to {
                text-shadow: 
                    0 0 20px #fff,
                    0 0 30px var(--christmas-green),
                    0 0 40px var(--christmas-green),
                    0 0 50px var(--christmas-green),
                    0 0 60px var(--christmas-green),
                    0 0 70px var(--christmas-green),
                    0 0 80px var(--christmas-green);
            }
        }

        .neon-subtitle {
            font-size: 1.5rem;
            color: var(--gold-primary);
            margin-top: 10px;
            animation: subtitlePulse 2s ease-in-out infinite;
            text-shadow: 0 0 10px var(--gold-primary);
        }

        @keyframes subtitlePulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.7;
                transform: scale(1.05);
            }
        }

        /* Christmas Tree */
        .christmas-tree {
            position: fixed;
            bottom: 50px;
            right: 50px;
            z-index: 1001;
            font-size: 8rem;
            filter: drop-shadow(0 0 20px rgba(15, 122, 62, 0.5));
            animation: treeAppear 2s ease-out 3s both;
        }

        @keyframes treeAppear {
            from {
                transform: scale(0) rotate(-180deg);
                opacity: 0;
            }
            to {
                transform: scale(1) rotate(0deg);
                opacity: 1;
            }
        }

        .tree-lit {
            animation: treeLightUp 0.5s ease-out forwards;
        }

        @keyframes treeLightUp {
            0% {
                filter: drop-shadow(0 0 20px rgba(15, 122, 62, 0.5));
            }
            100% {
                filter: drop-shadow(0 0 60px gold) 
                        drop-shadow(0 0 80px gold) 
                        drop-shadow(0 0 100px gold);
                transform: scale(1.1);
            }
        }

        /* Santa and Reindeer Animation */
        .santa-sleigh {
            position: fixed;
            width: 250px;
            height: auto;
            z-index: 10000;
            filter: drop-shadow(0 0 30px rgba(255, 215, 0, 0.9));
            animation: santaJourney 35s linear infinite;
            pointer-events: none;
        }

        .santa-sleigh img {
            width: 100%;
            height: auto;
            display: block;
        }

        /* Santa's Magical Journey Path */
        @keyframes santaJourney {
            0% {
                left: -300px;
                top: 15%;
                transform: rotate(-10deg) scale(0.7);
            }
            8% {
                left: 15%;
                top: 8%;
                transform: rotate(5deg) scale(0.9);
            }
            16% {
                left: 35%;
                top: 12%;
                transform: rotate(-3deg) scale(1);
            }
            24% {
                left: 55%;
                top: 18%;
                transform: rotate(8deg) scale(1.1);
            }
            32% {
                left: 75%;
                top: 25%;
                transform: rotate(-5deg) scale(1);
            }
            40% {
                left: 90%;
                top: 35%;
                transform: rotate(-12deg) scale(0.9);
            }
            48% {
                left: 85%;
                top: 50%;
                transform: rotate(-8deg) scale(1);
            }
            56% {
                left: 70%;
                top: 65%;
                transform: rotate(5deg) scale(1.05);
            }
            64% {
                left: 50%;
                top: 75%;
                transform: rotate(10deg) scale(1.1);
            }
            72% {
                left: 30%;
                top: 70%;
                transform: rotate(-5deg) scale(1);
            }
            80% {
                left: 15%;
                top: 55%;
                transform: rotate(8deg) scale(0.95);
            }
            88% {
                left: 5%;
                top: 40%;
                transform: rotate(-10deg) scale(0.85);
            }
            100% {
                left: -300px;
                top: 15%;
                transform: rotate(-10deg) scale(0.7);
            }
        }

        /* Bright Glowing Trail */
        .santa-glow {
            position: fixed;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(
                circle,
                rgba(255, 215, 0, 0.4) 0%,
                rgba(255, 237, 78, 0.3) 25%,
                rgba(255, 255, 255, 0.2) 50%,
                transparent 70%
            );
            pointer-events: none;
            z-index: 9999;
            animation: glowPulse 1.5s ease-in-out infinite;
            transform: translate(-50%, -50%);
            filter: blur(15px);
        }

        @keyframes glowPulse {
            0%, 100% {
                opacity: 0.8;
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1.3);
            }
        }

        /* Light Trail Particles */
        .light-trail {
            position: fixed;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: radial-gradient(circle, #ffd700 0%, #ffed4e 40%, transparent 80%);
            box-shadow: 
                0 0 20px #ffd700,
                0 0 40px #ffed4e,
                0 0 60px #ffd700,
                0 0 80px #fff;
            pointer-events: none;
            z-index: 9998;
            animation: trailFade 2.5s ease-out forwards;
        }

        @keyframes trailFade {
            0% {
                opacity: 1;
                transform: scale(1);
            }
            100% {
                opacity: 0;
                transform: scale(0.2);
            }
        }

        /* Sparkle Effects */
        .sparkle {
            position: fixed;
            font-size: 1.8rem;
            z-index: 9998;
            pointer-events: none;
            animation: sparkleFall 3s ease-out forwards;
            text-shadow: 0 0 10px gold;
        }

        @keyframes sparkleFall {
            0% {
                opacity: 1;
                transform: translateY(0) rotate(0deg) scale(1);
            }
            100% {
                opacity: 0;
                transform: translateY(120px) rotate(720deg) scale(0.2);
            }
        }

        /* Magic Dust */
        .magic-dust {
            position: fixed;
            width: 5px;
            height: 5px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 
                0 0 10px #ffd700,
                0 0 20px #fff;
            z-index: 9998;
            pointer-events: none;
            animation: dustFloat 3.5s ease-out forwards;
        }

        @keyframes dustFloat {
            0% {
                opacity: 1;
                transform: translate(0, 0) scale(1);
            }
            100% {
                opacity: 0;
                transform: translate(
                    calc(-80px + (var(--random-x, 0) * 160px)),
                    calc(60px + (var(--random-y, 0) * 80px))
                ) scale(0);
            }
        }

        /* Christmas Lights */
        .christmas-lights {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 30px;
            z-index: 999;
            display: flex;
            justify-content: space-around;
        }

        .light {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin: 5px;
            animation: blink 1s infinite;
        }

        .light:nth-child(odd) {
            background: var(--christmas-red);
            box-shadow: 0 0 15px var(--christmas-red);
        }

        .light:nth-child(even) {
            background: var(--christmas-green);
            box-shadow: 0 0 15px var(--christmas-green);
            animation-delay: 0.5s;
        }

        @keyframes blink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.3;
            }
        }

        /* Floating Elements */
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
            padding-top: 150px;
        }

        .hero-content {
            max-width: 1200px;
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

        /* Card Section */
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            padding: 2rem;
        }

        .hotel-card {
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-radius: 15px;
            width: 300px;
            padding: 2rem;
            text-align: center;
            color: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hotel-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(212, 175, 55, 0.3);
        }

        .card-logo-circle {
            position: absolute;
            top: -50px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, var(--gold-primary), var(--gold-secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .card-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        .hotel-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin-top: 3rem;
            margin-bottom: 1rem;
            color: var(--gold-primary);
        }

        .hotel-card p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .btn-card {
            background: linear-gradient(45deg, var(--gold-primary), var(--dark-gold));
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.4s ease;
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }

        .btn-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.4);
            color: white;
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

        /* Responsive Design */
        @media (max-width: 576px) {
            .neon-text {
                font-size: 2rem;
            }
            .neon-subtitle {
                font-size: 1rem;
            }
            .christmas-banner {
                top: 80px;
            }
            .hero {
                padding-top: 120px;
            }
            .hero-content h1 {
                font-size: 2.5rem;
            }
            .hero-subtitle {
                font-size: 1rem;
                letter-spacing: 1px;
            }
            .hotel-card {
                padding: 1.5rem;
                width: 100%;
                max-width: 300px;
            }
            .card-container {
                gap: 1rem;
                padding: 1rem;
            }
            .navbar-brand {
                font-size: 1.8rem;
            }
            .navbar-logo {
                width: 35px;
                height: 35px;
            }
            .christmas-tree {
                font-size: 4rem;
                bottom: 20px;
                right: 20px;
            }
            .santa-sleigh {
                width: 150px;
            }
            .santa-glow {
                width: 200px;
                height: 200px;
            }
        }

        @media (max-width: 768px) {
            .neon-text {
                font-size: 2.5rem;
            }
            .christmas-banner {
                top: 90px;
            }
            .hero {
                padding-top: 130px;
            }
            .hero-content h1 {
                font-size: 3.5rem;
            }
            .hotel-card {
                width: 100%;
                max-width: 300px;
            }
            .video-background video {
                display: none;
            }
            .video-background {
                background-image: url('images/fallback-bg.jpg');
                background-size: cover;
                background-position: center;
            }
            .santa-sleigh {
                width: 180px;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .hero-content h1 {
                font-size: 4.5rem;
            }
            .hotel-card {
                width: 45%;
            }
        }

        @media (min-width: 1201px) {
            .hero-content {
                max-width: 1400px;
            }
            .hotel-card {
                width: 22%;
            }
            .hero-content h1 {
                font-size: 6rem;
            }
        }

        @media (max-width: 768px) and (orientation: landscape) {
            .hero {
                height: auto;
                padding: 2rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Christmas Lights -->
    <div class="christmas-lights" id="christmasLights"></div>

    <!-- Neon Merry Christmas Banner -->
    <div class="christmas-banner">
        <div class="neon-text">🎄 Merry Christmas 🎄</div>
        <div class="neon-subtitle">✨ Season's Greetings ✨</div>
    </div>

    <!-- Christmas Tree -->
    <div class="christmas-tree" id="christmasTree">🎄</div>

    <!-- Santa and Reindeer with Glow -->
    <div class="santa-glow" id="santaGlow"></div>
    <div class="santa-sleigh" id="santaSleigh">
        <img src="santa-reindeer.png" alt="Santa with Reindeer" id="santaImage">
    </div>

    <!-- Video Background -->
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="image/vc.mp4" type="video/mp4">
        </video>
    </div>

    <!-- Floating Elements -->
    <div class="floating-elements" id="floatingElements"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <img src="images/logo.avif" alt="Grand Luxe Logo" class="navbar-logo">
                <i class="me-2"></i>Guardia HMS
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
                <div class="card-container">
                    <div class="hotel-card" data-aos="fade-up">
                        <div class="card-logo-circle">
                            <img src="images/logo.avif" alt="Hotel Grand Guardian Logo" class="card-logo">
                        </div>
                        <h3>Hotel Grand Guardian</h3>
                        <p>Luxury and comfort in the heart of the city.</p>
                        <a href="hgg.php" class="btn btn-card">Continue</a>
                    </div>
                    <div class="hotel-card" data-aos="fade-up" data-aos-delay="100">
                        <div class="card-logo-circle">
                            <img src="image/Grand View lodge logo png.png" alt="Grand View Lodge Logo" class="card-logo">
                        </div>
                        <h3>Grand View Lodge</h3>
                        <p>Experience nature with unparalleled elegance.</p>
                        <a href="lodge/frontoffice_login.php" class="btn btn-card">Continue</a>
                    </div>
                    <div class="hotel-card" data-aos="fade-up" data-aos-delay="200">
                        <div class="card-logo-circle">
                            <img src="image/rose logo png.png" alt="Hotel Rose Garden Logo" class="card-logo">
                        </div>
                        <h3>Hotel Rose Garden</h3>
                        <p>A serene escape with timeless charm.</p>
                        <a href="rose/frontoffice_login.php" class="btn btn-card">Continue</a>
                    </div>
                    <div class="hotel-card" data-aos="fade-up" data-aos-delay="300">
                        <div class="card-logo-circle">
                            <img src="image/sapthapadhi logo png.png" alt="Hotel Sapthapadhi Logo" class="card-logo">
                        </div>
                        <h3>Hotel Sapthapadhi</h3>
                        <p>Celebrate tradition with modern luxury.</p>
                        <a href="sapthapadhi/frontoffice_login.php" class="btn btn-card">Continue</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
        // Create snowfall effect
        function createSnowflakes() {
            const snowflakeCount = window.innerWidth <= 768 ? 30 : 50;
            const snowflakes = ['❄', '❅', '❆'];
            
            for (let i = 0; i < snowflakeCount; i++) {
                const snowflake = document.createElement('div');
                snowflake.classList.add('snowflake');
                snowflake.innerHTML = snowflakes[Math.floor(Math.random() * snowflakes.length)];
                snowflake.style.left = Math.random() * 100 + '%';
                snowflake.style.fontSize = (Math.random() * 1.5 + 0.5) + 'rem';
                snowflake.style.animationDuration = (Math.random() * 3 + 2) + 's';
                snowflake.style.animationDelay = Math.random() * 5 + 's';
                snowflake.style.opacity = Math.random() * 0.6 + 0.4;
                document.body.appendChild(snowflake);
            }
        }

        // Create Christmas lights
        function createChristmasLights() {
            const lightsContainer = document.getElementById('christmasLights');
            const lightCount = window.innerWidth <= 768 ? 15 : 30;
            
            for (let i = 0; i < lightCount; i++) {
                const light = document.createElement('div');
                light.classList.add('light');
                light.style.animationDelay = (Math.random() * 1) + 's';
                lightsContainer.appendChild(light);
            }
        }

        // Create floating luxury elements
        function createFloatingElements() {
            const container = document.getElementById('floatingElements');
            const icons = ['★', '◆', '♦', '✦', '✧'];
            const elementCount = window.innerWidth <= 768 ? 10 : 25;

            for (let i = 0; i < elementCount; i++) {
                const element = document.createElement('div');
                element.classList.add('floating-star');
                element.innerHTML = icons[Math.floor(Math.random() * icons.length)];
                element.style.left = Math.random() * 100 + '%';
                element.style.fontSize = (Math.random() * 0.8 + 0.4) + 'rem';
                element.style.animationDuration = (Math.random() * 15 + 15) + 's';
                element.style.animationDelay = Math.random() * 20 + 's';
                container.appendChild(element);
            }
        }

        // Santa Animation Functions
        const santa = document.getElementById('santaSleigh');
        const santaGlow = document.getElementById('santaGlow');
        const santaImage = document.getElementById('santaImage');

        // Fallback if image doesn't load
        santaImage.onerror = function() {
            santa.innerHTML = '<div style="font-size: 120px;">🎅🦌🦌</div>';
        };

        // Update glow position to follow Santa
        function updateGlowPosition() {
            const rect = santa.getBoundingClientRect();
            santaGlow.style.left = (rect.left + rect.width / 2) + 'px';
            santaGlow.style.top = (rect.top + rect.height / 2) + 'px';
            requestAnimationFrame(updateGlowPosition);
        }

        // Create bright light trail
        function createLightTrail() {
            const rect = santa.getBoundingClientRect();
            const trail = document.createElement('div');
            trail.className = 'light-trail';
            trail.style.left = (rect.left + rect.width / 2) + 'px';
            trail.style.top = (rect.top + rect.height / 2) + 'px';
            document.body.appendChild(trail);

            setTimeout(() => trail.remove(), 2500);
        }

        // Create sparkle effects
        function createSparkle() {
            const rect = santa.getBoundingClientRect();
            const sparkle = document.createElement('div');
            sparkle.className = 'sparkle';
            sparkle.innerHTML = ['✨', '⭐', '💫', '🌟'][Math.floor(Math.random() * 4)];
            sparkle.style.left = (rect.left + Math.random() * rect.width) + 'px';
            sparkle.style.top = (rect.top + Math.random() * rect.height) + 'px';
            document.body.appendChild(sparkle);

            setTimeout(() => sparkle.remove(), 3000);
        }

        // Create magic dust particles
        function createMagicDust() {
            const rect = santa.getBoundingClientRect();
            for (let i = 0; i < 3; i++) {
                const dust = document.createElement('div');
                dust.className = 'magic-dust';
                dust.style.left = (rect.left + rect.width / 2 + (Math.random() - 0.5) * 100) + 'px';
                dust.style.top = (rect.top + rect.height / 2 + (Math.random() - 0.5) * 100) + 'px';
                dust.style.setProperty('--random-x', Math.random());
                dust.style.setProperty('--random-y', Math.random());
                document.body.appendChild(dust);

                setTimeout(() => dust.remove(), 3500);
            }
        }

        // Button click effects with ripple
        document.querySelectorAll('.btn, .access-btn').forEach(button => {
            button.addEventListener('click', function(e) {
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
            createSnowflakes();
            createChristmasLights();
            createFloatingElements();
            AOS.init({
                disable: window.innerWidth < 768 ? 'mobile' : false,
                duration: window.innerWidth < 768 ? 500 : 1000
            });
            
            // Start Santa animations
            updateGlowPosition();
            setInterval(createLightTrail, 40);
            setInterval(createSparkle, 250);
            setInterval(createMagicDust, 120);
        });
    </script>
</body>
</html>