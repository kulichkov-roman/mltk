<?php
/**
 * Общая конфигурация для всех сайтов и окружений
 */
\Your\Environment\EnvironmentManager::getInstance()->addConfig(
	new \Your\Environment\Configuration\CommonConfiguration(
		array(
			/**
			 *
			 */
			'newsIBlockId' => 6,
			'newsPropsId' => 114,
		)
	)
);

\Bitrix\Main\Loader::includeModule('tpic');
