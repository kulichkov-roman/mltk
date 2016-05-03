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
    public function getPageCurl($arParams)
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
    public function countList($pattern)
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
     * @param $count
     * @param $patternDate
     * @param $patternDetailPageUrl
     * @param $patternPreviewText
     *
     * @return bool|mixed
     */
    public function htmlToArray($count, $patternDate, $patternDetailPageUrl, $patternPreviewText)
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

    private function arrayShiftObjects()
    {
        return array_shift($this->arObj);
    }

    /**
     * @param $pattern
     */
    public function getTextDate($pattern)
    {
        if($pattern)
        {
            $obj = $this->arrayShiftObjects();

            $htmlObj = \phpQuery::newDocument($obj->textContent);


            $date = trim(pq($htmlObj->find($pattern))->text());
        }
        return false;
    }
}
?>
