<?php

namespace OrcaServices\NovaApi\Test\TestCase\Client;

use Cake\Chronos\Chronos;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use OrcaServices\NovaApi\Client\NovaApiClient;
use OrcaServices\NovaApi\Configuration\NovaApiConfiguration;
use OrcaServices\NovaApi\Parameter\NovaCheckSwissPassValidityParameter;
use OrcaServices\NovaApi\Parameter\NovaConfirmReceiptsParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateOffersParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateReceiptsParameter;
use OrcaServices\NovaApi\Parameter\NovaCreateServicesParameter;
use OrcaServices\NovaApi\Parameter\NovaIdentifierParameter;
use OrcaServices\NovaApi\Parameter\NovaPurchaseServicesParameter;
use OrcaServices\NovaApi\Parameter\NovaSearchPartnerParameter;
use OrcaServices\NovaApi\Parameter\NovaSearchServicesParameter;
use OrcaServices\NovaApi\Test\Traits\UnitTestTrait;
use OrcaServices\NovaApi\Type\GenderType;
use PHPUnit\Framework\TestCase;

/**
 * Tests.
 */
class NovaApiClientTest extends TestCase
{
    use UnitTestTrait;

    /**
     * Create instance.
     *
     * @param array $responses The mocked responses
     *
     * @return NovaApiClient The instance
     */
    private function createNovaApiClient(array $responses): NovaApiClient
    {
        Chronos::setTestNow('2019-09-01 00:00:00');

        $settings = $this->getSettings();

        // To make real http calls, just comment out this line
        $settings = $this->mockNovaGuzzleClient($settings, $responses);

        $this->getContainer()->set(NovaApiConfiguration::class, new NovaApiConfiguration($settings));

        return $this->getContainer()->get(NovaApiClient::class);
    }

    /**
     * Mock NOVA Guzzle client and single sign on (SSO).
     *
     * @param array $settings The nova api settings
     * @param array $responses The mocked responses
     *
     * @return array
     */
    private function mockNovaGuzzleClient(array $settings, array $responses): array
    {
        // Append the login as first response
        $loginResponse = new Response();
        $loginResponse->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/LoginResponse.json')
        );

        array_unshift($responses, $loginResponse);

        $settings['default']['handler'] = HandlerStack::create(new MockHandler($responses));

        return $settings;
    }

    /**
     * Returns the default settings.
     *
     * @return array
     */
    private function getSettings(): array
    {
        $filename = file_exists(__DIR__ . '/../../config.php')
            ? '/../../config.php'
            : '/../../config.php.dist';

        return include __DIR__ . $filename;
    }

    /**
     * Set identifier.
     *
     * @param NovaIdentifierParameter $parameter The params
     *
     * @return void
     */
    private function setTestIdentifier(NovaIdentifierParameter $parameter)
    {
        $parameter->correlationId = '101563d5-f3c4-4723-888b-6ea4bf321c32';
        $parameter->serviceAgent = '00';
        $parameter->channelCode = '000';
        $parameter->pointOfSale = '0000';
        $parameter->distributionPoint = $parameter->pointOfSale;
        $parameter->saleDeviceId = '1';
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSearchPartnerByTkid()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/SearchPartnerResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaSearchPartnerParameter();
        $this->setTestIdentifier($parameter);
        $parameter->tkId = '949e2e6a-fdd1-4f07-8784-201e588ae834';

        $actual = $client->searchPartner($parameter);

        static::assertEmpty($actual->messages);
        static::assertNotEmpty($actual->partners);
        static::assertCount(1, $actual->partners);

        $partner = $actual->partners[0];

        static::assertSame('949e2e6a-fdd1-4f07-8784-201e588ae834', $partner->tkId);
        static::assertSame('164-937-314-5', $partner->ckm);
        static::assertSame('DAW856', $partner->cardNumber);
        static::assertSame('4133', $partner->postalCode);
        static::assertSame('CH', $partner->country);
        static::assertSame('Pratteln', $partner->city);
        static::assertSame('4133', $partner->postalCode);
        static::assertNull($partner->additional);
        static::assertSame('Bahnhofstrasse 1', $partner->street);
        static::assertSame('1234', $partner->poBox);
        static::assertSame('+41612330975', $partner->phoneNumber);
        static::assertSame('+41792330976', $partner->mobileNumber);
        static::assertSame('max.mustermann@example.com', $partner->email);
        static::assertSame('Mustermann', $partner->firstName); // should be the lastName
        static::assertSame('Max', $partner->lastName); // should be the firstName
        static::assertSame('1982-03-28 00:00:00', $partner->dateOfBirth->toDateTimeString());
        static::assertSame(1, $partner->genderTypeId);
        static::assertSame('2019-09-02 08:13:28', $partner->changedAt->toDateTimeString());
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSearchPartnerByCardNumber()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/SearchPartnerResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaSearchPartnerParameter();
        $this->setTestIdentifier($parameter);
        $parameter->cardNumber = 'DAW856';

        $actual = $client->searchPartner($parameter);

        static::assertEmpty($actual->messages);
        static::assertNotEmpty($actual->partners);
        static::assertCount(1, $actual->partners);

        $partner = $actual->partners[0];

        static::assertSame('949e2e6a-fdd1-4f07-8784-201e588ae834', $partner->tkId);
        static::assertSame('164-937-314-5', $partner->ckm);
        static::assertSame('DAW856', $partner->cardNumber);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSearchPartnerByPassengerInformation()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/SearchPartnerResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaSearchPartnerParameter();
        $this->setTestIdentifier($parameter);
        $parameter->firstName = 'Mustermann';
        $parameter->lastName = 'Max';
        $parameter->mail = 'max.mustermann@example.com';
        $parameter->country = 'CH';
        $parameter->city = 'Pratteln';
        $parameter->postalCode = '4133';
        $parameter->street = 'Bahnhofstrasse 1';
        $parameter->dateOfBirth = Chronos::parse('1982-03-28');

        $actual = $client->searchPartner($parameter);

        static::assertEmpty($actual->messages);
        static::assertNotEmpty($actual->partners);
        static::assertCount(1, $actual->partners);

        $partner = $actual->partners[0];

        static::assertSame('949e2e6a-fdd1-4f07-8784-201e588ae834', $partner->tkId);
        static::assertSame('164-937-314-5', $partner->ckm);
        static::assertSame('DAW856', $partner->cardNumber);
    }

    /**
     * Test.
     *
     * @dataProvider checkSwissPassValidityProvider
     *
     * @param string $tkId The tkId
     * @param string $responseFile The responseFile
     * @param string $status The status
     * @param string $result The result
     * @param int $messageCount The number of messages
     *
     * @return void
     */
    public function testCheckSwissPassValidity(
        string $tkId,
        string $responseFile,
        string $status,
        string $result,
        int $messageCount
    ) {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents($responseFile)
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaCheckSwissPassValidityParameter();
        $this->setTestIdentifier($parameter);

        $parameter->tkId = $tkId;

        $actual = $client->checkSwissPassValidity($parameter);

        static::assertSame($actual->status, $status);
        static::assertSame($actual->result, $result);
        static::assertCount($messageCount, $actual->messages);
    }

    /**
     * Data provider.
     *
     * @return array The data
     */
    public function checkSwissPassValidityProvider(): array
    {
        return [
            'OK' => [
                // Max Mustermann with valid SwissPass
                '949e2e6a-fdd1-4f07-8784-201e588ae834',
                __DIR__ . '/../../Ressources/Response/CheckSwissPassValidityResponse.xml',
                'OK',
                'SP_OK',
                0,
            ],
            'SP_NICHT_OK_FOTO_NICHT_OK' => [
                // Hans Meier, with pobox and email
                '05cd0051-649e-4c0e-a54e-3e5e0596f8dc',
                __DIR__ . '/../../Ressources/Response/CheckSwissPassValidityNotOkResponse.xml',
                'OK',
                'SP_NICHT_OK_FOTO_NICHT_OK',
                1,
            ],
        ];
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCreateOffersClass2()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/CreateOffersResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaCreateOffersParameter();
        $this->setTestIdentifier($parameter);
        $parameter->tkId = '949e2e6a-fdd1-4f07-8784-201e588ae834';
        $parameter->novaProductNumber = '51648';
        $parameter->dateOfBirth = Chronos::createFromDate(1982, 03, 28);
        $parameter->genderTypeId = GenderType::MEN;
        $parameter->travelClass = 2;
        $parameter->validFrom = Chronos::now()->setTime(0, 0);

        $actual = $client->createOffers($parameter);

        static::assertCount(1, $actual->getOffers());

        $offer = $actual->getOffers()[0];

        static::assertSame('_5c63dc7d-62e5-4f3a-a761-464488e92000', $offer->novaOfferId);
        static::assertSame('105.00', $offer->price);
        static::assertSame('CHF', $offer->currency);
        static::assertSame('51648', $offer->productNumber);
        static::assertSame('Alle Zonen, Erwachsene, Monate', $offer->title);
        static::assertSame('2019-09-01 00:00:00', $offer->validFrom->toDateTimeString());
        static::assertSame('2019-09-30 23:59:59', $offer->validTo->toDateTimeString());
        static::assertSame('SWISSPASS', $offer->carrierMedium);
        static::assertSame('KLASSE_2', $offer->travelClass);

        static::assertCount(1, $actual->messages);
        static::assertSame(
            [
                'id' => 'M0',
                'code' => '33098',
                'timestamp' => '2019-09-05T13:40:28.000+02:00',
                'type' => 'WARNUNG',
                'customerRelevant' => 'false',
                'message' => 'Der Reisende 1 erhält kein SwissPass-Angebot, weil er bereits einen ' .
                    'gültigen SwissPass, oder einen laufenden Kartenauftrag hat.',
            ],
            (array)$actual->messages[0]
        );
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCreateService()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/CreateServiceResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaCreateServicesParameter();
        $this->setTestIdentifier($parameter);
        $parameter->tkId = '949e2e6a-fdd1-4f07-8784-201e588ae834';
        $parameter->novaOfferId = '_5c63dc7d-62e5-4f3a-a761-464488e92000';

        $actual = $client->createService($parameter);

        static::assertCount(1, $actual->getServices());
        static::assertSame('OFFERIERT', $actual->getServices()[0]->serviceStatus);

        $service = (array)$actual->getServices()[0];

        static::assertSame(
            [
                'tkId' => '949e2e6a-fdd1-4f07-8784-201e588ae834',
                'serviceId' => '15900011821804',
                'serviceStatus' => 'OFFERIERT',
                'serviceReference' => '11821804',
                'productNumber' => '51648',
                'price' => '105.00',
                'currency' => 'CHF',
                'vatAmount' => '105.00',
                'vatPercent' => '7.70',
            ],
            $service
        );

        static::assertEmpty($actual->messages);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testPurchaseService()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/PurchaseServiceResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaPurchaseServicesParameter();
        $this->setTestIdentifier($parameter);
        $parameter->novaServiceId = '15900011821804';
        $parameter->price = 105.00;
        $parameter->currency = 'CHF';
        $parameter->paymentTypeCode = 'BAR';

        $actual = $client->purchaseService($parameter);

        static::assertCount(1, $actual->services);
        static::assertSame('VERKAUFT', $actual->services[0]->serviceStatus);

        $service = (array)$actual->services[0];

        static::assertSame(
            [
                'tkId' => '949e2e6a-fdd1-4f07-8784-201e588ae834',
                'serviceId' => '15900011821804',
                'serviceStatus' => 'VERKAUFT',
                'serviceReference' => '11821804',
                'productNumber' => '51648',
                'price' => '105.00',
                'currency' => 'CHF',
                'vatAmount' => '105.00',
                'vatPercent' => '7.70',
            ],
            $service
        );

        static::assertEmpty($actual->messages);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testCreateReceipt()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/CreateReceiptsResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaCreateReceiptsParameter();
        $this->setTestIdentifier($parameter);
        $parameter->novaServiceId = '15900011821804';

        $actual = $client->createReceipt($parameter);

        static::assertCount(1, $actual->services);
        static::assertSame('PRODUKTION_BEREIT', $actual->services[0]->serviceStatus);

        $service = (array)$actual->services[0];

        static::assertSame(
            [
                'tkId' => '949e2e6a-fdd1-4f07-8784-201e588ae834',
                'serviceId' => '15900011821804',
                'serviceStatus' => 'PRODUKTION_BEREIT',
                'serviceReference' => '11821804',
                'productNumber' => '51648',
                'price' => '105.00',
                'currency' => 'CHF',
                'vatAmount' => '105.00',
                'vatPercent' => '7.70',
            ],
            $service
        );

        static::assertEmpty($actual->messages);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testConfirmReceipt()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/ConfirmReceiptResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaConfirmReceiptsParameter();
        $this->setTestIdentifier($parameter);
        $parameter->novaServiceId = '15900011821804';

        $actual = $client->confirmReceipt($parameter);

        static::assertCount(1, $actual->getServices());
        static::assertSame('PRODUKTION_ERFOLGREICH', $actual->getServices()[0]->serviceStatus);

        $service = (array)$actual->getServices()[0];

        static::assertSame(
            [
                'tkId' => '949e2e6a-fdd1-4f07-8784-201e588ae834',
                'serviceId' => '15900011821804',
                'serviceStatus' => 'PRODUKTION_ERFOLGREICH',
                'serviceReference' => '11821804',
                'productNumber' => '51648',
                'price' => '105.00',
                'currency' => 'CHF',
                'vatAmount' => '105.00',
                'vatPercent' => '7.70',
            ],
            $service
        );

        static::assertEmpty($actual->messages);
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testSearchServicesByTkId()
    {
        // Create a mocked response queue
        $response = new Response();
        $response->getBody()->write(
            (string)file_get_contents(__DIR__ . '/../../Ressources/Response/SearchServicesResponse.xml')
        );

        $client = $this->createNovaApiClient([$response]);

        $parameter = new NovaSearchServicesParameter();
        $this->setTestIdentifier($parameter);
        $parameter->tkId = '949e2e6a-fdd1-4f07-8784-201e588ae834';
        $parameter->periodOfUseStart = Chronos::now()->subDays(1);
        $parameter->periodOfUseEnd = Chronos::now()->addDay();

        $actual = $client->searchServices($parameter);

        $this->assertCount(19, $actual->services);
        $this->assertEquals('949e2e6a-fdd1-4f07-8784-201e588ae834', $actual->services[0]->tkId);
        $this->assertEquals('2019-09-01 00:00:00', $actual->services[0]->validFrom->toDateTimeString());
        $this->assertEquals('2019-10-01 05:00:00', $actual->services[0]->validTo->toDateTimeString());
        $this->assertEquals('51648', $actual->services[0]->productNumber);
        $this->assertEquals(['100', '123'], $actual->services[0]->zones);
        $this->assertEquals([], $actual->services[1]->zones);
    }
}
