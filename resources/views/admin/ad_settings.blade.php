@extends('layouts.admin')

@section('title', 'AdMob Settings')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/banner-ads.css') }}">

<div class="banner-container">
    <div class="header-container">
        <div>
            <h3 class="categories-label" style="font-size: 1.5rem; font-weight: 700; color: #2c3e50; margin-bottom: 5px;">AdMob Unit Configuration</h3>
            <p class="categories-description" style="color: #777; font-size: 0.95rem;">Enable/Disable ads and manage Ad Unit IDs for the Maru Mehsana mobile app.</p>
        </div>
    </div>

    @if (session('success'))
    <div class="alert alert-success">
        <span class="alert-icon">&#10004;</span>
        <div class="alert-text">{{ session('success') }}</div>
        <button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
    @endif

    <div class="premium-table-container">
        <form action="{{ route('admin.ad-settings.update') }}" method="POST">
            @csrf
            <table class="premium-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Sr.</th>
                        <th>Placement Name</th>
                        <th style="width: 120px;">Status</th>
                        <th>Android Ad Unit ID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settings as $index => $setting)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span style="font-weight: 600; color: #2c3e50;">{{ $setting->name }}</span><br>
                            <small style="color: #94a3b8;">{{ $setting->placement_key }}</small>
                        </td>
                        <td>
                            <label class="switch">
                                <input type="checkbox" name="settings[{{ $setting->id }}][is_active]" {{ $setting->is_active ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                            <span style="font-size: 0.8rem; margin-left: 10px; color: {{ $setting->is_active ? '#27ae60' : '#e74c3c' }}; font-weight: 600;">
                                {{ $setting->is_active ? 'ON' : 'OFF' }}
                            </span>
                        </td>
                        <td>
                            <input type="text" name="settings[{{ $setting->id }}][ad_unit_id_android]" 
                                   value="{{ $setting->ad_unit_id_android }}" 
                                   class="form-control" 
                                   style="width: 100%; padding: 8px; border: 1px solid #e2e8f0; border-radius: 6px;"
                                   placeholder="ca-app-pub-xxxxxxxxxxxxxxxx/yyyyyyyyyy">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div style="margin-top: 30px; text-align: right;">
                <button type="submit" class="btn-add-premium" style="background: #27ae60; border: none; padding: 12px 30px; border-radius: 8px; color: white; font-weight: 700; cursor: pointer; transition: 0.3s;">
                    <i class="fas fa-save" style="margin-right: 8px;"></i> Save All Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .banner-container { padding: 20px; }
    .switch { position: relative; display: inline-block; width: 46px; height: 24px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; }
    input:checked + .slider { background-color: #27ae60; }
    input:checked + .slider:before { transform: translateX(22px); }
    .slider.round { border-radius: 34px; }
    .slider.round:before { border-radius: 50%; }
    
    .form-control:focus { outline: none; border-color: #3498db; box-shadow: 0 0 0 2px rgba(52,152,219,0.2); }
</style>
@endsection
