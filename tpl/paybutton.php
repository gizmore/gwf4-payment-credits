<gwf-payment-button class="gwf-credits-button">
	<form method="post" action="<?php echo $form_action; ?>">
		<div><?php echo $form_hidden; ?></div>
		<input type="submit" name="<?php echo $button_name; ?>" value="<?php echo $button_label; ?>" />
	</form>
</gwf-payment-button>
