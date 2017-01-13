<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot\Twitter;

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter
{
	/**
	 * @var	Abraham\TwitterOAuth\TwitterOAuth
	 */
	protected $connection;

	/**
	 * @var	array
	 */
	protected $credentials=[];

	/**
	 * @param	array	$credentials
	 * @return	void
	 */
	public function __construct( array $credentials )
	{
		$this->credentials = $credentials;
	}

	/**
	 * @param
	 * @return	void
	 */
	protected function getConnectionObject()
	{
		if( $this->connection instanceof TwitterOAuth )
		{
			return $this->connection;
		}

		$requiredKeys = ['consumerKey','consumerSecret','accessToken','accessTokenSecret'];
		foreach( $requiredKeys as $requiredKey )
		{
			if( !isset( $this->credentials[$requiredKey] ) )
			{
				throw new \InvalidArgumentException( "Missing required Twitter credentials key '{$requiredKey}'" );
			}
		}

		extract( $this->credentials );
		$this->connection = new TwitterOAuth( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );

		return $this->connection;
	}

	/**
	 * @param	Cranberry\Bot\Twitter\Tweet	$tweet
	 * @return	array
	 */
	public function postTweet( Tweet $tweet )
	{
		$connection = $this->getConnectionObject();

		$contents['status'] = $tweet->getStatus();
		$attachments = $tweet->getAttachments();

		/*
		 * Process any media attachments
		 */
		if( count( $attachments ) > 0 )
		{
			foreach( $attachments as $attachment )
			{
				$uploadResponse = $connection->upload( 'media/upload', ['media' => $attachment['source']] );
				$mediaIDs[] = $uploadResponse->media_id;

				if( isset( $attachment['altText'] ) )
				{
					// @todo	Add alt text when https://github.com/abraham/twitteroauth/issues/456 is fixed
				}
			}

			$contents['media_ids'] = implode( ',', $mediaIDs );
		}

		$connection->post( 'statuses/update', $contents );

		$response['body'] = get_object_vars( $connection->getLastBody() );
		$response['httpCode'] = $connection->getLastHttpCode();

		if( $response['httpCode'] != 200 )
		{
			throw new \Exception( $response['body']['errors'][0]->message, $response['httpCode'] );
		}

		return $response;
	}
}
