<?
namespace Your\Tools\Parser\News;

use Your\Common\SingletonInterface;
use Your\Tools\Parser\News\SourceFactory;
use Your\Tools\Parser\News\SourceBP;
use Your\Tools\Logger\FileLogger;

/**
 * Импорт новостей
 *
 * Class Parser
 *
 * @package Your\Tools\Parser\News
 */
class Parser implements SingletonInterface
{
    const USER_AGENT = 'Opera/10.00 (Windows NT 5.1; U; ru) Presto/2.2.0';
    const FORMAT_DATE = 'DD.MM.YYYY';

    /**
     * @var self
     */
    protected static $instance = null;

    /**
     * @var
     */
    protected $htmlPage;

    /**
     * @var
     */
    protected $count;

    /**
     * Фабрика для обработки источника импорта
     *
     * @var SourceFactory
     */
    protected $sourceClass;

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance))
        {
            self::$instance = new self(new SourceFactory());
        }

        return self::$instance;
    }

    /**
     * @param SourceFactory $sourceFactory
     */
    protected function __construct($sourceClass)
    {
        $this->sourceClass = $sourceClass;
    }

    private function __clone()
    {
    }

    /**
     * @return string
     */
    public function getSourceClass()
    {
        return $this->sourceClass;
    }

    /**
     */
    private function buildSource()
    {
        SourceFactory::createSource($this->getSourceClass());
    }

    /**
     * @param $arParams
     *
     * @return bool
     * @throws \Exception
     */
    public function getPage($arParams)
    {
        if($arParams['URL'])
        {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $arParams['URL']);
            curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
            curl_setopt($curl, CURLOPT_FAILONERROR, 1);
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);

            ob_start();
            curl_exec($curl);
            curl_close($curl);

            $this->htmlPage = ob_get_contents();

            ob_end_clean();

            return true;
        }
        else
        {
            throw new \Exception('Не задан параметр URL страницы');
        }
    }

    /**
     * @param $pattern
     *
     * @throws \Exception
     */
    public function count($pattern)
    {
        if($pattern)
        {
            $objHtmlPage = \phpQuery::newDocument($this->htmlPage);

            if(is_object($objHtmlPage))
            {
                $this->count = sizeof($objHtmlPage->find($pattern));

                if($this->count)
                {
                    return $this->count;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        return false;
    }

    /**
     *
     * Упаковка html в массив объектов
     *
     * @param $patternDate
     * @param $patternDetailPageUrl
     * @param $patternPreviewText
     *
     * @return bool|mixed
     */
    public function htmlToArray($patternDate, $patternDetailPageUrl, $patternPreviewText)
    {
        $arResult = array();
        $arItems  = array();

        $objHtmlPage = \phpQuery::newDocument($this->htmlPage);

        foreach ($objHtmlPage->find($patternDate) as $date)
        {
            $arItems['DATE'][] = trim(pq($date)->text());
        }

        foreach ($objHtmlPage->find($patternDetailPageUrl) as $detailPageUrl)
        {
            $arItems['DETAIL_PAGE_URL'][] = trim(pq($detailPageUrl)->attr('href'));
        }

        foreach ($objHtmlPage->find($patternPreviewText) as $previewText)
        {
            $arItems['PREVIEW_TEXT'][] = trim(pq($previewText)->html());
        }

        if(sizeof($arItems['DATE']) > 0)
        {
            foreach ($arItems['DATE'] as $key => $value)
            {
                $arResult['ITEMS'][] = array(
                    'DATE'              => $value,
                    'DETAIL_PAGE_URL'   => $arItems['DETAIL_PAGE_URL'][$key],
                    'PREVIEW_TEXT'      => $arItems['PREVIEW_TEXT'][$key],
                    'DETAIL_TEXT'       => '',
                    'PREVIEW_PICTURE'   => array(),
                    'DETAIL_PICTURE'    => array(),
                );
            }
        }

        if(sizeof($arResult['ITEMS']))
        {
            return $arResult['ITEMS'];
        }
        return false;
    }

    /**
     * Конвертирование даты
     *
     * @param $date
     *
     * @return bool
     */
    public function convertDate($strDate)
    {
        $arMonth = array(
            'января',
            'февраля',
            'марта',
            'апреля',
            'мая',
            'июня',
            'июля',
            'августа',
            'сентября',
            'октября',
            'ноября',
            'декабря',
        );

        if(mb_stripos($strDate, ',') !== false)
        {
            $strDate = mb_stristr($strDate, ', ', true);
            $arDate = ParseDateTime($strDate, self::FORMAT_DATE);
            $arDate['MM'] = intval(array_search($arDate['MM'], $arMonth)) + 1;
            $strDate = implode('.', $arDate);

            return $strDate;
        }
        else
        {
            return false;
        }
    }

    /**
     * Сравнение дат в UNIX формате
     *
     * @param $date1
     * @param $date2
     *
     * @return int
     */
    public function compareDate($date1, $date2)
    {
        if($date1 > $date2)
        {
            return 1;
        }
        elseif($date1 == $date2)
        {
            return 0;
        }
        // $date1 < $date2
        else
        {
            return -1;
        }
    }

    /**
     * @param $pattern
     */
    public function getTextDate($pattern)
    {
        if($pattern)
        {


            //$htmlObj = \phpQuery::newDocument($obj->textContent);


            //$date = trim(pq($htmlObj->find($pattern))->text());
        }
        return false;
    }
}
?>
