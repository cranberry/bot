<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot\Slack;

use Cranberry\Core\HTTP;

class Request extends HTTP\Request
{
	/**
	 * @param	string	webhookURL
	 */
	public function __construct( $webhookURL )
	{
		$this->addHeader( 'Content-Type', 'application/json' );
		$this->url = $webhookURL;
	}
}
