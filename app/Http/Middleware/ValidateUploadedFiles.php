<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ValidateUploadedFiles
{
    protected array $allowedExtensions = [
        'jpg', 'jpeg', 'png', 'gif', 'webp',
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv',
        'txt', 'zip',
    ];

    protected int $maxBytes = 10240 * 1024;

    public function handle(Request $request, Closure $next)
    {
        foreach ($request->allFiles() as $file) {
            $this->validateFile($file);
        }

        return $next($request);
    }

    private function validateFile($file): void
    {
        if (is_array($file)) {
            foreach ($file as $item) {
                $this->validateFile($item);
            }

            return;
        }

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            abort(422, 'Invalid uploaded file.');
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $this->allowedExtensions, true) || $file->getSize() > $this->maxBytes) {
            abort(422, 'Uploaded file type or size is not allowed.');
        }
    }
}
