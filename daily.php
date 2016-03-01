<?php
class Controller_Admin_Daily extends Controller_Admin
{
	private $_weekday = array('日','月','火','水','木','金','土');

	public function action_index()
	{		
		$today = date('Ymd');
		// 日別のデータを取得
		$daily_query = DB::select()->from('dailys')->where('today', $today)->order_by('no', 'desc');
		$dailys = $daily_query->execute()->as_array();

		$daily_data = Model_Daily::to_grid_table_data($dailys);
		$this->template->set_global('daily_data', $daily_data, false); // 画面にデータを設定

		// 担当者のデータを取得
		$person_query = DB::select()->from('persons');
		$persons = $person_query->execute()->as_array();
		// 担当者データを連結
		$person_data = Model_Daily::to_grid_list_data($persons, 'personname');
		$this->template->set_global('person_data', $person_data, false); // 画面にデータを設定

		// 商品のデータを取得
		$item_query = DB::select()->from('items');
		$items = $item_query->execute()->as_array();
		if (empty($persons) or empty($items)) {
			Session::set_flash('error', e('担当者、商品のデータを登録してください。'));						
		}
		// 商品データを連結
		$item_data = Model_Daily::to_grid_list_data($items, 'itemname');
		$this->template->set_global('item_data', $item_data, false); // 画面にデータを設定
		
		$this->template->title = "日別管理　　".date('Y年m月d日').'('.$this->_weekday[date('w')].')';
		$this->template->content = View::forge('admin/daily/index');
	}

	public function action_create()
	{
		$param = Input::post('param');
		if (Input::method() == 'POST')
		{
			// 追加ボタン押下時の処理
			if ($param == 'add') {
					// noを取得して１加算する。
					$arraymaxno = DB::query('select MAX(no) as no FROM cm_dailys where today = '.date('Ymd'))->execute()->current();
					$maxno = 1;
					if (!is_null($arraymaxno['no'])) {
						$maxno = (Int) $arraymaxno['no'] + 1;
					}
					// 時間を取得をする。
					$datetime = Input::post('datetime');
					if (!empty($datetime)) {
						$datetime =Input::post('datetime');			
					}
					
					// 担当者マスタより担当者を取得
					$personname = DB::select('personname')->from('persons')->where('id', '=', Input::post('personname'))->execute()->current();
					// 商品マスタより商品名、単価を取得
					$items = DB::select('itemname', 'unit')->from('items')->where('id', '=', Input::post('itemname'))->execute()->current();
					$daily = Model_Daily::forge(array(
						'no' => $maxno,
						'customerid' => 0,
						'customername' => Input::post('customername'),
						'personname' => $personname,
						'datetime' => $datetime,
						'itemname' => $items['itemname'],
						'biko' => Input::post('biko'),
						'today' => date('Ymd',strtotime(Input::post('date'))),
					));

					$dailysave = $daily->save();
					// 来店履歴を登録
					$arraymaxid = DB::query('select MAX(id) as id FROM cm_dailys')->execute()->current();
					$maxid = 0;
					if (!is_null($arraymaxid['id'])) {
						$maxid = (Int) $arraymaxid['id'];
					}
					$visithistory = Model_Visithistory::forge(array(
						'dailyid' => $maxid,
						'customerid' => 0,
						'customername' => Input::post('customername'),
						'visitdate' => date('Ymd',strtotime(Input::post('date'))),
						'amount' => $items['unit'],
						'personname' => $personname,
						'memo' => Input::post('biko')
					));
					
					$historysave = $visithistory->save();
					// 顧客管理に追加
					$customer = Model_Customer::forge(array(
						'last_name' => Input::post('customername'),
						'firs_tname' => 'クイック',
						'person_id' => Input::post('personname'),
						'personname' => $personname,
						'biko' => Input::post('biko'),
					));

					if (($dailysave) and ($historysave) and ($customer->save()))
					{
						Session::set_flash('success', e('新しい予約 '.Input::post('itemname').' を追加しました。'));
					}

					else
					{
						Session::set_flash('error', e('予約の追加に失敗しました。'));
					}

			// 更新ボタンが押下時の処理
			} else if ($param == 'edit') {

				// 時間の編集をする。
				$datetime = Input::post('datetime');
				if (!empty($datetime)) {
					$datetime = Input::post('datetime');			
				}
				// 担当者マスタより担当者を取得
				$personname = DB::select('personname')->from('persons')->where('id', '=', Input::post('personname'))->execute()->current();
				// 商品マスタより商品名、単価を取得
				$items = DB::select('itemname', 'unit')->from('items')->where('id', '=', Input::post('itemname'))->execute()->current();

				$id = Input::post('id');
				$daily = Model_Daily::find($id);

				$daily->customername = Input::post('customername');
				$daily->personname = $personname;
				$daily->datetime = $datetime;
				$daily->itemname = $items['itemname'];
				$daily->biko = Input::post('biko');
				$daily->today = date('Ymd',strtotime(Input::post('date')));

				// 来店履歴を取得
				$visithistory = Model_Visithistory::find('first', array('where' => array(array('dailyid', '=', $id))));				
				// 来店履歴を更新
				$visithistory->customername = Input::post('customername');
				$visithistory->visitdate = date('Ymd',strtotime(Input::post('date')));
				$visithistory->itemname = $items['itemname'];
				$visithistory->amount = $items['unit'];
				$visithistory->personname = $personname;
				$visithistory->memo = Input::post('biko');
				
				// 日別管理と来店履歴の更新
				if ($daily->save() and $visithistory->save())
				{
					Session::set_flash('success', e('更新しました。'));
				}

				else
				{
					Session::set_flash('error', e('更新に失敗しました。'));
				}
				
			// 削除ボタン押下時の処理
			} else {
				$id = Input::post('id');
				if ($daily = Model_Daily::find($id))
				{
					$daily->delete();

					Session::set_flash('success', e('予約情報を削除しました。'));
				}

				else
				{
					Session::set_flash('error', e('予約情報の削除に失敗しました。'));
				}
				if ($visithistory = Model_Visithistory::find('first', array('where' => array(array('dailyid', '=', $id)))))
				{
					$visithistory->delete();

					Session::set_flash('success', e('履歴情報を削除しました。'));
				}

				else
				{
					Session::set_flash('error', e('履歴情報の削除に失敗しました。'));
				}

			}
		}

		$this->template->title = "日別管理";
		$this->template->content = View::forge('admin/daily/index');
	}

	public function action_search()
	{
		// 担当者リストを取得
		$person_selects = Model_Customer::get_PersonList('担当者');	
		$this->template->set_global('person_selects', $person_selects, false);

		// 検索条件の取得
		$serch_kana = Input::post('search_kana');
		$person = Input::post('search_person');
		$last_name = Input::post('search_last_name');
		$firs_tname = Input::post('search_firs_tname');
		
		if ($serch_kana == 1) {
			// 担当者が選択　かつ　顧客性と顧客名が入力
			if (!empty($person) and (!empty($last_name) and !empty($firs_tname))) {
				$data['customers'] = Model_Customer::query()
					->where('person_id', $person)
					->and_where_open()
						->where('last_name', 'like', '%'.$last_name.'%')
						->where('firs_tname', 'like', '%'.$firs_tname.'%')
					->and_where_close()
					->order_by('updated_at', 'DESC')->get();
			// 担当者が選択　かつ　顧客性が入力
			} else if (!empty($person) and !empty($last_name)) {
				$data['customers'] = Model_Customer::query()
					->where('person_id', $person)
					->where('last_name', 'like', '%'.$last_name.'%')
					->order_by('updated_at', 'DESC')->get();
			// 担当者が選択　かつ　顧客名が入力
			} else if (!empty($person) and !empty($firs_tname)) {
				$data['customers'] = Model_Customer::query()
					->where('person_id', $person)
					->where('firs_tname', 'like', '%'.$firs_tname.'%')
					->order_by('updated_at', 'DESC')->get();
			// 顧客性と顧客名が入力
			} else if (!empty($last_name) and !empty($firs_tname)) {
				$data['customers'] = Model_Customer::query()
					->where('last_name', 'like', '%'.$last_name.'%')
					->where('firs_tname', 'like', '%'.$firs_tname.'%')
					->order_by('updated_at', 'DESC')->get();
			// 顧客性が入力
			} else if (!empty($last_name)) {
				$data['customers'] = Model_Customer::query()
					->where('last_name', 'like', '%'.$last_name.'%')
					->order_by('updated_at', 'DESC')->get();
			// 顧客名が入力
			} else if (!empty($firs_tname)) {
				$data['customers'] = Model_Customer::query()
					->where('firs_tname', 'like', '%'.$firs_tname.'%')
					->order_by('updated_at', 'DESC')->get();
			// 担当者が入力
			} else if (!empty($person)) {
				$data['customers'] = Model_Customer::query()
					->where('person_id', $person)
					->order_by('updated_at', 'DESC')->get();
			// 検索条件なし
			} else {
				$_perpage = 10;
				$data = array();
				$data['page'] = Input::get('page');
				$cnt = DB::select()->from('customers')->execute()->count();

				$pagination = \Pagination::forge('default', array(
								'wrapper' => "<div class=\"pagination\">\n\t{pagination}\n</div>\n",
								'uri_segment' => 'page',
								'total_items' =>  $cnt,
								'per_page' => $_perpage,
								'name' => 'bootstrap'
				));

				$data['paginate'] = \Pagination::instance('default')->render();

				if (\Pagination::get('current_page') == 1)
				{
					$data['offset'] = 0;
				}
				else
				{
					$data['offset'] = ($data['page'] -1) * $_perpage;
				}

				$data['customers'] = Model_Customer::query()
					->order_by('updated_at', 'DESC')
					->rows_offset($data['offset'])
					->rows_limit($_perpage)
				->get();			

				$this->template->set_global('total_customers', $pagination->total_items, false);
			}
		} else {
				$kana_last = mb_convert_kana(Input::post('search_last_name'), "k", "UTF-8");
				$kana_first = mb_convert_kana(Input::post('search_firs_tname'), "k", "UTF-8");

			
			// 担当者が選択　かつ　顧客性と顧客名が入力
			if (!empty($person) and (!empty($kana_last) and !empty($kana_first))) {
				$data['customers'] = Model_Customer::query()
					->where('person_id', $person)
					->and_where_open()
						->where('ph_family_name', 'like', '%'.$kana_last.'%')
						->where('ph_name', 'like', '%'.$kana_first.'%')
					->and_where_close()
					->order_by('updated_at', 'DESC')->get();
			// 担当者が選択　かつ　顧客性が入力
			} else if (!empty($person) and !empty($kana_last)) {
				$data['customers'] = Model_Customer::query()
					->where('person_id', $person)
					->where('ph_family_name', 'like', '%'.$kana_last.'%')
					->order_by('updated_at', 'DESC')->get();
			// 担当者が選択　かつ　顧客名が入力
			} else if (!empty($person) and !empty($kana_first)) {
				$data['customers'] = Model_Customer::query()
					->where('person_id', $person)
					->where('ph_name', 'like', '%'.$kana_first.'%')
					->order_by('updated_at', 'DESC')->get();
			// 顧客性と顧客名が入力
			} else if (!empty($kana_last) and !empty($kana_first)) {
				$data['customers'] = Model_Customer::query()
					->where('ph_family_name', 'like', '%'.$kana_last.'%')
					->where('ph_name', 'like', '%'.$kana_first.'%')
					->order_by('updated_at', 'DESC')->get();
			// 顧客性が入力
			} else if (!empty($kana_last)) {
				$data['customers'] = Model_Customer::query()
					->where('ph_family_name', 'like', '%'.$kana_last.'%')
					->order_by('updated_at', 'DESC')->get();
			// 顧客名が入力
			} else if (!empty($kana_first)) {
				$data['customers'] = Model_Customer::query()
					->where('ph_name', 'like', '%'.$kana_first.'%')
					->order_by('updated_at', 'DESC')->get();
			// 担当者が入力
			} else if (!empty($person)) {
				$data['customers'] = Model_Customer::query()
					->where('person_id', $person)
					->order_by('updated_at', 'DESC')->get();
			// 検索条件なし
			} else {
				$_perpage = 10;
				$data = array();
				$data['page'] = Input::get('page');
				$cnt = DB::select()->from('customers')->execute()->count();

				$pagination = \Pagination::forge('default', array(
								'wrapper' => "<div class=\"pagination\">\n\t{pagination}\n</div>\n",
								'uri_segment' => 'page',
								'total_items' =>  $cnt,
								'per_page' => $_perpage,
								'name' => 'bootstrap'
				));

				$data['paginate'] = \Pagination::instance('default')->render();

				if (\Pagination::get('current_page') == 1)
				{
					$data['offset'] = 0;
				}
				else
				{
					$data['offset'] = ($data['page'] -1) * $_perpage;
				}

				$data['customers'] = Model_Customer::query()
					->order_by('updated_at', 'DESC')
					->rows_offset($data['offset'])
					->rows_limit($_perpage)
				->get();			

				$this->template->set_global('total_customers', $pagination->total_items, false);
			}
		}
/*		// 担当者が選択　かつ　顧客性と顧客名が入力
		if (!empty($person) and (!empty($last_name) and !empty($firs_tname))) {
			$data['customers'] = Model_Customer::query()
				->where('person_id', $person)
				->and_where_open()
					->where('last_name', 'like', '%'.$last_name.'%')
					->where('firs_tname', 'like', '%'.$firs_tname.'%')
				->and_where_close()
				->order_by('updated_at', 'DESC')->get();
		// 担当者が選択　かつ　顧客性が入力
		} else if (!empty($person) and !empty($last_name)) {
			$data['customers'] = Model_Customer::query()
				->where('person_id', $person)
				->where('last_name', 'like', '%'.$last_name.'%')
				->order_by('updated_at', 'DESC')->get();
		// 担当者が選択　かつ　顧客名が入力
		} else if (!empty($person) and !empty($firs_tname)) {
			$data['customers'] = Model_Customer::query()
				->where('person_id', $person)
				->where('firs_tname', 'like', '%'.$firs_tname.'%')
				->order_by('updated_at', 'DESC')->get();
		// 顧客性と顧客名が入力
		} else if (!empty($last_name) and !empty($firs_tname)) {
			$data['customers'] = Model_Customer::query()
				->where('last_name', 'like', '%'.$last_name.'%')
				->where('firs_tname', 'like', '%'.$firs_tname.'%')
				->order_by('updated_at', 'DESC')->get();
		// 顧客性が入力
		} else if (!empty($last_name)) {
			$data['customers'] = Model_Customer::query()
				->where('last_name', 'like', '%'.$last_name.'%')
				->order_by('updated_at', 'DESC')->get();
		// 顧客名が入力
		} else if (!empty($firs_tname)) {
			$data['customers'] = Model_Customer::query()
				->where('firs_tname', 'like', '%'.$firs_tname.'%')
				->order_by('updated_at', 'DESC')->get();
		// 担当者が入力
		} else if (!empty($person)) {
			$data['customers'] = Model_Customer::query()
				->where('person_id', $person)
				->order_by('updated_at', 'DESC')->get();
		// 検索条件なし
		} else {
			$_perpage = 10;
			$data = array();
			$data['page'] = Input::get('page');
			$cnt = DB::select()->from('customers')->execute()->count();

			$pagination = \Pagination::forge('default', array(
							'wrapper' => "<div class=\"pagination\">\n\t{pagination}\n</div>\n",
							'uri_segment' => 'page',
							'total_items' =>  $cnt,
							'per_page' => $_perpage,
							'name' => 'bootstrap'
			));

			$data['paginate'] = \Pagination::instance('default')->render();

			if (\Pagination::get('current_page') == 1)
			{
				$data['offset'] = 0;
			}
			else
			{
				$data['offset'] = ($data['page'] -1) * $_perpage;
			}

			$data['customers'] = Model_Customer::query()
				->order_by('updated_at', 'DESC')
				->rows_offset($data['offset'])
				->rows_limit($_perpage)
			->get();			
	
			$this->template->set_global('total_customers', $pagination->total_items, false);
		} */


		$this->template->title = "日別管理"; //default
		$this->template->content = View::forge('admin/daily/search', $data);//default
	}
	
	public function action_reflec($id = null)
	{
		if ( ! $customer = Model_Customer::find($id))
		{
			Session::set_flash('error', '顧客データが見つかりませんでした #'.$id);
			Response::redirect('admin/customer');
		} else {
			$customerid = $customer['id'];
			$customername = $customer['last_name'].'  '.$customer['firs_tname'];
			$personid = $customer['person_id'];
			$personname = $customer['personname'];
		}

		// noを取得して１加算する。
		$arraymaxno = DB::query('select MAX(no) as no FROM cm_dailys where today = '.date('Ymd'))->execute()->current();
		$maxno = 1;
		if (!is_null($arraymaxno['no'])) {
			$maxno = (Int) $arraymaxno['no'] + 1;
		}

		$daily = Model_Daily::forge(array(
			'no' => $maxno,
			'customerid' => $customerid,
			'customername' => $customername,
			'personid' => $personid,
			'personname' => $personname,
			'today' => date('Ymd'),
		));
		$dailysave = $daily->save();
		// 来店履歴を登録
		$arraymaxid = DB::query('select MAX(id) as id FROM cm_dailys')->execute()->current();
		$maxid = 0;
		if (!is_null($arraymaxid['id'])) {
			$maxid = (Int) $arraymaxid['id'];
		}
		$visithistory = Model_Visithistory::forge(array(
			'dailyid' => $maxid,
			'customerid' => $customerid,
			'customername' => $customername,
			'visitdate' => date('Ymd'),
			'amount' => 0,
			'personname' => $personname,
		));

		if (($dailysave) and ($visithistory and $visithistory->save()))
		{
			Session::set_flash('success', e('新しい予約 '.Input::post('itemname').' を追加しました。'));
			Response::redirect('admin/daily');
		}

		else
		{
			Session::set_flash('error', e('予約の追加に失敗しました。'));
		}

		$this->template->title = "日別管理";
		$this->template->content = View::forge('admin/daily/index');
	}

}
