<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function notifPreview(url) {
        const wrap = document.getElementById('notif-preview-wrap');
        const img  = document.getElementById('notif-preview');
        if (url && /^https?:\/\//i.test(url)) { img.src = url; wrap.style.display = 'block'; }
        else { wrap.style.display = 'none'; }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const urlInput   = document.getElementById('notification_image_url');
        const fileInput  = document.getElementById('notification_image_file');
        const uploadStat = document.getElementById('notif-upload-status');
        const schedInput = document.getElementById('scheduled_at');

        document.getElementById('btn-paste-url').addEventListener('click', function () {
            urlInput.focus();
        });
        document.getElementById('btn-upload-device').addEventListener('click', function () {
            fileInput.click();
        });

        // Upload a chosen file to the server; fill the URL field on success.
        fileInput.addEventListener('change', function () {
            if (!fileInput.files.length) return;
            const fd = new FormData();
            fd.append('image', fileInput.files[0]);
            fd.append('_token', '{{ csrf_token() }}');
            uploadStat.style.display = 'block';

            fetch('{{ route('notifications.uploadImage') }}', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(json => {
                    uploadStat.style.display = 'none';
                    if (json && json.url) {
                        urlInput.value = json.url;
                        notifPreview(json.url);
                    } else {
                        Swal.fire({ icon:'error', title:'Upload failed', text:(json && json.error) || 'Try again.' });
                    }
                })
                .catch(() => {
                    uploadStat.style.display = 'none';
                    Swal.fire({ icon:'error', title:'Upload failed', text:'Network error.' });
                });
        });

        // Send-at presets.
        document.querySelectorAll('.preset-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const p = btn.dataset.preset;
                if (p === 'clear') { schedInput.value = ''; return; }
                const d = new Date();
                if (p !== 'now') d.setMinutes(d.getMinutes() + parseInt(p, 10));
                // Format to yyyy-MM-ddTHH:mm in local time.
                const pad = n => String(n).padStart(2, '0');
                schedInput.value = `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
            });
        });

        // Additional data fields: add / remove rows.
        const extraWrap = document.getElementById('extra-fields');
        const addExtraBtn = document.getElementById('add-extra');
        if (addExtraBtn && extraWrap) {
            addExtraBtn.addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'row g-2 mb-2 extra-row';
                row.innerHTML =
                    '<div class="col-5"><input type="text" name="extra_keys[]" class="form-control" placeholder="key"></div>' +
                    '<div class="col-6"><input type="text" name="extra_vals[]" class="form-control" placeholder="value"></div>' +
                    '<div class="col-1 d-grid"><button type="button" class="btn btn-outline-danger remove-extra" title="Remove">&times;</button></div>';
                extraWrap.appendChild(row);
            });
            extraWrap.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-extra');
                if (btn) btn.closest('.extra-row').remove();
            });
        }

        notifPreview(urlInput.value);

        @if (session('success'))
        Swal.fire({ toast:true, position:'top-end', icon:'success',
            title:@json(session('success')), showConfirmButton:false, timer:5000, timerProgressBar:true });
        @endif
        @if ($errors->any())
        Swal.fire({ toast:true, position:'top-end', icon:'error', title:'Validation Error',
            text:@json($errors->first()), showConfirmButton:false, timer:6000, timerProgressBar:true });
        @endif
    });
</script>
