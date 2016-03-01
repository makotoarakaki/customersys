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
</div>
<h2>顧客一覧</h2>
<br>
<?php if ($customers): ?>
<!--<table class="table table-striped indexTable">-->
<table class="table table-striped indexTable">
	<thead>
		<tr>
			<th width="55">&nbsp;</th>
			<th width="160">顧客名</th>
			<th width="160">フリガナ</th>
			<th width="130">担当者</th>
		</tr>
	</thead>
	 <tbody>
<?php foreach ($customers as $item): ?>
		 <tr>
			<td width="55">
				<div class="btn-toolbar">
					<div class="btn-group">
						<?php echo Html::anchor('admin/daily/reflec/'.$item->id, '<i class="icon-eye-open"></i> 選択', array('class' => 'btn btn-default btn-sm')); ?>						
					</div>
				</div>
			</td>
			<td width="160"><?php echo $item->last_name.'　'.$item->firs_tname; ?></td>
			<td width="80"><?php echo $item->ph_family_name.'　'.$item->ph_name; ?></td>
			<td width="130"><?php echo $item->personname; ?></td>
		</tr>
<?php endforeach; ?>	
	</tbody>
</table>
<!-- 「<<<< << 1 2 3 4 5 >> >>>>」 -->
<?php echo Pagination::create_links(); ?>
<?php else: ?>
<p><strong style="color:red">該当する顧客情報がありません。</strong></p>

<?php endif; ?>
<?php echo Form::close(); ?>
<p>
	<?php echo Html::anchor('admin/daily', '戻る', array('class' => 'btn btn-success')); ?>
</p>


