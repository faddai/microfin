<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 19/07/2017
 * Time: 17:36
 */

namespace App\Exceptions;


class NoDataAvailableForExportException extends \Exception
{
    protected $message = 'No data is available to export';
}