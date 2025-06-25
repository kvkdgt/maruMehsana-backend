<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>maruMehsana - Explore Mehsana like never before</title>
    <meta name="description" content="Discover local businesses, read breaking news, explore tourist spots and connect with the Mehsana community through maruMehsana app.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2596be;
            --primary-dark: #1e7a99;
            --primary-light: #e8f4f8;
            --text-dark: #1a1a1a;
            --text-light: #666666;
            --text-muted: #888888;
            --background: #ffffff;
            --surface: #f8fafc;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: var(--background);
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .logo i {
            font-size: 2rem;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .download-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .download-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Mobile Menu */
        .mobile-menu {
            display: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--text-dark);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            background: linear-gradient(135deg, var(--primary-light) 0%, #ffffff 100%);
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: -50%;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23' + encodeURIComponent('2596be') + '" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23' + encodeURIComponent('2596be') + '" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="%23' + encodeURIComponent('2596be') + '" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
            opacity: 0.3;
            z-index: 1;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 2;
            padding: 8rem 0 4rem;
        }

        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--text-dark) 0%, var(--primary-color) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-tagline {
            font-size: 1.25rem;
            color: var(--text-light);
            margin-bottom: 2rem;
            font-weight: 400;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .hero-stats {
            display: flex;
            gap: 2rem;
            margin-top: 2rem;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .hero-visual {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .phone-mockup {
            width: 300px;
            height: 600px;
            background: linear-gradient(145deg, #1e293b, #334155);
            border-radius: 40px;
            padding: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            position: relative;
            transform: rotate(-5deg);
            animation: float 6s ease-in-out infinite;
        }

        .phone-screen {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 2rem;
        }

        .phone-screen i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .phone-screen h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .phone-screen p {
            opacity: 0.8;
            font-size: 0.875rem;
        }

        @keyframes float {
            0%, 100% { transform: rotate(-5deg) translateY(0px); }
            50% { transform: rotate(-5deg) translateY(-20px); }
        }

        /* Features Section */
        .features {
            padding: 6rem 0;
            background: var(--background);
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .section-subtitle {
            font-size: 1.125rem;
            color: var(--text-light);
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .feature-card {
            background: var(--background);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            text-align: center;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--primary-color);
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-dark);
        }

        .feature-description {
            color: var(--text-light);
            line-height: 1.6;
        }

        /* Screenshots Section */
        .screenshots {
            padding: 6rem 0;
            background: var(--surface);
        }

        .screenshots-carousel {
            display: flex;
            gap: 2rem;
            overflow-x: auto;
            padding: 2rem 0;
            scroll-snap-type: x mandatory;
        }

        .screenshot-item {
            flex: 0 0 250px;
            height: 500px;
            background: linear-gradient(145deg, #f8fafc, #e2e8f0);
            border-radius: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            scroll-snap-align: start;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .screenshot-item:hover {
            transform: scale(1.05);
        }

        .screenshot-item::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 6px;
            background: #1e293b;
            border-radius: 3px;
        }

        .screenshot-content {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-light);
        }

        .screenshot-content i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        /* Testimonials Section */
        .testimonials {
            padding: 6rem 0;
            background: var(--background);
        }

        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }

        .testimonial-card {
            background: var(--background);
            padding: 2rem;
            border-radius: 20px;
            border: 1px solid var(--border);
            position: relative;
        }

        .testimonial-quote {
            font-size: 1.125rem;
            line-height: 1.6;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            font-style: italic;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-weight: 600;
        }

        .author-info h4 {
            font-weight: 600;
            color: var(--text-dark);
        }

        .author-info p {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* About Section */
        .about {
            padding: 6rem 0;
            background: var(--surface);
        }

        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .about-text {
            font-size: 1.125rem;
            line-height: 1.8;
            color: var(--text-light);
        }

        .about-visual {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .about-icon {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        /* Contact Section */
        .contact {
            padding: 6rem 0;
            background: var(--background);
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
        }

        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 500;
            color: var(--text-dark);
        }

        .form-group input,
        .form-group textarea {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .contact-item i {
            width: 50px;
            height: 50px;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .social-link {
            width: 50px;
            height: 50px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
        }

        /* Footer */
        .footer {
            background: var(--text-dark);
            color: white;
            padding: 3rem 0 2rem;
            text-align: center;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 0.5rem;
        }

        .footer-section ul li a {
            color: #cbd5e1;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: var(--primary-color);
        }

        .footer-bottom {
            border-top: 1px solid #374151;
            padding-top: 2rem;
            color: #9ca3af;
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu {
                display: block;
            }

            .hero-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 2rem;
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }

            .hero-stats {
                justify-content: center;
            }

            .phone-mockup {
                width: 250px;
                height: 500px;
            }

            .section-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .about-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .contact-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }

            .hero-text h1 {
                font-size: 2rem;
            }

            .hero-tagline {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="nav">
                <a href="#" class="logo">
                    <i class="fas fa-map-marked-alt"></i>
                    maruMehsana
                </a>
                <ul class="nav-links">
                    <li><a href="#features">Features</a></li>
                    <li><a href="#screenshots">Screenshots</a></li>
                    <li><a href="#about">About</a></li>
                    <li><a href="#contact">Contact</a></li>
                </ul>
                <a href="https://play.google.com/store" class="download-btn">
                    <i class="fab fa-google-play"></i>
                    Download
                </a>
                <div class="mobile-menu">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text fade-in">
                    <h1>maruMehsana</h1>
                    <p class="hero-tagline">Explore Mehsana like never before</p>
                    <div class="hero-buttons">
                        <a href="https://play.google.com/store" class="btn-primary">
                            <i class="fab fa-google-play"></i>
                            Download Now
                        </a>
                    </div>
                    <div class="hero-stats">
                        <div class="stat">
                            <div class="stat-number">10K+</div>
                            <div class="stat-label">Happy Users</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Local Businesses</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">4.8</div>
                            <div class="stat-label">App Rating</div>
                        </div>
                    </div>
                </div>
                <div class="hero-visual fade-in">
                    <div class="phone-mockup">
                        <div class="phone-screen">
                            <i class="fas fa-map-marker-alt"></i>
                            <h3>Discover Mehsana</h3>
                            <p>Your local guide to businesses, news, and events</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Why Choose maruMehsana?</h2>
                <p class="section-subtitle">Everything you need to stay connected with your local Mehsana community</p>
            </div>
            <div class="features-grid">
                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-compass"></i>
                    </div>
                    <h3 class="feature-title">Discover Local Businesses</h3>
                    <p class="feature-description">Find the best restaurants, shops, services, and hidden gems in Mehsana with detailed information and reviews.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <h3 class="feature-title">Local News Updates</h3>
                    <p class="feature-description">Stay informed with breaking news and updates from trusted local news agencies in Mehsana.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="feature-title">Events & Festivals</h3>
                    <p class="feature-description">Never miss out on local festivals, cultural events, and community gatherings happening in Mehsana.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-map-pin"></i>
                    </div>
                    <h3 class="feature-title">Tourist Spots</h3>
                    <p class="feature-description">Explore Mehsana's rich heritage with guides to historical sites, temples, and tourist attractions.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3 class="feature-title">Free Business Listing</h3>
                    <p class="feature-description">Local business owners can list their services for free and connect with thousands of potential customers.</p>
                </div>
                <div class="feature-card fade-in">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="feature-title">Community Driven</h3>
                    <p class="feature-description">Built by locals, for locals. Join a thriving community of Mehsana residents sharing experiences.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Screenshots Section -->
    <section id="screenshots" class="screenshots">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">App Screenshots</h2>
                <p class="section-subtitle">Take a look at our beautiful and intuitive app interface</p>
            </div>
            <div class="screenshots-carousel">
                <div class="screenshot-item">
                    <div class="screenshot-content">
                        <i class="fas fa-home"></i>
                        <h4>Home Screen</h4>
                        <p>Quick access to all features</p>
                    </div>
                </div>
                <div class="screenshot-item">
                    <div class="screenshot-content">
                        <i class="fas fa-search"></i>
                        <h4>Business Search</h4>
                        <p>Find local businesses easily</p>
                    </div>
                </div>
                <div class="screenshot-item">
                    <div class="screenshot-content">
                        <i class="fas fa-newspaper"></i>
                        <h4>News Feed</h4>
                        <p>Latest local news updates</p>
                    </div>
                </div>
                <div class="screenshot-item">
                    <div class="screenshot-content">
                        <i class="fas fa-calendar"></i>
                        <h4>Events</h4>
                        <p>Upcoming local events</p>
                    </div>
                </div>
                <div class="screenshot-item">
                    <div class="screenshot-content">
                        <i class="fas fa-user"></i>
                        <h4>Profile</h4>
                        <p>Personalized experience</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">What Our Users Say</h2>
                <p class="section-subtitle">Trusted by thousands of Mehsana locals</p>
            </div>
            <div class="testimonials-grid">
                <div class="testimonial-card fade-in">
                    <p class="testimonial-quote">"maruMehsana has become my go-to app for discovering new places and staying updated with local news. It's like having a local friend who knows everything about Mehsana!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">RK</div>
                        <div class="author-info">
                            <h4>Rahul Karia</h4>
                            <p>Local Resident</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card fade-in">
                    <p class="testimonial-quote">"As a business owner, listing my restaurant on maruMehsana was completely free and brought so many new customers. The app truly supports local businesses."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">PM</div>
                        <div class="author-info">
                            <h4>Priya Mehta</h4>
                            <p>Restaurant Owner</p>
                        </div>
                    </div>
                </div>
                <div class="testimonial-card fade-in">
                    <p class="testimonial-quote">"The news section keeps me informed about everything happening in our city. It's reliable, fast, and covers all the important local stories."</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">AS</div>
                        <div class="author-info">
                            <h4>Amit Shah</h4>
                            <p>News Enthusiast</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="about-content">
                <div class="fade-in">
                    <h2 class="section-title">About maruMehsana</h2>
                    <div class="about-text">
                        <p>maruMehsana was born from a simple idea: to create a digital bridge connecting the beautiful city of Mehsana with its residents. Our mission is to strengthen the local community by making it easier for people to discover businesses, stay informed with local news, and participate in cultural events.</p>
                        
                        <p>We believe that technology should serve communities, not replace them. That's why we've built maruMehsana as a platform that celebrates local culture, supports small businesses, and keeps everyone connected to what matters most in their hometown.</p>
                        
                        <p>From the bustling markets to the serene temples, from breaking news to festival celebrations, maruMehsana is your companion in exploring and staying connected with the heart of Gujarat.</p>
                    </div>
                </div>
                <div class="about-visual fade-in">
                    <div class="about-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header fade-in">
                <h2 class="section-title">Get In Touch</h2>
                <p class="section-subtitle">Have questions or suggestions? We'd love to hear from you!</p>
            </div>
            <div class="contact-content">
                <div class="contact-form fade-in">
                    <form>
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>
                <div class="contact-info fade-in">
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email Us</h4>
                            <p>hello@marumehsana.com</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h4>Call Us</h4>
                            <p>+91 98765 43210</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h4>Visit Us</h4>
                            <p>Mehsana, Gujarat, India</p>
                        </div>
                    </div>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>maruMehsana</h3>
                    <p>Your local companion for discovering the best of Mehsana. Stay connected, stay informed, stay local.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#screenshots">Screenshots</a></li>
                        <li><a href="#about">About Us</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Download App</h3>
                    <p>Available on Google Play Store</p>
                    <a href="https://play.google.com/store" class="btn-primary" style="margin-top: 1rem;">
                        <i class="fab fa-google-play"></i>
                        Download Now
                    </a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 maruMehsana. All rights reserved. Made with ❤️ in Mehsana, Gujarat.</p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        // Observe all elements with fade-in class
        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.querySelector('.header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
            }
        });

        // Mobile menu toggle (basic implementation)
        const mobileMenu = document.querySelector('.mobile-menu');
        const navLinks = document.querySelector('.nav-links');
        
        mobileMenu.addEventListener('click', () => {
            navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
        });

        // Form submission handler
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            // Simple validation
            if (!data.name || !data.email || !data.message) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Simulate form submission
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                alert('Thank you for your message! We\'ll get back to you soon.');
                this.reset();
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        });

        // Add some interactive hover effects for feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.borderColor = 'var(--primary-color)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.borderColor = 'var(--border)';
            });
        });

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            const heroHeight = hero.offsetHeight;
            
            if (scrolled < heroHeight) {
                const phoneSpeed = scrolled * 0.3;
                const phone = document.querySelector('.phone-mockup');
                if (phone) {
                    phone.style.transform = `rotate(-5deg) translateY(${phoneSpeed}px)`;
                }
            }
        });

        // Statistics counter animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = counter.innerText;
                const isK = target.includes('K');
                const isDecimal = target.includes('.');
                
                let targetNum;
                if (isK) {
                    targetNum = parseFloat(target.replace('K+', '')) * 1000;
                } else if (isDecimal) {
                    targetNum = parseFloat(target);
                } else {
                    targetNum = parseInt(target.replace('+', ''));
                }
                
                let current = 0;
                const increment = targetNum / 100;
                
                const timer = setInterval(() => {
                    current += increment;
                    
                    if (current >= targetNum) {
                        current = targetNum;
                        clearInterval(timer);
                    }
                    
                    if (isK) {
                        counter.innerText = (current / 1000).toFixed(0) + 'K+';
                    } else if (isDecimal) {
                        counter.innerText = current.toFixed(1);
                    } else {
                        counter.innerText = Math.floor(current) + '+';
                    }
                }, 20);
            });
        }

        // Trigger counter animation when hero section is visible
        const heroObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    heroObserver.unobserve(entry.target);
                }
            });
        });

        heroObserver.observe(document.querySelector('.hero-stats'));

        // Add loading animation
        window.addEventListener('load', () => {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease';
            
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>