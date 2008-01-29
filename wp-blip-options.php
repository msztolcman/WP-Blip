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
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" /></p>
		<table class="niceblue">
			<tr valign="top">
				<th scope="row"><label for="wp_blip_login">Login:</label></th>
				<td><input type="text" name="wp_blip_login" id="wp_blip_login" value="<?php echo htmlentities2 (get_option ('wp_blip_login')) ?>" /><br />
					Twój login w serwisie <a href="http://blip.pl/">Blip!</a>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_password">Hasło:</label></th>
				<td><input type="password" name="wp_blip_password" id="wp_blip_password" value="<?php echo htmlentities2 (get_option ('wp_blip_password')) ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_quant">Ilość statusów do pobrania:</label></th>
				<td><input type="text" name="wp_blip_quant" id="wp_blip_quant" value="<?php echo htmlentities2 ($quant) ?>" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_time">Okres trwałości pamięci podręcznej:</label></th>
				<td><input type="text" name="wp_blip_time" id="wp_blip_time" value="<?php echo htmlentities2 ($time) ?>" /><br />
					W sekundach</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="wp_blip_tpl">Szablon wiadomości:</label></th>
				<td><input type="text" name="wp_blip_tpl" id="wp_blip_tpl" value="<?php echo htmlentities2 ($tpl) ?>" size="50"/><br />
					%url - zostanie zastąpione permalinkiem do statusu<br />
					%body - treść statusu<br />
					%date - data ustawienia statusu<br />
					Przykład: &lt;li&gt;&lt;h4&gt;&lt;a href=&quot;%url&quot;&gt;%date&lt;/a&gt;&lt;/h4&gt;&lt;br /&gt;%body&lt;/li&gt;
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="Submit" value="<?php _e('Update Options &raquo;') ?>" /></p>

		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="wp_blip_login,wp_blip_password,wp_blip_quant,wp_blip_time,wp_blip_tpl" />
	</form>
</div>
