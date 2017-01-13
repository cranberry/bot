<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot\Slack;

use Cranberry\Core\HTTP;

class Slack
{
	/**
	 * @param	string	$message
	 * @return	Huxtable\Core\HTTP\Response
	 */
	public function postMessage( $webhookURL, Message $message )
	{
		$request = new Request( $webhookURL );
		$request = $message->populateRequest( $request );

		$response = HTTP\HTTP::post( $request );

		return $response;
	}
}
