<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot\Twitter;

trait Consumer
{
	/**
	 * @var	array
	 */
	protected $credentials=[];

	/**
	 * @var	Cranberry\Bot\Twitter\Twitter
	 */
	protected $twitter;

	/**
	 * @return	Cranberry\Bot\Twitter\Twitter
	 */
	protected function getTwitterObject()
	{
		if( !($this->twitter instanceof Twitter) )
		{
			$this->twitter = new Twitter( $this->credentials );
		}

		return $this->twitter;
	}

	/**
	 * @param	Cranberry\Bot\Twitter\Tweet	$tweet
	 * @return	array
	 */
	public function postTweetToTwitter( Tweet $tweet )
	{
		$twitter = $this->getTwitterObject();
		$response = $twitter->postTweet( $tweet );

		if( $response['httpCode'] != 200 )
		{
			$responseError = $response['body']['errors'][0];
			throw new \Exception( $responseError->message, $responseError->code );
		}

		return $response;
	}

	/**
	 * @param	array	$credentials
	 */
	public function setTwitterCredentials( $credentials )
	{
		if( is_array( $credentials ) )
		{
			$this->credentials = $credentials;
		}
	}
}
