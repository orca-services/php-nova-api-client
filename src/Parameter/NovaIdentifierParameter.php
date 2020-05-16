<?php

namespace OrcaServices\NovaApi\Parameter;

/**
 * Data.
 */
class NovaIdentifierParameter
{
    /**
     * Random correlation ID.
     *
     * @var string Must be a UUID
     */
    public $correlationId;

    /**
     * TU-Code for Inkassostelle DV Saldierung
     * Element: leistungsvermittler.
     *
     * @var string
     */
    public $serviceAgent = '37';

    /**
     * Channel code.
     *
     * Fix for BLT
     * Element: kanalCode
     *
     * @var string
     */
    public $channelCode;

    /**
     * Point of sale.
     *
     * DiDok code point of sale, 5 digits without checksum
     * Must belong to the TU (service agent)
     * More details: https://confluence-ext.sbb.ch/display/NOVAUG/DIDOK+Mutationen
     *
     * Element: verkaufsstelle
     *
     * @var string
     */
    public $pointOfSale;

    /**
     * Distribution point.
     *
     * Must be the same like point of sale
     * Element: vertriebspunkt
     *
     * @var string
     */
    public $distributionPoint;

    /**
     * Sale device ID.
     *
     * Fix value, just for statistic purpose.
     * Element: verkaufsgeraeteId
     *
     * @var string
     */
    public $saleDeviceId;
}
