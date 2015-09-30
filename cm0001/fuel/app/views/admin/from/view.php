<h2>Viewing #<?php echo $from->id; ?></h2>

<p>
	<strong>Name:</strong>
	<?php echo $from->name; ?></p>
<p>
	<strong>Email:</strong>
	<?php echo $from->email; ?></p>
<p>
	<strong>Comment:</strong>
	<?php echo $from->comment; ?></p>
<p>
	<strong>Ip address:</strong>
	<?php echo $from->ip_address; ?></p>
<p>
	<strong>User agent:</strong>
	<?php echo $from->user_agent; ?></p>

<?php echo Html::anchor('admin/from/edit/'.$from->id, 'Edit'); ?> |
<?php echo Html::anchor('admin/from', 'Back'); ?>