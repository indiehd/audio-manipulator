<?php

namespace IndieHD\AudioManipulator;

use IndieHD\AudioManipulator\Container;

if (!defined('app')) {
    function app()
    {
        return new Container();
    }
}
