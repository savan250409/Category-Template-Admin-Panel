# Upload Limit Fix

## Problem
The application was limited to uploading only 20 images at a time due to PHP's `max_input_vars` setting being set to 1000.

## Solution Applied

### 1. Root Cause
- PHP's `max_input_vars` was set to 1000
- Each file upload creates multiple input variables
- With 1000 max input variables, only ~20 files could be uploaded

### 2. Changes Made

#### A. Created `.htaccess` file in root directory
- Sets PHP upload limits via Apache configuration
- Increases `max_input_vars` to 10000
- Increases `max_file_uploads` to 1000
- Sets other upload-related limits

#### B. Created `config/upload.php`
- Laravel configuration file for upload settings
- Centralized upload configuration
- Environment variable support

#### C. Created `app/Http/Middleware/SetUploadLimits.php`
- Middleware to set PHP upload limits at runtime
- Applied to upload routes
- Ensures limits are set for each request

#### D. Updated `app/Http/Controllers/FrameController.php`
- Uses configuration values instead of hardcoded limits
- Better error messages for upload limits
- Consistent validation across add and update methods

#### E. Updated `public/index.php`
- Includes upload configuration file
- Sets limits before Laravel starts

#### F. Updated routes
- Applied upload limits middleware to upload routes
- Ensures proper configuration for file uploads

### 3. New Limits
- `max_file_uploads`: 1000 (was 9999999)
- `max_input_vars`: 10000 (was 1000)
- `upload_max_filesize`: 100M
- `post_max_size`: 100M
- `memory_limit`: 512M
- `max_execution_time`: 300 seconds
- `max_input_time`: 300 seconds

### 4. Testing
To test the fix:

1. **Check current limits:**
   ```
   http://your-domain/upload_config.php?debug=upload
   ```

2. **Try uploading more than 20 images:**
   - Go to the add frame page
   - Select more than 20 images
   - Upload should now work

3. **Check Laravel configuration:**
   ```php
   php artisan config:cache
   php artisan route:cache
   ```

### 5. Files Modified/Created
- `.htaccess` (new)
- `config/upload.php` (new)
- `app/Http/Middleware/SetUploadLimits.php` (new)
- `app/Http/Controllers/FrameController.php` (modified)
- `app/Http/Kernel.php` (modified)
- `routes/web.php` (modified)
- `public/index.php` (modified)
- `public/upload_config.php` (new)

### 6. Troubleshooting
If the fix doesn't work:

1. **Check Apache configuration:**
   - Ensure `mod_php` is enabled
   - Check if `.htaccess` is being read

2. **Check PHP configuration:**
   - Some hosting providers don't allow `php_value` in `.htaccess`
   - Contact hosting provider to increase limits

3. **Alternative solution:**
   - Modify `php.ini` file directly
   - Set limits in hosting control panel

### 7. Verification
After applying the fix, you should be able to upload up to 100 images at once (configurable in `config/upload.php`). 
