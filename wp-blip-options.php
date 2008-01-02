<?php
/*
 * Interface file for WP Blip! Wordpress plugin
 *
 * Author: Marcin Sztolcman (http://urzenia.net)
 */

$quant	= get_option ('wp_blip_quant');
if (!$quant) {
	$quant = 10;
}

$time	= get_option ('wp_blip_time');
if (!$time) {
	$time = 300;
}

$tpl	= get_option ('wp_blip_tpl');
if (!$tpl) {
	$tpl = '<li>(%date) %body</li>';
}
?>
<div class="wrap">
	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options') ?>
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" /></label></p>
		<p>
			<label for="wp_blip_login">
				<input type="text" name="wp_blip_login" value="<?php echo htmlentities2 (get_option ('wp_blip_login')) ?>" />
				Login</label></p>
		<p>
			<label for="wp_blip_password">
				<input type="password" name="wp_blip_password" value="<?php echo htmlentities2 (get_option ('wp_blip_password')) ?>" />
				Hasło</label></p>
		<p>
			<label for="wp_blip_quant">
				<input type="text" name="wp_blip_quant" value="<?php echo htmlentities2 ($quant) ?>" />
				Ilość statusów do pobrania</label></p>
		<p>
			<label for="wp_blip_time">
				<input type="text" name="wp_blip_time" value="<?php echo htmlentities2 ($time) ?>" />
				Okres trwałości pamięci podręcznej</label></p>
		<p>
			<label for="wp_blip_tpl">
				<input type="text" name="wp_blip_tpl" value="<?php echo htmlentities2 ($tpl) ?>" />
				Szablon wiadomości
				</label></p>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="wp_blip_login,wp_blip_password,wp_blip_quant,wp_blip_time,wp_blip_tpl" />
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" /></label></p>
	</form>
</div>
