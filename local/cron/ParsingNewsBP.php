<?
define('BX_BUFFER_USED', true);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_STATISTIC', true);
define('STOP_STATISTICS', true);
define('SITE_ID', 's1');

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['HTTP_HOST'] = 'mltk.cv24440.tmweb.ru';
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../');
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

while (ob_get_level()) {
    ob_end_flush();
}

if (!\CModule::IncludeModule('iblock'))
{
    die('Unable to include "iblock" module');
}

$source = \Your\Tools\Parser\News\Parser::getInstance();

$arParams = array(
    'URL' => 'http://www.bigpowernews.ru/search/?reff_id=&smode=razdel&source=ALL&source2=BP&source3=BP&source4=BP&region=2405&rubrika_bpd=&theme=22420&theme_doc=9740&rubrika=2920&razdel=35&q=&select_enabled_from=&select_year_from=2016&select_month_from=4&select_day_from=22&type=&select_enabled_to=&select_year_to=2016&select_month_to=4&select_day_to=22&type=&page=&sortby=&perpage=&outtype=',
    'REFERER' => 'http://www.bigpowernews.ru/',
);

$curlInit = $source::loadUsingCurl($arParams);

echo "<pre>"; var_dump($arResult['HTML']); echo "</pre>";
?>
