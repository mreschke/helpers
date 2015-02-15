<?php namespace Mreschke\Helpers;

use Mreschke\Helpers\Date;
use Mreschke\Helpers\File;

/**
 * Mreschke style logging
 * @copyright 2014 Matthew Reschke
 * @license http://mreschke.com/license/mit
 * @author Matthew Reschke <mail@mreschke.com>
 */
class Log implements LogInterface
{

	private $logFile;
	private $format;

	/**
	 * Create a new Log instance
	 * @param string $logFile path to log
	 * @param string $format
	 */
	public function __construct($logFile, $format = 'readable')
	{
		$this->logFile = $logFile;
		$this->format = $format;
	}
  	
  	/**
  	 * Write one line of log data
  	 * @param  string $data
  	 * @param  string $summary
  	 * @param  string $type
  	 * @param  string $action
  	 * @return void
  	 */
	public function write($data, $summary = 'Main', $type = 'log', $action = 'next')
	{

		/*
		Date                    | Type       | Action     | Summary         | Log Description
		------------------------|------------|------------|-----------------|----------------

		2013-12-02 19:00:00.859 | ########## | ########## | Main            | ESP3Generator Started at 12/2/
		2013-12-02 19:00:01.000 | Log        | Next       | Mode            | Running in resume mode
		2013-12-02 19:00:01.078 | Log        | Next       | StartGen        | Processing each enabled, unpro
		2013-12-02 19:00:01.093 | Log        | Done       | Finished        | ESP3Generator Finished (comple
		*/

		#Output Separator
		$os = "\n";
		if ($this->format == 'readable') {
			$os = "\r\n";
		}
		$date = Date::date('Y-m-d H:i:s.u');

		# Define Type
		$type = trim(strtolower($type));
		if (strlen($type) > 10) $type = substr($type, 0, 10);
		$types = array('critical', 'error', 'expected', 'log', 'unexpected', 'unusual', '##########', '==========', '----------', '++++++++++');
		if (in_array($type, $types)) {
			$type = ucfirst($type);	
		} else {
			$type = "Log";
		}

		#Define Action
		$action = trim(strtolower($action));
		if (strlen($action) > 10) $action = substr($action, 0, 10);
		$actions = array('done', 'halt', 'next', 'skip', '##########', '==========', '----------', '++++++++++');
		if (in_array($action, $actions)) {
			$action = ucfirst($action);	
		} else {
			$action = "Next";
		}

		#Define Summary
		$summary = trim($summary);
		if (strlen($summary) > 15) $summary = substr($summary, 0, 15);

		# Create initial File
		if (!file_exists($this->logFile)) {
			touch($this->logFile);
			#NO, I don't want these headers
			#if ($this->format == 'readable') {
			#	\Snippets\File::append($this->logFile, "Date                    | Type       | Action     | Summary         | Log Description$os");
			#	\Snippets\File::append($this->logFile, "------------------------|------------|------------|-----------------|----------------$os");
			#	\Snippets\File::append($this->logFile, "$os");				
			#}
		}

		if (file_exists($this->logFile)) {
			if ($this->format == 'readable') {
				if ($data) {
					$output = "$date | ";
					$output .= str_pad($type, 10).' | ';
					$output .= str_pad($action, 10).' | ';
					$output .= str_pad($summary, 15).' | ';
					$output .= $data;
					$output .= $os;
					
				} else {
					$output = $data.$os;
				}
				File::append($this->logFile, $output);
			}
		}
	}

}
