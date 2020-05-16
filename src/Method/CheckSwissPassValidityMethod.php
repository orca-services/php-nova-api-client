<?php

namespace OrcaServices\NovaApi\Method;

use DOMDocument;
use Exception;
use OrcaServices\NovaApi\Parameter\NovaCheckSwissPassValidityParameter;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use OrcaServices\NovaApi\Parser\NovaMessageParser;
use OrcaServices\NovaApi\Result\NovaCheckSwissPassValidityResult;
use OrcaServices\NovaApi\Soap\NovaApiSoapAction;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * SOAP method.
 */
final class CheckSwissPassValidityMethod implements NovaMethod
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
     * Check SwissPass validity.
     *
     * https://confluence-ext.sbb.ch/display/NOVAUG/pruefeSwissPassGueltigkeit
     *
     * @param NovaCheckSwissPassValidityParameter $parameter The parameter
     *
     * @throws Exception If an error occurs
     *
     * @return NovaCheckSwissPassValidityResult The result
     */
    public function checkSwissPassValidity(
        NovaCheckSwissPassValidityParameter $parameter
    ): NovaCheckSwissPassValidityResult {
        // The SOAP endpoint url
        $url = $this->novaSoapAction->getNovaSwisspassServiceUrl();

        // The SOAP action (http header)
        $soapAction = $this->novaSoapAction->getSoapAction('swisspass', 'pruefeSwissPassGueltigkeit');

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
     * @param NovaCheckSwissPassValidityParameter $parameter The parameters
     *
     * @return string The xml content
     */
    private function createRequestBody(NovaCheckSwissPassValidityParameter $parameter): string
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

        $method = $dom->createElement('novasp-swisspass:pruefeSwissPassGueltigkeit');
        $body->appendChild($method);

        $this->novaSoapAction->appendMethodNamespaces($method);

        $method->setAttribute('novasp-swisspass:tkid', $parameter->tkId);

        $this->novaSoapAction->appendDomClientIdentifier($dom, $method, $parameter, 'novasp-swisspass:');
        $this->novaSoapAction->appendDomCorrelationContext($dom, $method, $parameter, 'novasp-swisspass:');

        return (string)$dom->saveXML();
    }

    /**
     * Create result object.
     *
     * @param XmlDocument $xml The xml document
     *
     * @return NovaCheckSwissPassValidityResult The result
     */
    private function createResult(XmlDocument $xml): NovaCheckSwissPassValidityResult
    {
        $result = new NovaCheckSwissPassValidityResult();
        $xml = $xml->withoutNamespaces();
        //$content = $xml->getXml();

        // Find and append all messages
        foreach ($this->novaMessageParser->findNovaMessages($xml) as $message) {
            $result->addMessage($message);
        }

        // Root node
        $responseNode = $xml->queryFirstNode('//pruefeSwissPassGueltigkeitResponse');

        $resultValue = $xml->findAttributeValue('//@resultat', $responseNode);
        $result->result = $resultValue ?: '';

        $status = $xml->findAttributeValue('//@status', $responseNode);
        $result->status = $status ?: '';

        return $result;
    }
}
