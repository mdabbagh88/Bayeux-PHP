<?php

namespace Bayeux\Api\Server\Authorizer\Result;


use Bayeux\Api\Server\Authorizer\Result;

final class Granted extends Result
{
    private static $GRANTED = null;

    public static function getInstance() {
        if (self::$GRANTED === null) {
            self::setInstance(new Granted());
        }

        return self::$GRANTED;
    }

    private static function setInstance(Granted $granted) {
        self::$GRANTED = $granted;
    }

    private function __construct()
    {
    }
}

