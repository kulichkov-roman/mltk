<?
namespace Your\Tools\Parser\News;

use Your\Common\SingletonInterface;

use Your\Exception\Data\Parsing\ParsingException;

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
    const UPLOAD_DIR_PICTURE = '/home/c/cv24440/mltk/public_html/upload/parser_news_tmp/';

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
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param SourceFactory $sourceFactory
     */
    protected function __construct($serverRoot)
    {
        $this->logger = new \Your\Tools\Logger\FileLogger('parser.log');
        $this->serverRoot = $serverRoot;
    }

    private function __clone()
    {
    }

    /**
     * Получить страницу по URL
     *
     * @param $url
     *
     * @return bool
     * @throws \Exception
     */
    public function getPageByUrl($url)
    {
        if($url)
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

            ob_start();

            $rs = curl_exec($ch);
            if($rs)
            {
                curl_close($ch);
                $this->htmlPage = ob_get_contents();
                ob_end_clean();

                return true;
            }
            else
            {
                throw new \Exception(curl_error($ch));
            }
        }
        else
        {
            throw new \Exception('Параметр url для получения страницы не может быть пустым.');
        }
    }

    /**
     * Получить картинку по URL
     *
     * @param      $url
     * @param null $path
     *
     * @return bool|string
     * @throws \Exception
     */
    public function getImageByUrl($url, $path = self::UPLOAD_DIR_PICTURE)
    {
        if($url)
        {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $rs = curl_exec($ch);
            if($rs)
            {
                if (
                    curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200 &&
                    strpos(curl_getinfo($ch, CURLINFO_CONTENT_TYPE), 'image') !== false
                )
                {
                    $name = substr($url, strrpos($url, '/') + 1);

                    if ($path)
                    {
                        if (is_writeable($path))
                        {
                            file_put_contents(rtrim($path, '/') . '/' . $name, $rs);
                        }
                        else
                        {
                            throw new \Exception(sprintf('Недостаточно прав для записи в папку : %s', $path));
                        }
                    }
                    return $path.$name;
                }
                curl_close($ch);
                return false;
            }
            else
            {
                throw new \Exception('Не удалось получить картинку по cURL.');
            }
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
            }
            return false;
        }
        throw new \Exception('Не удалось получить количество элементов по паттерну.');
    }

    /**
     * @param $html
     *
     * @return mixed
     */
    public function delHtmlComments($html)
    {
        return preg_replace('/<!--(.*?)-->/', '', $html);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function delFileByPath($path)
    {
        return unlink($path);
    }

    /**
     * @param $urlSite
     * @param $urlDetail
     * @param $patternDetail
     * @param $patternDetailImages
     * @param $arExceptionPattern
     *
     * @return array|bool
     */
    public function getElementDetail(
        $urlSite,
        $urlDetail,
        $patternDetail,
        $patternDetailImages = null,
        $arPatternsException = array()
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

            if($this->getPageByUrl($urlPage))
            {
                $objHtmlDetailPage = \phpQuery::newDocument($this->htmlPage);

                if($patternDetailImages)
                {
                    $detailPage = $objHtmlDetailPage->find($patternDetailImages);
                    $src = trim(pq($detailPage)->attr('src'));
                    $arDetail['DETAIL_PICTURE'] = \CFile::MakeFileArray($this->getImageByUrl($src));
                }

                if(
                    is_array($arPatternsException) &&
                    sizeof($arPatternsException)
                )
                {
                    foreach ($arPatternsException as $arExceptionPattern)
                    {
                        $objHtmlDetailPage->find($arExceptionPattern)->remove();
                    }
                }

                $detailObj = $objHtmlDetailPage->find($patternDetail);
                $arDetail['DETAIL_TEXT'] = $this->delHtmlComments(
                    trim(pq($detailObj)->html())
                );
                return $arDetail;
            }
            else
            {
                return false;
            }
        }
        return false;
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
        if(
            $patternDate &&
            $patternDetailPageUrl &&
            $patternPreviewText
        )
        {
            $arResult = array();
            $arItems  = array();

            $objHtmlPage = \phpQuery::newDocument($this->htmlPage);

            foreach ($objHtmlPage->find($patternDate) as $date)
            {
                $arItems['DATE'][] = $this->convertDate(trim(pq($date)->html()));
            }

            if(sizeof($arItems['DATE']) > 0)
            {
                foreach ($objHtmlPage->find($patternDetailPageUrl) as $detailPageUrl)
                {
                    $arItems['DETAIL_PAGE_URL'][] = trim(pq($detailPageUrl)->attr('href'));
                    $arItems['NAME'][] = trim(pq($detailPageUrl)->text());
                }

                foreach ($objHtmlPage->find($patternPreviewText) as $previewText)
                {
                    $arItems['PREVIEW_TEXT'][] = trim(pq($previewText)->html());
                }

                foreach ($arItems['DATE'] as $key => $value)
                {
                    if($value == date(self::FORMAT_DATE_1))
                    {
                        $arResult['ITEMS'][] = array(
                            'DATE'              => $value,
                            'NAME'              => $arItems['NAME'][$key],
                            'DETAIL_PAGE_URL'   => $arItems['DETAIL_PAGE_URL'][$key],
                            'PREVIEW_TEXT'      => $arItems['PREVIEW_TEXT'][$key],
                        );
                    }
                }

                if(sizeof($arResult['ITEMS']))
                {
                    return $arResult['ITEMS'];
                }
                else
                {
                    return false;
                }
            }
            else
            {
                throw new \Exception('Не удалось получить список дат для списка новостей.');
            }
        }
        else
        {
            throw new \Exception('Не заполненны параметры для получения списка новостей.');
        }
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
        if($strDate)
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
            elseif(mb_stripos($strDate, ':') !== false)
            {
                return date(self::FORMAT_DATE_1);
            }
            else
            {
                return $strDate;
            }
        }
        else
        {
            throw new \Exception('Дата не может быть пустой.');
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
        {
            $str = iconv('UTF-8','windows-1251', $str );
            $str = substr($str, 0, $intLen);
            $str = iconv('windows-1251','UTF-8', $str );

            return $str;
        }
        else
        {
            return $str;
        }
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
