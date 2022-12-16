<?php

declare(strict_types=1);

namespace Arobases\SyliusTransporterLabelGenerationPlugin\Transporter\Colissimo;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\ShippingMethod;
use Sylius\Component\Resource\Model\CodeAwareInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

class ProductCode  {

    // FRANCE
    const DOM  = 'DOM';
    const COLD = 'COLD';
    const DOS  = 'DOS';
    const COL  = 'COL';
    const BPR  = 'BPR';
    const A2P  = 'A2P';
    const CORE = 'CORE';
    const COLR = 'COLR';

    // International
    const CORI = 'CORI';
    const COM  = 'COM';
    const CDS  = 'CDS';
    const ECO  = 'ECO';
    const CMT  = 'CMT';
    const PCS  = 'PCS';
    const BDP  = 'BDP';

    const VALUES = [
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.DOM'  => 'DOM',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COLD' => 'COLD',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.DOS'  => 'DOS',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COL'  => 'COL',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BPR'  => 'BPR',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.A2P'  => 'A2P',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CORE' => 'CORE',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COLR' => 'COLR',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CORI' => 'CORI',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.COM'  => 'COM',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CDS'  => 'CDS',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.ECO'  => 'ECO',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.CMT'  => 'CMT',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.PCS'  => 'PCS',
        'arobases_sylius_transporter_label_generation_plugin.colissimo.product_code.BDP'  => 'BDP'
    ];

}
