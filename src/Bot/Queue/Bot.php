<?php

/*
 * This file is part of Cranberry\Bot
 */
namespace Cranberry\Bot\Queue;

use Cranberry\Core\File;

trait Bot
{
	/**
	 * @var	Cranberry\Bot\Queue\Queue
	 */
	public $queue;

	/**
	 * @param	Cranberry\Bot\Queue\Queue	$queue
	 */
	public function setQueueObject( Queue $queue )
	{
		$this->queue = $queue;
	}
}
