<?php
class Controller_Admin_Customer extends Controller_Admin
{
	private $_classname = 'customer';
	private $_weekday = array('日','月','火','水','木','金','土');

	public function action_index()
	{
		// 担当者リストを取得
		$person_selects = Model_Customer::get_PersonList('担当者');	
		$this->template->set_global('person_selects', $person_selects, false);

		// 検索条件の取得
		$serch_kana = Input::post('search_kana');
Log::debug("☆☆☆☆☆☆☆☆ kana", $serch_kana);
		$person = Input::post('search_person');
		$last_name = Input::post('search_last_name');
		$firs_tname = Input::post('search_firs_tname');
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
			$data['page'] = \Input::get('page');
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

		$this->template->title = "顧客管理"; //default
		$this->template->content = View::forge('admin/customer/index', $data);//default
	}

	public function action_view($id = null)
	{
		is_null($id) and Response::redirect('admin/customer');

		if ( ! $data['customer'] = Model_Customer::find($id))
		{
			Session::set_flash('error', '顧客情報がありませんでした。'.$id);
			Response::redirect('admin/customer');
		}

		$this->template->title = "顧客管理";
		$this->template->content = View::forge('admin/customer/view', $data);

	}

	public function action_create()
	{
		// 担当者リストを取得
		$person_selects = Model_Customer::get_PersonList('選択してください。');	
		$this->template->set_global('person_selects', $person_selects, false);

		if (Input::method() == 'POST')
		{
			$val = Model_Customer::validate('create', $_POST);
			// 生年月日データ作成
			$birthday = Model_Customer::seireki(Input::post('gengo'),Input::post('birthday_year'),Input::post('birthday_month'),Input::post('birthday_day'));

			if ($val->run())
			{
				$customer = Model_Customer::forge(array(
					'last_name' => Input::post('last_name'),
					'firs_tname' => Input::post('firs_tname'),
					'ph_family_name' => mb_convert_kana(Input::post('ph_family_name'), "k", "UTF-8"),
					'ph_name' =>  mb_convert_kana(Input::post('ph_name'), "k", "UTF-8"),
					'sex' => Input::post('sex'),
					'post_code' => Input::post('post_code'),
					'adress1' => Input::post('adress1'),
					'adress2' => Input::post('adress2'),
					'adress3' => Input::post('adress3'),
					'phone' => Input::post('phone'),
					'age' => Input::post('age'),
					'gengo' => Input::post('gengo'),					
					'birthday_year' => Input::post('birthday_year'),
					'birthday_month' => Input::post('birthday_month'),
					'birthday_day' => Input::post('birthday_day'),
					'birthday' => $birthday,
					'mail' => Input::post('mail'),
					'person_id' => Input::post('person_id'),
					'personname' => Input::post('personname'),
					'last_visit_date' => Input::post('last_visit_date'),
					'biko' => Input::post('biko'),
				));

				if ($customer and $customer->save())
				{
					Session::set_flash('success', '顧客情報を追加しました。');

					Response::redirect('admin/customer');
				}

				else
				{
					Session::set_flash('error', 'Could not save customer.');
				}
			}
			else
			{
				Session::set_flash('error', $val->error());
			}
		}
		$this->template->title = "顧客管理";
		$this->template->content = View::forge('admin/customer/create');
	}

	public function action_edit($id = null)
	{
		is_null($id) and Response::redirect('admin/customer');

		// 担当者リストを取得
		$person_selects = Model_Customer::get_PersonList('選択してください。');
		$this->template->set_global('person_selects', $person_selects, false);

		if ( ! $customer = Model_Customer::find($id))
		{
			Session::set_flash('error', '更新するデータが見つかりませんでした #'.$id);
			Response::redirect('admin/customer');
		}
		
		// バリデーション
		$val = Model_Customer::validate('edit', $customer);
		
		// 誕生日を元号・年・月・日で切り分ける
		$birthday = Model_Customer::gengo($customer->birthday);
		// 切り分けた誕生日をそれぞれにセット
		$customer->gengo = $birthday['gengo'];
		$customer->birthday_year = $birthday['year'];
		$customer->birthday_month = $birthday['month'];
		$customer->birthday_day = $birthday['day'];

		if ($val->run())
		{
			$customer->last_name = Input::post('last_name');
			$customer->firs_tname = Input::post('firs_tname');
			$customer->ph_family_name = mb_convert_kana(Input::post('ph_family_name'), "k", "UTF-8");
			$customer->ph_name = mb_convert_kana(Input::post('ph_name'), "k", "UTF-8");
			$customer->sex = Input::post('sex');
			$customer->post_code = Input::post('post_code');
			$customer->adress1 = Input::post('adress1');
			$customer->adress2 = Input::post('adress2');
			$customer->adress3 = Input::post('adress3');
			$customer->phone = Input::post('phone');
			$customer->age = Input::post('age');
			$customer->birthday = Input::post('birthday');
			$customer->mail = Input::post('mail');
			$customer->person_id = Input::post('person_id');
			$customer->personname = Input::post('personname');
			$customer->last_visit_date = Input::post('last_visit_date');
			$customer->biko = Input::post('biko');

			if ($customer->save())
			{
				Session::set_flash('success', $customer->last_name. " " .$customer->firs_tname.' 様の顧客情報を更新しました。');

				Response::redirect('admin/customer');
			}

			else
			{
				Session::set_flash('error', $customer->last_name. " " .$customer->firs_tname.' 様の顧客情報の更新に失敗しました。');
			}
		}

		else
		{
			if (Input::method() == 'POST')
			{
				$customer->last_name = $val->validated('last_name');
				$customer->firs_tname = $val->validated('firs_tname');
				$customer->ph_family_name = $val->validated('ph_family_name');
				$customer->ph_name = $val->validated('ph_name');
				$customer->sex = $val->validated('sex');
				$customer->post_code = $val->validated('post_code');
				$customer->adress1 = $val->validated('adress1');
				$customer->adress2 = $val->validated('adress2');
				$customer->adress3 = $val->validated('adress3');
				$customer->phone = $val->validated('phone');
				$customer->age = $val->validated('age');
				$customer->birthday = $val->validated('birthday');
				$customer->mail = $val->validated('mail');
				$customer->person_id = $val->validated('person_id');
				$customer->personname = $val->validated('personname');
				$customer->last_visit_date = $val->validated('last_visit_date');
				$customer->biko = $val->validated('biko');

				Session::set_flash('error', $val->error());
			}

			$this->template->set_global('customer', $customer, false);
		}

		$this->template->title = "顧客管理";
		$this->template->content = View::forge('admin/customer/edit');
	}

	public function action_delete($id = null)
	{
		is_null($id) and Response::redirect('admin/customer');

		if ($customer = Model_Customer::find($id))
		{
			$customer->delete();

			Session::set_flash('success', '顧客情報を削除しました。'.$id);
		}

		else
		{
			Session::set_flash('error', 'Could not delete customer #'.$id);
		}

		Response::redirect('admin/customer');

	}
	
	public function action_csvimport()
	{
		$this->template->title = "顧客管理";
		$this->template->content = View::forge('admin/customer/csvimport');
	}

	public function action_csvupload()
	{
		Model_Csvimport::import(null);
		$this->template->title = "顧客管理";
		$this->template->content = View::forge('admin/customer/csvimport');
	}
	
	/**
	 * CSVエクスポート画面表示
	 */
	public function action_csvexport()
	{
		// 担当者リストを取得
		$person_selects = Model_Customer::get_PersonList('担当者');	
		$this->template->set_global('person_selects', $person_selects, false);

		// 検索条件の取得
		$person = Input::post('search_person');

		if (!empty($person)) {
			$data['customers'] = Model_Customer::query()
				->where('person_id', $person)
				->order_by('updated_at', 'DESC')->get();
		// 検索条件なし
		} else {
			$_perpage = 50;
			$data = array();

			$data['page'] = \Input::get('page');
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

		$this->template->title = "顧客管理"; //default
		$this->template->content = View::forge('admin/customer/csvexport', $data);//default
	}
	
	public function action_tocsv()
	{
		// 検索条件の取得
		$person = Input::post('hsearch_person');

		if (!empty($person)) {
			$data['customers'] = Model_Customer::query()
				->where('person_id', $person)
				->order_by('updated_at', 'DESC')->get();
		// 検索条件なし
		} else {

			$_perpage = 50;
			$data = array();
			$data['page'] = \Input::get('page');
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
		}
		
		// CSVファイル名
		$filename = 'customers'.date('Ymdhis');
		// CSVデータ
		$csv_data[] = array('顧客姓', '顧客名', 'フリガナ姓', 'フリガナ名', '性別', '担当者', '郵便番号', '住所１', '住所２', '住所３', '電話番号', '年齢', '生年月日', 'メールアドレス', '最終来店日', '備考', '更新日時');
		
		// チェックボックスのチェックされているＩＤを取得
		$selects = (array) Input::post('select', '[]');
		$i = 0;
		foreach ($data['customers'] as $item) {
			if (empty($selects[$i])) {
				$csv_data[] = 
					array(
						$item->last_name, 
						$item->firs_tname,
						$item->ph_family_name,
						$item->ph_name,
						$item->sex,
						$item->personname,
						$item->post_code,
						$item->adress1,
						$item->adress2,
						$item->adress3,
						$item->phone,
						$item->age,
						$item->birthday,
						$item->mail,
						$item->last_visit_date,
						$item->biko,
						Date::forge($item->updated_at)->format("%Y/%m/%d %H:%M")
					);					
			} else {
				if ($selects[$i] !== $item->id) {
					$csv_data[] = 
						array(
							$item->last_name, 
							$item->firs_tname,
							$item->ph_family_name,
							$item->ph_name,
							$item->sex,
							$item->personname,
							$item->post_code,
							$item->adress1,
							$item->adress2,
							$item->adress3,
							$item->phone,
							$item->age,
							$item->birthday,
							$item->mail,
							$item->last_visit_date,
							$item->biko,
							Date::forge($item->updated_at)->format("%Y/%m/%d %H:%M")
						);	
				} else {
					if ($i <= count($selects[$i])) {
						$i++;					
					}
				}
				
			}
		}
		// CSV出力
 		Model_Csvexport::export_csv($csv_data, $filename);
	}
	
	public function action_history($id = null)
	{
		$_perpage = 10;
		$data = array();
		$data['page'] = Input::get('page');
		$cnt = DB::select()->from('visithistorys')->where('customerid', $id)->execute()->count();

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

		$data['visithistorys'] = Model_Visithistory::query()
			->where('customerid', $id)
			->order_by('updated_at', 'DESC')
			->rows_offset($data['offset'])
			->rows_limit($_perpage)
		->get();			

		$this->template->set_global('total_visithistorys', $pagination->total_items, false);
		$this->template->set_global('weekday', $this->_weekday, false);
		$this->template->title = "来店履歴管理"; //default
		$this->template->content = View::forge('admin/customer/history', $data);//default
	}
	
	public function action_historyedit($id = null)
	{
		is_null($id) and Response::redirect('admin/customer/history');

		if ( ! $history = Model_Visithistory::find($id))
		{
			Session::set_flash('error', '更新するデータが見つかりませんでした #'.$id);
			Response::redirect('admin/customer/history');
		}
		
		// バリデーション
		$val = Model_Visithistory::validate('edit', $history);

		if ($val->run())
		{
			$history->memo = Input::post('memo');

			if ($history->save())
			{
				Session::set_flash('success', '来店履歴情報を更新しました。');

				Response::redirect('admin/customer/history/'.$history->customerid);
			}

			else
			{
				Session::set_flash('error', '来店履歴情報の更新に失敗しました。');
			}
		}

		else
		{
			if (Input::method() == 'POST')
			{
				$history->memo = $val->validated('memo');

				Session::set_flash('error', $val->error());
			}

			$this->template->set_global('history', $history, false);
		}

		$this->template->title = "来店履歴管理";
		$this->template->content = View::forge('admin/customer/historyedit');
	}

}
