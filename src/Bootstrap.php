<?php

namespace IndieHD\AudioManipulator;

use Dotenv\Dotenv;

use IndieHD\AudioManipulator\Container\Container;

if (!defined('app')) {
    function app()
    {
        return new Container();
    }
}
