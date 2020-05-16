<?php

namespace OrcaServices\NovaApi\Result;

/**
 * Data.
 */
final class NovaServiceItem
{
    /**
     * The NOVA customer TKID.
     *
     * @var string|null value
     */
    public $tkId;

    /**
     * A unique identifier called leistungsId.
     *
     * @var string|null value
     */
    public $serviceId;

    /**
     * Current status of this service.
     *
     * @var string|null value
     */
    public $serviceStatus;

    /**
     * The service reference number.
     *
     * @var string|null value
     */
    public $serviceReference;

    /**
     * The product number.
     *
     * @var string|null value
     */
    public $productNumber;

    /**
     * The price.
     *
     * @var string|null value
     */
    public $price;

    /**
     * The currency.
     *
     * @var string|null value
     */
    public $currency;

    /**
     * The VAT amount.
     *
     * @var string|null value
     */
    public $vatAmount;

    /**
     * The VAT percent.
     *
     * @var string|null value
     */
    public $vatPercent;
}
