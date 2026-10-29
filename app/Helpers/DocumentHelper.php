<?php

namespace App\Helpers;

class CacheHelper
{
    public function convertPdfToImages(string $path): array
    {
        $imagick = new \Imagick();

        // ⭐ optimal DPI
        $imagick->setResolution(150, 150);

        $imagick->readImage($path);

        $images = [];

        foreach ($imagick as $i => $page) {
            $page->setImageFormat('jpeg');
            $page->setImageCompressionQuality(85);

            $file = storage_path("app/pages/page_$i.jpg");

            $page->writeImage($file);

            $images[] = $file;
        }

        return $images;
    }
}
