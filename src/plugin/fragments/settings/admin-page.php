<?php
/**
 * Admin options display.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

?>
<div>
	<h1>xMaps Settings</h1>
	<form action="options.php" method="post">
	<?php settings_fields( 'xmaps-options' ); ?>
	<?php do_settings_sections( 'xmaps-options' ); ?>
	<input name="Submit" type="submit" 
			value="<?php esc_attr_e( 'Save Changes' ); ?>" />
	</form>
</div>
