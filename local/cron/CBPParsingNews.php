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

class CBParsingNews
{
    protected $urlFilter;
    protected $urlSite;

    private $source;

    public function __construct()
    {
        $this->source = \Your\Tools\Parser\News\Parser::getInstance();

        $this->urlFilter = 'http://www.bigpowernews.ru/search/?reff_id=&smode=razdel&source=ALL&source2=BP&source3=BP&source4=BP&region=2405&rubrika_bpd=&theme=22420&theme_doc=9740&rubrika=2920&razdel=35&q=&select_enabled_from=&select_year_from=2016&select_month_from=4&select_day_from=22&type=&select_enabled_to=&select_year_to=2016&select_month_to=4&select_day_to=22&type=&page=&sortby=&perpage=&outtype=';
        $this->urlSite = 'www.bigpowernews.ru';
    }

    /**
     * Применить парсинг
     */
    public function up()
    {
        $htmlPage = $this->source->getPage($this->urlFilter);

        if($htmlPage)
        {
            $count = $this->source->count('table.sres');
            if($count > 0)
            {
                $arResult = array(
                    'ITEMS' => $this->source->getElementList(
                        'table.sres td.text_81',
                        'table.sres td.pad_65 a',
                        'table.sres div.text_82'
                    )
                );

                if(sizeof($arResult['ITEMS']))
                {
                    $arFirstItem = array_shift($arResult['ITEMS']);
                    $arFirstItem['DATE'] = MakeTimeStamp(
                        $this->source->convertDate($arFirstItem['DATE']),
                        $this->source->FORMAT_DATE
                    );
                    $curDate = MakeTimeStamp(
                        date('d.m.Y'),
                        $this->source->FORMAT_DATE
                    );
                    $rsCompareDate = $this->source->compareDate(
                        $arFirstItem['DATE'],
                        $curDate
                    );

                    $rsCompareDate = 0;
                    if($rsCompareDate < 0)
                    {
                        throw new \Exception('Нет новостей за сегодняшне число');
                    }
                    elseif($rsCompareDate == 0)
                    {
                        foreach($arResult['ITEMS'] as &$arItem)
                        {
                            $arDetailPage = $this->source->getElementDetail(
                                $this->urlSite,
                                $arItem['DETAIL_PAGE_URL'],
                                'div.block_233'
                            );
                            $arItem['DETAIL_TEXT'] = $arDetailPage['DETAIL_TEXT'];
                        }
                        unset($arItem);
                    }
                    else
                    {
                        /**
                         * Получить и сохранить все новости за текущую дату
                         */
                    }
                }
            }
        }
    }
}

$parser = new CBParsingNews();
$parser->up();
?>
