<h2>顧客一覧</h2>
<br>
<div class="row">
<?php echo Form::open(array("class"=>"form-inline")); ?>
	<div class="form-group col-xs-2">
		<?php echo Form::select('search_kana', Input::post('search_kana'), array(1 => '漢字',2 => 'カナ'), array('class' => 'form-control')); ?>
	</div>
	<div class="form-group col-xs-2">
		<?php echo Form::input('search_last_name', Input::post('search_last_name', isset($customer) ? $customer->search_last_name : ''), array('class' => 'form-control','placeholder' => '顧客性')); ?>
	</div>
	<div class="form-group col-xs-2">
		<?php echo Form::input('search_firs_tname', Input::post('search_firs_tname', isset($customer) ? $customer->search_firs_tname : ''), array('class' => 'form-control','placeholder' => '顧客名')); ?>
	</div>
	<div class="form-group col-xs-2">
				<?php echo Form::select('search_person', Input::post('search_person', isset($customer) ? $customer->person_id : ''), $person_selects, array('class' => 'col-md-4 form-control')); ?>
	</div>
	<div class="form-group col-xs-1">
		<button class="btn btn-primary"><span class="glyphicon glyphicon-search"></span>検索</button>
	</div>
<?php echo Form::close(); ?>
<!--
	<div class="col-xs-1 pull-right">
		<?php echo Html::anchor('admin/customer/csvimport', 'インポート', array('class' => 'btn btn-success')); ?>
	</div>
-->
	<div class="col-xs-1 pull-right">
		<?php echo Html::anchor('admin/customer/csvexport', 'エクスポート', array('class' => 'btn btn-success')); ?>
	</div>
</div>
<br>
	<?php echo Html::anchor('admin/customer/create', '顧客追加', array('class' => 'btn btn-success')); ?>
<?php if ($customers): ?>
<!--<table class="table table-striped indexTable">-->
<table class="table table-striped indexTable">
	<thead>
		<tr>
			<th width="55">&nbsp;</th>
			<th width="80">顧客姓</th>
			<th width="80">顧客名</th>
			<th width="80">フリガナ姓</th>
			<th width="80">フリガナ名</th>
			<th width="30">性別</th>
			<th width="130">担当者</th>
			<th width="80">郵便番号</th>
			<th width="130">住所１</th>
			<th width="130">住所２</th>
			<th width="130">住所３</th>
			<th width="130">電話番号</th>
			<th width="30">年齢</th>
			<th width="130">生年月日</th>
			<th width="130">メールアドレス</th>
<!--			<th width="130">最終来店日</th> -->
			<th width="130">備考</th>
			<th width="130">更新日時</th>
		</tr>
	</thead>
	 <tbody>
<?php foreach ($customers as $item): ?>		<tr>

			<td width="55">
				<div class="btn-toolbar">
					<div class="btn-group">
						<?php echo Html::anchor('admin/customer/history/'.$item->id, '<i class="icon-eye-open"></i> 履歴', array('class' => 'btn btn-default btn-sm')); ?>							<?php echo Html::anchor('admin/customer/view/'.$item->id, '<i class="icon-eye-open"></i> 詳細', array('class' => 'btn btn-default btn-sm')); ?>						<?php echo Html::anchor('admin/customer/edit/'.$item->id, '<i class="icon-wrench"></i> 修正', array('class' => 'btn btn-default btn-sm')); ?>						<?php echo Html::anchor('admin/customer/delete/'.$item->id, '<i class="icon-trash icon-white"></i> 削除', array('class' => 'btn btn-sm btn-danger', 'onclick' => "return confirm('本当に削除しますか?')")); ?>					</div>
				</div>
			</td>
			<td width="80"><?php echo $item->last_name; ?></td>
			<td width="80"><?php echo $item->firs_tname; ?></td>
			<td width="80"><?php echo $item->ph_family_name; ?></td>
			<td width="80"><?php echo $item->ph_name; ?></td>
			<?php
				if(isset($item)) {
					$sex = '';
					if (isset($item->sex)) {
						if ($item->sex === '1') {
							$sex = '男';
						} else if ($item->sex === '2'){
							$sex = '女';						
						}						
					}
				}
			?>
			<td width="30"><?php echo $sex; ?></td>
			<td width="130"><?php echo $item->personname; ?></td>
			<td width="80"><?php echo $item->post_code; ?></td>
			<td width="130"><?php echo $item->adress1; ?></td>
			<td width="130"><?php echo $item->adress2; ?></td>
			<td width="130"><?php echo $item->adress3; ?></td>
			<td width="130"><?php echo $item->phone; ?></td>
			<td width="30"><?php echo $item->age; ?></td>
			<td width="130"><?php echo $item->birthday; ?></td>
			<td width="130"><?php echo $item->mail; ?></td>
<!--			<td width="130"><?php echo $item->last_visit_date; ?></td> -->
			<td width="130"><?php echo $item->biko; ?></td>
			<td width="130"><?php echo Date::forge($item->updated_at)->format("%Y/%m/%d %H:%M"); ?></td>
		</tr>
<?php endforeach; ?>	
	</tbody>
</table>
<!-- 「<<<< << 1 2 3 4 5 >> >>>>」 -->
<?php echo Pagination::create_links(); ?>
<?php else: ?>
<p><strong style="color:red">該当する顧客情報がありません。</strong></p>

<?php endif; ?><p>


	<?php echo Html::anchor('admin/customer/create', '顧客追加', array('class' => 'btn btn-success')); ?>

</p>
