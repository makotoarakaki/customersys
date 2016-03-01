<?php
//use Orm\Model;

//class Model_Customer extends Model
class Model_Customer extends \Orm\Model
{
	protected static $_properties = array(
		'id',
		'last_name',
		'firs_tname',
		'ph_family_name',
		'ph_name',
		'sex',
		'post_code',
		'adress1',
		'adress2',
		'adress3',
		'phone',
		'age',
		'birthday',
		'mail',
		'person_id',
		'personname',
		'last_visit_date',
		'biko',
		'created_at',
		'updated_at',
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => false,
		),
	);

	public static function validate($factory, $data)
	{
		$val = Validation::forge($factory);
		$val->add_callable('Validation_Japanese');
		$val->add_callable('Validation_Util');

		$val->add_field('last_name', '顧客姓', 'required|max_length[50]');
		$val->add_field('firs_tname', '顧客名', 'required|max_length[50]');
		$val->add_field('ph_family_name', 'フリガナ姓', 'katakana|max_length[50]');
		$val->add_field('ph_name', 'フリガナ名', 'katakana|max_length[50]');
		$val->add_field('sex', '性別', 'valid_string[numeric]');
		$val->add_field('post_code', '郵便番号', 'nozenkaku|max_length[10]');
		$val->add_field('adress1', '住所１', 'max_length[50]');
		$val->add_field('adress2', '住所２', 'max_length[50]');
		$val->add_field('adress3', '住所３', 'max_length[50]');
		$val->add_field('phone', '電話番号', 'nozenkaku|max_length[15]');
		$val->add_field('age', '年齢', 'alphanum|max_length[3]');
		
		// POSTデータ（$data）が空じゃなかったら
		if (isset($data['gengo'])) {
			$val->add_field('gengo', '元号', 'empty_gengo[' .$data['birthday_year'].  ']|empty_gengo[' .$data['birthday_month'].  ']|empty_gengo[' .$data['birthday_day'].  ']');
			$val->add_field('birthday_year', '誕生日 年', 'alphanum|empty_nengappi[' .$data['gengo'].  ']');
			$val->add_field('birthday_month', '誕生日 月', 'alphanum|empty_nengappi[' .$data['gengo'].  ']');
			$val->add_field('birthday_day', '誕生日 日', 'alphanum|empty_nengappi[' .$data['gengo'].  ']');			
		}
		$val->add_field('mail', 'メールアドレス', 'valid_email|max_length[50]');
		$val->add_field('person_id', '担当者', 'required');
//		$val->add_field('person', '担当者', 'required|max_length[50]');
		$val->add_field('last_visit_date', '最終来店日', 'nozenkaku|max_length[11]');
		$val->add_field('biko', '備考', 'max_length[500]');

		return $val;
	}
	
	/********************************

	 * 生年月日を元号に変換

	 *********************************/
	 public static function gengo($date){

		//日付を'/'で区切って変数に変換
		if($date && list($y,$m,$d)=explode('/',$date)){

		   if($date >= "1989/01/08"){

		   $gengo='heisei';

		   $y=$y-1988;

		   $m=sprintf("%02d",$m);

		   $d=sprintf("%02d",$d);

		} elseif($date >= "1926/12/25") {

		   $gengo='showa';

		   $y=$y-1925;

		   $m=sprintf("%02d",$m);

		   $d=sprintf("%02d",$d);

		} elseif($date >= "1912/07/30") {

		   $gengo='taisho';

		   $y=$y-1911;

		   $m=sprintf("%02d",$m);

		   $d=sprintf("%02d",$d);

		} else {

		   $gengo='seireki';

		   //$y=$y;

		   $m=sprintf("%02d",$m);

		   $d=sprintf("%02d",$d);

		}

			return(array('gengo'=>$gengo,'year'=>$y,'month'=>$m,'day'=>$d));

		}

	 }
	 
	 /********************************

	 * 生年月日を西暦に変換

	 *********************************/
	 public static function seireki($gengo,$y,$m,$d){

		if($gengo == 'heisei'){

			$y=$y+1988;

		}

		elseif($gengo =='showa'){

			$y=$y+1925;

		}

		elseif($gengo == 'taisho'){

			$y=$y+1911;

		}

		return sprintf("%04d/%02d/%02d",$y,$m,$d);

		}
		
	 /********************************

	 * 担当者リストを取得

	 *********************************/
	 public static function get_PersonList($empty = null) {
		$result = array();
		if (isset($empty)) {
			$result[0] = $empty;
		}
		$query = \DB::select(array('id','person_id'), 'personname')->from('persons');
		$query->order_by('person_id', 'asc');
		$selects = $query->execute()->as_array('person_id', 'personname');
		foreach ($selects as $id => $value) {
		  $result[$id] = $value;		  
		}
		return $result;
    }

	/********************************

	 * 担当者名を取得

	 *********************************/
	 public static function get_PersonName($id = null) {
		$query = DB::select('personname')->from('persons')->where('id', '=', $id)->execute();
		$person = Model_Person::query()->select('personname')->where('id', $id)->get_one();

		return $person->find();
    }
}
