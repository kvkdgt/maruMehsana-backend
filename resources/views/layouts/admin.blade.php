<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2c3e50;
            --primary-light: #34495e;
            --accent: #F2652D;
            --accent-hover: #e74c3c;
            --text-light: #ecf0f1;
            --bg-light: #f5f7fb;
            --card-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background-color: var(--bg-light);
            color: #444;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: var(--primary);
            background-image: linear-gradient(to bottom, #2c3e50, #1a252f);
            color: var(--text-light);
            display: flex;
            flex-direction: column;
            padding-top: 20px;
            position: fixed;
            height: 100vh;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.15);
            z-index: 100;
            transition: var(--transition);
        }

        .sidebar .logo {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding: 15px;
            text-transform: uppercase;
            color: #fff;
            letter-spacing: 1px;
            position: relative;
        }

        .sidebar .logo::after {
            content: '';
            display: block;
            width: 40px;
            height: 3px;
            background-color: var(--accent);
            margin: 8px auto 0;
            border-radius: 2px;
        }

        .sidebar .tagline {
            font-size: 0.7rem;
            font-weight: 300;
            letter-spacing: 0.5px;
            margin-top: 5px;
            text-transform: none;
        }

        .highlight {
            color: var(--accent);
            font-weight: 500;
        }

        .sidebar ul {
            list-style: none;
            padding: 10px 0;
            margin-top: 10px;
        }

        .sidebar ul li {
            padding: 0;
            transition: var(--transition);
            border-left: 4px solid transparent;
            margin-bottom: 5px;
        }

        .sidebar ul li:hover,
        .sidebar ul li.active {
            background-color: rgba(255, 255, 255, 0.08);
            border-left: 4px solid var(--accent);
        }

        .sidebar ul li a {
            text-decoration: none;
            color: var(--text-light);
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            padding: 14px 20px;
            transition: var(--transition);
        }

        .sidebar ul li:hover a,
        .sidebar ul li.active a {
            transform: translateX(5px);
        }

        .sidebar ul li a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        /* Main Content Styles */
        .content {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            position: relative;
        }

        .header {
            padding: 20px 30px;
            background: #fff;
            color: var(--primary);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            width: calc(100% - 260px);
            top: 0;
            left: 260px;
            z-index: 99;
            transition: var(--transition);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .header h1 {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--primary);
            letter-spacing: 0.5px;
        }

        .header .title-divider {
            display: inline-block;
            margin: 0 10px;
            color: #ccc;
        }

        .header .page-title {
            font-size: 1.2rem;
            font-weight: 500;
            color: var(--accent);
        }

        .header .user-info {
            font-size: 0.9rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .header .user-avatar {
            width: 40px;
            height: 40px;
            background-color: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 500;
            font-size: 1rem;
        }

        .header .user-details {
            display: flex;
            flex-direction: column;
        }

        .header .user-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .header .user-role {
            font-size: 0.7rem;
            color: #777;
        }

        .header .user-info .logout-btn {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 50px;
            background-color: var(--accent);
            transition: var(--transition);
            box-shadow: 0 3px 10px rgba(242, 101, 45, 0.3);
        }

        .header .user-info .logout-btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(242, 101, 45, 0.4);
        }

        .header .user-info .logout-btn i {
            margin-right: 5px;
        }

        .main-content {
            margin-top: 90px;
            padding: 30px;
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background-color: var(--accent);
            margin-left: 15px;
            border-radius: 2px;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            padding: 25px;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            border-top: 4px solid var(--primary);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(44, 62, 80, 0.05), rgba(242, 101, 45, 0.05));
            clip-path: circle(70% at 95% 10%);
            transition: var(--transition);
            z-index: 0;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card:hover::before {
            clip-path: circle(150% at 95% 10%);
        }

        .card .card-icon {
            font-size: 2.5rem;
            color: var(--accent);
            margin-bottom: 15px;
            background: rgba(242, 101, 45, 0.1);
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
            transition: var(--transition);
        }

        .card:hover .card-icon {
            background: var(--accent);
            color: white;
            transform: scale(1.1);
        }

        .card h3 {
            font-size: 1.2rem;
            color: var(--primary);
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .card p {
            font-size: 2rem;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .card .card-btn {
            text-decoration: none;
            color: var(--text-light);
            background-color: var(--primary);
            padding: 10px 20px;
            border-radius: 50px;
            font-size: 0.85rem;
            transition: var(--transition);
            display: inline-block;
            position: relative;
            z-index: 1;
            font-weight: 500;
        }

        .card .card-btn:hover {
            background-color: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(242, 101, 45, 0.3);
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tr:hover {
            background-color: rgba(44, 62, 80, 0.03);
        }

        .data-table .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.75rem;
            text-decoration: none;
            font-weight: 500;
            margin-right: 5px;
        }

        .data-table .edit-btn {
            background-color: rgba(52, 152, 219, 0.1);
            color: #3498db;
        }

        .data-table .delete-btn {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .data-table .view-btn {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
        }

        /* Form Styles */
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--primary);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            font-size: 0.9rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(242, 101, 45, 0.1);
            outline: none;
        }

        .btn {
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 62, 80, 0.2);
        }

        .btn-accent {
            background-color: var(--accent);
            color: white;
        }

        .btn-accent:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(242, 101, 45, 0.3);
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }

            .sidebar .logo {
                font-size: 1.2rem;
                padding: 15px 5px;
            }

            .sidebar .tagline {
                display: none;
            }

            .sidebar ul li a {
                padding: 14px 0;
                justify-content: center;
            }

            .sidebar ul li a i {
                margin-right: 0;
                font-size: 1.2rem;
            }

            .sidebar ul li a span {
                display: none;
            }

            .content {
                margin-left: 70px;
            }

            .header {
                left: 70px;
                width: calc(100% - 70px);
            }

            .card-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px;
            }

            .header .user-info {
                margin-top: 15px;
                width: 100%;
                justify-content: space-between;
            }

            .main-content {
                margin-top: 130px;
                padding: 20px;
            }

            .card-container {
                grid-template-columns: 1fr;
            }
        }

        .dropdown-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-in-out;
            background: rgba(0, 0, 0, 0.2);
        }

        .dropdown-menu.show {
            max-height: 200px;
            /* Adjust based on number of items */
        }

        .dropdown-toggle {
            position: relative;
            cursor: pointer;
        }

        .dropdown-toggle::after {
            content: '▶';
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            transition: transform 0.3s ease;
            font-size: 0.8rem;
        }

        .dropdown-toggle.active::after {
            transform: translateY(-50%) rotate(90deg);
        }

        .dropdown-item {
            padding-left: 50px !important;
            font-size: 0.85rem;
            border-left: 4px solid transparent;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid var(--accent);
        }

        .dropdown-item.active {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid var(--accent);
        }

        /* Extended Table & Filter Utilities */
        .premium-table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-top: 20px;
        }

        .premium-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .premium-table th {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 18px 20px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            text-align: left;
        }

        .premium-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
            font-size: 0.95rem;
            color: #555;
        }

        .premium-table tr:last-child td {
            border-bottom: none;
        }

        .premium-table tr:hover td {
            background-color: #f8faff;
        }

        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: flex-end;
        }

        .filter-control {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-control label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #666;
        }

        .filter-input {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-family: inherit;
            transition: var(--transition);
        }

        .filter-input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(242, 101, 45, 0.1);
            outline: none;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .badge-premium {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #e0f2fe; color: #075985; }

        /* Global Button Utilities */
        .filter-btn {
            background-color: var(--accent) !important;
            color: white !important;
            padding: 10px 28px !important;
            border-radius: 10px !important;
            border: none !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 4px 12px rgba(242, 101, 45, 0.15) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .filter-btn:hover {
            background-color: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(242, 101, 45, 0.25);
        }

        .reset-btn {
            background-color: #ffffff !important;
            color: #475569 !important;
            padding: 10px 28px !important;
            border-radius: 10px !important;
            border: 2px solid #cbd5e1 !important;
            text-decoration: none !important;
            font-weight: 700 !important;
            font-size: 0.95rem !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.3s ease !important;
        }

        .reset-btn:hover {
            background-color: #f8fafc !important;
            border-color: #94a3b8 !important;
            color: #1e293b !important;
        }

        .btn-add-premium {
            background-color: var(--accent);
            color: white;
            padding: 12px 28px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 15px rgba(242, 101, 45, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-add-premium i {
            font-size: 1.1rem;
        }

        .btn-add-premium:hover {
            background-color: var(--accent-hover);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(242, 101, 45, 0.3);
        }

        .edit-btn {
            background-color: #fff9db;
            color: #f08c00;
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #ffe066;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .edit-btn:hover {
            background-color: #fff3bf;
            color: #e67700;
            border-color: #fab005;
            transform: translateY(-1px);
        }

        .delete-btn {
            background-color: #fff5f5;
            color: #fa5252;
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #ffc9c9;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
            outline: none;
        }

        .delete-btn:hover {
            background-color: #ffe3e3;
            color: #f03e3e;
            border-color: #ffa8a8;
            transform: translateY(-1px);
        }

        /* Global Header Container */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            gap: 20px;
        }

        /* Global Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 550px;
            position: relative;
            animation: modalFadeIn 0.3s ease-out;
            max-height: 90vh;
            overflow-y: auto;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            color: #2c3e50;
            font-weight: 700;
        }

        .modal-body {
            padding: 25px;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #aaa;
            cursor: pointer;
            transition: var(--transition);
        }

        .close-btn:hover {
            color: var(--accent);
            transform: rotate(90deg);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>

    <script>
        // Dropdown toggle functionality
        function toggleDropdown(dropdownId, toggleElement) {
            const dropdown = document.getElementById(dropdownId);
            const isOpen = dropdown.classList.contains('show');

            // Close all dropdowns first
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
                toggle.classList.remove('active');
            });

            // If this dropdown wasn't open, open it
            if (!isOpen) {
                dropdown.classList.add('show');
                toggleElement.classList.add('active');
            }
        }

        // Auto-open dropdown if we're on a page within that section
        document.addEventListener('DOMContentLoaded', function() {
            // Check if we're on news management pages
            if (window.location.pathname.includes('/admin/news-agencies') ||
                window.location.pathname.includes('/admin/news-categories')) {
                const newsDropdown = document.getElementById('newsDropdown');
                const newsToggle = document.querySelector('[onclick*="newsDropdown"]');
                if (newsDropdown && newsToggle) {
                    newsDropdown.classList.add('show');
                    newsToggle.classList.add('active');
                }
            }
        });
    </script>
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            Maru Mehsana
            <div class="tagline">Locally made for <span class="highlight">local peoples</span></div>
        </div>
        
<ul>
    <li class="@if(request()->is('admin/dashboard')) active @endif">
        <a href="{{ url('admin/dashboard') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="@if(request()->is('admin/categories')) active @endif">
        <a href="{{ url('admin/categories') }}">
            <i class="fas fa-list"></i>
            <span>Categories</span>
        </a>
    </li>
    <li class="@if(request()->is('admin/businesses') || request()->is('admin/businesses/create')|| request()->is('admin/businesses/edit/*')) active @endif">
        <a href="{{ url('admin/businesses') }}">
            <i class="fas fa-briefcase"></i>
            <span>Businesses</span>
        </a>
    </li>
    <li class="@if(request()->is('admin/tourist-places')) active @endif">
        <a href="{{ url('admin/tourist-places') }}">
            <i class="fas fa-signs-post"></i>
            <span>Tourist Places</span>
        </a>
    </li>
    <li class="@if(request()->is('admin/facts')) active @endif">
        <a href="{{ url('admin/facts') }}">
            <i class="fas fa-check"></i>
            <span>Facts</span>
        </a>
    </li>
    
    <!-- News Management Dropdown -->
    <li class="dropdown-toggle @if(request()->is('admin/news-agencies*') || request()->is('admin/news-categories*')) active @endif" 
        onclick="toggleDropdown('newsDropdown', this)">
        <a href="javascript:void(0)">
            <i class="fas fa-newspaper"></i>
            <span>News Management</span>
        </a>
    </li>
    <div id="newsDropdown" class="dropdown-menu @if(request()->is('admin/news-agencies*') || request()->is('admin/news-categories*')) show @endif">
        <li class="dropdown-item @if(request()->is('admin/news-agencies*')) active @endif">
            <a href="{{ url('admin/news-agencies') }}">
                <i class="fas fa-building"></i>
                <span>News Agencies</span>
            </a>
        </li>
        <!-- <li class="dropdown-item @if(request()->is('admin/news-categories*')) active @endif">
            <a href="{{ url('admin/news-categories') }}">
                <i class="fas fa-tags"></i>
                <span>News Categories</span>
            </a>
        </li> -->
    </div>
    
    <li class="@if(request()->is('admin/app-users')) active @endif">
        <a href="{{ url('admin/app-users') }}">
            <i class="fas fa-users"></i>
            <span>App Users</span>
        </a>
    </li>
    
    <li class="@if(request()->is('admin/marketing') || request()->is('admin/marketing/banner-ads')) active @endif">
        <a href="{{ url('admin/marketing') }}">
            <i class="fas fa-bullhorn"></i>
            <span>Marketing</span>
        </a>
    </li>
    <li class="@if(request()->is('admin/business-enquiry')) active @endif">
        <a href="{{ url('admin/business-enquiry') }}">
            <i class="fa-solid fa-user-tie"></i>
            <span>Business Enquiry</span>
        </a>
    </li>
</ul>
    </div>

    <div class="content">
        <div class="header">
            <h1>Admin Panel <span class="title-divider">|</span> <span class="page-title">@yield('title')</span></h1>
            <div class="user-info">
                <div class="user-details">
                    <div class="user-avatar">
                        {{substr(Auth::user()->name, 0, 1)}}
                    </div>
                </div>
                <div class="user-details">
                    <span class="user-name">{{Auth::user()->name}}</span>
                    <span class="user-role">Administrator</span>
                </div>
                <a href="{{ url('admin/logout') }}" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        <div class="main-content">




            @yield('content')
        </div>
    </div>

</body>

</html>