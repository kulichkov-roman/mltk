<?php

namespace Your\Tools;

/**
 * Интерфейс логгера
 *
 * Interface LoggerInterface
 *
 * @author Grigory Bychek <gbychek@gmail.com>
 *
 * @package Your\Tools
 */
interface LoggerInterface
{
	/**
	 * @param $message
	 */
	public function log($message);
}
