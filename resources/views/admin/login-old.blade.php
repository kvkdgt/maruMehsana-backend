<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <style>
    /* General styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Arial', sans-serif;
      background: linear-gradient(135deg, #275570, #67a5c3);
      color: #333;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* Login container */
    .login-container {
      display: flex;
      width: 100%;
      max-width: 800px;
      background-color: #ffffff;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .login-left {
      flex: 1;
      background: linear-gradient(135deg, #275570, #1f4863);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px;
      color: #fff;
      text-align: center;
    }

    .welcome-text h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }

    .welcome-text p {
      font-size: 1rem;
      line-height: 1.6;
    }

    .login-right {
      flex: 1;
      padding: 40px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-box {
      width: 100%;
      max-width: 350px;
    }

    .login-title {
      font-size: 24px;
      color: #275570;
      text-align: center;
      margin-bottom: 20px;
    }

    /* Form styles */
    .form-group {
      margin-bottom: 15px;
    }

    label {
      font-size: 14px;
      color: #333;
      display: block;
      margin-bottom: 5px;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
      transition: all 0.3s ease;
    }

    input:focus {
      border-color: #275570;
      box-shadow: 0 0 8px rgba(39, 85, 112, 0.3);
    }

    /* Button styles */
    .login-btn {
      background-color: #275570;
      color: #fff;
      padding: 12px 18px;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      width: 100%;
      transition: background 0.3s ease;
    }

    .login-btn:hover {
      background-color: #1f4863;
    }

    /* Forgot password */
    .forgot-password {
      margin-top: 10px;
      text-align: center;
    }

    .forgot-password a {
      color: #275570;
      text-decoration: none;
    }

    .forgot-password a:hover {
      text-decoration: underline;
    }

    /* Media query for responsiveness */
    @media (max-width: 768px) {
      .login-container {
        flex-direction: column;
      }

      .login-left {
        display: none;
      }

      .login-right {
        padding: 20px;
      }
    }
  </style>
</head>

<body>
  <div class="login-container">
    <div class="login-left">
      <div class="welcome-text">
        <h1>Welcome Admin!</h1>
        <p>Manage your panel securely and effectively.</p>
      </div>
    </div>
    <div class="login-right">
      <div class="login-box">
        <h2 class="login-title">Admin Login</h2>
        @if ($errors->any())
        <div style="color: red; margin-bottom: 20px;text-align:center">
          @foreach ($errors->all() as $error)
          <p>{{ $error }}</p>
          @endforeach
        </div>
        @endif
        <form action="/admin/login" method="POST">
          @csrf
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
          </div>
          <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
          </div>
          <div class="form-actions">
            <button type="submit" class="login-btn">Login</button>
          </div>
        </form>

      </div>
    </div>
  </div>
</body>

</html>