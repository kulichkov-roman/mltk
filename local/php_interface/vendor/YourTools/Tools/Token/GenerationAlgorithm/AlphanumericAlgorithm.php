<?php

namespace Your\Tools\Token\GenerationAlgorithm;

/**
 * Генератор идентификаторов с использованием текущего времени,
 * идентификатора процесса и последовательного номера
 *
 * Class AlphanumericAlgorithm
 *
 * @package Your\Tools\Token\GenerationAlgorithm
 */
class AlphanumericAlgorithm implements AlgorithmInterface
{
	/**
	 * @var int
	 */
	public $seq = 0;

	/**
	 * @return string
	 */
	function generate()
	{
		return sha1(getmypid() . microtime(true) . $this->seq++);
	}
}
