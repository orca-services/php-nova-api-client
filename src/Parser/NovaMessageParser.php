<?php

namespace OrcaServices\NovaApi\Parser;

use DOMNode;
use OrcaServices\NovaApi\Result\NovaMessage;
use OrcaServices\NovaApi\Xml\XmlDocument;

/**
 * Class.
 */
final class NovaMessageParser
{
    /**
     * Get spring validation errors.
     *
     * @param XmlDocument $xml The xml document
     *
     * @return NovaMessage[] The nova validation error messages and codes
     */
    public function findNovaMessages(XmlDocument $xml): array
    {
        $messages = [];

        // Possible namespace combinations where to find messages
        $namespaces = [
            ['', ''],
            ['ns13', 'base'],
            ['ns1', 'ns2'],
            ['xmlns', 'base'],
            ['novasp-swisspass', 'base'],
        ];

        foreach ($namespaces as $namespace) {
            $messages = $this->appendNovaMessages($xml, $namespace, $messages);
        }

        return $messages;
    }

    /**
     * Add nova messages to array.
     *
     * @param XmlDocument $xml The xml document
     * @param string[] $namespace The namespace
     * @param array $messages The messages
     *
     * @return array The new messages
     */
    private function appendNovaMessages(XmlDocument $xml, array $namespace, array $messages): array
    {
        $ns1 = $namespace[0];
        $ns2 = $namespace[1];

        if ($ns1 && !$xml->existsNamespace($ns1)) {
            return $messages;
        }

        $ns1 = $ns1 ? $ns1 . ':' : $ns1;
        $ns2 = $ns2 ? $ns2 . ':' : $ns2;

        // Check for action response errors
        $messageNodes = $xml->queryNodes(sprintf('//%smeldungen/%smeldung', $ns1, $ns2));

        if (empty($messageNodes) || $messageNodes->length === 0) {
            return $messages;
        }

        /** @var DOMNode $messageNode */
        foreach ($messageNodes as $messageNode) {
            $message = new NovaMessage();

            $value = $xml->findAttributeValue(sprintf('@%smeldungsCode', $ns2), $messageNode);
            if ($value) {
                $message->code = $value;
            }

            $value = $xml->findAttributeValue(
                sprintf('//%sbeschreibung/@%sdefaultWert', $ns2, $ns2),
                $messageNode
            );
            if ($value) {
                $message->message = $value;
            }

            $value = $xml->findAttributeValue(sprintf('@%sid', $ns2), $messageNode);
            if ($value) {
                $message->id = $value;
            }

            $value = $xml->findAttributeValue(sprintf('@%styp', $ns2), $messageNode);
            if ($value) {
                $message->type = $value;
            }

            $value = $xml->findAttributeValue(sprintf('@%szeitStempel', $ns2), $messageNode);
            if ($value) {
                $message->timestamp = $value;
            }

            $value = $xml->findAttributeValue(sprintf('@%sendKundenRelevant', $ns2), $messageNode);
            if ($value) {
                $message->customerRelevant = $value;
            }

            $messages[] = $message;
        }

        return $messages;
    }
}
