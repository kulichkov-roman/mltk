<?
namespace Your\Tools\Import\News;

use Your\Common\SingletonInterface;
use Your\Tools\Import\News\Source;
use Your\Tools\Import\News\SourceFactory;

/**
 * Импорт новостей
 *
 * Class Import
 *
 * @package Your\Tools\Import\News
 */
class Import
{
    /**
     * @var string
     */
    protected $sourceClass;

    /**
     * Import constructor.
     *
     * @param null $sourceClass
     */
    public function __construct(
        $sourceClass = null
    ) {
        $this->sourceClass = (string)$sourceClass;
    }

    /**
 * @return string
 */
    public function getActionClass()
    {
        return $this->sourceClass;
    }


}
?>
