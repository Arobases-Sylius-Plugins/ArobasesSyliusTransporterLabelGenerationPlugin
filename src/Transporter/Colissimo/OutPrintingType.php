<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Transporter\Colissimo;

class OutPrintingType
{
    // TRANSPORTER TYPE
    public const TRANSPORTER_TYPE_COLISSMO = 'COLISSMO';
    public const TRANSPORTER_TYPE_CHRONOPOST = 'CHRONOPOST';

    // COLISSIMO
    public const ZPL_10_X_15_203_DPI = 'ZPL_10x15_203dpi';
    public const ZPL_10_X_15_300_DPI = 'ZPL_10x15_300dpi';
    public const DPL_10_X_15_203_DPI = 'DPL_10x15_203dpi';
    public const DPL_10_X_15_300_DPI = 'DPL_10x15_300dpi';
    public const PDF_10_X_15_300_DPI = 'PDF_10x15_300dpi';
    public const PDF_A_4_300_DPI = 'PDF_A4_300dpi';
    public const ZPL_10_X_10_203_DPI = 'ZPL_10x10_203dpi';
    public const ZPL_10_X_10_300_DPI = 'ZPL_10x10_300dpi';
    public const DPL_10_X_10_203_DPI = 'DPL_10x10_203dpi';
    public const DPL_10_X_10_300_DPI = 'DPL_10x10_300dpi';
    public const PDF_10_X_10_300_DPI = 'PDF_10x10_300dpi';

    // CHRONOPOST
    public const CHRONO_PDF = 'PDF';
    public const CHRONO_PPR = 'PPR';
    public const CHRONO_SPD = 'SPD';
    public const CHRONO_Z2D = 'Z2D';
    public const CHRONO_THE = 'THE';
    public const CHRONO_XML = 'XML';
    public const CHRONO_XML2D = 'XML2D';
    public const CHRONO_THEPSG = 'THEPSG';
    public const CHRONO_ZPLPSG = 'ZPLPSG';
    public const CHRONO_ZPL300 = 'ZPL300';

    public const OUTPRINTING_TYPE_COLISSIMO = [
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::ZPL_10_X_15_203_DPI => self::ZPL_10_X_15_203_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::ZPL_10_X_15_300_DPI => self::ZPL_10_X_15_300_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::DPL_10_X_10_203_DPI => self::DPL_10_X_10_203_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::DPL_10_X_10_300_DPI => self::DPL_10_X_10_300_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::DPL_10_X_15_203_DPI => self::DPL_10_X_15_203_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::DPL_10_X_15_300_DPI => self::DPL_10_X_15_300_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::ZPL_10_X_10_203_DPI => self::ZPL_10_X_10_203_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::ZPL_10_X_10_300_DPI => self::ZPL_10_X_10_300_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::PDF_10_X_10_300_DPI => self::PDF_10_X_10_300_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::PDF_10_X_15_300_DPI => self::PDF_10_X_15_300_DPI,
        self::TRANSPORTER_TYPE_COLISSMO . "_" . self::PDF_A_4_300_DPI => self::PDF_A_4_300_DPI,
    ];

    public const OUTPRINTING_TYPE_CHRONOPOST = [
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_PDF => self::CHRONO_PDF,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_PPR => self::CHRONO_PPR,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_SPD => self::CHRONO_SPD,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_Z2D => self::CHRONO_Z2D,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_THE => self::CHRONO_THE,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_XML => self::CHRONO_XML,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_XML2D => self::CHRONO_XML2D,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_THEPSG => self::CHRONO_THEPSG,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_ZPLPSG => self::CHRONO_ZPLPSG,
        self::TRANSPORTER_TYPE_CHRONOPOST . "_" . self::CHRONO_ZPL300 => self::CHRONO_ZPL300,
    ];
}
