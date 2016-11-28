<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot\Twitter;

trait Bot
{
	/**
	 * @var	Cranberry\Bot\Twitter\Twitter
	 */
	protected $twitter;

	/**
	 * @var	array
	 */
	protected $twitterCredentials=[];

	/**
	 * Lazy-load Twitter object
	 *
	 * @return	Cranberry\Bot\Twitter\Twitter
	 */
	protected function getTwitterObject()
	{
		if( !($this->twitter instanceof Twitter) )
		{
			$this->twitter = new Twitter( $this->twitterCredentials );
		}

		return $this->twitter;
	}

	/**
	 * Set credentials for later lazy-loading of Twitter object
	 *
	 * @param	array	$twitterCredentials
	 */
	public function setTwitterCredentials( $twitterCredentials )
	{
		if( is_array( $twitterCredentials ) )
		{
			$this->twitterCredentials = $twitterCredentials;
		}
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
}
