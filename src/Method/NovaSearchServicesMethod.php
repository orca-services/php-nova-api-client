<?php

namespace OrcaServices\NovaApi\Method;

use DOMDocument;
use DOMElement;
use Exception;
use OrcaServices\NovaApi\Parameter\NovaSearchServicesParameter;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use OrcaServices\NovaApi\Parser\NovaMessageParser;
use OrcaServices\NovaApi\Result\NovaSearchServicesResult;
use OrcaServices\NovaApi\Result\NovaService;
use OrcaServices\NovaApi\Soap\NovaApiSoapAction;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * SOAP method.
 */
final class NovaSearchServicesMethod implements NovaMethod
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
     * Search NOVA services (customer) for a customer.
     *
     * https://confluence-ext.sbb.ch/display/NOVAUG/sucheLeistungen
     *
     * @param NovaSearchServicesParameter $parameter The parameters
     *
     * @return NovaSearchServicesResult the services matching the search parameter
     * @throws Exception if an error occurs
     *
     */
    public function searchServices(NovaSearchServicesParameter $parameter): NovaSearchServicesResult
    {
        // The SOAP endpoint url
        $url = $this->novaSoapAction->getNovaSalesServiceUrl();

        // The SOAP action (http header)
        $soapAction = $this->novaSoapAction->getSoapAction('geschaeftspartner', 'sucheLeistungen');

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
     * @param NovaSearchServicesParameter $parameter The parameters
     *
     * @return string The xml content
     */
    private function createRequestBody(NovaSearchServicesParameter $parameter): string
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

        $method = $dom->createElement('sucheLeistungen');
        $body->appendChild($method);

        $searchQuery = $dom->createElement('leistungsSuchRequest');
        $this->novaSoapAction->appendDomClientIdentifier($dom, $searchQuery, $parameter, '');
        $this->novaSoapAction->appendDomCorrelationContext($dom, $searchQuery, $parameter, '');

        $service = $dom->createElement('leistung');
        $tkid = $dom->createElement('tkid');
        $tkid->appendChild($dom->createTextNode($parameter->tkId));
        $service->appendChild($tkid);
        $searchQuery->appendChild($service);
        $method->appendChild($searchQuery);

        $this->novaSoapAction->appendMethodNamespaces($method);

        return (string)$dom->saveXML();
    }

    /**
     * Create result object.
     *
     * @param XmlDocument $xml The xml document
     *
     * @return NovaSearchServicesResult The mapped result
     */
    private function createResult(XmlDocument $xml): NovaSearchServicesResult
    {
        $result = new NovaSearchServicesResult();

        $xml = $xml->withoutNamespaces();

        // Find and append all messages
        foreach ($this->novaMessageParser->findNovaMessages($xml) as $message) {
            $result->addMessage($message);
        }

        // Root node
        $responseNode = $xml->queryFirstNode('/Envelope/Body/sucheLeistungenResponse/leistungsSuchResponse');
        $serviceNodes = $xml->queryNodes('leistungsSuchErgebnis/leistung', $responseNode);

        /** @var DOMElement $serviceNode */
        foreach ($serviceNodes as $serviceNode) {
            $result->addService($this->createService($serviceNode, $xml));
        }

        return $result;
    }

    /**
     * Map partner node to NovaPartner object.
     *
     * @param DOMElement $serviceNode The partnerNode
     * @param XmlDocument $xml The xml document
     *
     * @return NovaService The new NovaService instance
     */
    private function createService(DOMElement $serviceNode, XmlDocument $xml): NovaService
    {
        $service = new NovaService();

        $service->tkId = $xml->findNodeValue('verkaufsParameter/wert/tkid', $serviceNode);
        $service->validFrom = $xml->createChronosFromXsDateTime(
            $xml->findAttributeValue('nutzungsInfo/nutzungsZeitraum/tarifierbarerZeitraum/@von', $serviceNode)
        );
        $service->validTo = $xml->createChronosFromXsDateTime(
            $xml->findAttributeValue('nutzungsInfo/nutzungsZeitraum/tarifierbarerZeitraum/@bis', $serviceNode)
        );

        $service->productNumber = $xml->findAttributeValue('@produktNummer', $serviceNode);

        $allZones = $xml->findAttributeValue('geltungsBereich/zonenGeltungsBereich/zonenBuendel/@alleZonen', $serviceNode) === 'true';

        if ($allZones) {
            $service->zones[] = 'all';

            return $service;
        }

        foreach ($xml->queryNodes('geltungsBereich/zonenGeltungsBereich/zonenBuendel/zonen', $serviceNode) as $zone) {
            $service->zones[] = $xml->findNodeValue('code', $zone);
        }

        return $service;
    }
}
