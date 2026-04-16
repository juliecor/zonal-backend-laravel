<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        if (! $request->hasFile('file')) {
            return response()->json([
                'success' => false,
                'message' => 'No file provided.',
                'filePath' => null,
            ], 422);
        }

        $request->validate([
            'file' => 'required|image|max:20120',
        ]);

        try {
            $file = $request->file('file');
            $prefix = env('UPLOADS_PREFIX', 'uploads');
            $path = $this->handleS3Upload($file, $prefix);

            return response()->json([
                'success' => true,
                'message' => 'Successfully uploaded!',
                'filePath' => $path,
            ], 200, [], JSON_UNESCAPED_SLASHES);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    protected function handleS3Upload($file, string $dir): string
    {
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $key = trim($dir, '/').'/'.$filename;

        // Upload with public visibility (adjust per your bucket policy)
        Storage::disk('s3')->put($key, file_get_contents($file), [
            'visibility' => 'public',
        ]);

        // Use the disk URL helper so it respects AWS_URL / CDN
        return Storage::disk('s3')->url($key);
    }
}
