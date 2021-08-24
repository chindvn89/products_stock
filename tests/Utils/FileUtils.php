<?php

namespace Tests\Utils;

use Illuminate\Http\UploadedFile;

class FileUtils
{
    public static function getUploadableFile($fileName)
    {
        $dummy = file_get_contents($fileName);
        file_put_contents(base_path("tests/data/" . basename($fileName)), $dummy);
        $path = base_path("tests/" . basename($fileName));
        $original_name = 'subscribers.csv';
        $mime_type = 'text/csv';
        $error = null;
        $test = true;

        $file = new UploadedFile($path, $original_name, $mime_type, $error, $test);
        return $file;
    }
}
