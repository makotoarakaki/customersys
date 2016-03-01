<?php

/**
 * 独自バリデーションを追加
 */
class Validation_Japanese
{
    /**
     * 
     */
    public static function _validation_hiragana($val)
    {
        if( empty($val) ){ return true; }
        if( static::_validation_hirakata($val) ){
            return mb_convert_kana($val, "sHcV");
        }

        return false;
    }

    /**
     * 
     */
    public static function _validation_katakana($val)
    {
		mb_regex_encoding("UTF-8");
		if( empty($val) ){ return true; }
		if (preg_match("/^[ァ-ヶー]+$/u", $val) === 1 || preg_match("/^[ｦ-ﾟ]+$/u", $val) === 1) {
			return true;
		} else {
			return false;
		}
//        return preg_match("/^[ァ-ヶー]+$/u", $val) === 1;
		/*
		if( empty($val) ){ return true; }
        if( static::_validation_hirakata($val) ){
            return mb_convert_kana($val, "sKVC");
        }

        return false;
		 * 
		 */
    }

    /**
     * 
     */
    public static function _validation_hirakata($val)
    {
        if( empty($val) ){ return true; }

        mb_regex_encoding("UTF-8");
        if (preg_match("/^[ 　ぁ-んァ-ヶーｦ-ﾟ｡-ﾟ0-9０-９]+$/u", $val)) {
            return true;
        }

        return false;
    }
	
    /**
     * 半角英数以外はエラー
     */
    public static function _validation_nozenkaku($val)
    {
        if( empty($val) ){ return true; }

        mb_regex_encoding("UTF-8");
        if (preg_match("/^[a-zA-Z0-9-\/\.\@]+$/", $val)) {
            return true;
        }

        return false;
    }

	// 半角英数字チェック
    public static function _validation_alphanum($data)
    {
        if(!empty($data)) {
            if (preg_match("/^[a-zA-Z0-9]+$/", $data)) {
                return true;
            }
            else {
                return false;
            }
        }
        return true;
    }
}