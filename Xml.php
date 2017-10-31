<?php namespace Mreschke\Helpers;

use DomDocument;
use SimpleXMLElement;

/**
 * Xml helpers
 * @copyright 2017 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class Xml
{
    /**
     * Return pretty printed (structured) string of this SimpleXMLElement
     * @param  SimpleXMLElement $xml
     * @return string
     */
    public function prettyPrint($xml)
    {
        // Load string into DomDocument to apply formatting
        $doc = new DomDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xml->saveXML());

        // Return formatted XML as string
        return $doc->saveXML();
    }

    /**
     * Append one SimpleXMLElement to another
     * @param  SimpleXMLElement &$simpleXmlMain this is the main element
     * @param  SimpleXMLElement &$simpleXmlAppend append this to main
     * @return void (no return, all ByRef)
     */
    public function appendElement(&$simpleXmlMain, &$simpleXmlAppend)
    {
        // Ignore if any are empty
        if (!isset($simpleXmlMain) || !isset($simpleXmlAppend)) return;
        foreach ($simpleXmlAppend->children() as $child) {
            $temp = $simpleXmlMain->addChild($child->getName(), (string) htmlentities($child));
            foreach ($child->attributes() as $attr_key => $attr_value) {
                $temp->addAttribute($attr_key, $attr_value);
            }
            // Recursively add each childs children
            $this->appendElement($temp, $child);
        }
    }

    /**
     * Save one SimpleXMLElement to file
     * @param  SimpleXMLElement $xml
     * @param  string $file
     * @param  boolean $prettyPrint = false
     * @return void
     */
    public function save(SimpleXMLElement $xml, $file, $prettyPrint = false)
    {
        if ($prettyPrint) {
            // Pretty print
            file_put_contents($file, $this->prettyPrint($xml));
        } else {
            // No formatting
            file_put_contents($file, $xml->saveXML());
        }
    }
}
