<?php

namespace Your\Exception\Token;

/**
 * Токен не найден в хранилище
 *
 * Class NotFoundException
 *
 * @author Grigory Bychek <gbychek@gmail.com>
 *
 * @package Your\Exception\Token
 */
class NotFoundException extends TokenException
{
	/**
	 * @param string $message
	 * @param int $code
	 */
	public function __construct($message = '', $code = 0)
	{
		parent::__construct($message, $code);
	}
}
