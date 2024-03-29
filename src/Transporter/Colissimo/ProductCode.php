<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Transporter\Colissimo;

class ProductCode
{
    // FRANCE
    public const DOM = 'DOM';
    public const COLD = 'COLD';
    public const DOS = 'DOS';
    public const COL = 'COL';
    public const BPR = 'BPR';
    public const A2P = 'A2P';
    public const CORE = 'CORE';
    public const COLR = 'COLR';

    // Europe
    public const CMT = 'CMT';
    public const BDP = 'BDP';
    public const BOM = 'BOM';
    public const BOS = 'BOS';

    // International
    public const CORI = 'CORI';
    public const ECO = 'ECO';
    public const PCS = 'PCS';
    public const COM = 'COM';
    public const CDS = 'CDS';
    public const COLI = 'COLI';
    public const VALUES = [
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.DOM' => 'DOM',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COLD' => 'COLD',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.DOS' => 'DOS',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COL' => 'COL',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BPR' => 'BPR',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.A2P' => 'A2P',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CORE' => 'CORE',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COLR' => 'COLR',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CORI' => 'CORI',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.ECO' => 'ECO',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CMT' => 'CMT',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.PCS' => 'PCS',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BDP' => 'BDP',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COM' => 'COM',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CDS' => 'CDS',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BOM' => 'BOM',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BOS' => 'BOS',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COLI' => 'COLI'
    ];
}
