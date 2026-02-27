<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Transporter;

class ProductCode
{
    // --------------------------
    // Colissimo - France
    // --------------------------
    public const DOM = 'DOM';
    public const COLD = 'COLD';
    public const DOS = 'DOS';
    public const COL = 'COL';
    public const BPR = 'BPR';
    public const A2P = 'A2P';
    public const CORE = 'CORE';
    public const COLR = 'COLR';

    // Colissimo - Europe
    public const CMT = 'CMT';
    public const BDP = 'BDP';
    public const BOM = 'BOM';
    public const BOS = 'BOS';

    // Colissimo - International
    public const CORI = 'CORI';
    public const ECO = 'ECO';
    public const PCS = 'PCS';
    public const COM = 'COM';
    public const CDS = 'CDS';
    public const COLI = 'COLI';

    public const CE = 'CE';       // Chronopost Domicile Express
    public const CEX = 'CEX';     // Chronopost Relais France
    public const CRE = 'CRE';     // Chronopost Relais Europe

    // --------------------------
    // Mapping pour i18n / valeurs affichables
    // --------------------------
    public const VALUES = [
        // Colissimo
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.DOM' => self::DOM,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COLD' => self::COLD,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.DOS' => self::DOS,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COL' => self::COL,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BPR' => self::BPR,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.A2P' => self::A2P,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CORE' => self::CORE,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COLR' => self::COLR,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CORI' => self::CORI,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.ECO' => self::ECO,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CMT' => self::CMT,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.PCS' => self::PCS,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BDP' => self::BDP,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COM' => self::COM,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CDS' => self::CDS,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BOM' => self::BOM,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BOS' => self::BOS,
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COLI' => self::COLI,

        // Chronopost
        'arobases_sylius_transporter_label_generation_plugin.chronopost.product_code.CE' => self::CE,
        'arobases_sylius_transporter_label_generation_plugin.chronopost.product_code.CEX' => self::CEX,
        'arobases_sylius_transporter_label_generation_plugin.chronopost.product_code.CDS' => self::CRE,
    ];
}