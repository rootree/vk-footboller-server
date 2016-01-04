<?php
// +----------------------------------------------------------------------+
// | Универсальная система постановки задач                               |
// +----------------------------------------------------------------------+
// | Copyright (c) 2010 - 2011                                            |
// +----------------------------------------------------------------------+
// | Authors: Ivan Chura <ivan.chura@gmail.com>                           |
// +----------------------------------------------------------------------+

/**
 * @version 1.0
 * @author Ivan Chura <ivan.chura@gmail.com>  
 * Date: Apr 25, 2010
 * Time: 1:54:16 PM
 */
class SuperPath {
    
    public function __construct(){
    
    }

	const top_level = 9;

        /**
         *
         * @param string $file_id ID файла
         * @param string $addon_path Подпапка (src/src_eps/0-6)
         * @param bool $relative Если необходимо получить относительный путь от корня сайта
         * @return string
         */
	static public function get($file_id, $storePath){

		$id = (int)$file_id;

		if(strlen($id) > self::top_level || $id == 0){
			return null;
		}

		$img_path = self::leading_zero($id, self::top_level);

		do {
			$sub_path = substr($img_path,0,2);
			$img_path = substr($img_path,2);

			$storePath .= '/'.$sub_path;

                        // Если каталога нет, создаём его
			if(!is_dir($storePath)){ 
                mkdir($storePath, 775);
			}

		} while (strlen($img_path) > 3);

		return $storePath.'/'.$file_id;
	}

        /**
         * Вспомогательныя функция, дополняет ID до нулями с переди
         */
	static protected function leading_zero( $aNumber, $intPart, $floatPart=NULL, $dec_point=NULL, $thousands_sep=NULL) {

		//Note: The $thousands_sep has no real function
		// because it will be "disturbed" by plain
		// leading zeros -> the main goal of the function
		$formattedNumber = $aNumber;

		if (!is_null($floatPart)) {
			// without 3rd parameters the "float part"
			// of the float shouldn't be touched
			$formattedNumber = number_format($formattedNumber, $floatPart, $dec_point, $thousands_sep);
		}

		$formattedNumber = str_repeat("0",($intPart + -1 - floor(log10($formattedNumber)))).$formattedNumber;

		return $formattedNumber;
	}
 
    
}
