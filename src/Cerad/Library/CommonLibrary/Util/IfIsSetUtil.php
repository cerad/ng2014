<?php
namespace Cerad\Library\CommonLibrary\Functions;

/* ==================================
 * Aliasing/Importing a function is not supported for ssome reason
 * Hence the static function isdie of a class
 */
class IfIsSetUtil
{
    static public function exe($data,$key,$default=null)
    {
        return isset($data[$key]) ? $data[$key] : $default;
    }
}
?>
