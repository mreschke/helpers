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
     * HTML Entity decode SimpleXMLElement into string
     * @param  $xml SimpleXMLElement
     * @return string
     */
    public static function toString($xml)
    {
        // If the XML string from $xml->saveXML() contains HTML chars like &amp, &nbsp, &ndash...
        // then loadXML() will crash with "DOMDocument::loadXML(): Entity 'ndash' not defined in Entity"
        // So we convert all HTML & to their actual chars with html_entity_decode
        // but even after that, if there is a stray & it can error with
        // "DOMDocument::loadXML(): xmlParseEntityRef: no name in Entity"
        // We we also replace any & and 'and'

        // Convert SimpleXmlElement into string
        $xmlString = $xml->saveXML();

        // BEFORE html_entity_decode or &lt; will translate to > and break XML
        #$xmlString = preg_replace('/\&lt;/', 'lt', $xmlString);
        #$xmlString = preg_replace('/\&gt;/', 'gt', $xmlString);
        #$xmlString = preg_replace('/\&lt/', 'lt', $xmlString);
        #$xmlString = preg_replace('/\&gt/', 'gt', $xmlString);


        // Translate all HTML chars (&nbsp) into actual character
        #$xmlString = html_entity_decode($xmlString);
        #$xmlString = preg_replace('/\&/', 'and', $xmlString);

        return $xmlString;
    }

    /**
     * Return pretty printed (structured) string of this SimpleXMLElement
     * @param  SimpleXMLElement $xml
     * @return string
     */
    public static function prettyPrint($xml)
    {
        // Convert SimpleXMLElement to string while decoding HTML
        $xmlString = Xml::toString($xml);

        // Load string into DomDocument to apply formatting
        $doc = new DomDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($xmlString);

        // Return formatted XML as string
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
            // Pretty print
            file_put_contents($file, Xml::prettyPrint($xml));
        } else {
            // No formatting
            file_put_contents($file, Xml::toString($xml));
        }
    }
}
