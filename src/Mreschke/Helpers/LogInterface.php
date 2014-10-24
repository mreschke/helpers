<?php namespace Mreschke\Helpers;

/**
 * Provides a contractual interface for Mreschke\Helpers\Log.
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
interface LogInterface
{

  	/**
  	 * Write one line of log data
  	 * @param  string $data
  	 * @param  string $summary
  	 * @param  string $type
  	 * @param  string $action
  	 * @return void
  	 */
	public function write($data, $summary = 'Main', $type = 'log', $action = 'next');

}
