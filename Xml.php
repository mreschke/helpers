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
    public static function prettyPrint($xml)
    {
        // Load xml into DomDocument to pretty print
        // XML comes in as one line (no line endings), for now, I want pretty printed for debuging
        $doc = new DomDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        // If the XML string from $xml->saveXML() contains HTML chars like &amp, &nbsp, &ndash...
        // then loadXML() will crash with "DOMDocument::loadXML(): Entity 'ndash' not defined in Entity"
        // So we convert all HTML & to their actual chars with html_entity_decode
        // but even after that, if there is a stray & it can error with
        // "DOMDocument::loadXML(): xmlParseEntityRef: no name in Entity"
        // We we also replace any & and 'and'
        $xmlString = html_entity_decode($xml->saveXML());
        $xmlString = preg_replace('/\&/', 'and', $xmlString);

        // Load string into DomDocument which converts back to XML but formatted
        $doc->loadXML($xmlString);

        // Convert and return the XML back into a pretty printed string
        return $doc->saveXML();
    }

    /**
     * Append one SimpleXMLElement to another
     * @param  SimpleXMLElement &$simpleXmlMain this is the main element
     * @param  SimpleXMLElement &$simpleXmlAppend append this to main
     * @return void (no return, all ByRef)
     */
    public static function appendElement(&$simpleXmlMain, &$simpleXmlAppend)
    {
        // Ignore if any are empty
        if (!isset($simpleXmlMain) || !isset($simpleXmlAppend)) return;
        foreach ($simpleXmlAppend->children() as $child) {
            $temp = $simpleXmlMain->addChild($child->getName(), (string) htmlentities($child));
            foreach ($child->attributes() as $attr_key => $attr_value) {
                $temp->addAttribute($attr_key, $attr_value);
            }
            // Recursively add each childs children
            Xml::appendElement($temp, $child);
        }
    }

    /**
     * Save one SimpleXMLElement to file
     * @param  SimpleXMLElement $xml
     * @param  string $file
     * @param  boolean $prettyPrint = false
     * @return void
     */
    public static function save(SimpleXMLElement $xml, $file, $prettyPrint = false)
    {
        if ($prettyPrint) {
            file_put_contents($file, Xml::prettyPrint($xml));
        } else {
            file_put_contents($file, $xml->saveXML());
        }
    }
}
