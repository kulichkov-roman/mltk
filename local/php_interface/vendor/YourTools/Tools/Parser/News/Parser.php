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
    const USER_AGENT    = 'Opera/10.00 (Windows NT 5.1; U; ru) Presto/2.2.0';
    const FORMAT_DATE   = 'DD.MM.YYYY';
    const FORMAT_DATE_1 = 'd.m.Y';

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
     * @param $url
     *
     * @return bool
     */
    public function getPage($url)
    {
        if($url)
        {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
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
            return false;
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
     * @param $urlSite
     * @param $urlDetail
     * @param $patternDetail
     * @param $patternDetailImages
     *
     * @return array|bool
     */
    public function getElementDetail(
        $urlSite,
        $urlDetail,
        $patternDetail,
        $patternDetailImages = null
    )
    {
        if(
            $urlSite &&
            $urlDetail &&
            $patternDetail
        )
        {
            $urlPage = $urlSite.$urlDetail;

            $arDetail = array();

            if($urlPage)
            {
                if($this->getPage($urlPage))
                {
                    $objHtmlDetailPage = \phpQuery::newDocument($this->htmlPage);

                    $detailText = $objHtmlDetailPage->find($patternDetail);
                    $arDetail['DETAIL_TEXT'] = trim(pq($detailText)->html());

                    if($patternDetailImages)
                    {
                        $arDetail['DETAIL_PICTURE'] = '';
                    }

                    return $arDetail;
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
        else
        {
            return false;
        }
    }

    /**
     * Упаковка html в массив объектов для списка
     *
     * @param $patternDate
     * @param $patternDetailPageUrl
     * @param $patternPreviewText
     *
     * @return bool|mixed
     */
    public function getElementList(
        $patternDate,
        $patternDetailPageUrl,
        $patternPreviewText
    )
    {
        $arResult = array();
        $arItems  = array();

        $objHtmlPage = \phpQuery::newDocument($this->htmlPage);

        foreach ($objHtmlPage->find($patternDate) as $date)
        {
            $arItems['DATE'][] = $this->convertDate(trim(pq($date)->text()));
        }

        foreach ($objHtmlPage->find($patternDetailPageUrl) as $detailPageUrl)
        {
            $arItems['DETAIL_PAGE_URL'][] = trim(pq($detailPageUrl)->attr('href'));
            $arItems['NAME'][] = trim(pq($detailPageUrl)->text());
        }

        foreach ($objHtmlPage->find($patternPreviewText) as $previewText)
        {
            $arItems['PREVIEW_TEXT'][] = trim(pq($previewText)->html());
        }

        if(sizeof($arItems['DATE']) > 0)
        {
            foreach ($arItems['DATE'] as $key => $value)
            {
                //if($arItems['DATE'] == date(self::FORMAT_DATE_1))
                if($value == '04.05.2016')
                {
                    $arResult['ITEMS'][] = array(
                        'DATE'              => $value,
                        'NAME'              => $arItems['NAME'][$key],
                        'DETAIL_PAGE_URL'   => $arItems['DETAIL_PAGE_URL'][$key],
                        'PREVIEW_TEXT'      => $arItems['PREVIEW_TEXT'][$key],
                        'DETAIL_TEXT'       => '',
                        'PREVIEW_PICTURE'   => array(),
                        'DETAIL_PICTURE'    => array(),
                    );
                }
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
            $arDate  = ParseDateTime($strDate, self::FORMAT_DATE);
            $num     = intval(array_search($arDate['MM'], $arMonth)) + 1;
            $arDate['MM'] = str_pad($num, 2, '0', STR_PAD_LEFT);
            $strDate = implode('.', $arDate);

            return $strDate;
        }
        else
        {
            return false;
        }
    }

    /**
     * Обрезать текст
     *
     * @param $str
     * @param $intLen
     *
     * @return string
     */
    function getTruncateStr($str, $intLen)
    {
        if(strlen($str) > $intLen)
            return rtrim(substr($str, 0, $intLen));
        else
            return $str;
    }

    /**
     * Транслитирация русских символов
     *
     * @param $str
     *
     * @return mixed
     */
    public function getTranslitStr($str)
    {
        $arConverter = array(
            'а' => 'a',   'б' => 'b',   'в' => 'v',
            'г' => 'g',   'д' => 'd',   'е' => 'e',
            'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
            'и' => 'i',   'й' => 'y',   'к' => 'k',
            'л' => 'l',   'м' => 'm',   'н' => 'n',
            'о' => 'o',   'п' => 'p',   'р' => 'r',
            'с' => 's',   'т' => 't',   'у' => 'u',
            'ф' => 'f',   'х' => 'h',   'ц' => 'c',
            'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
            'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
            'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

            'А' => 'A',   'Б' => 'B',   'В' => 'V',
            'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
            'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
            'И' => 'I',   'Й' => 'Y',   'К' => 'K',
            'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',
            'С' => 'S',   'Т' => 'T',   'У' => 'U',
            'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
            'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
            'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
            'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
        );
        return strtr($str, $arConverter);
    }

    /**
     * Получить символьный код элемента
     *
     * @param $str
     *
     * @return mixed
     */
    public function getTranslitElementCode($str, $arParams)
    {
        $str = $this->getTruncateStr($str, $arParams['max_len']);
        $str = $this->getTranslitStr($str);
        $str = strtolower($str);
        $str = preg_replace('~[^-a-z0-9_]+~u', $arParams['replace_space'], $str);
        $str = trim($str, $arParams['replace_other']);
        return $str;
    }
}
?>
