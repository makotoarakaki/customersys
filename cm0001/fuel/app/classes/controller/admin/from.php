<?php
class Controller_Admin_From extends Controller_Admin
{

	public function action_index()
	{
		$data['froms'] = Model_From::find('all');
		$this->template->title = "Froms";
		$this->template->content = View::forge('admin/from/index', $data);

	}

	public function action_view($id = null)
	{
		$data['from'] = Model_From::find($id);

		$this->template->title = "From";
		$this->template->content = View::forge('admin/from/view', $data);

	}

	public function action_create()
	{
		if (Input::method() == 'POST')
		{
			$val = Model_From::validate('create');

			if ($val->run())
			{
				$from = Model_From::forge(array(
					'name' => Input::post('name'),
					'email' => Input::post('email'),
					'comment' => Input::post('comment'),
					'ip_address' => Input::post('ip_address'),
					'user_agent' => Input::post('user_agent'),
				));

				if ($from and $from->save())
				{
					Session::set_flash('success', e('Added from #'.$from->id.'.'));

					Response::redirect('admin/from');
				}

				else
				{
					Session::set_flash('error', e('Could not save from.'));
				}
			}
			else
			{
				Session::set_flash('error', $val->error());
			}
		}

		$this->template->title = "Froms";
		$this->template->content = View::forge('admin/from/create');

	}

	public function action_edit($id = null)
	{
		$from = Model_From::find($id);
		$val = Model_From::validate('edit');

		if ($val->run())
		{
			$from->name = Input::post('name');
			$from->email = Input::post('email');
			$from->comment = Input::post('comment');
			$from->ip_address = Input::post('ip_address');
			$from->user_agent = Input::post('user_agent');

			if ($from->save())
			{
				Session::set_flash('success', e('Updated from #' . $id));

				Response::redirect('admin/from');
			}

			else
			{
				Session::set_flash('error', e('Could not update from #' . $id));
			}
		}

		else
		{
			if (Input::method() == 'POST')
			{
				$from->name = $val->validated('name');
				$from->email = $val->validated('email');
				$from->comment = $val->validated('comment');
				$from->ip_address = $val->validated('ip_address');
				$from->user_agent = $val->validated('user_agent');

				Session::set_flash('error', $val->error());
			}

			$this->template->set_global('from', $from, false);
		}

		$this->template->title = "Froms";
		$this->template->content = View::forge('admin/from/edit');

	}

	public function action_delete($id = null)
	{
		if ($from = Model_From::find($id))
		{
			$from->delete();

			Session::set_flash('success', e('Deleted from #'.$id));
		}

		else
		{
			Session::set_flash('error', e('Could not delete from #'.$id));
		}

		Response::redirect('admin/from');

	}

}
