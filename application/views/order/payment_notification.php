<?php $this->load->view('header');?>
<div class="alert alert-info">
  <strong>Success!</strong> <?php echo $message; ?>. <a href="<?php echo site_url(); ?>" class="alert-link">Back to home</a>.
</div>
<?php $this->load->view('footer');?>