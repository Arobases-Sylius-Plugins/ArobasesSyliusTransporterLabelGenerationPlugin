<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Files\Uploader;

use Arobases\SyliusTransporterLabelGenerationPlugin\Files\Provider\FileNameProvider;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class FileUploader
{
    protected Filesystem $filesystem;

    protected FileNameProvider $fileNameProvider;

    protected string $baseUploadPath;

    protected string $complementUploadPath;

    public function __construct(
        Filesystem $filesystem,
        FileNameProvider $fileNameProvider,
        string $baseUploadPath,
        string $complementUploadPath
    ) {
        $this->filesystem = $filesystem;
        $this->fileNameProvider = $fileNameProvider;

        $this->baseUploadPath = $baseUploadPath;
        $this->complementUploadPath = $complementUploadPath;
    }

    public function upload(UploadedFile $file): ?string
    {
        $uploadPath = $this->baseUploadPath . $this->complementUploadPath;

        try {
            $fileName = $this->fileNameProvider->getFileName($file, $uploadPath);
            $this->filesystem->copy($file->getRealPath(), $uploadPath . $fileName);
        } catch(\Exception $exception) {
            return null;
        }

        return $this->complementUploadPath . $fileName;
    }
}
