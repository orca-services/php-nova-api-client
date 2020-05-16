<?php

namespace OrcaServices\NovaApi\Method;

use Cake\Chronos\Chronos;
use DomainException;
use DOMDocument;
use DOMElement;
use Exception;
use InvalidArgumentException;
use OrcaServices\NovaApi\Parameter\NovaCreateOffersParameter;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use OrcaServices\NovaApi\Parser\NovaMessageParser;
use OrcaServices\NovaApi\Result\NovaCreateOffersResult;
use OrcaServices\NovaApi\Result\NovaOffer;
use OrcaServices\NovaApi\Soap\NovaApiSoapAction;
use OrcaServices\NovaApi\Type\GenderType;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * SOAP method.
 */
final class NovaCreateOffersMethod implements NovaMethod
{
    /**
     * @var NovaApiSoapAction
     */
    private $novaSoapAction;

    /**
     * @var NovaApiErrorParser
     */
    private $novaErrorParser;

    /**
     * @var NovaMessageParser
     */
    private $novaMessageParser;

    /**
     * NovaSearchPartnerMethod constructor.
     *
     * @param NovaApiSoapAction $novaSoapAction The novaSoapAction
     * @param NovaApiErrorParser $novaErrorParser The novaErrorParser
     * @param NovaMessageParser $novaMessageParser The message parser
     */
    public function __construct(
        NovaApiSoapAction $novaSoapAction,
        NovaApiErrorParser $novaErrorParser,
        NovaMessageParser $novaMessageParser
    ) {
        $this->novaSoapAction = $novaSoapAction;
        $this->novaErrorParser = $novaErrorParser;
        $this->novaMessageParser = $novaMessageParser;
    }

    /**
     * Create offers.
     *
     * https://confluence-ext.sbb.ch/display/NOVAUG/erstelleAngebote
     *
     * @param NovaCreateOffersParameter $parameter The parameter
     *
     * @throws Exception If an error occurs
     *
     * @return NovaCreateOffersResult List of offers
     */
    public function createOffers(NovaCreateOffersParameter $parameter): NovaCreateOffersResult
    {
        // The SOAP endpoint url
        $url = $this->novaSoapAction->getNovaSalesServiceUrl();

        // The SOAP action (http header)
        $soapAction = $this->novaSoapAction->getSoapAction('vertrieb', 'erstelleAngebote');

        // The SOAP content (http body)
        $body = $this->createRequestBody($parameter);

        try {
            $xmlContent = $this->novaSoapAction->invokeSoapRequest($url, $soapAction, $body);
            $xml = XmlDocument::createFromXmlString($xmlContent);

            return $this->createResult($xml);
        } catch (Exception $exception) {
            throw $this->novaErrorParser->createGeneralException($exception);
        }
    }

    /**
     * Create SOAP body XML content.
     *
     * @param NovaCreateOffersParameter $parameter The parameters
     *
     * @throws InvalidArgumentException
     *
     * @return string The xml content
     */
    private function createRequestBody(NovaCreateOffersParameter $parameter): string
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;

        $dom->appendChild($dom->createComment(' powered by Barakuda '));

        $envelope = $dom->createElement('soapenv:Envelope');
        $dom->appendChild($envelope);
        $envelope->setAttribute('xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');

        $soapHeader = $dom->createElement('soapenv:Header');
        $envelope->appendChild($soapHeader);

        $body = $dom->createElement('soapenv:Body');
        $envelope->appendChild($body);

        $method = $dom->createElement('ns18:erstelleAngebote');
        $body->appendChild($method);

        $this->novaSoapAction->appendMethodNamespaces($method);

        $methodRequest = $dom->createElement('ns18:angebotsRequest');
        $method->appendChild($methodRequest);

        $methodRequest->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $methodRequest->setAttribute('xsi:type', 'ns18:ZonenplanBasierterAngebotsRequest');

        // Ticket valid from
        $methodRequest->setAttribute('ns18:gueltigAbDatum', $parameter->validFrom->format('Y-m-d'));

        $methodRequest->setAttribute('ns18:kundenSegmenteGruppieren', 'false');
        $methodRequest->setAttribute('ns18:fachlogLevel', 'OFF');

        $this->novaSoapAction->appendDomClientIdentifier($dom, $methodRequest, $parameter, 'ns18:');
        $this->novaSoapAction->appendDomCorrelationContext($dom, $methodRequest, $parameter, 'ns18:');

        $methodRequest->appendChild($dom->createElement('ns18:traegerMedium', 'SWISSPASS'));

        $traveller = $dom->createElement('ns18:reisender');
        $methodRequest->appendChild($traveller);

        $traveller->setAttribute('ns18:externeReisendenReferenzId', '1');

        $tkId = $parameter->tkId;
        if (!empty($tkId)) {
            // with tkid
            $withTkid = $dom->createElement('ns18:mitTkid');
            $traveller->appendChild($withTkid);

            $withTkid->appendChild($dom->createElement('ns18:tkid', $parameter->tkId));
            $withTkid->appendChild($dom->createElement('ns18:ermaessigungsKarteCode', 'KEINE_ERMAESSIGUNGSKARTE'));
        } else {
            // without tkid, search with personal information
            $withoutTkid = $dom->createElement('ns18:ohneTkid');
            $traveller->appendChild($withoutTkid);

            $withoutTkid->appendChild($dom->createElement('ns18:reisendenTyp', 'PERSON'));

            $genderTypeId = $parameter->genderTypeId;
            if ($genderTypeId) {
                $genderValue = $genderTypeId === GenderType::MEN ? 'MAENNLICH' : 'WEIBLICH';
                $withoutTkid->appendChild($dom->createElement('ns18:geschlecht', $genderValue));
            }

            $dateOfBirth = $parameter->dateOfBirth;
            if ($dateOfBirth !== null) {
                $dateOfBirthValue = $dateOfBirth->format('Y-m-d');
                $withoutTkid->appendChild($dom->createElement('ns18:geburtsTag', $dateOfBirthValue));
            }

            $withoutTkid->appendChild($dom->createElement('ns18:ermaessigungsKarteCode', 'KEINE_ERMAESSIGUNGSKARTE'));
        }

        $offerFilter = $dom->createElement('ns18:angebotsFilter');
        $methodRequest->appendChild($offerFilter);

        $offerFilter->setAttribute('xsi:type', 'vertriebsbase:ProduktNummerFilter');
        $offerFilter->appendChild(
            $dom->createElement('vertriebsbase:produktNummer', (string)$parameter->novaProductNumber)
        );

        if (empty($parameter->travelClass)) {
            throw new InvalidArgumentException('Travel class is required');
        }

        $travelClassValue = $parameter->travelClass === 1 ? 'KLASSE_1' : 'KLASSE_2';
        $methodRequest->appendChild($dom->createElement('ns18:klasse', $travelClassValue));

        $zoneRequest = $dom->createElement('ns18:zonenRequest');
        $methodRequest->appendChild($zoneRequest);

        $zoneRequest->setAttribute('ns18:tarifOwner', $parameter->tariffOwner);

        $zoneRequest->appendChild($dom->createElement('ns18:alleZonen', 'true'));

        return (string)$dom->saveXML();
    }

    /**
     * Create result object.
     *
     * @param XmlDocument $xml The xml document
     *
     * @throws DomainException
     *
     * @return NovaCreateOffersResult The mapped result
     */
    private function createResult(XmlDocument $xml): NovaCreateOffersResult
    {
        $result = new NovaCreateOffersResult();

        $xml = $xml->withoutNamespaces();

        // Find and append all messages
        foreach ($this->novaMessageParser->findNovaMessages($xml) as $message) {
            $result->addMessage($message);
        }

        // Root node
        $responseNode = $xml->queryFirstNode('/Envelope/Body/erstelleAngeboteResponse');
        $offerNodes = $xml->queryNodes('angebotsResponse/angebote/angebot', $responseNode);

        /** @var DOMElement $offerNode */
        foreach ($offerNodes as $offerNode) {
            $offer = new NovaOffer();

            $offerId = $offerNode->getAttribute('angebotsId');

            if (empty($offerId)) {
                throw new DomainException('SBB-NOVA offer ID not found');
            }

            $offer->novaOfferId = $offerId;

            $titles = [];

            // All Zones
            $titles[] = $xml->getAttributeValue(
                'nutzungsInfo/tarifStufe/tarifStufenText/@defaultWert',
                $offerNode
            );

            // Adults
            $titles[] = $xml->getAttributeValue(
                'produktEinflussFaktoren/kundenSegment/bezeichnung/@defaultWert',
                $offerNode
            );

            // Months
            $titles[] = $xml->getAttributeValue(
                'produktEinflussFaktoren/geltungsDauer/nutzungsGeltungsDauer/einheit/bezeichnung/@defaultWert',
                $offerNode
            );
            $title = trim(implode(', ', array_filter($titles)));

            $offer->title = $title;

            $price = $xml->getAttributeValue('verkaufsPreis/geldBetrag/@betrag', $offerNode);
            $offer->price = $price;

            $currency = $xml->getAttributeValue('verkaufsPreis/geldBetrag/@waehrung', $offerNode);

            $offer->currency = $currency;

            $productNumber = $offerNode->getAttribute('produktNummer');
            $offer->productNumber = $productNumber;

            $validFrom = $xml->getAttributeValue('nutzungsInfo/nutzungsZeitraum/ausweisbarerZeitraum/@von', $offerNode);
            $offer->validFrom = (new Chronos($validFrom))->setTime(0, 0);

            $validTo = $xml->getAttributeValue('nutzungsInfo/nutzungsZeitraum/ausweisbarerZeitraum/@bis', $offerNode);
            $offer->validTo = (new Chronos($validTo))->setTime(23, 59, 59);

            $carrierMedium = $xml->getAttributeValue('produktEinflussFaktoren/@traegerMedium', $offerNode);
            $offer->carrierMedium = $carrierMedium;

            $travelClass = $xml->getAttributeValue('produktEinflussFaktoren/@klasse', $offerNode);
            $offer->travelClass = $travelClass;

            $result->addOffer($offer);
        }

        return $result;
    }
}
