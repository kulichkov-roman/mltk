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
    protected $objHtmlPage;

    /**
     * @var
     */
    protected $countNewsOnPage;

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
    public function getCountNewsOnPage($pattern)
    {
        if($pattern)
        {
            $this->objHtmlPage = \phpQuery::newDocument($this->htmlPage);

            if(is_object($this->objHtmlPage))
            {
                $this->countNewsOnPage = sizeof($this->objHtmlPage->find($pattern));

                if($this->countNewsOnPage)
                {
                    return $this->countNewsOnPage;
                }
                else
                {
                    throw new \Exception('На странице по паттерну новостей не найденно');
                }
            }
            else
            {
                throw new \Exception('Не удалось получить объект документа');
            }
        }
        else
        {
            throw new \Exception('Паттерн не может быть пустым');
        }
    }
}
?>
