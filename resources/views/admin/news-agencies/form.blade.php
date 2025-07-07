@extends('layouts.admin')

@section('title', isset($agency) ? 'Edit News Agency' : 'Add News Agency')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
.news-agency-container {
    /* max-width: 900px; */
    /* margin: 0 auto; */
    padding: 15px;
}

.form-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(52, 73, 94, 0.15);
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, #34495E 0%, #2c3e50 100%);
    padding: 20px 25px;
    color: white;
    position: relative;
}

.form-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
}

.form-header-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 15px;
}

.form-icon {
    width: 45px;
    height: 45px;
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.form-title h2 {
    margin: 0 0 5px 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.form-title p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.form-body {
    padding: 25px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-row.single {
    grid-template-columns: 1fr;
}

.form-group {
    margin-bottom: 0;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #34495E;
    font-size: 0.9rem;
}

.form-label.required::after {
    content: ' *';
    color: #e74c3c;
    font-weight: 700;
}

.form-input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #ecf0f1;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-input:focus {
    border-color: #34495E;
    background: white;
    box-shadow: 0 0 0 3px rgba(52, 73, 94, 0.1);
    outline: none;
}

.section-divider {
    border-top: 2px solid #ecf0f1;
    margin: 25px 0;
    padding-top: 25px;
    position: relative;
}

.section-title {
    position: absolute;
    top: -12px;
    left: 20px;
    background: white;
    padding: 0 15px;
    font-weight: 600;
    color: #34495E;
    font-size: 1rem;
}

.password-group {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #7f8c8d;
    cursor: pointer;
    font-size: 1rem;
    padding: 5px;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: #34495E;
}

.file-upload {
    border: 2px dashed #bdc3c7;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    background: #f8f9fa;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.file-upload:hover {
    border-color: #34495E;
    background: #ecf0f1;
}

.file-upload input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-text {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.upload-text i {
    font-size: 1.5rem;
    display: block;
    margin-bottom: 8px;
    color: #95a5a6;
}

.logo-preview {
    margin-top: 15px;
    text-align: center;
}

.logo-preview img {
    max-width: 80px;
    max-height: 80px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.form-actions {
    padding: 20px 25px;
    background: #f8f9fa;
    border-top: 1px solid #ecf0f1;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.btn {
    padding: 12px 25px;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #34495E;
    color: white;
    box-shadow: 0 2px 8px rgba(52, 73, 94, 0.3);
}

.btn-primary:hover {
    background: #2c3e50;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(52, 73, 94, 0.4);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

.alert {
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-success {
    background: rgba(39, 174, 96, 0.1);
    color: #27ae60;
    border-left: 4px solid #27ae60;
}

.alert-error {
    background: rgba(231, 76, 60, 0.1);
    color: #e74c3c;
    border-left: 4px solid #e74c3c;
}

.error-message {
    color: #e74c3c;
    font-size: 0.8rem;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    margin-left: auto;
    opacity: 0.7;
}

.close-btn:hover {
    opacity: 1;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        justify-content: center;
    }
    
    .form-header-content {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
}

.loading {
    opacity: 0.7;
    pointer-events: none;
}

.loading .btn-primary {
    background: #7f8c8d;
}
</style>

<div class="news-agency-container">
    <!-- Display Errors -->
    @if(session('error'))
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        {{ session('error') }}
        <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        <button class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
    </div>
    @endif

    <form id="agencyForm" method="POST" action="{{ isset($agency) ? route('admin.news-agencies.update', $agency->id) : route('admin.news-agencies.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($agency))
            @method('PUT')
        @endif

        <div class="form-card">
            <div class="form-header">
                <div class="form-header-content">
                    <div class="form-icon">
                        <i class="fas fa-{{ isset($agency) ? 'edit' : 'plus' }}"></i>
                    </div>
                    <div class="form-title">
                        <h2>{{ isset($agency) ? 'Edit News Agency' : 'Add News Agency' }}</h2>
                        <p>{{ isset($agency) ? 'Update agency and administrator details' : 'Create new agency with administrator' }}</p>
                    </div>
                </div>
            </div>

            <div class="form-body">
                <!-- Agency Details -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="agency_name" class="form-label required">Agency Name</label>
                        <input type="text" id="agency_name" name="agency_name" 
                               value="{{ old('agency_name', $agency->name ?? '') }}" 
                               class="form-input" placeholder="Enter agency name" required>
                        @error('agency_name')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="agency_username" class="form-label required">Username</label>
                        <input type="text" id="agency_username" name="agency_username" 
                               value="{{ old('agency_username', $agency->username ?? '') }}" 
                               class="form-input" placeholder="Enter unique username" required>
                        @error('agency_username')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="agency_email" class="form-label required">Agency Email</label>
                        <input type="email" id="agency_email" name="agency_email" 
                               value="{{ old('agency_email', $agency->email ?? '') }}" 
                               class="form-input" placeholder="Enter agency email" required>
                        @error('agency_email')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="logo" class="form-label">Agency Logo</label>
                        <div class="file-upload" onclick="document.getElementById('logo').click()">
                            <input type="file" id="logo" name="logo" accept="image/*" onchange="previewLogo(event)">
                            <div class="upload-text">
                                <i class="fas fa-cloud-upload-alt"></i>
                                Click to upload logo
                            </div>
                        </div>
                        
                        @if(isset($agency) && $agency->logo)
                            <div class="logo-preview">
                                <img id="logo-preview" src="{{ asset('storage/' . $agency->logo) }}" alt="Logo">
                            </div>
                        @else
                            <div class="logo-preview" style="display: none;">
                                <img id="logo-preview" src="#" alt="Logo Preview">
                            </div>
                        @endif
                        
                        @error('logo')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Administrator Section -->
                <div class="section-divider">
                    <div class="section-title">
                        <i class="fas fa-user-shield"></i> Administrator Details
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_name" class="form-label required">Full Name</label>
                        <input type="text" id="admin_name" name="admin_name" 
                               value="{{ old('admin_name', $agency->admin->name ?? '') }}" 
                               class="form-input" placeholder="Enter admin full name" required>
                        @error('admin_name')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="admin_username" class="form-label required">Username</label>
                        <input type="text" id="admin_username" name="admin_username" 
                               value="{{ old('admin_username', $agency->admin->username ?? '') }}" 
                               class="form-input" placeholder="Enter admin username" required>
                        @error('admin_username')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="admin_email" class="form-label required">Email</label>
                        <input type="email" id="admin_email" name="admin_email" 
                               value="{{ old('admin_email', $agency->admin->email ?? '') }}" 
                               class="form-input" placeholder="Enter admin email" required>
                        @error('admin_email')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="admin_phone" class="form-label">Phone Number</label>
                        <input type="tel" id="admin_phone" name="admin_phone" 
                               value="{{ old('admin_phone', $agency->admin->phone ?? '') }}" 
                               class="form-input" placeholder="Enter phone number">
                        @error('admin_phone')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label for="admin_password" class="form-label {{ isset($agency) ? '' : 'required' }}">
                            Password {{ isset($agency) ? '(Leave blank to keep current)' : '' }}
                        </label>
                        <div class="password-group">
                            <input type="password" id="admin_password" name="admin_password" 
                                   class="form-input" placeholder="Enter password" {{ isset($agency) ? '' : 'required' }}>
                            <button type="button" class="password-toggle" onclick="togglePassword('admin_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        @error('admin_password')
                            <div class="error-message">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.news-agencies') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-{{ isset($agency) ? 'save' : 'plus' }}"></i>
                    {{ isset($agency) ? 'Update Agency' : 'Create Agency' }}
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function previewLogo(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('logo-preview');
    const previewContainer = preview.parentElement;
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Form submission handling
document.getElementById('agencyForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const form = this;
    
    // Add loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;
    form.classList.add('loading');
    
    // If there's an error, re-enable the button after 3 seconds
    setTimeout(function() {
        if (submitBtn.disabled) {
            submitBtn.innerHTML = '{{ isset($agency) ? "Update Agency" : "Create Agency" }}';
            submitBtn.disabled = false;
            form.classList.remove('loading');
        }
    }, 10000);
});

// Debug form data
document.getElementById('agencyForm').addEventListener('submit', function(e) {
    console.log('Form is being submitted');
    const formData = new FormData(this);
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }
});
</script>
@endsection