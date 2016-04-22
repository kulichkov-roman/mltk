<?php

namespace Your\Data\Common;

/**
 * Абстрактный класс модели
 *
 * Class Model
 *
 * @author Grigory Bychek <gbychek@gmail.com>
 *
 * @package Your\Data
 */
abstract class Model
{
	/**
	 * @return mixed
	 */
	abstract public function getId();
}
