<?php

namespace OrcaServices\NovaApi\Method;

use Cake\Chronos\Chronos;
use DOMDocument;
use DOMElement;
use Exception;
use OrcaServices\NovaApi\Parameter\NovaSearchPartnerParameter;
use OrcaServices\NovaApi\Parser\NovaApiErrorParser;
use OrcaServices\NovaApi\Parser\NovaMessageParser;
use OrcaServices\NovaApi\Result\NovaPartner;
use OrcaServices\NovaApi\Result\NovaSearchPartnerResult;
use OrcaServices\NovaApi\Soap\NovaApiSoapAction;
use OrcaServices\NovaApi\Soap\NovaParameterMap;
use OrcaServices\NovaApi\Soap\NovaParameterWriter;
use OrcaServices\NovaApi\Type\GenderType;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * SOAP method.
 */
final class NovaSearchPartnerMethod implements NovaMethod
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
     * Search a NOVA partner (customer).
     *
     * https://confluence-ext.sbb.ch/display/NOVAUG/suchePartner
     *
     * @param NovaSearchPartnerParameter $parameter The parameters
     *
     * @return NovaSearchPartnerResult the partners matching the search parameter
     * @throws Exception if an error occurs
     *
     */
    public function searchPartner(NovaSearchPartnerParameter $parameter): NovaSearchPartnerResult
    {
        // The SOAP endpoint url
        // https://echo-api.3scale.net/novagp/geschaeftspartner/public/MAJOR.MINOR/GeschaeftspartnerService
        $url = $this->novaSoapAction->getNovaBusinessPartnerServiceUrl();

        // The SOAP action (http header)
        $soapAction = $this->novaSoapAction->getSoapAction('geschaeftspartner', 'suchePartner');

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
     * @param NovaSearchPartnerParameter $parameter The parameters
     *
     * @return string The xml content
     */
    private function createRequestBody(NovaSearchPartnerParameter $parameter): string
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

        $method = $dom->createElement('novagp:suchePartner');
        $body->appendChild($method);

        $this->novaSoapAction->appendMethodNamespaces($method);
        $this->novaSoapAction->appendDomClientIdentifier($dom, $method, $parameter, 'novagp:');
        $this->novaSoapAction->appendDomCorrelationContext($dom, $method, $parameter, 'novagp:');

        $partnerSearchParameter = $dom->createElement('novagp:partnerSuchParameter');
        $method->appendChild($partnerSearchParameter);

        $parameterWriter = new NovaParameterWriter($dom, $partnerSearchParameter);

        $parameterWriter->appendToDocument(new NovaParameterMap([
            'tkid' => $parameter->tkId,
            'grundkartenNummer' => $parameter->cardNumber,
            'ckm' => $parameter->ckm,
            'name' => $parameter->lastName,
            'vorname' => $parameter->firstName,
            'mail' => $parameter->mail,
            'land' => $parameter->country,
            'ort' => $parameter->city,
            'plz' => $parameter->postalCode,
            'strasseHnr' => $parameter->street,
            'geburtsDatum' => $parameter->dateOfBirth ? $parameter->dateOfBirth->format('Y-m-d') : null,
        ]));

        $pagingElement = $dom->createElement('novagp:pagingParameter');
        $partnerSearchParameter->appendChild($pagingElement);

        return (string)$dom->saveXML();
    }

    /**
     * Create result object.
     *
     * @param XmlDocument $xml The xml document
     *
     * @return NovaSearchPartnerResult The mapped result
     */
    private function createResult(XmlDocument $xml): NovaSearchPartnerResult
    {
        $result = new NovaSearchPartnerResult();

        $xml = $xml->withoutNamespaces();

        // Find and append all messages
        foreach ($this->novaMessageParser->findNovaMessages($xml) as $message) {
            $result->addMessage($message);
        }

        // Root node
        $responseNode = $xml->queryFirstNode('/Envelope/Body/suchePartnerResponse');
        $partnerNodes = $xml->queryNodes('partner', $responseNode);

        /** @var DOMElement $partnerNode */
        foreach ($partnerNodes as $partnerNode) {
            $result->addPartner($this->createPartner($partnerNode, $xml));
        }

        return $result;
    }

    /**
     * Map partner node to NovaPartner object.
     *
     * @param DOMElement $partnerNode The partnerNode
     * @param XmlDocument $xml The xml document
     *
     * @return NovaPartner The new NovaPartner instance
     */
    private function createPartner(DOMElement $partnerNode, XmlDocument $xml): NovaPartner
    {
        $partner = new NovaPartner();

        $partner->tkId = $xml->getAttributeValue('@tkid', $partnerNode);
        $partner->ckm = $xml->findAttributeValue('@ckm', $partnerNode);
        $partner->cardNumber = $xml->findAttributeValue('@grundkartenNummer', $partnerNode);

        $changeDate = $xml->findAttributeValue('@mutDatum', $partnerNode);
        if ($changeDate !== null) {
            $partner->changedAt = $xml->createChronosFromXsDateTime($changeDate);
        }

        $partner->lastName = $xml->findAttributeValue('name/@name', $partnerNode);
        $partner->firstName = $xml->findAttributeValue('name/@vorname', $partnerNode);
        $partner->title = $xml->findAttributeValue('@titel', $partnerNode);

        $deceased = $xml->findAttributeValue('@verstorben', $partnerNode);
        if ($deceased !== null) {
            $partner->deceased = $deceased === 'false' ? 0 : 1;
        }

        // Date of birth can be empty sometimes
        $dateOfBirth = $xml->findAttributeValue('@geburtsDatum', $partnerNode);
        if ($dateOfBirth) {
            $dateOfBirth = Chronos::createFromFormat('Y-m-d', $dateOfBirth)->setTime(0, 0);
            $partner->dateOfBirth = $dateOfBirth;
        }

        $gender = $xml->findAttributeValue('@geschlecht', $partnerNode);
        $genderTypeId = $gender === 'MAENNLICH' ? GenderType::MEN : GenderType::WOMEN;
        $partner->genderTypeId = $genderTypeId;

        // Address
        $addressNodes = $xml->queryNodes('sitz/adresse', $partnerNode);

        // Could be empty, one, or more then one address: postal addresse, communication address etc.
        if ($addressNodes->length >= 1) {
            $addressNode = $xml->getFirstNode($addressNodes);

            $country = $xml->findAttributeValue('@land', $addressNode);
            $partner->country = $country;

            $city = $xml->findAttributeValue('@ort', $addressNode);
            $partner->city = $city;

            $postalCode = $xml->findAttributeValue('@plz', $addressNode);
            $partner->postalCode = $postalCode;

            $additional = $xml->findAttributeValue('@adressZusatz', $addressNode);
            if ($additional !== null) {
                $partner->additional = $additional;
            }

            $street = $xml->findAttributeValue('@strasseHnr', $addressNode);
            $partner->street = $street;

            $poBox = $xml->findAttributeValue('@postfach', $addressNode);
            if ($poBox !== null) {
                $partner->poBox = $poBox;
            }
        }

        // MOBIL, FESTNETZ or MAIL
        $phoneNumber = $xml->findAttributeValue('festnetz/@formatiertE123', $partnerNode);
        if ($phoneNumber !== null) {
            $partner->phoneNumber = str_replace(' ', '', $phoneNumber);
        }

        $mobileNumber = $xml->findAttributeValue('mobil/@formatiertE123', $partnerNode);
        if ($mobileNumber !== null) {
            $partner->mobileNumber = str_replace(' ', '', $mobileNumber);
        }

        $email = $xml->findAttributeValue('@email', $partnerNode);
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            $partner->email = $email;
        }

        return $partner;
    }
}
