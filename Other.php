<?php namespace Mreschke\Helpers;

/**
 * Other misc helpers.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class Other
{

    /**
     * Check if array is an associative array.
     * @param array $arr
     * @return boolean
     */
    public static function isAssoc($arr)
    {
        return (is_array($arr) && array_keys($arr) !== range(0, cnt($arr) - 1));
    }

    /**
     * Collapse a basic subentity into the single level master entity
     * @param  \Illuminate\Support\Collection|array $data
     * @return \Illuminate\Support\Collection|array
     */
    public static function collapse($data)
    {
        foreach ($data as $i => $row) {
            foreach ($row as $key => $value) {
                $found = false;
                if (is_array($value) || is_object($value)) {
                    if (is_array(head($value)) || is_object(head($value))) {
                        // Cannot handle multi level subeneity, only 1-1
                        if (is_array($data[$i])) {
                            $data[$i][$key] = '--Complex--';
                        } elseif (is_object($data[$i])) {
                            $data[$i]->$key = '--Complex--';
                        }
                    } else {
                        // Subeneity is single level
                        $found = true;
                        foreach ($value as $subkey => $subvalue) {
                            if (is_array($subvalue)) {
                                $data[$i][$key] = '--Complex--';
                            } elseif (is_object($subvalue)) {
                                $data[$i]->$key =  '--Complex--';
                            } else {
                                $collapsedKey = "$key.$subkey";
                                if (is_array($value)) {
                                    $data[$i][$collapsedKey] = $subvalue;
                                } elseif (is_object($value)) {
                                    $data[$i]->$collapsedKey = $subvalue;
                                }
                            }
                        }
                    }
                }
                if ($found) {
                    if (is_array($value)) {
                        unset($data[$i][$key]);
                    } elseif (is_object($value)) {
                        unset($data[$i]->$key);
                    }
                }
            }
        }
        return $data;
    }
}
