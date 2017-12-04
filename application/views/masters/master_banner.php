
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">			
		<div class="row">
			<ol class="breadcrumb">
				<li><a href="<?php echo base_url('index.php/admin/dashboard'); ?>"><svg class="glyph stroked home"><use xlink:href="#stroked-home"></use></svg></a></li>
				<li class="active"><?php echo $title; ?></li>
			</ol>
		</div><!--/.row-->
		
		<!-- INSERT CHARTS AND WHATEVER YOU WANT OVER HERE -->
		
		<div class="row" style="margin: 25px;">
			<b>Active banner:</b> <br/>
			<img src='<?php echo base_url('images/promo1.png');?>' alt='LOL' id="zw" style="margin-right: 25px; margin-top: 10px; width: 500px; "/>
			<img src='<?php echo base_url('images/promo2.png');?>' alt='LOLOL' id="zw2" style="width: 500px;"/>
			<br/><br/>
			<?php 
			echo form_open_multipart('admin/masterBanner');
				echo "First banner: " . form_upload('foto1') . "</br>";
				echo "Second banner: " . form_upload('foto2') . "</br>";
				echo form_submit('submit','Submit', 'type="button" class="btn btn-info"');
			echo form_close();
			?>
			
			<br>
			<!--?php echo "Upload status: " . $conf . "<br><br>"; ?-->
			
		</div>
		
	</div>	<!--/.main-->
  	