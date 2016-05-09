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

class CRGParsingNews implements ParsingInterface
{
    const TEXT_TYPE = 'html';
    const SITE_ID   = 's1';

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
        $this->logger = new \Your\Tools\Logger\FileLogger('parserRG.log');
        $this->iBlockId = \Your\Environment\EnvironmentManager::getInstance()->get('newsIBlockId');

        $this->urlFilter = 'http://rg.ru/tema/ekonomika/industria/energo/';
        $this->urlSite = 'www.rg.ru';
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
            $countSumList = $this->source->count('div.b-news-inner__list-item');
            if($countSumList > 0)
            {
                $this->logger->log(sprintf('Новостей на странице: %s', $countSumList));

                $arResult = array(
                    'ITEMS' => $this->source->getElementList(
                        'div.b-news-inner__list-item div.b-news-inner__list-item-date._date',
                        'div.b-news-inner__list-item h2.b-news-inner__list-item-title a',
                        'div.b-news-inner__list-item p.b-news-inner__list-item-text a'
                    )
                );

                $countCurDate = sizeof($arResult['ITEMS']);
                if($countCurDate)
                {
                    $this->logger->log(sprintf('Новостей за сегодня: %s', $countCurDate));

                    $patternDetailImages = '';

                    $arPatternsException = array(
                        'div.ga-element.b-read-more.b-read-more_230x200.b-read-more_left',
                        'div.ga-element.b-read-more.b-read-more_230x200.b-read-more_right',
                        'div.b-read-more.b-read-more_50x50.b-read-more_left',
                        'div.b-material-img.b-material-img_art'
                    );

                    foreach($arResult['ITEMS'] as &$arItem)
                    {
                        $arDetailPage = $this->source->getElementDetail(
                            $this->urlSite,
                            $arItem['DETAIL_PAGE_URL'],
                            'article',
                            $patternDetailImages,
                            $arPatternsException
                        );
                        $arItem['DETAIL_TEXT'] = $arDetailPage['DETAIL_TEXT'];

                        $arTranslitParams = array(
                            'max_len' => 100,
                            'replace_space' => '_',
                            'replace_other' => '_'
                        );

                        $code = $this->source->getTranslitElementCode(
                            $arItem['NAME'],
                            $arTranslitParams
                        );

                        $arElement = array(
                            'SITE_ID'          => self::SITE_ID,
                            'CODE'             => $code,
                            'IBLOCK_ID'        => $this->iBlockId,
                            'DATE_ACTIVE_FROM' => $arItem['DATE'],
                            'NAME'             => $arItem['NAME'],
                            //'DETAIL_PAGE_URL'  => $arItem['DETAIL_PAGE_URL'],
                            'PREVIEW_TEXT'     => $arItem['PREVIEW_TEXT'],
                            'PREVIEW_TEXT_TYPE'=> self::TEXT_TYPE,
                            'DETAIL_TEXT'      => $arItem['DETAIL_TEXT'],
                            'DETAIL_TEXT_TYPE' => self::TEXT_TYPE
                        );

                        if ($id = $manager->add($arElement))
                        {
                            $this->logger->log(sprintf('Новость добавлена: "%s" (%s) ', $arItem['NAME'], $id));
                        }
                        else
                        {
                            $this->logger->log(sprintf('Новость не добавлена: "%s" ', $arItem['NAME']));
                        }
                    }
                    unset($arItem);
                }
            }
        }
    }
}

$parser = new CRGParsingNews();

try
{
    $parser->up();
}
catch (\Your\Exception\Data\Parsing\ParsingException $e)
{
    echo sprintf('Ошибка парсинга: "%s"', $e->getMessage()) . PHP_EOL;
}
?>