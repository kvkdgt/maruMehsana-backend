@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="quick-actions-container">
    <h3>Quick Actions</h3>
    <div class="actions">
      <a href="{{ url('admin/categories') }}" class="quick-action-btn">See all Categories</a>
      <a href="{{ url('admin/businesses/create') }}" class="quick-action-btn">Add New Business</a>
      <a href="{{ url('admin/businesses/create') }}" class="quick-action-btn">Send Push Notifications</a>

    </div>
  </div>
<div class="dashboard-container">
  <div class="card-container">
    <!-- Total Categories Card -->
    <div class="stat-card category-card">
      <div class="stat-content">
        <h3>Total Categories</h3>
        <p class="description">Here is the count of total active categories in the app.</p>
        <p class="count">{{ $totalCategories }}</p>
      </div>
    </div>

    <!-- Total Businesses Card -->
    <div class="stat-card business-card">
      <div class="stat-content">
        <h3>Total Businesses</h3>
        <p class="description">Here is the count of total active businesses in the app.</p>
        <p class="count">{{ $totalBusinesses }}</p>
      </div>
    </div>
  </div>

  <!-- Quick Actions Section -->
 
</div>

<style>
  .dashboard-container {
    background-color: #f5f7fb;
    border-radius: 10px;
    padding: 20px;
  }

  .card-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 20px;
  }

  .stat-card {
    background-color: #fff;
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    padding: 20px;
    text-align: center;
    color: #333;
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    transform: translateY(-5px); /* Slight lifting effect */
  }

  .stat-content {
    padding: 15px;
  }

  .stat-card h3 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
  }

  .description {
    font-size: 0.9rem;
    margin-bottom: 12px;
    color: #777;
  }

  .count {
    font-size: 2rem;
    font-weight: 700;
    color: #1abc9c;
  }

  /* Specific styles for Categories Card */
  .category-card {
    background: linear-gradient(135deg, #fdf2e3, #ffe156);
  }

  /* Specific styles for Businesses Card */
  .business-card {
    background: linear-gradient(135deg, #e0f7fa, #b2ebf2);
  }

  /* Quick Actions Section */
  .quick-actions-container {
    text-align: center;
    padding: 30px;
    background: linear-gradient(135deg, #2596be, #16a085);
    border-radius: 15px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    color: white;
  }

  .quick-actions-container h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: white;
  }

  .actions {
    display: flex;
    justify-content: center;
    gap: 25px;
  }

  .quick-action-btn {
    background: linear-gradient(135deg, #2596be, #16a085);
    padding: 12px 30px;
    font-size: 1.1rem;
    border-radius: 30px; /* Rounded corners */
    text-decoration: none;
    color: white;
    text-align: center;
    transition: all 0.3s ease;
    display: inline-block;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  }

  .quick-action-btn:hover {
    background: linear-gradient(135deg, #1abc9c, #1e7e70);
    transform: scale(1.05); /* Smooth scale effect */
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
    .card-container {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 768px) {
    .card-container {
      grid-template-columns: 1fr;
    }

    .stat-card {
      margin-bottom: 20px;
    }
  }
</style>
@endsection
