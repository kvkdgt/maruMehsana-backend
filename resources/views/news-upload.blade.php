<!DOCTYPE html>
<html lang="gu">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex">
    <meta name="referrer" content="no-referrer">
    <title>Upload News</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; background: #f2f5f9; color: #1c2b3a; }
        .wrap { max-width: 560px; margin: 0 auto; padding: 16px; }
        .card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 4px 16px rgba(0,0,0,.08); }
        h1 { font-size: 1.3rem; margin: 0 0 16px; display: flex; justify-content: space-between; align-items: center; }
        label { display: block; font-weight: 600; margin: 14px 0 6px; font-size: .95rem; }
        input[type=text], input[type=password], textarea, input[type=file] { width: 100%; padding: 12px; font-size: 1rem; border: 1px solid #cbd5e1; border-radius: 10px; background: #fff; font-family: inherit; }
        textarea { resize: vertical; }
        input:focus, textarea:focus { outline: 2px solid #1769b5; border-color: #1769b5; }
        .check { display: flex; align-items: center; gap: 10px; margin-top: 12px; font-weight: 500; }
        .check input { width: 20px; height: 20px; }
        button { width: 100%; margin-top: 20px; padding: 14px; font-size: 1.05rem; font-weight: 700; color: #fff; background: #1769b5; border: 0; border-radius: 10px; cursor: pointer; }
        button:disabled { opacity: .6; }
        .link { width: auto; margin: 0; padding: 6px 12px; font-size: .85rem; background: #e2e8f0; color: #1c2b3a; }
        .ok { background: #dcfce7; color: #14532d; padding: 12px; border-radius: 10px; margin-bottom: 14px; }
        .err { background: #fee2e2; color: #7f1d1d; padding: 12px; border-radius: 10px; margin-bottom: 14px; }
        .err ul { margin: 0; padding-left: 18px; }
        .hint { font-size: .8rem; color: #64748b; margin-top: 4px; }
        #preview { display: none; width: 100%; margin-top: 10px; border-radius: 10px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        @if (!$unlocked)
            <h1>Upload News</h1>
            @error('password')<div class="err">{{ $message }}</div>@enderror
            <form method="POST" action="{{ route('news-upload.unlock') }}">
                @csrf
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autofocus autocomplete="current-password">
                <button type="submit">Open</button>
            </form>
        @else
            <h1>
                Upload News
                <form method="POST" action="{{ route('news-upload.lock') }}">
                    @csrf
                    <button type="submit" class="link">Lock</button>
                </form>
            </h1>

            @if (session('success'))<div class="ok">{{ session('success') }}</div>@endif
            @if ($errors->any())
                <div class="err"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('news-upload.store') }}" enctype="multipart/form-data" id="news-form">
                @csrf
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" maxlength="255" required>

                <label for="excerpt">Short summary *</label>
                <textarea id="excerpt" name="excerpt" rows="2" maxlength="500" required>{{ old('excerpt') }}</textarea>

                <label for="content">Full news *</label>
                <textarea id="content" name="content" rows="8" required>{{ old('content') }}</textarea>

                <label for="image">Photo</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <div class="hint">JPG, PNG, GIF or WEBP. Max 2 MB.</div>
                <img id="preview" alt="">

                <label class="check"><input type="checkbox" name="is_for_mehsana" value="1" @checked(old('is_for_mehsana'))> Mehsana news</label>
                <label class="check"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))> Featured</label>
                <label class="check"><input type="checkbox" name="send_notification" value="1" @checked(old('send_notification', !$errors->any()))> Send push notification to all users</label>

                <button type="submit" id="submit-btn">Publish</button>
            </form>
        @endif
    </div>
</div>
<script>
    (function () {
        var input = document.getElementById('image');
        var form = document.getElementById('news-form');
        if (input) {
            input.addEventListener('change', function () {
                var img = document.getElementById('preview');
                var file = input.files[0];
                if (file && file.size > 2 * 1024 * 1024) {
                    input.value = '';
                    img.style.display = 'none';
                    window.alert('Photo is bigger than 2 MB. Choose a smaller one.');
                    return;
                }
                img.src = file ? URL.createObjectURL(file) : '';
                img.style.display = file ? 'block' : 'none';
            });
        }
        if (form) {
            form.addEventListener('submit', function () {
                var btn = document.getElementById('submit-btn');
                btn.disabled = true;
                btn.textContent = 'Publishing...';
            });
        }
    })();
</script>
</body>
</html>
