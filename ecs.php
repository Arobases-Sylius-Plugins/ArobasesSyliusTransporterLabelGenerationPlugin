<?php

use SlevomatCodingStandard\Sniffs\Commenting\InlineDocCommentDeclarationSniff;

return static function (\Symplify\EasyCodingStandard\Config\ECSConfig $config): void {
    $config->import(__DIR__ . '/vendor/sylius-labs/coding-standard/ecs.php');
    $config->skip([
        InlineDocCommentDeclarationSniff ::class . '.MissingVariable',
        InlineDocCommentDeclarationSniff::class . '.NoAssignment',
    ]);
    $services = $config->services();

    $services
        ->set(\PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer::class)
        ->call('configure', [['elements' => ['arrays']]])
    ;
};
