<?php

namespace OrcaServices\NovaApi\Method;

use DomainException;
use DOMDocument;
use DOMElement;
use Exception;
use OrcaServices\NovaApi\Parameter\NovaPurchaseServicesParameter;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use OrcaServices\NovaApi\Parser\NovaMessageParser;
use OrcaServices\NovaApi\Result\NovaPurchaseServicesResult;
use OrcaServices\NovaApi\Result\NovaServiceItem;
use OrcaServices\NovaApi\Soap\NovaApiSoapAction;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * 3 Klang.
 */
final class NovaPurchaseServicesMethod implements NovaMethod
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
     * 3. Klang.
     *
     * The next step is to accept the requested OFFER.
     * This subroutine checks the OFFER and whether the requested PERFORMANCE (e.g. for dynamic quotas)
     * is actually available. This turns the OFFER into a real PERFORMANCE, the sale of which is guaranteed by NOVA.
     * If desired, NOVA OFFER needs further information (e.g. about the customer) for processing.
     *
     * Service: https://confluence-ext.sbb.ch/display/NOVAUG/offeriereLeistungen
     *
     * @param NovaPurchaseServicesParameter $parameter The parameters
     *
     * @throws Exception if an error occurs
     *
     * @return NovaPurchaseServicesResult The result data
     */
    public function purchaseService(NovaPurchaseServicesParameter $parameter): NovaPurchaseServicesResult
    {
        // The SOAP endpoint url
        $url = $this->novaSoapAction->getNovaSalesServiceUrl();

        // The SOAP action (http header)
        $soapAction = $this->novaSoapAction->getSoapAction('vertrieb', 'kaufeLeistungen');

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
     * @param NovaPurchaseServicesParameter $parameter The parameters
     *
     * @return string The xml content
     */
    private function createRequestBody(NovaPurchaseServicesParameter $parameter): string
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

        $method = $dom->createElement('ns18:kaufeLeistungen');
        $body->appendChild($method);

        $this->novaSoapAction->appendMethodNamespaces($method);

        $methodRequest = $dom->createElement('ns18:kaufRequest');
        $method->appendChild($methodRequest);

        $methodRequest->setAttribute('ns18:transaktionsVerhalten', 'ROLLBACK_ON_ERROR');
        $methodRequest->setAttribute('ns18:fachlogLevel', 'OFF');

        $this->novaSoapAction->appendDomClientIdentifier($dom, $methodRequest, $parameter, 'ns18:');
        $this->novaSoapAction->appendDomCorrelationContext($dom, $methodRequest, $parameter, 'ns18:');

        $serviceRequest = $dom->createElement('ns18:leistungsKaufRequest');
        $methodRequest->appendChild($serviceRequest);

        $serviceRequest->setAttribute('ns18:leistungsId', $parameter->novaServiceId);

        $paymentInformation = $dom->createElement('ns18:zahlungsInformation');
        $serviceRequest->appendChild($paymentInformation);

        $paymentInformation->setAttribute('ns18:zahlungsArtCode', $parameter->paymentTypeCode);
        $paymentInformation->setAttribute('ns18:externeZahlungsReferenz', '');

        $amount = $dom->createElement('ns18:geldBetrag');
        $paymentInformation->appendChild($amount);

        $amount->setAttribute('base:betrag', $parameter->price);
        $amount->setAttribute('base:waehrung', $parameter->currency);

        return (string)$dom->saveXML();
    }

    /**
     * Create result object.
     *
     * @param XmlDocument $xml The xml document
     *
     * @throws DomainException
     *
     * @return NovaPurchaseServicesResult The mapped result
     */
    private function createResult(XmlDocument $xml): NovaPurchaseServicesResult
    {
        $result = new NovaPurchaseServicesResult();

        $xml = $xml->withoutNamespaces();

        // Find and append all messages
        foreach ($this->novaMessageParser->findNovaMessages($xml) as $message) {
            $result->addMessage($message);
        }

        // Root node
        $responseNode = $xml->queryFirstNode('/Envelope/Body/kaufeLeistungenResponse');
        $serviceNodes = $xml->queryNodes('kaufResponse/leistung', $responseNode);

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
