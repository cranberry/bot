<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot\History;

use Cranberry\Core\File;

trait Bot
{
	/**
	 * @var	Cranberry\Bot\History\History
	 */
	public $history;

	/**
	 * @param	Cranberry\Bot\History\History	$history
	 */
	public function setHistoryObject( History $history )
	{
		$this->history = $history;
	}

	/**
	 * @param
	 * @return	void
	 */
	public function writeHistory()
	{
		$this->history->write();
	}
}
