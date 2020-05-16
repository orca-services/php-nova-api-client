<?php

namespace OrcaServices\NovaApi\Method;

use DomainException;
use DOMDocument;
use DOMElement;
use Exception;
use OrcaServices\NovaApi\Parameter\NovaCreateServicesParameter;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use OrcaServices\NovaApi\Parser\NovaMessageParser;
use OrcaServices\NovaApi\Result\NovaCreateServicesResult;
use OrcaServices\NovaApi\Result\NovaServiceItem;
use OrcaServices\NovaApi\Soap\NovaApiSoapAction;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * SOAP method.
 */
final class NovaCreateServicesMethod implements NovaMethod
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
     * 2. Klang.
     *
     * The next step is to accept the requested OFFER.
     * This subroutine checks the OFFER and whether the requested PERFORMANCE (e.g. for dynamic quotas)
     * is actually available. This turns the OFFER into a real PERFORMANCE, the sale of which is guaranteed by NOVA.
     * If desired, NOVA OFFER needs further information (e.g. about the customer) for processing.
     *
     * Service: https://confluence-ext.sbb.ch/display/NOVAUG/offeriereLeistungen
     *
     * @param NovaCreateServicesParameter $parameter The parameters
     *
     * @throws Exception if an error occurs
     *
     * @return NovaCreateServicesResult the result data
     */
    public function createService(NovaCreateServicesParameter $parameter): NovaCreateServicesResult
    {
        // The SOAP endpoint url
        $url = $this->novaSoapAction->getNovaSalesServiceUrl();

        // The SOAP action (http header)
        $soapAction = $this->novaSoapAction->getSoapAction('vertrieb', 'offeriereLeistungen');

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
     * @param NovaCreateServicesParameter $parameter The parameters
     *
     * @return string The xml content
     */
    private function createRequestBody(NovaCreateServicesParameter $parameter): string
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

        $method = $dom->createElement('offeriereLeistungen');
        $body->appendChild($method);

        $this->novaSoapAction->appendMethodNamespaces($method);

        $methodRequest = $dom->createElement('ns18:offertenRequest');
        $method->appendChild($methodRequest);

        $methodRequest->setAttribute('ns18:transaktionsVerhalten', 'ROLLBACK_ON_ERROR');
        $methodRequest->setAttribute('ns18:fachlogLevel', 'OFF');

        $this->novaSoapAction->appendDomClientIdentifier($dom, $methodRequest, $parameter, 'ns18:');
        $this->novaSoapAction->appendDomCorrelationContext($dom, $methodRequest, $parameter, 'ns18:');

        $serviceRequest = $dom->createElement('ns18:leistungsRequest');
        $methodRequest->appendChild($serviceRequest);

        $serviceRequest->setAttribute('ns18:angebotsId', $parameter->novaOfferId);
        $serviceRequest->setAttribute('ns18:externeLeistungsReferenzId', '');
        $serviceRequest->setAttribute('ns18:externeReisendenReferenzId', '');

        $travellerParameter = $dom->createElement('ns18:verkaufsParameter');
        $serviceRequest->appendChild($travellerParameter);

        $travellerParameter->setAttribute('ns18:code', 'REISENDER');

        $travellerValue = $dom->createElement('ns18:wert');
        $travellerParameter->appendChild($travellerValue);

        $distributionBaseTkid = $dom->createElement('vertriebsbase:tkid', $parameter->tkId);
        $travellerValue->appendChild($distributionBaseTkid);

        return (string)$dom->saveXML();
    }

    /**
     * Create result object.
     *
     * @param XmlDocument $xml The xml document
     *
     * @throws DomainException
     *
     * @return NovaCreateServicesResult The mapped result
     */
    private function createResult(XmlDocument $xml): NovaCreateServicesResult
    {
        $result = new NovaCreateServicesResult();

        $xml = $xml->withoutNamespaces();

        // Find and append all messages
        foreach ($this->novaMessageParser->findNovaMessages($xml) as $message) {
            $result->addMessage($message);
        }

        // Root node
        $serviceResponseNode = $xml->queryFirstNode('/Envelope/Body/offeriereLeistungenResponse');
        $serviceNodes = $xml->queryNodes('offertenResponse/leistung', $serviceResponseNode);

        /** @var DOMElement $serviceNode */
        foreach ($serviceNodes as $serviceNode) {
            $serviceItem = new NovaServiceItem();

            $serviceId = $serviceNode->getAttribute('leistungsId');

            if (empty($serviceId)) {
                throw new DomainException('SBB-NOVA service ID not found');
            }

            $serviceItem->serviceId = $serviceId;
            $serviceItem->serviceReference = $serviceNode->getAttribute('leistungsReferenz');
            $serviceItem->serviceStatus = $serviceNode->getAttribute('leistungsStatus');
            $serviceItem->productNumber = $serviceNode->getAttribute('produktNummer');
            $serviceItem->tkId = $xml->getNodeValue('//tkid', $serviceNode);
            $serviceItem->price = $xml->getAttributeValue('verkaufsPreis/geldBetrag/@betrag', $serviceNode);
            $serviceItem->currency = $xml->getAttributeValue('verkaufsPreis/geldBetrag/@waehrung', $serviceNode);
            $serviceItem->vatAmount = $xml->getAttributeValue('verkaufsPreis/mwstAnteil/@betrag', $serviceNode);
            $serviceItem->vatPercent = $xml->getAttributeValue('verkaufsPreis/mwstAnteil/@mwstSatz', $serviceNode);

            $result->addService($serviceItem);
        }

        return $result;
    }
}
