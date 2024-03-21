<?php

namespace Ras\Exceptions;

class RasBlockNotFoundException extends \Exception
{
    protected $message = 'Block not found';
    protected $code = 404;
}