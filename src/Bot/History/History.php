<?php

/*
 * This file is part of Cranberry\Bot\History
 */
namespace Cranberry\Bot\History;

use Cranberry\Core\File;
use Cranberry\Core\JSON;

class History
{
	/**
	 * @var	array
	 */
	public $items=[];

	/**
	 * @var	Cranberry\Core\File\File
	 */
	protected $source;

	/**
	 * @param	Cranberry\Core\File\File	$source
	 */
	public function __construct( File\File $source )
	{
		$this->source = $source;
		if( $source->exists() )
		{
			$queueJSON = $source->getContents();
			$queueData = JSON::decode( $queueJSON, true );

			if( isset( $queueData['items'] ) )
			{
				$this->items = $queueData['items'];
			}
		}
	}

	/**
	* @param	string	$domain
	* @param	string	$value
	* @return	void
	*/
	public function addDomainEntry( $domain, $value )
	{
		$this->items[$domain][] = $value;
	}

	/**
	 * @param	string	$domain
	 * @param	string	$value
	 * @return	boolean
	 */
	public function domainEntryExists( $domain, $value )
	{
		if( !isset( $this->items[$domain] ) )
		{
			return false;
		}

		return in_array( $value, $this->items[$domain] );
	}

	/**
	 * @param	string	$domain
	 * @return	void
	 */
	public function resetDomain( $domain )
	{
		if( isset( $this->items[$domain] ) )
		{
			$this->items[$domain] = [];
		}
	}

	/**
	 * @return	void
	 */
	public function write()
	{
		$historyData = JSON::encode( $this, JSON_PRETTY_PRINT );
		$this->source->putContents( $historyData );
	}
}
