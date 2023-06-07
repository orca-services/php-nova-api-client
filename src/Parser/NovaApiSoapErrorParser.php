<?php

namespace OrcaServices\NovaApi\Parser;

use DOMNode;
use OrcaServices\NovaApi\Exception\InvalidXmlException;
use OrcaServices\NovaApi\Result\NovaApiErrorList;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * Error parser.
 */
final class NovaApiSoapErrorParser
{
    /**
     * Get SOAP errors.
     *
     * @param XmlDocument $xml The xml document
     * @param NovaApiErrorList $errors The errors
     *
     * @throws InvalidXmlException
     *
     * @return NovaApiErrorList The error messages
     */
    public function getSoapErrors(XmlDocument $xml, NovaApiErrorList $errors): NovaApiErrorList
    {
        $xml = $xml->withoutNamespaces();
        $xmlContent = $xml->getXml();

        // Spring validation errors
        if (strpos($xmlContent, 'ValidationError')) {
            return $this->getSoapValidationErrors($xml);
        }

        // Root node
        $faultNodeList = $xml->queryNodes('/Envelope/Body/Fault');

        $faultNode = $faultNodeList->item(0);
        if ($faultNode === null) {
            throw new InvalidXmlException('SOAP fault node not found');
        }

        $faultCode = $xml->getNodeValue('faultcode', $faultNode);
        $faultString = $xml->getNodeValue('faultstring', $faultNode);

        $errors = $errors->withError($faultCode, $faultString);

        $errorInfoNode = $xml->queryFirstNode('detail/errorInfo', $faultNode);
        $errorCode = $xml->getNodeValue('error-code', $errorInfoNode);
        $errorHeaders = $xml->getNodeValue('error-headers', $errorInfoNode);
        $errorMessage = $xml->getNodeValue('error-message', $errorInfoNode);
        $errorProtocolReasonPhrase = $xml->getNodeValue('error-protocol-reason-phrase', $errorInfoNode);
        $errorProtocolResponse = $xml->getNodeValue('error-protocol-response', $errorInfoNode);
        $errorSubCode = $xml->getNodeValue('error-subcode', $errorInfoNode);
        $inputExtError = $xml->getNodeValue('input-ext-error', $errorInfoNode);
        $errorXProtocolResponse = $xml->getNodeValue('error-x-protocol-response', $errorInfoNode);
        $responseContent = $xml->getNodeValue('response-content', $errorInfoNode);

        $responseContentXml = null;
        $errorDetailMessage = null;

        // Parse xml in xml
        if (strpos($responseContent, '<?xml') === 0) {
            $xmlError = XmlDocument::createFromXmlString($responseContent);
            $responseContentXml = $xmlError->getXml();

            $faultNode = $xmlError->queryNodes('/Envelope/Body/Fault');

            if ($faultNode->length === 1) {
                $faultCode = $xml->getNodeValue('faultcode', $faultNode->item(0));
                $faultString = $xml->getNodeValue('faultstring', $faultNode->item(0));
                $faultDetail = $xml->getNodeValue('detail', $faultNode->item(0));
                $errorDetailMessage = sprintf('%s %s %s', $faultCode, $faultString, $faultDetail);
            }
        }

        $details = [
            'error_headers' => $errorHeaders,
            'error_message' => $errorMessage,
            'error_protocol_reason_phrase' => $errorProtocolReasonPhrase,
            'error_protocol_response' => $errorProtocolResponse,
            'error_subcode' => $errorSubCode,
            'input_ext_error' => $inputExtError,
            'error_x_protocol_response' => $errorXProtocolResponse,
            'response_content' => $responseContent,
            'response_content_xml' => $responseContentXml,
        ];

        return $errors->withError($errorCode, $errorDetailMessage ?: $errorMessage, $details);
    }

    /**
     * Get spring validation errors.
     *
     * @param XmlDocument $xml The xml document
     *
     * @return NovaApiErrorList the validation error messages and codes
     */
    private function getSoapValidationErrors(XmlDocument $xml): NovaApiErrorList
    {
        $errors = new NovaApiErrorList();

        $faultNodeList = $xml->queryNodes('//Fault');

        if ($faultNodeList->length === 0) {
            return $errors;
        }

        $faultNode = $faultNodeList->item(0);
        $faultCode = $xml->getNodeValue('faultcode', $faultNode);
        $faultString = $xml->getNodeValue('faultstring', $faultNode);

        $errors = $errors->withError($faultCode, $faultString);

        $errorList = $xml->queryNodes('//ValidationError');

        if ($errorList->length === 0) {
            return $errors;
        }

        /** @var DOMNode $errorDetail */
        foreach ($errorList as $errorDetail) {
            $errors = $errors->withError($errorDetail->localName ?? '', $errorDetail->nodeValue ?? '');
        }

        return $errors;
    }
}
