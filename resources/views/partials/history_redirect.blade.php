<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Saving…</title>
    <meta name="robots" content="noindex">
    <style>body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;color:#666;}</style>
</head>
<body>
    <p>Saving and returning to your previous page…</p>
    <script>
        (function () {
            var fallback = @json($fallback);
            var msg      = @json($message ?? null);
            var icon     = @json($icon ?? 'success');

            if (msg) {
                try {
                    sessionStorage.setItem('admin_flash', JSON.stringify({ message: msg, icon: icon }));
                } catch (e) {}
            }

            window.location.replace(fallback);
        })();
    </script>
</body>
</html>
