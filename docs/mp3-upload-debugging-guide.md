# MP3 File Upload Debugging Guide

This guide helps you debug issues when uploading MP3 files (or any files) to the private files system.

## Logging Points

We've added comprehensive logging throughout the upload process. Here's where to look:

### 1. Frontend (Browser Console)

In your browser's developer console, you'll see logs prefixed with `[PrivateFileForm]`:

- **File Selection**: When you select a file, it logs:

    - File name, size, type
    - Whether it's an MP3 file
    - File size validation (checks against 512MB limit)

- **Form Submission**: When you submit the form, it logs:
    - All form data being submitted
    - FormData preparation details
    - Upload progress events
    - Success or error responses

### 2. Backend (Laravel Logs)

Check your Laravel logs at `storage/logs/laravel.log` for the following:

#### PrivateFileRequest Validation

- `[PrivateFileRequest]` - Logs validation details:
    - Request method and data
    - File validation details (size, mime type)
    - Validation rules being applied

#### PrivateFileController

- `[PrivateFileController]` - Logs controller actions:
    - Request details when store method is called
    - File details (name, size, mime type, validity)
    - MP3 detection
    - Validated data
    - Success or failure of upload

#### PrivateFileService

- `[PrivateFileService]` - Logs service layer operations:
    - File upload initiation
    - Storage path generation
    - Database record creation
    - Any exceptions during the process

#### FileUploadService (S3 Upload)

- `[FileUploadService]` - Logs S3 operations:
    - S3 upload initiation with file details
    - Successful upload with stored path
    - S3 errors if upload fails

## Common Issues and Solutions

### 1. File Size Too Large

**Symptoms**:

- Frontend shows "File size exceeds the 512MB limit"
- Backend logs show file size validation failure

**Solutions**:

- Check file size before upload
- Consider compressing MP3 files
- Adjust `max:524288` in `PrivateFileRequest` if needed (current limit is 512MB)

### 2. Invalid Content Type

**Symptoms**:

- Validation error about content type

**Current allowed content types**:

- `ebook`
- `audiobook` (use this for MP3 files)
- `video`
- `document`
- `other`

### 3. S3 Upload Failures

**Symptoms**:

- `[FileUploadService] S3 upload failed` in logs

**Check**:

- AWS credentials in `.env`
- S3 bucket permissions
- Network connectivity

### 4. PHP Upload Limits

**Symptoms**:

- File uploads fail for large files even under 512MB

**Check these PHP settings**:

```ini
upload_max_filesize = 512M
post_max_size = 512M
max_execution_time = 300
memory_limit = 512M
```

### 5. Web Server Limits

**For Nginx**, check:

```nginx
client_max_body_size 512M;
```

**For Apache**, check:

```apache
LimitRequestBody 536870912
```

## Debugging Steps

1. **Open Browser Console** before starting upload
2. **Select your MP3 file** and check console for file details
3. **Submit the form** and watch for progress/errors
4. **Check Laravel logs** for backend processing:
    ```bash
    tail -f storage/logs/laravel.log | grep -E '\[PrivateFile|FileUploadService\]'
    ```
5. **Check Network tab** in browser DevTools for request/response details

## MP3 Specific Considerations

- MP3 files should use content type `audiobook`
- Expected MIME type: `audio/mpeg`
- File extension: `.mp3`
- Consider file compression for large MP3s (e.g., reduce bitrate)

## Testing Upload

To test if uploads are working:

1. Try a small file first (< 10MB)
2. If that works, try your MP3 file
3. Check all log points mentioned above

## Need More Help?

If you're still having issues:

1. Clear browser cache and Laravel cache:

    ```bash
    php artisan cache:clear
    php artisan config:clear
    ```

2. Check Laravel and web server error logs

3. Verify S3 bucket exists and is accessible

4. Test with different file types to isolate MP3-specific issues
