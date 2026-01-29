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
            --primary: #0077b6;
            --primary-dark: #023e8a;
            --primary-light: #caf0f8;
            --secondary: #48cae4;
            --accent: #f59e0b;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --bg-body: #ffffff;
            --bg-card: #ffffff;
            --bg-alt: #f1f5f9;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --radius: 1rem;
            --container-width: 1200px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background-color: var(--bg-body);
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: var(--container-width);
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: 0.3s ease;
        }

        ul {
            list-style: none;
        }

        /* Header & Nav */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 4.5rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            z-index: 1001;
        }

        .logo i {
            font-size: 1.75rem;
        }

        .logo span {
            color: var(--text-main);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
        }

        .nav-links a {
            font-weight: 600;
            color: var(--text-main);
            font-size: 0.95rem;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .mobile-toggle {
            display: none;
            font-size: 1.5rem;
            color: var(--text-main);
            cursor: pointer;
            z-index: 1001;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            transition: all 0.3s ease;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--primary);
            color: var(--primary);
        }

        .btn-outline:hover {
            background: var(--primary-light);
        }

        /* Hero Section */
        .hero {
            padding: 10rem 0 6rem;
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
            position: relative;
        }

        .hero-content {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 4rem;
            align-items: center;
        }

        .hero-text h1 {
            font-size: 3.5rem;
            line-height: 1.2;
            font-weight: 900;
            margin-bottom: 1.5rem;
            color: var(--text-main);
        }

        .hero-text h1 span {
            color: var(--primary);
        }

        .hero-text p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 3.5rem;
        }

        .hero-stats {
            display: flex;
            gap: 4rem;
        }

        .stat-item h3 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-main);
        }

        .stat-item p {
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero-image {
            position: relative;
            background: #fff;
            padding: 1rem;
            border-radius: 2rem;
            box-shadow: var(--shadow-lg);
        }

        .hero-image img {
            width: 100%;
            height: auto;
            border-radius: 1.5rem;
        }

        /* Sections General */
        section {
            padding: 6rem 0;
        }

        .section-header {
            margin-bottom: 4rem;
        }

        .section-header h2 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }

        .section-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        /* Category Grid */
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .category-card {
            background: #fff;
            padding: 1.5rem;
            border-radius: 1.25rem;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: 0.3s ease;
        }

        .category-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: var(--shadow);
        }

        .category-icon {
            width: 3.5rem;
            height: 3.5rem;
            background: var(--bg-alt);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1rem;
            font-size: 1.5rem;
        }

        .category-card h3 {
            font-size: 1rem;
            font-weight: 700;
        }

        /* Horizontal List Style (Like App) */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: white;
            border-radius: 1.25rem;
            overflow: hidden;
            border: 1px solid var(--border);
            transition: 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            box-shadow: var(--shadow);
            border-color: var(--primary-light);
        }

        .card-image {
            height: 200px;
            position: relative;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .badge {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: var(--primary);
            color: white;
            padding: 0.35rem 0.85rem;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .card-content {
            padding: 1.5rem;
            flex: 1;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-main);
        }

        .card-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--border);
            padding-top: 1rem;
        }

        .card-footer-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
        }

        /* Tourist Grid */
        .tourist-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1rem;
        }

        .tourist-card {
            display: flex;
            background: #fff;
            border-radius: 1.25rem;
            border: 1px solid var(--border);
            overflow: hidden;
            min-height: 140px;
        }

        .tourist-card img {
            width: 140px;
            height: 100%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .tourist-info {
            padding: 1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .tourist-info h3 {
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .tourist-info p {
            font-size: 0.9rem;
            color: var(--text-muted);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Facts Slider */
        .facts-bar {
            background: var(--primary);
            color: white;
            padding: 0.75rem 0;
            margin-bottom: 0;
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        .fact-slider {
            position: relative;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fact-item {
            position: absolute;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            width: 100%;
            text-align: center;
            display: flex;
            justify-content: center;
            pointer-events: none;
        }

        .fact-item.active {
            opacity: 1;
            position: relative;
            pointer-events: auto;
        }

        .fact-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            letter-spacing: 0.3px;
        }

        .fact-content i {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Newsletter */
        .cta-section {
            background: var(--bg-alt);
            border-radius: 2rem;
            padding: 5rem;
            text-align: center;
        }

        /* Footer */
        footer {
            background: #fff;
            border-top: 1px solid var(--border);
            padding: 5rem 0 2rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }

        .footer-logo {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 1.5rem;
            display: block;
        }

        .footer-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: var(--text-muted);
            font-weight: 500;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        @media (max-width: 1024px) {
            .hero-content { grid-template-columns: 1fr; text-align: center; }
            .hero-actions { justify-content: center; }
            .hero-stats { justify-content: center; gap: 2rem;}
            .footer-grid { grid-template-columns: 1fr 1fr; }
            .hero-image { max-width: 500px; margin: 0 auto; }
        }

        @media (max-width: 768px) {
            .mobile-toggle { display: block; }
            .nav-links { 
                position: fixed;
                top: 0;
                right: -100%;
                width: 80%;
                height: 100vh;
                background: white;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                transition: 0.3s ease;
                box-shadow: -10px 0 20px rgba(0,0,0,0.05);
                z-index: 1000;
            }
            .nav-links.active { right: 0; }
            .nav-actions { display: none; }
            
            .hero-text h1 { font-size: 2.5rem; }
            .hero-stats { flex-wrap: wrap; }
            .section-header h2 { font-size: 1.75rem; }
            .footer-grid { grid-template-columns: 1fr; }
            .cta-section { padding: 3rem 1.5rem; }
            .tourist-card { flex-direction: column; min-height: auto; }
            .tourist-card img { width: 100%; height: 180px; }
        }
    </style>
</head>
<body>
    <header id="header">
        <div class="container">
            <nav>
                <a href="/" class="logo">
                    <i class="fas fa-compass"></i>
                    maru<span>Mehsana</span>
                </a>
                <ul class="nav-links" id="navLinks">
                    <li><a href="#categories">Categories</a></li>
                    <li><a href="#businesses">Featured</a></li>
                    <li><a href="#news">News</a></li>
                    <li><a href="#tourist">Places</a></li>
                </ul>
                <div class="nav-actions">
                    <a href="#download" class="btn btn-primary">
                        <i class="fab fa-google-play"></i>
                        Get App
                    </a>
                </div>
                <div class="mobile-toggle" onclick="toggleMenu()">
                    <i class="fas fa-bars"></i>
                </div>
            </nav>
        </div>
    </header>

    <section class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <h1>The Heart of <span>Mehsana</span><br>in Your Pocket</h1>
                <p>Stay connected with your community. Discover local businesses, read real-time news, and explore the heritage of Mehsana city.</p>
                <div class="hero-actions">
                    <a href="#download" class="btn btn-primary">Download Now</a>
                    <a href="#categories" class="btn btn-outline">Browse Categories</a>
                </div>
                <div class="hero-stats">
                    <div class="stat-item">
                        <h3>10K+</h3>
                        <p>Downloads</p>
                    </div>
                    <div class="stat-item">
                        <h3>500+</h3>
                        <p>Businesses</p>
                    </div>
                    <div class="stat-item">
                        <h3>150+</h3>
                        <p>News Daily</p>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <img src="https://images.unsplash.com/photo-1524230572899-a752b3835840?q=80&w=1000&auto=format&fit=crop" alt="Mehsana Banner">
            </div>
        </div>
    </section>

    @if(count($facts) > 0)
    <div class="facts-bar">
        <div class="container">
            <div class="fact-slider">
                @foreach($facts as $key => $fact)
                <div class="fact-item {{ $key == 0 ? 'active' : '' }}">
                    <div class="fact-content">
                        <i class="fas fa-info-circle"></i>
                        <span>DID YOU KNOW? {{ $fact->fact }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <section id="categories">
        <div class="container">
            <div class="section-header">
                <h2>Explore Categories</h2>
                <p>Everything you need, organized just for you.</p>
            </div>
            <div class="category-grid">
                @foreach($categories as $category)
                <div class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-th-large"></i>
                    </div>
                    <h3>{{ $category->name }}</h3>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="businesses" style="background: var(--bg-alt);">
        <div class="container">
            <div class="section-header">
                <h2>Featured Businesses</h2>
                <p>Supporting local entrepreneurs and service providers.</p>
            </div>
            <div class="items-grid">
                @foreach($featuredBusinesses as $business)
                <div class="card">
                    <div class="card-image">
                        <img src="{{ Str::startsWith($business->thumbnail, 'http') ? $business->thumbnail : asset('storage/' . $business->thumbnail) }}" alt="{{ $business->name }}">
                        <span class="badge">{{ $business->category->name }}</span>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">{{ $business->name }}</h3>
                        <p class="card-desc">{{ $business->description }}</p>
                        <div class="card-footer">
                            <div class="card-footer-info">
                                <i class="fas fa-users"></i>
                                <span>{{ $business->visitors }} visitors</span>
                            </div>
                            <a href="/business/{{ $business->id }}" style="color: var(--primary); font-weight: 700;">DETAILS <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="news">
        <div class="container">
            <div class="section-header">
                <h2>Latest Headlines</h2>
                <p>Stay updated with instant local news from trusted sources.</p>
            </div>
            <div class="items-grid">
                @foreach($latestNews as $article)
                <div class="card">
                    <div class="card-image">
                        <img src="{{ $article->imageUrl ?? 'https://images.unsplash.com/photo-1504711331083-9c897511ff04?q=80&w=1000' }}" alt="{{ $article->title }}">
                        <span class="badge" style="background: var(--text-main);">{{ $article->agency->name }}</span>
                    </div>
                    <div class="card-content">
                        <p style="font-size: 0.75rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase;">{{ $article->created_at->diffForHumans() }}</p>
                        <h3 class="card-title" style="font-size: 1.1rem; margin-top: 0.5rem;">{{ $article->title }}</h3>
                        <p class="card-desc" style="-webkit-line-clamp: 3;">{{ $article->excerpt }}</p>
                        <a href="/news/{{ $article->id }}" class="btn btn-outline" style="width: 100%; border-radius: 0.5rem; padding: 0.5rem;">Read Story</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="tourist" style="background: var(--bg-alt);">
        <div class="container">
            <div class="section-header">
                <h2>Visit Mehsana</h2>
                <p>Discover historical gems and natural wonders in our district.</p>
            </div>
            <div class="tourist-list">
                @foreach($touristPlaces as $place)
                <div class="tourist-card">
                    <img src="{{ asset('storage/' . $place->thumbnail) }}" alt="{{ $place->name }}">
                    <div class="tourist-info">
                        <span style="font-size: 0.7rem; color: var(--primary); font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">{{ $place->location }}</span>
                        <h3>{{ $place->name }}</h3>
                        <p>{{ $place->description }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="download">
        <div class="container">
            <div class="cta-section">
                <i class="fas fa-mobile-alt" style="font-size: 4rem; color: var(--primary); margin-bottom: 2rem;"></i>
                <h2 style="font-size: 2.5rem; font-weight: 900; margin-bottom: 1rem;">Experience maruMehsana on Android</h2>
                <p style="font-size: 1.1rem; color: var(--text-muted); max-width: 600px; margin: 0 auto 3rem;">Join thousands of Mehsana citizens. Get the app now and stay closer to your city than ever before.</p>
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <a href="#" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.1rem;">
                        <i class="fab fa-google-play"></i> Get it on Google Play
                    </a>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div>
                    <a href="/" class="footer-logo">maruMehsana</a>
                    <p style="color: var(--text-muted); font-size: 0.95rem;">Building a digital community for Mehsana. Connecting residents with businesses, news, and history.</p>
                </div>
                <div>
                    <h4 class="footer-title">Explore</h4>
                    <ul class="footer-links">
                        <li><a href="#categories">Business Categories</a></li>
                        <li><a href="#businesses">Local Shops</a></li>
                        <li><a href="#news">Daily News</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Guidelines</h4>
                    <ul class="footer-links">
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Disclaimer</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Connect</h4>
                    <ul class="footer-links">
                        <li><a href="mailto:hello@marumehsana.com">Email Us</a></li>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Instagram</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                &copy; {{ date('Y') }} maruMehsana. Dedicated to the people of Mehsana.
            </div>
        </div>
    </footer>

    <script>
        // Fact Slider
        const factItems = document.querySelectorAll('.fact-item');
        let currentFact = 0;

        function showNextFact() {
            if (factItems.length === 0) return;
            factItems[currentFact].classList.remove('active');
            currentFact = (currentFact + 1) % factItems.length;
            factItems[currentFact].classList.add('active');
        }

        if (factItems.length > 1) {
            setInterval(showNextFact, 4000);
        }

        // Mobile Menu toggle
        function toggleMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
            const icon = document.querySelector('.mobile-toggle i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        }

        // Close menu on link click
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                const navLinks = document.getElementById('navLinks');
                if(navLinks.classList.contains('active')) {
                    toggleMenu();
                }
            });
        });

        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                const target = document.querySelector(targetId);
                if (target) {
                    window.scrollTo({
                        top: target.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Simple Fade in on scroll
        const sections = document.querySelectorAll('section');
        const observerOptions = { threshold: 0.1 };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        sections.forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(20px)';
            section.style.transition = 'all 0.6s ease-out';
            observer.observe(section);
        });
    </script>
</body>
</html>