<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot;

$pathCranberryBotBase = __DIR__;
$pathCranberryBotSrc = $pathCranberryBotBase . '/src/Bot';
$pathCranberryBotVendor = $pathCranberryBotBase . '/vendor';

/*
 * Initialize autoloading
 */
include_once( $pathCranberryBotSrc . '/Autoloader.php' );
Autoloader::register();

/*
 * Initialize vendor autoloading
 */
include_once( $pathCranberryBotVendor . '/twitteroauth/autoload.php' );
