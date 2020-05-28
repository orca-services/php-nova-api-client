<?php

namespace OrcaServices\NovaApi\Soap;

use DOMDocument;
use DOMElement;

class NovaParameterWriter
{
    /**
     * Dom document.
     *
     * @var DOMDocument
     */
    private $document;

    /**
     * Parent element.
     *
     * @var DOMElement
     */
    private $parent;

    /**
     * Parameter prefix.
     *
     * @var string
     */
    private $parameterPrefix;

    /**
     * NovaParameterWriter constructor.
     *
     * @param DOMDocument $document DOM Document
     * @param DOMElement $parent Parent element
     * @param string $namespace Parameter namespace
     */
    public function __construct(DOMDocument $document, DOMElement $parent, string $namespace = 'novagp:')
    {
        $this->document = $document;
        $this->parent = $parent;
        $this->parameterPrefix = $namespace;
    }

    /**
     * Appends the given parameters to the document inside the parent element.
     *
     * @param NovaParameterMap $parameterMap Parameter map
     *
     * @return void
     */
    public function appendToDocument(NovaParameterMap $parameterMap)
    {
        foreach ($parameterMap->map as $parameterName => $value) {
            if (!$value) {
                continue;
            }

            $element = $this->document->createElement("{$this->parameterPrefix}{$parameterName}");
            $this->parent->appendChild($element);
            $element->appendChild($this->document->createTextNode($value));
        }
    }
}
