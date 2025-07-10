<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>News Agency Portal - Login</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Inter', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #1a252f 0%, #2c3e50 50%, #34495e 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow-x: hidden;
      position: relative;
    }

    /* Animated background elements */
    .bg-overlay {
      position: absolute;
      width: 100%;
      height: 100%;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="0.5" fill="rgba(255,255,255,0.03)"/><circle cx="75" cy="75" r="0.3" fill="rgba(255,255,255,0.02)"/><circle cx="50" cy="10" r="0.4" fill="rgba(255,255,255,0.025)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      z-index: 1;
    }

    .floating-elements {
      position: absolute;
      width: 100%;
      height: 100%;
      overflow: hidden;
      z-index: 2;
    }

    .news-particle {
      position: absolute;
      color: rgba(255, 255, 255, 0.1);
      font-size: 12px;
      font-weight: 600;
      animation: newsFloat 15s linear infinite;
    }

    @keyframes newsFloat {
      0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
      10% { opacity: 1; }
      90% { opacity: 1; }
      100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
    }

    .login-container {
      width: 90%;
      max-width: 1200px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border-radius: 24px;
      overflow: hidden;
      display: flex;
      box-shadow: 
        0 25px 50px rgba(0, 0, 0, 0.4),
        0 0 0 1px rgba(255, 255, 255, 0.1);
      position: relative;
      z-index: 10;
      min-height: 600px;
    }

    /* Content Side */
    .content-side {
      flex: 1.2;
      background: linear-gradient(135deg, #2c3e50 0%, #34495e 50%, #2c3e50 100%);
      padding: 50px;
      color: white;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .content-side::before {
      content: '';
      position: absolute;
      width: 400px;
      height: 400px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      top: -150px;
      right: -150px;
      animation: pulse 8s ease-in-out infinite;
    }

    .content-side::after {
      content: '';
      position: absolute;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.03);
      border-radius: 50%;
      bottom: -100px;
      left: -100px;
      animation: pulse 6s ease-in-out infinite reverse;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); opacity: 0.3; }
      50% { transform: scale(1.1); opacity: 0.1; }
    }

    .brand-header {
      position: relative;
      z-index: 3;
    }

    .news-logo {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 30px;
    }

    .logo-icon {
      width: 50px;
      height: 50px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .brand-title {
      font-size: 32px;
      font-weight: 800;
      letter-spacing: -1px;
      background: linear-gradient(135deg, #ffffff, #ecf0f1);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .content-main {
      position: relative;
      z-index: 3;
      margin: 40px 0;
    }

    .welcome-title {
      font-size: 42px;
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 20px;
      color: #ffffff;
    }

    .welcome-subtitle {
      font-size: 18px;
      color: rgba(255, 255, 255, 0.8);
      line-height: 1.6;
      margin-bottom: 30px;
    }

    .feature-list {
      list-style: none;
      margin-bottom: 30px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
      font-size: 15px;
      color: rgba(255, 255, 255, 0.9);
    }

    .feature-icon {
      width: 20px;
      height: 20px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .content-footer {
      position: relative;
      z-index: 3;
      font-size: 14px;
      color: rgba(255, 255, 255, 0.6);
    }

    /* Form Side */
    .form-side {
      flex: 1;
      padding: 50px;
      background: #ffffff;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
    }

    .form-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .form-title {
      font-size: 28px;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 8px;
      letter-spacing: -0.5px;
    }

    .form-subtitle {
      color: #7f8c8d;
      font-size: 15px;
      font-weight: 400;
    }

    .error-message {
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-size: 14px;
      font-weight: 500;
      box-shadow: 0 4px 12px rgba(231, 76, 60, 0.25);
      border-left: 4px solid #c0392b;
    }

    .form-container {
      margin-bottom: 30px;
    }

    .input-group {
      position: relative;
      margin-bottom: 25px;
    }

    .input-label {
      display: block;
      color: #2c3e50;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 8px;
      transition: all 0.3s ease;
    }

    .input-wrapper {
      position: relative;
    }

    .form-input {
      width: 100%;
      padding: 18px 55px 18px 18px;
      border: 2px solid #ecf0f1;
      border-radius: 14px;
      font-size: 15px;
      color: #2c3e50;
      background: #ffffff;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      font-weight: 500;
    }

    .form-input:focus {
      outline: none;
      border-color: #2c3e50;
      box-shadow: 0 0 0 4px rgba(44, 62, 80, 0.1);
      transform: translateY(-2px);
    }

    .form-input::placeholder {
      color: #bdc3c7;
      font-weight: 400;
    }

    .input-icon {
      position: absolute;
      right: 18px;
      top: 50%;
      transform: translateY(-50%);
      color: #7f8c8d;
      transition: all 0.3s ease;
    }

    .form-input:focus + .input-icon {
      color: #2c3e50;
      transform: translateY(-50%) scale(1.1);
    }

    .login-button {
      width: 100%;
      padding: 18px;
      background: linear-gradient(135deg, #2c3e50, #34495e);
      border: none;
      border-radius: 14px;
      color: white;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      margin-bottom: 25px;
    }

    .login-button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.6s;
    }

    .login-button:hover::before {
      left: 100%;
    }

    .login-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 30px rgba(44, 62, 80, 0.4);
    }

    .login-button:active {
      transform: translateY(-1px);
    }

    .form-footer {
      text-align: center;
      padding-top: 25px;
      border-top: 1px solid #ecf0f1;
    }

    .support-text {
      color: #7f8c8d;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .support-link {
      color: #2c3e50;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }

    .support-link:hover {
      color: #3498db;
      transform: translateY(-1px);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
        margin: 20px;
        min-height: auto;
      }

      .content-side {
        padding: 40px 30px;
        order: 2;
      }

      .form-side {
        padding: 40px 30px;
        order: 1;
      }

      .welcome-title {
        font-size: 32px;
      }

      .brand-title {
        font-size: 24px;
      }

      .form-input {
        padding: 16px 50px 16px 16px;
      }

      .login-button {
        padding: 16px;
      }
    }

    @media (max-width: 480px) {
      .content-side, .form-side {
        padding: 30px 20px;
      }

      .welcome-title {
        font-size: 28px;
      }

      .form-title {
        font-size: 24px;
      }
    }

    /* Loading state */
    .loading .login-button::after {
      content: '';
      position: absolute;
      width: 20px;
      height: 20px;
      border: 2px solid transparent;
      border-top: 2px solid white;
      border-radius: 50%;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% { transform: translateY(-50%) rotate(0deg); }
      100% { transform: translateY(-50%) rotate(360deg); }
    }
  </style>
</head>

<body>
  <!-- Animated background -->
  <div class="bg-overlay"></div>
  
  <div class="floating-elements" id="floatingElements"></div>

  <div class="login-container">
    <!-- Content Side -->
    <div class="content-side">
      <div class="brand-header">
        <div class="news-logo">
          <div class="logo-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
              <path d="M18 14h-8"/>
              <path d="M15 18h-5"/>
              <path d="M10 6h8v4h-8z"/>
            </svg>
          </div>
          <div class="brand-title">MaruMehsana</div>
        </div>
      </div>

      <div class="content-main">
        <h1 class="welcome-title">Your News, Your Way</h1>
        <p class="welcome-subtitle">
          Access your personalized news management dashboard and take control of your content distribution, audience engagement, and publishing workflow.
        </p>

        <ul class="feature-list">
          <li class="feature-item">
            <div class="feature-icon">✓</div>
            <span>Real-time content management</span>
          </li>
          <li class="feature-item">
            <div class="feature-icon">✓</div>
            <span>Advanced analytics & insights</span>
          </li>
          <li class="feature-item">
            <div class="feature-icon">✓</div>
            <span>Multi-platform publishing</span>
          </li>
          <li class="feature-item">
            <div class="feature-icon">✓</div>
            <span>Collaborative newsroom tools</span>
          </li>
        </ul>
      </div>

      <div class="content-footer">
        <p>© 2025 MaruMehsana Portal • Secure & Reliable</p>
      </div>
    </div>

    <!-- Form Side -->
    <div class="form-side">
      <div class="form-header">
        <h2 class="form-title">Welcome Back</h2>
        <p class="form-subtitle">Sign in to your agency account</p>
      </div>

      @if ($errors->any())
      <div class="error-message">
        @foreach ($errors->all() as $error)
        {{ $error }}
        @endforeach
      </div>
      @endif

      <form action="/agency/login" method="POST" class="form-container" id="loginForm">
        @csrf
        
        <div class="input-group">
          <label for="username" class="input-label">Username</label>
          <div class="input-wrapper">
            <input 
              type="text" 
              id="username" 
              name="username" 
              class="form-input" 
              placeholder="Enter your username"
              value="{{ old('username') }}"
              required
            >
            <div class="input-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </div>
          </div>
        </div>

        <div class="input-group">
          <label for="password" class="input-label">Password</label>
          <div class="input-wrapper">
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-input" 
              placeholder="Enter your password"
              required
            >
            <div class="input-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </div>
          </div>
        </div>

        <button type="submit" class="login-button" id="loginBtn">
          Sign In to Dashboard
        </button>
      </form>

      <div class="form-footer">
        <p class="support-text">Need assistance?</p>
        <a href="#" class="support-link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 11a3 3 0 1 1 6 0c0 1.1-.3 2.1-.9 3l-.6.9a.7.7 0 0 1-1 0l-.6-.9c-.6-.9-.9-1.9-.9-3z"/>
            <circle cx="12" cy="11" r="2"/>
          </svg>
          Contact Support Team
        </a>
      </div>
    </div>
  </div>

  <script>
    // Create floating news elements
    function createNewsParticles() {
      const container = document.getElementById('floatingElements');
      const newsTerms = ['NEWS', 'LIVE', 'BREAKING', 'UPDATE', 'REPORT', 'STORY', 'PRESS'];
      
      setInterval(() => {
        if (document.querySelectorAll('.news-particle').length < 8) {
          const particle = document.createElement('div');
          particle.className = 'news-particle';
          particle.textContent = newsTerms[Math.floor(Math.random() * newsTerms.length)];
          particle.style.left = Math.random() * 100 + '%';
          particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
          particle.style.animationDelay = Math.random() * 5 + 's';
          
          container.appendChild(particle);
          
          setTimeout(() => {
            if (particle.parentNode) {
              particle.parentNode.removeChild(particle);
            }
          }, 20000);
        }
      }, 3000);
    }

    // Initialize animations
    window.addEventListener('load', () => {
      createNewsParticles();
      
      // Animate the login container entrance
      gsap.from('.login-container', {
        opacity: 0,
        y: 50,
        scale: 0.95,
        duration: 1.2,
        ease: "back.out(1.7)"
      });

      // Animate content side elements
      gsap.from('.news-logo', {
        opacity: 0,
        x: -30,
        duration: 0.8,
        delay: 0.3,
        ease: "power2.out"
      });

      gsap.from('.welcome-title', {
        opacity: 0,
        y: 30,
        duration: 0.8,
        delay: 0.5,
        ease: "power2.out"
      });

      gsap.from('.welcome-subtitle', {
        opacity: 0,
        y: 20,
        duration: 0.6,
        delay: 0.7,
        ease: "power2.out"
      });

      gsap.from('.feature-item', {
        opacity: 0,
        x: -20,
        stagger: 0.1,
        duration: 0.5,
        delay: 0.9,
        ease: "power2.out"
      });

      // Animate form side elements
      gsap.from('.form-header', {
        opacity: 0,
        y: 20,
        duration: 0.6,
        delay: 0.4,
        ease: "power2.out"
      });

      gsap.from('.input-group', {
        opacity: 0,
        y: 20,
        stagger: 0.1,
        duration: 0.5,
        delay: 0.6,
        ease: "power2.out"
      });

      gsap.from('.login-button, .form-footer', {
        opacity: 0,
        y: 20,
        stagger: 0.1,
        duration: 0.5,
        delay: 0.9,
        ease: "power2.out"
      });
    });

    // Enhanced form interactions
    document.addEventListener('DOMContentLoaded', function() {
      const loginForm = document.getElementById('loginForm');
      const loginBtn = document.getElementById('loginBtn');
      const inputs = document.querySelectorAll('.form-input');

      // Add interactive effects to inputs
      inputs.forEach(input => {
        input.addEventListener('focus', function() {
          gsap.to(this.closest('.input-group').querySelector('.input-label'), {
            color: '#2c3e50',
            scale: 1.05,
            duration: 0.3,
            ease: "power2.out"
          });
        });

        input.addEventListener('blur', function() {
          gsap.to(this.closest('.input-group').querySelector('.input-label'), {
            color: '#2c3e50',
            scale: 1,
            duration: 0.3,
            ease: "power2.out"
          });
        });

        input.addEventListener('input', function() {
          gsap.to(this, {
            scale: 1.01,
            duration: 0.1,
            yoyo: true,
            repeat: 1,
            ease: "power2.inOut"
          });
        });
      });

      // Form submission handling
      loginForm.addEventListener('submit', function(e) {
        loginBtn.textContent = 'Signing In...';
        document.body.classList.add('loading');
        
        gsap.to(loginBtn, {
          scale: 0.98,
          duration: 0.1,
          ease: "power2.out"
        });
      });

      // Error message animation
      const errorMessage = document.querySelector('.error-message');
      if (errorMessage) {
        gsap.from(errorMessage, {
          x: -10,
          duration: 0.1,
          repeat: 5,
          yoyo: true,
          ease: "power2.inOut"
        });
      }
    });

    // Interactive logo animation
    document.querySelector('.logo-icon').addEventListener('mouseenter', function() {
      gsap.to(this, {
        scale: 1.1,
        rotation: 10,
        duration: 0.3,
        ease: "back.out(1.7)"
      });
    });

    document.querySelector('.logo-icon').addEventListener('mouseleave', function() {
      gsap.to(this, {
        scale: 1,
        rotation: 0,
        duration: 0.3,
        ease: "power2.out"
      });
    });
  </script>
</body>

</html>