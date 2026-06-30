@extends('layouts.admin')

@section('title', 'Businesses')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/business-form.css'); }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="business-form-container">

    {{-- Flash / validation messages --}}
    @if (session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;">
            {{ session('error') }}
        </div>
    @endif
    @if (session('success'))
        <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
            <strong>Please fix the following:</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="business-form" action="{{isset($business) ? url('admin/businesses/update') : url('admin/businesses/store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Basic Details Section -->
        <div class="form-section">
            <div class="section-title">
                <h3>Basic Details</h3>
            </div>
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <input type="hidden" id="id" name="id" class="form-control" value="{{ isset($business) ? $business->id : '' }}" required>

                        <label for="name" class="required-label">Business Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter business name" value="{{ old('name', isset($business) ? $business->name : '') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="description" class="required-label">Business Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Enter business description" rows="5" required>{{ old('description', isset($business) ? $business->description : '') }}</textarea>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="category" class="required-label">Category</label>
                        <select id="category" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id', isset($business) ? $business->category_id : '') === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="thumbnail" class="required-label">Thumbnail</label>
                        <div class="image-upload">
                            <input type="file" id="thumbnail" name="thumbnail" class="form-control" accept="image/*" @if(!isset($business)) required @endif  onchange="previewImage(event)">
                            <div class="image-preview">
                                <img id="preview" src="{{ isset($business) && $business->thumbnail ? asset('storage/' . $business->thumbnail) : '#' }}" alt="Image Preview" style="{{ isset($business) && $business->thumbnail ? 'display:block;' : 'display:none;' }}">
                            </div>

                        </div>


                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-title">
                <h3>Advance Details</h3>
            </div>
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <label for="owner_search">Business Owner <small style="color:#94a3b8;">(optional)</small></label>
                        <div style="position:relative;">
                            <input type="text" id="owner_search" class="form-control" autocomplete="off"
                                placeholder="Search a registered user by name or email..."
                                onfocus="document.getElementById('owner_options').style.display='block'"
                                onkeyup="filterOwners()">
                            <input type="hidden" name="owner_id" id="owner_id" value="{{ old('owner_id', isset($business) ? $business->owner_id : '') }}">
                            <div id="owner_options" style="display:none; position:absolute; left:0; right:0; top:100%; background:#fff; border:1px solid #e2e8f0; border-radius:8px; max-height:220px; overflow:auto; z-index:50; box-shadow:0 6px 18px rgba(0,0,0,0.08);">
                                <div class="owner-option" data-id="" data-label="" style="padding:10px 14px; cursor:pointer;">— No owner —</div>
                                @foreach($appUsers as $u)
                                    <div class="owner-option" data-id="{{ $u->id }}" data-label="{{ $u->name }} ({{ $u->email }})" style="padding:10px 14px; cursor:pointer;">
                                        {{ $u->name }} <small style="color:#777;">({{ $u->email }})</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <small style="display:block;color:#64748b;margin-top:4px;">Links this business to a registered app user shown as the owner.</small>
                    </div>

                    <div class="form-group">
                        <label for="name">Mobile No.</label>
                        <input type="text" id="mobile" name="mobile" value="{{ isset($business) ? $business->mobile_no : '' }}" class="form-control" placeholder="Enter Mobile No.">
                    </div>

                    <div class="form-group">
                        <label for="name">Whatsapp No.</label>
                        <input type="text" id="whatsapp" name="whatsapp" value="{{ isset($business) ? $business->whatsapp_no : '' }}" class="form-control" placeholder="Enter Whatsapp No.">
                    </div>

                    <div class="form-group">
                <label for="products">Our Products</label>
                <textarea id="products" name="products" class="form-control" placeholder="Enter all products with , seprated" rows="5">{{ isset($business) ? $business->products : '' }}</textarea>
            </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="name">Website</label>
                        <input type="url" id="website" name="website" value="{{ isset($business) ? $business->website_url : '' }}" class="form-control" placeholder="Enter your website link">
                    </div>

                    <div class="form-group">
                        <label for="name">Email</label>
                        <input type="email" id="email" name="email" value="{{ isset($business) ? $business->email_id : '' }}" class="form-control" placeholder="Enter your email id">
                    </div>

                    <div class="form-group">
                <label for="services">Our Services</label>
                <textarea id="services" name="services" class="form-control" placeholder="Enter all services with , seprated" rows="5">{{ isset($business) ? $business->services : '' }}</textarea>
            </div>
                </div>


            </div>
        </div>

        <!-- Business Images Section -->
        <div class="form-section">
            <div class="section-title">
                <h3>Business Images</h3>
            </div>
            <div class="form-row">
                <div class="form-column">
                    <div class="form-group">
                        <div class="image-upload">
                            <input type="file" id="images" name="images[]" class="form-control" accept="image/*" onchange="previewImages(event)" multiple>
                            <div id="image-previews" class="images-preview">
                                <!-- Multiple Image Previews will appear here -->
                            </div>
                            @if (isset($business) && $business->businessImages->count() > 0)
                            <div class="preview-image-title">Already added images</div>
                            @endif
                            <div id="current-image-previews" class="current-images-grid">
                                @if (isset($business) && $business->businessImages->count() > 0) @foreach ($business->businessImages as $image)
                                <div class="image-card" id="image-card-{{ $image->id }}">
                                    <img src="{{ asset('storage/' . $image->image) }}" class="image-preview-current" alt="Business Image">
                                    <button type="button" class="delete-button" onclick="deleteImage({{ $image->id }})">
                                        &times;
                                    </button>
                                </div>
                                @endforeach
                                @endif
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Submit Button -->
        <div class="form-action">
            <button type="submit" class="btn-submit">
                <?php if (isset($business)) {
                ?> Update Business
                <?php
                } else { ?>
                    Add Business
                <?php } ?>
            </button>
        </div>
    </form>
</div>

<script>
    // ── Searchable "Business Owner" dropdown ──
    function filterOwners() {
        const q = document.getElementById('owner_search').value.toLowerCase();
        document.querySelectorAll('#owner_options .owner-option').forEach(opt => {
            const label = (opt.getAttribute('data-label') || '').toLowerCase();
            opt.style.display = (opt.getAttribute('data-id') === '' || label.includes(q)) ? 'block' : 'none';
        });
        document.getElementById('owner_options').style.display = 'block';
    }

    document.addEventListener('DOMContentLoaded', function () {
        const options = document.querySelectorAll('#owner_options .owner-option');
        const search = document.getElementById('owner_search');
        const hidden = document.getElementById('owner_id');
        const box = document.getElementById('owner_options');
        if (!search) return;

        options.forEach(opt => {
            opt.addEventListener('click', function () {
                hidden.value = this.getAttribute('data-id');
                search.value = this.getAttribute('data-label');
                box.style.display = 'none';
            });
            opt.addEventListener('mouseenter', function () { this.style.background = '#f1f5f9'; });
            opt.addEventListener('mouseleave', function () { this.style.background = '#fff'; });
        });

        // Pre-fill the search box from the current owner (edit / validation redirect)
        if (hidden.value) {
            const match = Array.from(options).find(o => o.getAttribute('data-id') === String(hidden.value));
            if (match) search.value = match.getAttribute('data-label');
        }

        // Close when clicking outside
        document.addEventListener('click', function (e) {
            if (!box.contains(e.target) && e.target !== search) box.style.display = 'none';
        });
    });

    function previewImage(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            const preview = document.getElementById('preview');
            preview.src = e.target.result;
            preview.style.display = 'block';
        }

        if (file) {
            reader.readAsDataURL(file);
        }
    }

    function previewImages(event) {
        const files = event.target.files;
        const previewContainer = document.getElementById('image-previews');
        previewContainer.innerHTML = ''; // Clear existing previews

        // Loop through the selected files
        Array.from(files).forEach(file => {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('image-preview');
                    previewContainer.appendChild(img);
                };

                reader.readAsDataURL(file); // Read file as data URL

            }

        );
    }

    function deleteImage(imageId) {
        if (confirm('Are you sure you want to delete this image?')) {
            fetch(`/admin/business-image/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`#image-card-${imageId}`).remove();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    }
</script>

@endsection