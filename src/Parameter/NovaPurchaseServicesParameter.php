<?php

namespace OrcaServices\NovaApi\Parameter;

/**
 * Data.
 */
final class NovaPurchaseServicesParameter extends NovaIdentifierParameter
{
    /**
     * NOVA service ID.
     *
     * @var string
     */
    public $novaServiceId = '';

    /**
     * The SBB article price.
     *
     * @var float
     */
    public $price = 0.00;

    /**
     * The currency.
     *
     * @var string
     */
    public $currency = 'CHF';

    /**
     * The payment type code.
     *
     * Codes: UNBEKANNT; BAR; BON; MAE; FAK; DOS; DIN; AMX; JCB; VEG; VIS; PCD; YWD; MC; EC; MIG; ONE; REK; UAP
     *
     * @var string
     */
    public $paymentTypeCode = 'BAR';
}
