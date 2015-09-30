<h2>Listing Froms</h2>
<br>
<?php if ($froms): ?>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Name</th>
			<th>Email</th>
			<th>Comment</th>
			<th>Ip address</th>
			<th>User agent</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
<?php foreach ($froms as $item): ?>		<tr>

			<td><?php echo $item->name; ?></td>
			<td><?php echo $item->email; ?></td>
			<td><?php echo $item->comment; ?></td>
			<td><?php echo $item->ip_address; ?></td>
			<td><?php echo $item->user_agent; ?></td>
			<td>
				<?php echo Html::anchor('admin/from/view/'.$item->id, 'View'); ?> |
				<?php echo Html::anchor('admin/from/edit/'.$item->id, 'Edit'); ?> |
				<?php echo Html::anchor('admin/from/delete/'.$item->id, 'Delete', array('onclick' => "return confirm('Are you sure?')")); ?>

			</td>
		</tr>
<?php endforeach; ?>	</tbody>
</table>

<?php else: ?>
<p>No Froms.</p>

<?php endif; ?><p>
	<?php echo Html::anchor('admin/from/create', 'Add new From', array('class' => 'btn btn-success')); ?>

</p>
