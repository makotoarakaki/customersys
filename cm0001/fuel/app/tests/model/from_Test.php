<?php
/**
 * 電子書籍『はじめてのフレームワークとしてのFuelPHP 第2版』の一部です。
 *
 * @version    1.0
 * @author     Kenji Suzuki <https://github.com/kenjis>
 * @license    MIT License
 * @copyright  2014 Kenji Suzuki
 * @link       https://github.com/kenjis/fuelphp1st-2nd-contact-from
 * @link       https://github.com/kenjis/fuelphp1st-2nd
 */
/**
 * Model Form class Tests
 * 
 * @group App
 */
class model_from_Test extends DbTestCase
{
	protected $tables = array(
		// テーブル名 => YAMLファイル名
		'froms' => 'from',
	);
	
	public function test_IDでレコードを検索する()
	{
		foreach ($this->from_fixt as $row)
		{
			$from = Model_From::find($row['id']);
			$test = array();
			
			foreach ($row as $field => $value)
			{
				$test = $from->$field;
				$expected = $row[$field];
				$this->assertEquals($expected, $test);
			}
		}
	}
	
	public function test_新規データをテーブルに保存する()
	{
		$data = array(
			'name'       => '藤原義孝',
			'email'      => 'yoshitaka@example.jp',
			'comment'    => '君がため 惜しからざりし 命さえ 長くもがなと 思ひけるかな',
			'ip_address' => '10.11.12.13',
			'user_agent' => 'Mozilla/2.02 (Macintosh; I; PPC)',
		);
		
		$from = Model_From::forge($data);
		
		// 新規データをデータベースに挿入
		$ret = $from->save();
		
		// 挿入されたデータをデータベースから検索
		$from = Model_From::find($from->id);
		
		foreach ($data as $field => $value)
		{
			$this->assertEquals($value, $from[$field]);
		}
	}
}