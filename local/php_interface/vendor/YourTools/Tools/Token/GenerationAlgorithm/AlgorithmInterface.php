<?php

namespace Your\Tools\Token\GenerationAlgorithm;

/**
 * Алгоритм формирования токена
 *
 * Interface AlgorithmInterface
 *
 * @package Your\Tools\Token\GenerationAlgorithm
 */
interface AlgorithmInterface
{
	/**
	 * @return string идентификатор токена
	 */
	public function generate();
}
