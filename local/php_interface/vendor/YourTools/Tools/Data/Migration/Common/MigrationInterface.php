<?php

namespace Your\Tools\Data\Migration\Common;

/**
 * Интерфейс миграций
 *
 * Interface MigrationInterface
 *
 * @author Grigory Bychek <gbychek@gmail.com>
 *
 * @package Your\Tools\Data\Migration\Common
 */
interface MigrationInterface
{
	/**
	 * Применяет миграцию
	 */
	public function up();

	/**
	 * Отменяет миграцию
	 */
	public function down();
}
