<?php

namespace OrcaServices\NovaApi\Client;

use OrcaServices\NovaApi\Method\CheckSwissPassValidityMethod;
use OrcaServices\NovaApi\Method\NovaConfirmReceiptsMethod;
use OrcaServices\NovaApi\Method\NovaCreateOffersMethod;
use OrcaServices\NovaApi\Method\NovaCreateReceiptsMethod;
use OrcaServices\NovaApi\Method\NovaCreateServicesMethod;
use OrcaServices\NovaApi\Method\NovaPurchaseServicesMethod;
use OrcaServices\NovaApi\Method\NovaSearchPartnerMethod;
use OrcaServices\NovaApi\Method\NovaSearchServicesMethod;
use OrcaServices\NovaApi\Parameter\NovaCheckSwissPassValidityParameter;
use OrcaServices\NovaApi\Parameter\NovaConfirmReceiptsParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateOffersParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateReceiptsParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateServicesParameter;
use OrcaServices\NovaApi\Parameter\NovaPurchaseServicesParameter;
use OrcaServices\NovaApi\Parameter\NovaSearchPartnerParameter;
use OrcaServices\NovaApi\Parameter\NovaSearchServicesParameter;
use OrcaServices\NovaApi\Result\NovaCheckSwissPassValidityResult;
use OrcaServices\NovaApi\Result\NovaConfirmReceiptsResult;
use OrcaServices\NovaApi\Result\NovaCreateOffersResult;
use OrcaServices\NovaApi\Result\NovaCreateReceiptsResult;
use OrcaServices\NovaApi\Result\NovaCreateServicesResult;
use OrcaServices\NovaApi\Result\NovaPurchaseServicesResult;
use OrcaServices\NovaApi\Result\NovaSearchPartnerResult;
use OrcaServices\NovaApi\Result\NovaSearchServicesResult;

/**
 * NOVA API client.
 */
final class NovaApiClient
{
    /**
     * @var NovaSearchServicesMethod
     */
    private $novaSearchServicesMethod;

    /**
     * @var NovaSearchPartnerMethod
     */
    private $novaSearchPartnerMethod;

    /**
     * @var CheckSwissPassValidityMethod
     */
    private $checkSwissPassValidityMethod;

    /**
     * @var NovaCreateOffersMethod
     */
    private $novaCreateOffersMethod;

    /**
     * @var NovaCreateServicesMethod
     */
    private $novaCreateServicesMethod;

    /**
     * @var NovaPurchaseServicesMethod
     */
    private $novaPurchaseServicesMethod;

    /**
     * @var NovaCreateReceiptsMethod
     */
    private $novaCreateReceiptsMethod;

    /**
     * @var NovaConfirmReceiptsMethod
     */
    private $novaConfirmReceiptsMethod;

    /**
     * NovaApiClient constructor.
     *
     * @param NovaSearchPartnerMethod $novaSearchPartnerMethod The method
     * @param CheckSwissPassValidityMethod $checkSwissPassValidityMethod The method
     * @param NovaCreateOffersMethod $novaCreateOffersMethod The method
     * @param NovaCreateServicesMethod $novaCreateServicesMethod The method
     * @param NovaPurchaseServicesMethod $novaPurchaseServicesMethod The method
     * @param NovaCreateReceiptsMethod $novaCreateReceiptsMethod The method
     * @param NovaConfirmReceiptsMethod $novaConfirmReceiptsMethod The method
     * @param NovaSearchServicesMethod $novaSearchServicesMethod The method
     */
    public function __construct(
        NovaSearchPartnerMethod $novaSearchPartnerMethod,
        CheckSwissPassValidityMethod $checkSwissPassValidityMethod,
        NovaCreateOffersMethod $novaCreateOffersMethod,
        NovaCreateServicesMethod $novaCreateServicesMethod,
        NovaPurchaseServicesMethod $novaPurchaseServicesMethod,
        NovaCreateReceiptsMethod $novaCreateReceiptsMethod,
        NovaConfirmReceiptsMethod $novaConfirmReceiptsMethod,
        NovaSearchServicesMethod $novaSearchServicesMethod
    ) {
        $this->novaSearchPartnerMethod = $novaSearchPartnerMethod;
        $this->checkSwissPassValidityMethod = $checkSwissPassValidityMethod;
        $this->novaCreateOffersMethod = $novaCreateOffersMethod;
        $this->novaCreateServicesMethod = $novaCreateServicesMethod;
        $this->novaPurchaseServicesMethod = $novaPurchaseServicesMethod;
        $this->novaCreateReceiptsMethod = $novaCreateReceiptsMethod;
        $this->novaConfirmReceiptsMethod = $novaConfirmReceiptsMethod;
        $this->novaSearchServicesMethod = $novaSearchServicesMethod;
    }

    /**
     * Search partner (customers).
     *
     * @param NovaSearchPartnerParameter $parameter The search parameters
     *
     * @return NovaSearchPartnerResult The search result
     */
    public function searchPartner(NovaSearchPartnerParameter $parameter): NovaSearchPartnerResult
    {
        return $this->novaSearchPartnerMethod->searchPartner($parameter);
    }

    /**
     * Check SwissPass validity.
     *
     * @param NovaCheckSwissPassValidityParameter $parameter The parameters
     *
     * @return NovaCheckSwissPassValidityResult The result
     */
    public function checkSwissPassValidity(
        NovaCheckSwissPassValidityParameter $parameter
    ): NovaCheckSwissPassValidityResult {
        return $this->checkSwissPassValidityMethod->checkSwissPassValidity($parameter);
    }

    /**
     * Create offers.
     *
     * @param NovaCreateOffersParameter $parameter The offers parameters
     *
     * @return NovaCreateOffersResult The result
     */
    public function createOffers(NovaCreateOffersParameter $parameter): NovaCreateOffersResult
    {
        return $this->novaCreateOffersMethod->createOffers($parameter);
    }

    /**
     * Create service.
     *
     * @param NovaCreateServicesParameter $parameter The service parameters
     *
     * @return NovaCreateServicesResult The result
     */
    public function createService(NovaCreateServicesParameter $parameter): NovaCreateServicesResult
    {
        return $this->novaCreateServicesMethod->createService($parameter);
    }

    /**
     * Purchase service.
     *
     * @param NovaPurchaseServicesParameter $parameter The parameters
     *
     * @return NovaPurchaseServicesResult The result
     */
    public function purchaseService(NovaPurchaseServicesParameter $parameter): NovaPurchaseServicesResult
    {
        return $this->novaPurchaseServicesMethod->purchaseService($parameter);
    }

    /**
     * Create receipt.
     *
     * @param NovaCreateReceiptsParameter $parameter The parameters
     *
     * @return NovaCreateReceiptsResult The result
     */
    public function createReceipt(NovaCreateReceiptsParameter $parameter): NovaCreateReceiptsResult
    {
        return $this->novaCreateReceiptsMethod->createReceipts($parameter);
    }

    /**
     * Confirm receipt.
     *
     * @param NovaConfirmReceiptsParameter $parameter The parameters
     *
     * @return NovaConfirmReceiptsResult The result
     */
    public function confirmReceipt(NovaConfirmReceiptsParameter $parameter): NovaConfirmReceiptsResult
    {
        return $this->novaConfirmReceiptsMethod->confirmReceipts($parameter);
    }

    /**
     * Search partner (customers).
     *
     * @param NovaSearchPartnerParameter $parameter The search parameters
     *
     * @return NovaSearchPartnerResult The search result
     */
    public function searchServices(NovaSearchServicesParameter $parameter): NovaSearchServicesResult
    {
        return $this->novaSearchServicesMethod->searchServices($parameter);
    }
}
