<?php

namespace OrcaServices\NovaApi\Method;

use DomainException;
use DOMDocument;
use DOMElement;
use Exception;
use InvalidArgumentException;
use OrcaServices\NovaApi\Parameter\NovaSavCreateOffersParameter;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use OrcaServices\NovaApi\Parser\NovaMessageParser;
use OrcaServices\NovaApi\Result\NovaSavCreateOffersResult;
use OrcaServices\NovaApi\Result\NovaSavOffer;
use OrcaServices\NovaApi\Soap\NovaApiSoapAction;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * SOAP method.
 */
final class NovaSavCreateOffersMethod implements NovaMethod
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
     * Create SAV offers.
     *
     * https://confluence.sbb.ch/display/NOVAUG/erstelleSAVAngebote
     *
     * @param NovaSavCreateOffersParameter $parameter The parameter
     *
     * @throws Exception If an error occurs
     *
     * @return NovaSavCreateOffersResult List of SAV offers
     */
    public function createSavOffers(NovaSavCreateOffersParameter $parameter): NovaSavCreateOffersResult
    {
        // The SOAP endpoint url
        $url = $this->novaSoapAction->getNovaSalesServiceUrl();

        // The SOAP action (http header)
        $soapAction = $this->novaSoapAction->getSoapAction('vertrieb', 'erstelleSAVAngebote');

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
     * @param NovaSavCreateOffersParameter $parameter The parameters
     *
     * @throws InvalidArgumentException
     *
     * @return string The xml content
     */
    private function createRequestBody(NovaSavCreateOffersParameter $parameter): string
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

        $method = $dom->createElement('ns21:erstelleSAVAngebote');
        $body->appendChild($method);
        $this->appendMethodNamespaces($method);

        $methodRequest = $dom->createElement('ns21:savRequest');
        $method->appendChild($methodRequest);

        $methodRequest->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $methodRequest->setAttribute('xsi:type', 'ns21:ErstattungsAngebotsRequest');
        $methodRequest->setAttribute('ns21:fachlogLevel', 'OFF');

        $this->novaSoapAction->appendDomClientIdentifier($dom, $methodRequest, $parameter, 'ns21:');
        $this->novaSoapAction->appendDomCorrelationContext($dom, $methodRequest, $parameter, 'ns21:');

        $refundService = $dom->createElement('ns21:zuErstattendeLeistung');
        $methodRequest->appendChild($refundService);

        $refundService->setAttribute('ns21:leistungsId', $parameter->serviceId);
        // $refundService->setAttribute('ns21:erstattungsGrund', $parameter->reason);

        return (string)$dom->saveXML();
    }

    /**
     * Create result object.
     *
     * @param XmlDocument $xml The xml document
     *
     * @throws DomainException
     *
     * @return NovaSavCreateOffersResult The mapped result
     */
    private function createResult(XmlDocument $xml): NovaSavCreateOffersResult
    {
        $result = new NovaSavCreateOffersResult();

        $xml = $xml->withoutNamespaces();

        // Find and append all messages
        foreach ($this->novaMessageParser->findNovaMessages($xml) as $message) {
            $result->addMessage($message);
        }

        // Root node
        $responseNode = $xml->queryFirstNode('/Envelope/Body/erstelleSAVAngeboteResponse');
        $savOfferNodes = $xml->queryNodes('angebotsResponse/angebote/angebot', $responseNode);

        /** @var DOMElement $savOfferNode */
        foreach ($savOfferNodes as $savOfferNode) {
            $offer = new NovaSavOffer();

            $offerId = $savOfferNode->getAttribute('angebotsId');

            if (empty($offerId)) {
                throw new DomainException('SBB-NOVA SAV offer ID not found');
            }

            $offer->novaOfferId = $offerId;
            $offer->tkId = $xml->findNodeValue('//tkid', $savOfferNode);

            $result->offers[] = $offer;
        }

        return $result;
    }

    /**
     * Append default SOAP method namespaces.
     *
     * @param DOMElement $method The method node
     *
     * @return void
     */
    private function appendMethodNamespaces(DOMElement $method)
    {
        $method->setAttribute('xmlns:ns22', 'http://nova.voev.ch/services/internal/leistungnotification');
        $method->setAttribute('xmlns:ns21', $this->novaSoapAction->getServiceNamespace('vertrieb'));
        $method->setAttribute('xmlns:nova-leistungnotiz', $this->novaSoapAction->getServiceNamespace('leistungnotiz'));
        $method->setAttribute('xmlns:ns19', $this->novaSoapAction->getServiceNamespace('vertrag'));
        $method->setAttribute('xmlns:vertriebsbase', $this->novaSoapAction->getServiceNamespace('vertriebsbase'));
        $method->setAttribute('xmlns:ns17', $this->novaSoapAction->getServiceNamespace('internal'));
        $method->setAttribute('xmlns:vs', $this->novaSoapAction->getServiceNamespace('vertrieb/vertriebsstammdaten'));
        $method->setAttribute('xmlns:nova-protokoll', $this->novaSoapAction->getServiceNamespace('vertrieb/protokoll'));
        $method->setAttribute(
            'xmlns:nova-erneuerungsinfo',
            $this->novaSoapAction->getServiceNamespace('vertrieb/erneuerungsinfo')
        );
        $method->setAttribute('xmlns:ns13', $this->novaSoapAction->getServiceNamespace('kuko'));
        $method->setAttribute(
            'xmlns:offlinemanagement',
            $this->novaSoapAction->getServiceNamespace('vertrieb/offlinemanagement')
        );
        $method->setAttribute(
            'xmlns:nova-monitoring',
            $this->novaSoapAction->getServiceNamespace('internal/monitoring')
        );
        $method->setAttribute('xmlns:nova-preisauskunft', $this->novaSoapAction->getServiceNamespace('preisauskunft'));
        $method->setAttribute('xmlns:base', $this->novaSoapAction->getServiceNamespace('base'));
        $method->setAttribute('xmlns:ns8', $this->novaSoapAction->getServiceNamespace('fachlichervertrag'));
        $method->setAttribute('xmlns:inkasso', $this->novaSoapAction->getServiceNamespace('inkasso'));
        $method->setAttribute('xmlns:nova-vertragskonto', $this->novaSoapAction->getServiceNamespace('vertragskonto'));
        $method->setAttribute('xmlns:novasp-swisspass', $this->novaSoapAction->getServiceNamespace('swisspass'));
        $method->setAttribute('xmlns:novakuko', $this->novaSoapAction->getServiceNamespace('kundeninteraktion'));
        $method->setAttribute('xmlns:novagp', $this->novaSoapAction->getServiceNamespace('geschaeftspartner'));
        $method->setAttribute('xmlns', $this->novaSoapAction->getServiceNamespace('vertrieb'));
        $method->setAttribute('xmlns:novavt-vertrag', $this->novaSoapAction->getServiceNamespace('vertragbase'));
    }
}
