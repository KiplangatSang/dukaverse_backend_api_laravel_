<?php
namespace App\Http\Resources;

class StoreFileResource
{
    public static function storeImageFile($file, string $path): array
    {
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $path . '/' . $fileName;

        // Store the file in the specified path
        $file->move(public_path($path), $fileName);

        return [
            'name' => $fileName,
            'path' => $filePath,
        ];
    }
}
