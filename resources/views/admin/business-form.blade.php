@extends('layouts.admin')

@section('title', 'Businesses')

@section('content')
<link rel="stylesheet" href="{{ URL::asset('assets/css/admin/business-form.css'); }}">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="business-form-container">

    <form action="{{isset($business) ? url('admin/businesses/update') : url('admin/businesses/store') }}" method="POST" enctype="multipart/form-data">
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

                        <label for="name">Business Name</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Enter business name" value="{{ isset($business) ? $business->name : '' }}" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Business Description</label>
                        <textarea id="description" name="description" class="form-control" placeholder="Enter business description" rows="5" required>{{ isset($business) ? $business->description : '' }}</textarea>
                    </div>
                </div>

                <div class="form-column">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select id="category" name="category_id" class="form-control" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ isset($business) && $business->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="thumbnail">Thumbnail</label>
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
                        <label for="name">Mobile No.</label>
                        <input type="text" id="mobile" name="mobile" value="{{ isset($business) ? $business->mobile_no : '' }}" class="form-control" placeholder="Enter Mobile No.">
                    </div>

                    <div class="form-group">
                        <label for="name">Whatsapp No.</label>
                        <input type="text" id="whatsapp" name="whatsapp" value="{{ isset($business) ? $business->whatsapp_no : '' }}" class="form-control" placeholder="Enter Whatsapp No.">
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