<?php

namespace Your\Common;

/**
 * Базовый класс исключений
 *
 * Class Exception
 *
 * @author Roman Kulichkov <roman@kulichkov.pro>
 *
 * @package Your\Common
 */
class Exception extends \Exception
{
	/**
	 * @param string $message
	 * @param int $code
	 * @param \Exception $previous
	 */
	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
