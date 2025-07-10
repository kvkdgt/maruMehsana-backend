{{-- resources/views/agency/profile.blade.php --}}
@extends('agency.layout.app')

@section('title', 'Profile Settings')
@section('header-title', 'Profile Settings')

<style>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
}

.profile-header {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
}

.header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.header-info h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.header-info p {
    color: #64748b;
    font-size: 1rem;
}

.agency-logo {
    width: 80px;
    height: 80px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #f1f5f9;
}

.logo-placeholder {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
}

.profile-form {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
}

.form-section {
    margin-bottom: 3rem;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f5f9;
}

.section-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.section-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.section-subtitle {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-label.required::after {
    content: ' *';
    color: #ef4444;
}

.form-input {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    background: #ffffff;
}

.form-input:focus {
    outline: none;
    border-color: #2c3e50;
    box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
    transform: translateY(-1px);
}

.form-input[readonly] {
    background: #f8fafc;
    color: #64748b;
    cursor: not-allowed;
}

.file-input-wrapper {
    position: relative;
    overflow: hidden;
    display: inline-block;
    width: 100%;
}

.file-input {
    position: absolute;
    left: -9999px;
}

.file-input-label {
    display: block;
    padding: 1rem;
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8fafc;
    color: #64748b;
    font-size: 0.875rem;
}

.file-input-label:hover {
    border-color: #2c3e50;
    background: #f1f5f9;
    color: #2c3e50;
}

.password-section {
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 1rem;
}

.password-toggle {
    background: none;
    border: none;
    color: #2c3e50;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: underline;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.password-toggle:hover {
    color: #34495e;
}

.password-fields {
    display: none;
    animation: slideDown 0.3s ease;
}

.password-fields.active {
    display: block;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 2rem;
    border-top: 2px solid #f1f5f9;
    margin-top: 2rem;
}

.btn {
    padding: 0.875rem 1.75rem;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(44, 62, 80, 0.3);
}

.btn-secondary {
    background: #f8fafc;
    color: #64748b;
    border: 2px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #f1f5f9;
    color: #2c3e50;
    border-color: #cbd5e1;
}

.info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.info-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    text-align: center;
}

.info-card .label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.info-card .value {
    font-size: 0.875rem;
    color: #2c3e50;
    font-weight: 600;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

@media (max-width: 768px) {
    .header-content {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .info-cards {
        grid-template-columns: 1fr;
    }
}
</style>

@section('content')
<div class="profile-container">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="header-content">
            <div class="header-info">
                <h1>{{ $agency->name }}</h1>
                <p>{{ $admin->name }} • Administrator</p>
            </div>
            <div class="agency-logo-container">
                @if($agency->logo)
                    <img src="{{ $agency->logo_url }}" alt="{{ $agency->name }}" class="agency-logo">
                @else
                    <div class="logo-placeholder">
                        {{ $agency->initial }}
                    </div>
                @endif
            </div>
        </div>

        <div class="info-cards">
            <div class="info-card">
                <div class="label">Status</div>
                <div class="value">
                    <span class="status-badge {{ $admin->status ? 'status-active' : 'status-inactive' }}">
                        {{ $admin->status ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div class="info-card">
                <div class="label">Username</div>
                <div class="value">{{ $admin->username }}</div>
            </div>
            <div class="info-card">
                <div class="label">Member Since</div>
                <div class="value">{{ $admin->created_at->format('M Y') }}</div>
            </div>
            <div class="info-card">
                <div class="label">Last Updated</div>
                <div class="value">{{ $admin->updated_at->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="profile-form">
        <form action="{{ route('agency.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Agency Information -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/>
                            <path d="M18 14h-8"/>
                            <path d="M15 18h-5"/>
                            <path d="M10 6h8v4h-8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="section-title">Agency Information</h3>
                        <p class="section-subtitle">Update your news agency details</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="agency_name" class="form-label required">Agency Name</label>
                        <input 
                            type="text" 
                            id="agency_name" 
                            name="agency_name" 
                            class="form-input" 
                            value="{{ old('agency_name', $agency->name) }}"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="agency_email" class="form-label required">Agency Email</label>
                        <input 
                            type="email" 
                            id="agency_email" 
                            name="agency_email" 
                            class="form-input" 
                            value="{{ old('agency_email', $agency->email) }}"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="agency_username" class="form-label">Agency Username</label>
                    <input 
                        type="text" 
                        id="agency_username" 
                        class="form-input" 
                        value="{{ $agency->username }}"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <label for="agency_logo" class="form-label">Agency Logo</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="agency_logo" name="agency_logo" class="file-input" accept="image/*">
                        <label for="agency_logo" class="file-input-label">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7,10 12,15 17,10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Choose Logo or Drop Here
                        </label>
                    </div>
                    @if($agency->logo)
                        <p style="margin-top: 0.5rem; font-size: 0.75rem; color: #64748b;">
                            Current logo: {{ basename($agency->logo) }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Administrator Information -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="section-title">Administrator Information</h3>
                        <p class="section-subtitle">Update your personal details</p>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="admin_name" class="form-label required">Full Name</label>
                        <input 
                            type="text" 
                            id="admin_name" 
                            name="admin_name" 
                            class="form-input" 
                            value="{{ old('admin_name', $admin->name) }}"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email" class="form-label required">Email Address</label>
                        <input 
                            type="email" 
                            id="admin_email" 
                            name="admin_email" 
                            class="form-input" 
                            value="{{ old('admin_email', $admin->email) }}"
                            required
                        >
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="admin_phone" class="form-label">Phone Number</label>
                        <input 
                            type="text" 
                            id="admin_phone" 
                            name="admin_phone" 
                            class="form-input" 
                            value="{{ old('admin_phone', $admin->phone) }}"
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            id="admin_username" 
                            class="form-input" 
                            value="{{ $admin->username }}"
                            readonly
                        >
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="section-title">Security Settings</h3>
                        <p class="section-subtitle">Change your password</p>
                    </div>
                </div>
                
                <div class="password-section">
                    <button type="button" class="password-toggle" onclick="togglePasswordSection()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Change Password
                    </button>
                    
                    <div class="password-fields" id="passwordFields">
                        <div class="form-group">
                            <label for="current_password" class="form-label required">Current Password</label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password" 
                                class="form-input"
                            >
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="new_password" class="form-label required">New Password</label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    class="form-input"
                                    minlength="6"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password_confirmation" class="form-label required">Confirm New Password</label>
                                <input 
                                    type="password" 
                                    id="new_password_confirmation" 
                                    name="new_password_confirmation" 
                                    class="form-input"
                                    minlength="6"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('agency.dashboard') }}" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5"/>
                        <polyline points="12,19 5,12 12,5"/>
                    </svg>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20,6 9,17 4,12"/>
                    </svg>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePasswordSection() {
    const passwordFields = document.getElementById('passwordFields');
    const toggleBtn = document.querySelector('.password-toggle');
    
    if (passwordFields.classList.contains('active')) {
        passwordFields.classList.remove('active');
        toggleBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            Change Password
        `;
        
        // Clear password fields
        document.getElementById('current_password').value = '';
        document.getElementById('new_password').value = '';
        document.getElementById('new_password_confirmation').value = '';
    } else {
        passwordFields.classList.add('active');
        toggleBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
            Cancel Password Change
        `;
    }
}

// File upload drag & drop functionality
const fileLabel = document.querySelector('.file-input-label');
const fileInput = document.getElementById('agency_logo');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    fileLabel.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    fileLabel.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    fileLabel.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    fileLabel.style.borderColor = '#2c3e50';
    fileLabel.style.background = '#f1f5f9';
    fileLabel.style.color = '#2c3e50';
}

function unhighlight(e) {
    fileLabel.style.borderColor = '#cbd5e1';
    fileLabel.style.background = '#f8fafc';
    fileLabel.style.color = '#64748b';
}

fileLabel.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;
    
    if (files.length > 0 && files[0].type.startsWith('image/')) {
        fileInput.files = files;
        updateFileName(files[0].name);
    }
}

// Update file name display
fileInput.addEventListener('change', function(e) {
    if (e.target.files.length > 0) {
        updateFileName(e.target.files[0].name);
    }
});

function updateFileName(fileName) {
    const label = document.querySelector('.file-input-label');
    label.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
            <polyline points="20,6 9,17 4,12"/>
        </svg>
        ${fileName}
    `;
    label.style.color = '#10b981';
    label.style.borderColor = '#10b981';
    label.style.background = '#f0fdf4';
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const passwordFields = document.getElementById('passwordFields');
    
    if (passwordFields.classList.contains('active')) {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('new_password_confirmation').value;
        
        if (!currentPassword) {
            e.preventDefault();
            alert('Current password is required when changing password.');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New password and confirmation do not match.');
            return;
        }
        
        if (newPassword.length < 6) {
            e.preventDefault();
            alert('New password must be at least 6 characters long.');
            return;
        }
    }
});
</script>

@endsection