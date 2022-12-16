<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Files\Uploader;

use Arobases\SyliusTransporterLabelGenerationPlugin\Files\Provider\FileNameProvider;
use Symfony\Component\Filesystem\Filesystem;

final class LabelUploader extends FileUploader {

    public function __construct(Filesystem $fileSystem, FileNameProvider $fileNameProvider, string $brandBaseUploadPath, string $brandComplementUploadPath )
    {
        parent::__construct($fileSystem, $fileNameProvider, $brandBaseUploadPath, $brandComplementUploadPath);
    }
}
