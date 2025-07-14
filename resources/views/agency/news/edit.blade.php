@extends('agency.layout.app')

@section('title', 'Edit News Article')
@section('header-title', 'Edit News Article')

@push('styles')
<style>
    .form-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 2rem;
    }

    .form-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .form-title {
        color: #2c3e50;
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .form-description {
        color: #64748b;
        font-size: 0.875rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .form-control:focus {
        outline: none;
        border-color: #f2652d;
        box-shadow: 0 0 0 3px rgba(242, 101, 45, 0.1);
    }

    .form-control.error {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .form-error {
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .textarea {
        min-height: 120px;
        resize: vertical;
    }

   
    .file-input-container {
        position: relative;
    }

    .file-input {
        position: absolute;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }

    .file-input-label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 2rem;
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
    }

    .file-input-label:hover {
        border-color: #f2652d;
        background: rgba(242, 101, 45, 0.05);
    }

    .file-input-label.has-file {
        border-color: #10b981;
        background: rgba(16, 185, 129, 0.05);
    }

    .current-image {
        margin-bottom: 1rem;
        text-align: center;
    }

    .current-image img {
        max-width: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .current-image-label {
        display: block;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        color: #64748b;
    }

    .checkbox-group {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .checkbox {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .checkbox:checked {
        background: #f2652d;
        border-color: #f2652d;
    }

    .checkbox-label {
        font-size: 0.875rem;
        color: #374151;
        cursor: pointer;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
        margin-top: 2rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #f2652d, #e55a26);
        color: white;
        box-shadow: 0 2px 8px rgba(242, 101, 45, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(242, 101, 45, 0.4);
    }

    .btn-secondary {
        background: #6b7280;
        color: white;
    }

    .btn-secondary:hover {
        background: #4b5563;
    }

    .image-preview {
        margin-top: 1rem;
        max-width: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    @media (max-width: 768px) {
        .form-card {
            margin: 0 1rem;
            padding: 1.5rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .checkbox-group {
            flex-direction: column;
            gap: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="form-container">
    <div class="form-card">
        <div class="form-header">
            <h2 class="form-title">Edit Article</h2>
            <p class="form-description">Update the article details below</p>
        </div>

        <form action="{{ route('agency.news.update', $news) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="title" class="form-label">Article Title *</label>
                <input type="text" id="title" name="title" class="form-control {{ $errors->has('title') ? 'error' : '' }}" 
                       value="{{ old('title', $news->title) }}" placeholder="Enter article title" required>
                @error('title')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="excerpt" class="form-label">Article Excerpt *</label>
                <textarea id="excerpt" name="excerpt" class="form-control textarea {{ $errors->has('excerpt') ? 'error' : '' }}" 
                          placeholder="Brief description of the article (max 500 characters)" required>{{ old('excerpt', $news->excerpt) }}</textarea>
                @error('excerpt')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="content" class="form-label">Article Content *</label>
                <textarea id="content" name="content" class="form-control {{ $errors->has('content') ? 'error' : '' }}"
                          placeholder="Write your article content here..." required>{{ old('content', $news->content) }}</textarea>
                @error('content')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="image" class="form-label">Featured Image</label>
                
                @if($news->image)
                    <div class="current-image">
                        <span class="current-image-label">Current Image:</span>
                        <img src="{{ $news->image_url }}" alt="{{ $news->title }}">
                    </div>
                @endif

                <div class="file-input-container">
                    <input type="file" id="image" name="image" class="file-input" accept="image/*" onchange="previewImage(this)">
                    <label for="image" class="file-input-label" id="file-label">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21,15 16,10 5,21"/>
                        </svg>
                        <div>
                            <div style="font-weight: 600; margin-bottom: 0.25rem;">
                                {{ $news->image ? 'Replace image' : 'Choose an image' }}
                            </div>
                            <div style="font-size: 0.75rem; color: #64748b;">PNG, JPG, GIF up to 2MB</div>
                        </div>
                    </label>
                </div>
                <img id="image-preview" class="image-preview" style="display: none;">
                @error('image')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Article Settings</label>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="is_active" name="is_active" class="checkbox" 
                               {{ old('is_active', $news->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="checkbox-label">Active (Visible to users)</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="is_featured" name="is_featured" class="checkbox" 
                               {{ old('is_featured', $news->is_featured) ? 'checked' : '' }}>
                        <label for="is_featured" class="checkbox-label">Featured Article</label>
                    </div>
                    <div class="checkbox-item">
    <input type="checkbox" id="is_for_mehsana" name="is_for_mehsana" class="checkbox" 
           {{ old('is_for_mehsana', $news->is_for_mehsana) ? 'checked' : '' }}>
    <label for="is_for_mehsana" class="checkbox-label">For Mehsana</label>
</div>
                </div>
            </div>

            <div class="form-actions">
                <a href="{{ route('agency.news.index') }}" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17,21 17,13 7,13 7,21"/>
                        <polyline points="7,3 7,8 15,8"/>
                    </svg>
                    Update Article
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const label = document.getElementById('file-label');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            label.classList.add('has-file');
            label.innerHTML = `
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20,6 9,17 4,12"/>
                </svg>
                <div>
                    <div style="font-weight: 600; margin-bottom: 0.25rem;">New image selected</div>
                    <div style="font-size: 0.75rem; color: #64748b;">${input.files[0].name}</div>
                </div>
            `;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.7.0/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#content',
    height: 400,
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    content_style: 'body { font-family: Inter, sans-serif; font-size: 14px }',
    images_upload_url: '{{ route("agency.upload.image") }}',
    automatic_uploads: true,
    file_picker_types: 'image',
    setup: function (editor) {
        editor.on('change', function () {
            editor.save();
        });
    }
});
</script>
@endpush
@endsection