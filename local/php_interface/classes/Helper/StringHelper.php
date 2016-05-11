<?
/**
 * Хелпер для работы со строками
 *
 * Class StringHelper
 *
 * @package Your\Helper
 *
 * @author Kulichkov Roman <roman@kulichkov.pro>
 */

class StringHelper
{
    /**
     * Удалить комментарии из html
     *
     * @param $html
     *
     * @return mixed
     */
    public static function removeHtmlComments($html)
    {
        return preg_replace('/<!--(.*?)-->/', '', $html);
    }

    /**
     * Обрезать текст
     *
     * @param $str
     * @param $intLen
     *
     * @return string
     */
    public static function getTruncateStr($str, $intLen)
    {
        if(strlen($str) > $intLen)
        {
            $str = iconv('UTF-8','windows-1251', $str );
            $str = substr($str, 0, $intLen);
            $str = iconv('windows-1251','UTF-8', $str );

            return $str;
        }
        return $str;
    }

    /**
     * Транслитирация русских символов
     *
     * @param $str
     *
     * @return mixed
     */
    public static function getTranslitStr($str)
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
}
?>
