<?php
/**
 * Display fragment for Map Object post edit screen when the
 * Google Maps API key is not configured.
 *
 * @package xmaps
 * @author Dominic Price <dominic.price@nottingham.ac.uk>
 * @license http://www.gnu.org/licenses/agpl-3.0.en.html
 * @link https://github.com/horizon-institute/xmaps
 */

?>
The xMaps plugin is not configured correctly. Please visit the 
<a href="<?php echo esc_url( admin_url( 'options-general.php?page=xmaps-options' ) ); ?>">options page</a>
to register a valid <a href="https://developers.google.com/maps/signup">Google Maps API key</a>.
