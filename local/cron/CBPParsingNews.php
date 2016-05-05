<?
use Your\Tools\Data\Parsing\Common\ParsingInterface;

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

class CBParsingNews implements ParsingInterface
{
    protected $urlFilter;
    protected $urlSite;
    protected $iBlockId;

    private $source;
    private $logger;

    /**
     * CBParsingNews constructor.
     */
    public function __construct()
    {
        $this->source = \Your\Tools\Parser\News\Parser::getInstance();
        $this->logger = new \Your\Tools\Logger\FileLogger('parser.log');
        $this->iBlockId = \Your\Environment\EnvironmentManager::getInstance()->get('newsIBlockId');

        $this->urlFilter = 'http://www.bigpowernews.ru/search/?reff_id=&smode=razdel&source=ALL&source2=BP&source3=BP&source4=BP&region=2405&rubrika_bpd=&theme=22420&theme_doc=9740&rubrika=2920&razdel=35&q=&select_enabled_from=&select_year_from=2016&select_month_from=4&select_day_from=22&type=&select_enabled_to=&select_year_to=2016&select_month_to=4&select_day_to=22&type=&page=&sortby=&perpage=&outtype=';
        $this->urlSite = 'www.bigpowernews.ru';
    }

    /**
     * Применить парсинг
     */
    public function up()
    {
        $manager = \Your\Data\Bitrix\IBlockElementManager::getInstance();

        $htmlPage = $this->source->getPage($this->urlFilter);

        if($htmlPage)
        {
            $countSumList = $this->source->count('table.sres');
            if($countSumList > 0)
            {
                $this->logger->log(sprintf('Новостей на странице: %s', $countSumList));

                $arResult = array(
                    'ITEMS' => $this->source->getElementList(
                        'table.sres td.text_81',
                        'table.sres td.pad_65 a',
                        'table.sres div.text_82'
                    )
                );

                $countCurDate = sizeof($arResult['ITEMS']);
                if($countCurDate)
                {
                    $this->logger->log(sprintf('Новостей за сегодня: %s', $countCurDate));

                    foreach($arResult['ITEMS'] as &$arItem)
                    {
                        $arDetailPage = $this->source->getElementDetail(
                            $this->urlSite,
                            $arItem['DETAIL_PAGE_URL'],
                            'div.block_233'
                        );
                        $arItem['DETAIL_TEXT'] = $arDetailPage['DETAIL_TEXT'];

                        $arTranslitParams = array(
                            'max_len' => 10,
                            'replace_space' => '_',
                        );

                        $str = 'олололОлОлололо';

                        $code = $this->source->translit(
                            $str,
                            "ru" ,
                            $arTranslitParams
                        );

                        $code = $this->source->str2url($arItem['NAME']);

                        echo "<pre>"; var_dump($code); echo "</pre>"; die();

                        $arElement = array(
                            'SITE_ID'          => 's1',
                            'CODE'             => $code,
                            'IBLOCK_ID'        => $this->iBlockId,
                            'DATE_ACTIVE_FROM' => $arItem['DATE'],
                            'NAME'             => $arItem['NAME'],
                            'DETAIL_PAGE_URL'  => $arItem['DETAIL_PAGE_URL'],
                            'PREVIEW_TEXT'     => $arItem['PREVIEW_TEXT'],
                            'DETAIL_TEXT'      => $arItem['DETAIL_TEXT']
                        );

                        if ($id = $manager->add($arElement))
                        {
                            $this->logger->log(sprintf('Новость добавленна: "%s" (%s) ', $arItem['NAME'], $id));
                        }
                        else
                        {
                            $this->logger->log(sprintf('Новость не добавленна: "%s" ', $arItem['NAME']));
                        }
                    }
                    unset($arItem);
                }
            }
        }
    }
}

$parser = new CBParsingNews();

try
{
    $parser->up();
}
catch (\Your\Exception\Data\Parsing\ParsingException $e)
{
    echo sprintf('Ошибка парсинга: "%s"', $e->getMessage()) . PHP_EOL;
}
?>
