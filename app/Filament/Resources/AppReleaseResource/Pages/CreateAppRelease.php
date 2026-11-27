<?php

namespace App\Filament\Resources\AppReleaseResource\Pages;

use App\Filament\Resources\AppReleaseResource;
use App\Models\AppRelease;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class CreateAppRelease extends CreateRecord
{
    protected static string $resource = AppReleaseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        abort_unless(AppReleaseResource::canCreate(), 403);
        $file = $data['apk_upload'] ?? null;
        if (!$file instanceof UploadedFile || strtolower($file->getClientOriginalExtension()) !== 'apk'
            || $file->getSize() > 150 * 1024 * 1024) {
            throw ValidationException::withMessages(['data.apk_upload' => 'Select an APK file up to 150 MB.']);
        }

        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath(), ZipArchive::CHECKCONS) !== true) {
            throw ValidationException::withMessages(['data.apk_upload' => 'Invalid APK archive.']);
        }
        try {
            if ($zip->locateName('AndroidManifest.xml') === false || $zip->locateName('classes.dex') === false) {
                throw ValidationException::withMessages(['data.apk_upload' => 'The file does not contain an Android application.']);
            }
        } finally {
            $zip->close();
        }

        $size = $file->getSize();
        $hash = hash_file('sha256', $file->getRealPath());
        $path = 'app-releases/'.Str::uuid().'.apk';
        $stored = Storage::disk('local')->putFileAs('app-releases', $file, basename($path));
        if ($stored !== $path) {
            throw ValidationException::withMessages(['data.apk_upload' => 'Unable to store the APK.']);
        }
        try {
            return AppRelease::create([
                'version' => $data['version'],
                'release_notes' => $data['release_notes'] ?? null,
                'apk_path' => $path,
                'size_bytes' => $size,
                'sha256' => $hash,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
