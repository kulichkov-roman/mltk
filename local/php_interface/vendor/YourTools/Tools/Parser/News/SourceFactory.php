<?
namespace Your\Tools\Parser\News;

/**
 * Фабрика действий источников импорта
 *
 * Class SourceFactory
 *
 * @package Your\Tools\Parser\News
 */
class SourceFactory
{
    public function __construct()
    {
    }

    /**
     * Инстанцирует экземляр SourceFactory
     *
     * @param string $sourceClass Имя класса для создания обработки источника импорта
     * @param array $data Параметры действия
     *
     * @return Source
     *
     * @throws \RuntimeException
     */
    public static function createSource($sourceClass)
    {
        if (!class_exists($sourceClass))
        {
            throw new \RuntimeException('Не верный класс источника');
        }

        $class = new \ReflectionClass($sourceClass);

        if (!$class->isSubclassOf('Your\Tools\Parser\News\Source'))
        {
            throw new \RuntimeException('Класс источника не потомок \Parser\News\Source');
        }

        return new $sourceClass();
    }
}


?>
