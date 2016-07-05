<?php
/**
 * Input field for Google Maps API key.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

$option = get_option( 'xmaps-google-maps-api-key' );
?>
<input id="xmaps-google-maps-api-key" name="xmaps-google-maps-api-key" 
		size="40" type="text" value="<?php echo esc_attr( $option ); ?>" />
