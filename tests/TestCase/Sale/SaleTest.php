<?php

namespace OrcaServices\NovaApi\Test\TestCase\SAV;

use Cake\Chronos\Chronos;
use OrcaServices\NovaApi\Method\NovaConfirmReceiptsMethod;
use OrcaServices\NovaApi\Method\NovaCreateOffersMethod;
use OrcaServices\NovaApi\Method\NovaCreateReceiptsMethod;
use OrcaServices\NovaApi\Method\NovaCreateServicesMethod;
use OrcaServices\NovaApi\Method\NovaPurchaseServicesMethod;
use OrcaServices\NovaApi\Parameter\NovaConfirmReceiptsParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateOffersParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateReceiptsParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateServicesParameter;
use OrcaServices\NovaApi\Parameter\NovaPurchaseServicesParameter;
use OrcaServices\NovaApi\Test\Traits\NovaClientTestTrait;
use OrcaServices\NovaApi\Test\Traits\UnitTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * NOVA Orchestration 4-Klang.
 */
class SaleTest extends TestCase
{
    use NovaClientTestTrait;
    use UnitTestTrait;

    /**
     * Test NOVA Orchestration 4-Klang.
     *
     * @return void
     */
    public function testSale()
    {
        // Create a mocked response queue
        $responses = $this->createResponses(
            [
                __DIR__ . '/../../Resources/Response/Sale/CreateOffersResponse.xml',
                __DIR__ . '/../../Resources/Response/Sale/CreateServiceResponse.xml',
                __DIR__ . '/../../Resources/Response/Sale/PurchaseServiceResponse.xml',
                __DIR__ . '/../../Resources/Response/Sale/CreateReceiptsResponse.xml',
                __DIR__ . '/../../Resources/Response/Sale/ConfirmReceiptsResponse.xml',
            ]
        );

        $this->createNovaApiClient($responses);

        $tkId = '7f80a2ab-23b7-4903-8811-4800aa5a6845';

        // 1. sequence (create offers / quotations).
        $parameter = new NovaCreateOffersParameter();
        $this->setIdentifier($parameter);
        $parameter->tkId = $tkId;
        $parameter->novaProductNumber = 51648;
        $parameter->dateOfBirth = Chronos::parse('1975-01-16');
        $parameter->genderTypeId = 1;
        $parameter->travelClass = 2;
        $parameter->validFrom = Chronos::parse('2021-05-15');
        $parameter->tariffOwner = '460';

        $method = $this->container->get(NovaCreateOffersMethod::class);
        $createOffersResult = $method->createOffers($parameter);

        $this->assertCount(1, $createOffersResult->offers);
        $this->assertSame('105.00', $createOffersResult->offers[0]->price);

        // 2. sequence (create services / verify offer).
        $novaOfferId = $createOffersResult->offers[0]->novaOfferId;

        $parameter = new NovaCreateServicesParameter();
        $this->setIdentifier($parameter);
        $parameter->tkId = $tkId;
        $parameter->novaOfferId = $novaOfferId;

        $method = $this->container->get(NovaCreateServicesMethod::class);
        $createServicesResult = $method->createService($parameter);

        $this->assertSame('OFFERIERT', $createServicesResult->services[0]->serviceStatus);

        // 3. sequence.
        $parameter = new NovaPurchaseServicesParameter();
        $this->setIdentifier($parameter);
        $parameter->novaServiceId = $createServicesResult->services[0]->serviceId;
        $parameter->price = $createServicesResult->services[0]->price;
        $parameter->currency = $createServicesResult->services[0]->currency;
        // default
        $parameter->paymentTypeCode = 'BAR';

        $method = $this->container->get(NovaPurchaseServicesMethod::class);
        $purchaseServiceResult = $method->purchaseService($parameter);

        $this->assertSame('VERKAUFT', $purchaseServiceResult->services[0]->serviceStatus);

        // 4.1 sequence - create receipts
        $parameter = new NovaCreateReceiptsParameter();
        $this->setIdentifier($parameter);
        $parameter->novaServiceId = $purchaseServiceResult->services[0]->serviceId;

        $method = $this->container->get(NovaCreateReceiptsMethod::class);
        $receiptsResult = $method->createReceipts($parameter);

        $this->assertSame('PRODUKTION_BEREIT', $receiptsResult->services[0]->serviceStatus);

        // 4.2 sequence - confirm receipt production
        $parameter = new NovaConfirmReceiptsParameter();
        $this->setIdentifier($parameter);
        $parameter->novaServiceId = $receiptsResult->services[0]->serviceId;

        $method = $this->container->get(NovaConfirmReceiptsMethod::class);
        $confirmReceiptsResult = $method->confirmReceipts($parameter);

        $this->assertSame('PRODUKTION_ERFOLGREICH', $confirmReceiptsResult->services[0]->serviceStatus);
    }
}
