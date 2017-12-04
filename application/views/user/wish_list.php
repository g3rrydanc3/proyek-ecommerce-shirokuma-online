<div class="page-header">
	<?php echo "<h2>Wish list</h2>"; ?>
</div>
<div class="container">
<?php if(is_array($list_wish) && count($list_wish) < 1):// NO WISH ?>
	You don't have any item in wish list.
<?php else:// NOT EMPTY ?>
<table  class="table table-striped">
	<tr>
		<th>REMOVE</th>
		<th>IMAGE</th>
		<th>NAME</th>
		<th>PRICE</th>
	</tr>
<?php 
	foreach($list_wish as $data): 
		$item = $this->wish->detail($data->id_barang);
?>
	<tr>
		<td><?php echo anchor("profile/wish_list?remove=$item->id",'<button type="button" class="btn btn-danger"><span class="glyphicon glyphicon-remove-circle"></span> REMOVE</button> '); ?></td>
		<td><?php echo anchor("items/detail/$item->id","<img width='100' height='100' src='".base_url()."$item->gambar'/>"); ?></td>
		<td><?php echo anchor("items/detail/$item->id",$item->nama); ?></td>
		<td><?php echo $this->cart->format_number($item->harga); ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php endif;?>
</div>