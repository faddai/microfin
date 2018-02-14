<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 24/04/2017
 * Time: 00:38
 */

namespace App\Exceptions;


use Exception;

class ReportJobClassNotDefinedException extends \Exception
{
    public function __construct($report = '', $code = 0, Exception $previous = null)
    {
        $message = 'Report job class '. $report . ' was not found. It may not be defined.';

        parent::__construct($message, $code, $previous);
    }
}