<?php namespace Mreschke\Helpers;

/**
 * Other misc helpers.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
Class Other
{

	/**
	 * Check if array is an associative array.
	 * @param array $array
	 * @return boolean
	 */
    public static function isAssoc($array)
    {
        return (is_array($array) && (count($array)==0 || 0 !== count(array_diff_key($array, array_keys(array_keys($array))) )));
    }

}
