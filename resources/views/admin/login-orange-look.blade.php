<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maru Mehsana Admin Portal</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f5f7fa;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow-x: hidden;
      background: url('/api/placeholder/1920/1080') center/cover;
      position: relative;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(8px);
      z-index: 1;
    }

    .login-container {
      width: 90%;
      max-width: 1000px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 20px;
      overflow: hidden;
      display: flex;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      position: relative;
      z-index: 2;
    }

    .brand-side {
      flex: 1;
      background: linear-gradient(135deg, #FF5722, #FF9800);
      padding: 40px;
      color: white;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .brand-side::before {
      content: '';
      position: absolute;
      width: 300px;
      height: 300px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      top: -100px;
      left: -100px;
    }

    .brand-side::after {
      content: '';
      position: absolute;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      bottom: -50px;
      right: -50px;
    }

    .logo {
      font-size: 32px;
      font-weight: 700;
      letter-spacing: 1px;
      position: relative;
      z-index: 1;
    }

    .logo span {
      color: #FFD700;
    }

    .brand-tagline {
      font-size: 18px;
      margin-top: 8px;
      opacity: 0.9;
    }

    .brand-message {
      margin-top: 60px;
      position: relative;
      z-index: 1;
    }

    .brand-message h2 {
      font-size: 28px;
      margin-bottom: 15px;
    }

    .brand-message p {
      font-size: 16px;
      line-height: 1.6;
      opacity: 0.9;
    }

    .brand-footer {
      margin-top: 40px;
      font-size: 14px;
      opacity: 0.8;
      position: relative;
      z-index: 1;
    }

    .login-side {
      flex: 1;
      padding: 50px;
      background: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .login-header {
      margin-bottom: 40px;
      text-align: center;
    }

    .login-header h2 {
      font-size: 28px;
      color: #333;
      font-weight: 600;
    }

    .login-header p {
      color: #666;
      margin-top: 8px;
    }

    .login-form .form-group {
      margin-bottom: 25px;
      position: relative;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 14px;
      color: #555;
      font-weight: 500;
    }

    .form-group input {
      width: 100%;
      padding: 15px;
      border: none;
      background: #f0f5ff;
      border-radius: 12px;
      font-size: 15px;
      color: #333;
      transition: all 0.3s ease;
    }

    .form-group input:focus {
      background: #e0e9ff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.2);
    }

    .form-group .input-icon {
      position: absolute;
      right: 15px;
      bottom: 15px;
      color: #999;
    }

    .error-message {
      color: #e53e3e;
      margin-bottom: 20px;
      text-align: center;
      font-size: 14px;
      padding: 10px;
      background: #fff5f5;
      border-radius: 8px;
    }

    .login-btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(to right, #FF5722, #FF9800);
      border: none;
      border-radius: 12px;
      color: white;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .login-btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.5s;
    }

    .login-btn:hover::before {
      left: 100%;
    }

    .login-btn:hover {
      box-shadow: 0 7px 15px rgba(255, 87, 34, 0.4);
      transform: translateY(-2px);
    }

    .login-footer {
      margin-top: 30px;
      text-align: center;
      color: #666;
      font-size: 14px;
    }

    .animated-circle {
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.05);
    }

    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
        max-width: 400px;
      }

      .brand-side {
        padding: 30px;
      }

      .brand-message {
        margin-top: 30px;
      }

      .login-side {
        padding: 30px;
      }
    }

    /* Decorative elements */
    .shape {
      position: absolute;
      z-index: 0;
    }

    .shape1 {
      width: 80px;
      height: 80px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      top: 20%;
      right: 10%;
    }

    .shape2 {
      width: 60px;
      height: 60px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 40%;
      bottom: 20%;
      left: 15%;
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="brand-side">
      <div class="logo">MARU<span>MEHSANA</span></div>
      <div class="brand-tagline">Administrative Portal</div>

      <div class="brand-message">
        <h2>Welcome Back!</h2>
        <p>Access your dashboard to manage operations, track performance metrics, and oversee all Maru Mehsana activities from one central location.</p>
      </div>

      <div class="brand-footer">
        Maru Mehsana © 2025 | Secured Access
      </div>

      <!-- Decorative shapes -->
      <div class="shape shape1"></div>
      <div class="shape shape2"></div>
    </div>

    <div class="login-side">
      <div class="login-header">
        <h2>Admin Login</h2>
        <p>Please enter your credentials to continue</p>
      </div>

      @if ($errors->any())
      <div class="error-message">
        @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
        @endforeach
      </div>
      @endif

      <form action="/admin/login" method="POST" class="login-form">
        @csrf
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
          <div class="input-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </div>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
          <div class="input-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
          </div>
        </div>

        <button type="submit" class="login-btn">Sign In</button>
      </form>

      <div class="login-footer">
        Need help? Contact IT Support
      </div>
    </div>
  </div>

  <script>
    // Create animated background circles
    function createCircles() {
      const body = document.querySelector('body');
      const colors = ['rgba(255, 87, 34, 0.2)', 'rgba(255, 152, 0, 0.15)', 'rgba(255, 215, 0, 0.1)'];
      
      for (let i = 0; i < 6; i++) {
        const circle = document.createElement('div');
        circle.classList.add('animated-circle');
        
        const size = Math.random() * 200 + 50;
        const posX = Math.random() * window.innerWidth;
        const posY = Math.random() * window.innerHeight;
        
        circle.style.width = `${size}px`;
        circle.style.height = `${size}px`;
        circle.style.left = `${posX}px`;
        circle.style.top = `${posY}px`;
        circle.style.background = colors[Math.floor(Math.random() * colors.length)];
        
        body.appendChild(circle);
        
        // Animate with GSAP
        gsap.to(circle, {
          x: Math.random() * 100 - 50,
          y: Math.random() * 100 - 50,
          duration: Math.random() * 10 + 10,
          repeat: -1,
          yoyo: true,
          ease: "sine.inOut"
        });
      }
    }

    // Run animations when page loads
    window.addEventListener('load', () => {
      createCircles();
      
      // Animate login container
      gsap.from('.login-container', {
        opacity: 0,
        y: 30,
        duration: 1,
        ease: "power3.out"
      });
      
      // Animate brand elements
      gsap.from('.logo, .brand-tagline', {
        opacity: 0,
        x: -30,
        stagger: 0.2,
        duration: 0.8,
        delay: 0.3,
        ease: "power2.out"
      });
      
      // Animate brand message
      gsap.from('.brand-message h2, .brand-message p', {
        opacity: 0,
        y: 20,
        stagger: 0.2,
        duration: 0.8,
        delay: 0.6,
        ease: "power2.out"
      });
      
      // Animate login form elements
      gsap.from('.login-header, .form-group, .login-btn, .login-footer', {
        opacity: 0,
        y: 20,
        stagger: 0.15,
        duration: 0.7,
        delay: 0.5,
        ease: "power2.out"
      });
    });
  </script>
</body>

</html>