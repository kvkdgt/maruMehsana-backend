<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background-color: #f5f7fb;
        }

        .sidebar {
            width: 240px;
            background: #2c3e50;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding-top: 30px;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar .logo {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 40px;
            text-transform: uppercase;
            color: #fff;
        }

        .sidebar .tagline {
            font-size: 0.6rem;
            text-transform: none;
        }

        .highlight {
            color: #F2652D;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }

        .sidebar ul li {
            padding: 12px 15px;
            transition: background-color 0.3s ease;
            cursor: pointer;
            border-left: 4px solid transparent;
        }

        .sidebar ul li:hover,
        .sidebar ul li.active {
            background-color: #34495e;
            border-left: 4px solid #2980b9;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: #ecf0f1;
            font-size: 1rem;
            display: flex;
            align-items: center;
        }

        .sidebar ul li a i {
            margin-right: 15px;
        }

        .content {
            margin-left: 240px;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .header {
            padding: 20px 30px;
            background: #2c3e50;
            /* Keep this color for modern look */
            color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: calc(100% - 240px);
            top: 0;
            left: 240px;
            z-index: 100;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 600;
            /* Slightly lighter for modern feel */
            color: #fff;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.2);
            /* Softer shadow for modern look */
            letter-spacing: 0.5px;
            font-family: 'Roboto', sans-serif;
            /* Ensure consistency in font */
        }

        .header .user-info {
            font-size: 1rem;
            color: #ecf0f1;
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .header .user-info span {
            font-weight: 500;
            color: #ecf0f1;
        }

        .header .user-info a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 20px;
            background-color: #e74c3c;
            /* Updated background color */
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .header .user-info a:hover {
            background-color: #c0392b;
            transform: scale(1.05);
            /* Hover effect */
        }


        .main-content {
            margin-top: 80px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .card-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 25px;
            width: 300px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 15px;
        }

        .card p {
            font-size: 2.5rem;
            color: #2980b9;
            font-weight: 700;
            margin-bottom: 25px;
        }

        .card a {
            text-decoration: none;
            color: #fff;
            background-color: #2980b9;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            transition: background-color 0.3s ease;
        }

        .card a:hover {
            background-color: #1abc9c;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .sidebar ul li {
                padding: 15px;
                text-align: center;
            }

            .sidebar ul li a i {
                margin-right: 0;
            }

            .content {
                margin-left: 60px;
            }

            .header {
                left: 60px;
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Maru Mehsana
            <div class="tagline">Locally made for <span class="highlight"> local peoples </span></div>

        </div>
        <ul>
            <li class="@if(request()->is('admin/dashboard')) active @endif">
                <a href="{{ url('admin/dashboard') }}"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="@if(request()->is('admin/categories')) active @endif">
                <a href="{{ url('admin/categories') }}"><i class="fas fa-list"></i> Categories</a>
            </li>
            <li class="@if(request()->is('admin/businesses') || request()->is('admin/businesses/create')|| request()->is('admin/businesses/edit/*')  ) active @endif">
                <a href="{{ url('admin/businesses') }}"><i class="fas fa-briefcase"></i> Businesses</a>
            </li>
            <li class="@if(request()->is('admin/tourist-places')) active @endif">
                <a href="{{ url('admin/tourist-places') }}"><i class="fas fa-signs-post"></i> Tourist Places</a>
            </li>
            <li class="@if(request()->is('admin/facts') ) active @endif">
                <a href="{{ url('admin/facts') }}"><i class="fas fa-check"></i> Facts</a>
            </li>
            <li class="@if(request()->is('admin/marketing')) active @endif">
                <a href="{{ url('admin/marketing') }}"><i class="fas fa-bullhorn"></i> Marketing</a>
            </li>
            <li class="@if(request()->is('admin/business-enquiry')) active @endif">
                <a href="{{ url('admin/business-enquiry') }}"><i class="fa-solid fa-user-tie"></i> Business Enquiry</a>
            </li>
        </ul>
    </div>

    <div class="content">
        <div class="header">

            <h1>Admin Panel <span style="font-size: 1.2rem;">| @yield('title')</span></h1>
            <div class="user-info">
                <span>Welcome, {{Auth::user()->name}}</span>
                <a href="{{ url('admin/logout') }}" class="logout-btn">Logout</a>
            </div>
        </div>
        <div class="main-content">
            @yield('content')
        </div>
    </div>

</body>

</html>