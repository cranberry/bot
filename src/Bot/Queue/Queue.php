<?php

/*
 * This file is part of Cranberry\Bot\Queue
 */
namespace Cranberry\Bot\Queue;

use Cranberry\Core\File;
use Cranberry\Core\JSON;
use Cranberry\Core\Utils;

class Queue
{
	/**
	 * @var	array
	 */
	public $contents=[];

	/**
	 * @var	int
	 */
	public $limit=5;

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

			if( isset( $queueData['contents'] ) )
			{
				$this->contents = $queueData['contents'];
			}
			if( isset( $queueData['limit'] ) )
			{
				$this->limit = $queueData['limit'];
			}
		}
	}

	/**
	 * @param	int		$index
	 * @return	void
	 */
	public function deleteItemByIndex( $index )
	{
		if( isset( $this->contents[$index] ) )
		{
			unset( $this->contents[$index] );
			Utils::reindexArray( $this->contents );
		}
	}

	/**
	 * @return	array
	 */
	public function getItems()
	{
		return $this->contents;
	}

	/**
	 * @param	string	$item
	 * @return	boolean
	 */
	public function pushItem( $item )
	{
		if( count( $this->contents ) < $this->limit )
		{
			$this->contents[] = $item;
			return true;
		}

		return false;
	}

	/**
	 * @param	int		$limit
	 * @return	void
	 */
	public function setLimit( $limit )
	{
		$this->limit = $limit;
	}

	/**
	 * Shift an element off the beginning of array
	 *
	 * @return	string
	 */
	public function shiftItem()
	{
		return array_shift( $this->contents );
	}

	/**
	 * Write contents to file
	 */
	public function write()
	{
		$queueJSON = JSON::encode( $this, JSON_PRETTY_PRINT );
		$this->source->putContents( $queueJSON );
	}
}
