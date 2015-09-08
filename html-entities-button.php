<?php
/*
Plugin Name: HTML entities button
Plugin URI: http://elearn.jp/wpman/column/html-entities-button.html
Description: HTML entities button is a few inserting HTML entities button add to the admin post/page editor.
Author: tmatsuur
Version: 1.7.0
Author URI: http://12net.jp/
*/

/*
    Copyright (C) 2011-2015 tmatsuur (Email: takenori dot matsuura at 12net dot jp)
           This program is licensed under the GNU GPL Version 2.
*/
define( 'HTML_ENTITIES_BUTTON_DOMAIN', 'html-entities-button' );
define( 'HTML_ENTITIES_BUTTON_DB_VERSION_NAME', 'html-entities-button-db-version' );
define( 'HTML_ENTITIES_BUTTON_DB_VERSION', '1.7.0' );

$plugin_html_entities_button = new html_entities_button();
class html_entities_button {
	var $wpsmiliestrans = array(
		':mrgreen:' => 'icon_mrgreen.gif',
		':neutral:' => 'icon_neutral.gif',
		':twisted:' => 'icon_twisted.gif',
		  ':arrow:' => 'icon_arrow.gif',
		  ':shock:' => 'icon_eek.gif',
		  ':smile:' => 'icon_smile.gif',
		    ':???:' => 'icon_confused.gif',
		   ':cool:' => 'icon_cool.gif',
		   ':evil:' => 'icon_evil.gif',
		   ':grin:' => 'icon_biggrin.gif',
		   ':idea:' => 'icon_idea.gif',
		   ':oops:' => 'icon_redface.gif',
		   ':razz:' => 'icon_razz.gif',
		   ':roll:' => 'icon_rolleyes.gif',
		   ':wink:' => 'icon_wink.gif',
		    ':cry:' => 'icon_cry.gif',
		    ':eek:' => 'icon_surprised.gif',
		    ':lol:' => 'icon_lol.gif',
		    ':mad:' => 'icon_mad.gif',
		    ':sad:' => 'icon_sad.gif',
		      '8-)' => 'icon_cool.gif',
		      '8-O' => 'icon_eek.gif',
		      ':-(' => 'icon_sad.gif',
		      ':-)' => 'icon_smile.gif',
		      ':-?' => 'icon_confused.gif',
		      ':-D' => 'icon_biggrin.gif',
		      ':-P' => 'icon_razz.gif',
		      ':-o' => 'icon_surprised.gif',
		      ':-x' => 'icon_mad.gif',
		      ':-|' => 'icon_neutral.gif',
		      ';-)' => 'icon_wink.gif',
		// This one transformation breaks regular text with frequency.
		//     '8)' => 'icon_cool.gif',
		       '8O' => 'icon_eek.gif',
		       ':(' => 'icon_sad.gif',
		       ':)' => 'icon_smile.gif',
		       ':?' => 'icon_confused.gif',
		       ':D' => 'icon_biggrin.gif',
		       ':P' => 'icon_razz.gif',
		       ':o' => 'icon_surprised.gif',
		       ':x' => 'icon_mad.gif',
		       ':|' => 'icon_neutral.gif',
		       ';)' => 'icon_wink.gif',
		      ':!:' => 'icon_exclaim.gif',
		      ':?:' => 'icon_question.gif',
		);
	var $properties = null;
	const PROPERTIES_PAGE_NAME = 'html-entities-button';

	function __construct() {
		register_activation_hook( __FILE__ , array( $this , 'init' ) );
		if ( is_admin() ) {
			global $pagenow;

			load_plugin_textdomain( HTML_ENTITIES_BUTTON_DOMAIN, false, plugin_basename( dirname( __FILE__ ) ).'/languages' );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			$_pagenow = $pagenow;
			if ( is_null( $_pagenow ) ) { // Why null ?
				if ( is_network_admin() )
					$admin_pattern = '#/wp-admin/network/?(.*?)$#i';
				else if ( is_user_admin() )
					$admin_pattern = '#/wp-admin/user/?(.*?)$#i';
				else
					$admin_pattern = '#/wp-admin/?(.*?)$#i';
				preg_match( $admin_pattern, $_SERVER['PHP_SELF'], $self_matches );
				$_pagenow = $self_matches[1];
				$_pagenow = trim( $_pagenow, '/' );
				$_pagenow = preg_replace( '#\?.*?$#', '', $_pagenow );
			}
			if ( in_array( $_pagenow, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) ) ) {
				add_action( 'admin_head', array( $this, 'style' ) );
				add_action( 'admin_footer', array( $this, 'setup' ) );
			} else if ( $_pagenow == 'options-general.php' && isset( $_GET['page'] ) && $_GET['page'] == self::PROPERTIES_PAGE_NAME ) {
				add_action( 'admin_head', array( $this, 'style' ) );
			}
		} else {
			add_filter( 'template_include', array( $this, 'rewind_classic_smiley' ) );
		}
	}
	function init() {
		if ( get_option( HTML_ENTITIES_BUTTON_DB_VERSION_NAME ) != HTML_ENTITIES_BUTTON_DB_VERSION ) {
			update_option( HTML_ENTITIES_BUTTON_DB_VERSION_NAME, HTML_ENTITIES_BUTTON_DB_VERSION );
		}
	}
	function admin_menu() {
		add_options_page( __( 'html entities button' ), __( 'html entities button' ), 'manage_options', self::PROPERTIES_PAGE_NAME, array( $this, 'properties' ) );
	}
	function rewind_classic_smiley( $template ) {
		if ( $this->_properties( 'classicSmiley' ) && $this->_classic_smiley_available() ) {
			global $wpsmiliestrans;
			$wpsmiliestrans = $this->wpsmiliestrans;
		}
		return $template;
	}
	private function _default_properties() {
		return array( 'place'=>'front', 'convertSpeChars'=>true, 'decodeSpeChars'=>true, 'htmlEntity'=>true, 'htmlSmily'=>true, 'postLink'=>true, 'classicSmiley'=>false );
	}
	private function _properties( $name = '', $value = true, $default = true ) {
		if ( is_null( $this->properties ) )
			$this->properties = get_option( 'html_entities_button', $this->_default_properties() );
		if ( !isset( $this->properties[$name] ) )
			return $default;
		return ( $this->properties[$name] == $value );
	}
	private function _classic_smiley_available() {
		static $available;
		if ( isset( $available ) )
			return $available;
		$img = 'icon_smile.gif';
		$src_url = apply_filters( 'smilies_src', includes_url( "images/smilies/$img" ), $img, site_url() );
		$available = ( @file_get_contents( $src_url, NULL, NULL, 0, 3 ) === 'GIF' );
		return $available;
	}
	private function _nonce_suffix() {
		return date_i18n( 'His TO', filemtime( __FILE__ ) );
	}
	function properties() {
		global $wp_version;
		$message = '';
		$properties = get_option( 'html_entities_button', $this->_default_properties() );
		if ( isset( $_POST['properties'] ) ) {
			check_admin_referer( self::PROPERTIES_PAGE_NAME.$this->_nonce_suffix() );

			$properties['place'] = in_array( $_POST['properties']['place'], array( 'front', 'after' ) )? $_POST['properties']['place']: 'front';
			$properties['convertSpeChars'] = isset( $_POST['properties']['convertSpeChars'] );
			$properties['decodeSpeChars'] = isset( $_POST['properties']['decodeSpeChars'] );
			$properties['htmlEntity'] = isset( $_POST['properties']['htmlEntity'] );
			$properties['htmlSmily'] = isset( $_POST['properties']['htmlSmily'] );
			$properties['wpEmoji'] = isset( $_POST['properties']['wpEmoji'] );
			$properties['postLink'] = isset( $_POST['properties']['postLink'] );
			$properties['classicSmiley'] = isset( $_POST['properties']['classicSmiley'] );
			update_option( 'html_entities_button', $properties );
			$message = __( 'Settings saved.' );
		}
		if ( !isset( $properties['classicSmiley'] ) )
			$properties['classicSmiley'] = false;
		if ( !isset( $properties['wpEmoji'] ) )
			$properties['wpEmoji'] = false;

		if ( $properties['classicSmiley'] && $this->_classic_smiley_available() ) {
			global $wpsmiliestrans;
			$new_wpsmiliestrans = $wpsmiliestrans;	// save
			$wpsmiliestrans = $this->wpsmiliestrans;
		}
?>
<div id="<?php echo self::PROPERTIES_PAGE_NAME; ?>-properties" class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2><?php echo __( 'Settings' ); ?></h2>
<?php if ( $message != '' ) { ?>
<?php if ( version_compare( $wp_version, '3.5', '>=' ) ) { ?>
<div id="setting-error-settings_updated" class="updated settings-error"><p><strong><?php echo $message; ?></strong></p></div>
<?php } else { ?>
<div id="message" class="update fade"><p><?php echo $message; ?></p></div>
<?php } } ?>

<form method="post" id="form-properties">
<?php wp_nonce_field( self::PROPERTIES_PAGE_NAME.$this->_nonce_suffix() ); ?>
<table class="form-table">
<tr style="vertical-align: top;">
<th><?php _e( 'Buttons', HTML_ENTITIES_BUTTON_DOMAIN ); ?></th>
<td class="quicktags-toolbar">
<input type="checkbox" name="properties[convertSpeChars]" value="1" <?php checked( $properties['convertSpeChars'] ); ?> />&nbsp;<div id="convertSpeCharsButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void()" title="<?php _e( 'Convert special characters to HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton">&raquo; &amp;amp;</span></a></td></tr></table></div><br />
<input type="checkbox" name="properties[decodeSpeChars]" value="1" <?php checked( $properties['decodeSpeChars'] ); ?> />&nbsp;<div id="decodeSpeCharsButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void()" title="<?php _e( 'Convert HTML entities to special characters', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton">&amp; &laquo;</span></a></td></tr></table></div><br />
<input type="checkbox" name="properties[htmlEntity]" value="1" <?php checked( $properties['htmlEntity'] ); ?> />&nbsp;<div id="htmlEntityButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void()" title="<?php _e( 'Insert a HTML entitiy', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title="&amp;lt;">&lt;</span></a></td><td><a href="javascript:void()" title="<?php _e( 'Choose HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div><br />
<input type="checkbox" name="properties[htmlSmily]" value="1" <?php checked( $properties['htmlSmily'] ); ?> />&nbsp;<div id="htmlSmilyButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void()" title="<?php _e( 'Insert a emoticon', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title=":smile:"><?php echo trim( str_replace( '\'', '"', convert_smilies( ':smile:' ) ) ); ?></span></a></td><td><a href="javascript:void()" title="<?php _e( 'Choose emoticons', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div><br />
<?php if ( version_compare( $wp_version, '4.2-alpha', '>=' ) ) { ?>
<input type="checkbox" name="properties[wpEmoji]" value="1" <?php checked( $properties['wpEmoji'] ); ?> />&nbsp;<div id="wpEmojiButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void()" title="<?php _e( 'Insert a wpemoji', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title="&#x1f600;"><?php echo '&#x1f600;'; ?></span></a></td><td><a href="javascript:void()" title="<?php _e( 'Choose wpemoji', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div> <?php _e( '(Version 4.2 or later)', HTML_ENTITIES_BUTTON_DOMAIN ); ?><br />
<?php } ?>
<input type="checkbox" name="properties[postLink]" value="1" <?php checked( $properties['postLink'] ); ?> />&nbsp;<div id="postLinkButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void()" title="<?php _e( 'Insert most recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span  class="mceActionButton">&nbsp;</span></a></td><td><a href="javascript:void()" title="<?php _e( 'Choose recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span  class="mceOpen"></span></a></td></tr></table></div><br />
</td>
</tr>
<tr style="vertical-align: top;">
<th><?php _e( 'Placement', HTML_ENTITIES_BUTTON_DOMAIN ); ?></th>
<td>
<input type="radio" name="properties[place]" id="place_prev" value="front" <?php checked( $properties['place'] != 'after' ); ?> />&nbsp;<label for="place_prev"><?php _e( 'It arranges in front of standard buttons.', HTML_ENTITIES_BUTTON_DOMAIN ); ?></label><br />
<input type="radio" name="properties[place]" id="place_after" value="after" <?php checked( $properties['place'] == 'after' ); ?> />&nbsp;<label for="place_after"><?php _e( 'It arranges after standard buttons. ', HTML_ENTITIES_BUTTON_DOMAIN ); ?></label><br />
</td>
</tr>
<?php if ( version_compare( $wp_version, '4.2-alpha', '>=' ) && $this->_classic_smiley_available() ) { ?>
<tr style="vertical-align: top;">
<th><?php _e( 'Optional' ); ?></th>
<td>
<input type="checkbox" name="properties[classicSmiley]" id="classicSmiley" value="1" <?php checked( $properties['classicSmiley'] ); ?> />&nbsp;<label for="classicSmiley"><?php _e( 'Use classic smiley.', HTML_ENTITIES_BUTTON_DOMAIN ); ?></label><br />
</td>
</tr>
<?php } ?>
<tr style="vertical-align: top;">
<td colspan="2">
<input type="submit" name="save" value="<?php _e( 'Save' ); ?>" class="button-primary" />
</td>
</tr>
</table>
</form>
</div>
<?php
		if ( $properties['classicSmiley'] && $this->_classic_smiley_available() ) {
			$wpsmiliestrans = $new_wpsmiliestrans;	// restore
		}
	}
	function style() {
		global $wp_version;
?>
<style type="text/css">
<!--
.quicktags-toolbar .htmlAdvancedButton { padding: 2px 1px 0px 1px; display: inline-block; vertical-align: bottom; }
.quicktags-toolbar .htmlAdvancedButton table { border-collapse: collapse; border-spacing: 0px; }
.quicktags-toolbar .htmlAdvancedButton table td { padding: 0px; text-align: center; vertical-align: top; }
.quicktags-toolbar .htmlAdvancedButton a { border-width: 1px;border-style: solid;border-color: #C3C3C3;line-height: 18px;font-size: 12px;display: inline-block;text-decoration: none;color: #464646;margin: 0px 0px 4px;height: 18px;background: #eee; background-image: -webkit-gradient(linear,left bottom,left top,from(#e3e3e3),to(#fff));background-image: -webkit-linear-gradient(bottom,#e3e3e3,#fff);background-image: -moz-linear-gradient(bottom,#e3e3e3,#fff);background-image: -o-linear-gradient(bottom,#e3e3e3,#fff);background-image: linear-gradient(to top,#e3e3e3,#fff); }
.quicktags-toolbar .htmlAdvancedButton a:hover { border-color: #AAA; background: #DDD; }
.quicktags-toolbar .htmlAdvancedButton a.mceActionButton { width: 16px; padding: 2px 4px; -moz-border-radius-bottomleft: 4px; -webkit-border-bottom-left-radius: 4px; -khtml-border-bottom-left-radius: 4px; border-bottom-left-radius: 4px; -moz-border-radius-topleft: 4px; -webkit-border-top-left-radius: 4px; -khtml-border-top-left-radius: 4px; border-top-left-radius: 4px; }
.quicktags-toolbar #convertSpeCharsButton a.mceActionButton { font-size: 75%; width: 5em; -moz-border-radius: 4px; -webkit-border-radius: 4px; -khtml-border-radius: 4px; border-radius: 4px; }
.quicktags-toolbar #decodeSpeCharsButton a.mceActionButton { font-size: 75%; width: 2.5em; -moz-border-radius: 4px; -webkit-border-radius: 4px; -khtml-border-radius: 4px; border-radius: 4px; }
.quicktags-toolbar #htmlSmilyButton span.mceActionButton img { padding: 1px 0px 2px 0px; height: 15px; }
.quicktags-toolbar #postLinkButton span.mceActionButton { display: inline-block; width: 16px; background: transparent url('./images/menu.png') no-repeat scroll -96px -38px; }
.quicktags-toolbar .htmlAdvancedButton a.mceOpen { width: 12px; height: 22px; border-left: 0 none !important; -moz-border-radius-bottomright: 4px; -webkit-border-bottom-right-radius: 4px; -khtml-border-bottom-right-radius: 4px; border-bottom-right-radius: 4px; -moz-border-radius-topright: 4px; -webkit-border-top-right-radius: 4px; -khtml-border-top-right-radius: 4px; border-top-right-radius: 4px; }
.quicktags-toolbar .htmlAdvancedButton span.mceOpen { background-image: url('<?php echo plugins_url( '', __FILE__ ); ?>/images/down_arrow.gif'); background-position: 1px 2px; background-repeat: no-repeat; padding: 1px; width: 10px; height: 20px; display: inline-block; }

.mcePulldownList { position: absolute; display: none; z-index: 200000; border: 1px solid #AAA; background-color: #EEEEEE; padding: 5px; box-shadow: 3px 3px 5px rgba(0,0,0,0.5); }
#htmlEntityList a { display: inline-block; padding: 2px 4px 2px 4px; width: 1.5em; text-align: center; text-decoration: none; border: 2px solid #EEEEEE; }
#htmlEntityList a:hover { border: 2px solid #80B0FF; background-color: #FFFFFF; border-radius: 5px; }
#htmlSmilyList { font-size: 16px; }
#wpEmojiList { font-size: 24px; }
#htmlSmilyList a, #wpEmojiList a { display: inline-block; padding: .2em .2em .2em .2em; width: 1.2em; text-align: center; text-decoration: none; border: 2px solid #EEEEEE; }
#htmlSmilyList a:hover, #wpEmojiList a:hover { border: 2px solid #80B0FF; background-color: #FFFFFF; border-radius: 5px; }
#postLinkList { width: 25em; max-height: 15.2em; overflow: auto; }
#postLinkList ul { -webkit-margin-before: 0; margin-left: 0; }
#postLinkList li { float: left; clear: both; margin-bottom: 2px; line-height: 120%; border: 2px solid #EEEEEE; }
#postLinkList li:hover { border: 2px solid #80B0FF; background-color: #FFFFFF; border-radius: 5px; }
#postLinkList li a { text-decoration: none; }
#postLinkList li span { display: inline-block; line-height: 120%; }
#postLinkList li span.date { width: 7em; }
#postLinkList li span.title { width: 24em; white-space: nowrap; overflow: hidden; padding: 4px .25em 2px .25em; }

td.quicktags-toolbar input[type=checkbox] { vertical-align: 0.3em; }

.Tabs_navigation { padding: .4em .2em 0 .2em; background: linear-gradient(to bottom, #eeeeee 97%,#999999 100%); margin-bottom: .2em; }
.Tabs_navigation span { display: inline-block; border-left: 1px solid #CCCCCC; border-top: 1px solid #CCCCCC; border-right: 1px solid #CCCCCC; margin: 0 .1em; padding: .15em .15em .3em .15em; cursor: pointer; }
.Tabs_navigation span:hover { border-radius: 5px 5px 0px 0px; background: linear-gradient(to bottom, #ffffff 0%, #eeeeee 100%); }
.Tabs_navigation span.Tab_active { border-left: 1px solid #999999; border-top: 1px solid #999999; border-right: 1px solid #999999; border-bottom: 1px solid #eeeeee; border-radius: 5px 5px 0px 0px; background: linear-gradient(to bottom, #ffffff 0%, #eeeeee 100%); }

<?php if ( version_compare( $wp_version, '3.8', '>=' ) ) { ?>
@media screen and (max-width: 782px) {
<?php if ( version_compare( $wp_version, '3.9', '>=' ) ) { ?>
.quicktags-toolbar .htmlAdvancedButton a.mceActionButton { padding: 6px 12px; }
.quicktags-toolbar .htmlAdvancedButton a.mceOpen { width: 32px; height: 30px; }
.quicktags-toolbar .htmlAdvancedButton span.mceOpen { background-position: 1px 6px; height: 26px; }
<?php } else { ?>
.quicktags-toolbar .htmlAdvancedButton a.mceActionButton { padding: 10px 12px; }
.quicktags-toolbar .htmlAdvancedButton a.mceOpen { width: 32px; height: 38px; }
.quicktags-toolbar .htmlAdvancedButton span.mceOpen { background-position: 1px 10px; height: 34px; }
<?php } ?>
#htmlEntityList a, #htmlSmilyList a { padding: 10px; }
#postLinkList li span { line-height: 200%; }
}
<?php } ?>
-->
</style>
<?php
	}
	function setup() {
		global $wpsmiliestrans;
		if ( $this->_properties( 'classicSmiley' ) && $this->_classic_smiley_available() ) {
			$new_wpsmiliestrans = $wpsmiliestrans;	// save
			$wpsmiliestrans = $this->wpsmiliestrans;
			$this->_setup();
			$wpsmiliestrans = $new_wpsmiliestrans;	// restore
		} else {
			$this->_setup();
		}
	}
	private function _setup() {
		global $wp_version;
		$smiles = ( get_option( 'use_smilies' ) && $this->_properties( 'htmlSmily' ) );
		if ( $this->_properties( 'postLink' ) )
			$recent_posts = get_posts( 'numberposts=30' );
		else
			$recent_posts = array();
		$wpemoji = ( version_compare( $wp_version, '4.2-alpha', '>=' ) && $this->_properties( 'wpEmoji' ) );
?>
<script type="text/javascript">
//<![CDATA[
var keepPulldownList = false;
// Create recent post list
var posts = new Array( <?php echo count( $recent_posts ); ?> );
<?php
	foreach ( $recent_posts as $id=>$recent_post ) {
		echo "\tposts[$id] = {id:".$recent_post->ID.", title: '".htmlspecialchars( $recent_post->post_title, ENT_QUOTES )."', date:'".date( 'm/d h:i', strtotime( $recent_post->post_date ) )."', url:'".get_permalink( $recent_post->ID )."'};\n";
	}
?>
jQuery.event.add( window, 'load', function( ) {
jQuery( '#ed_toolbar' ).each( function() {
	var hab_buttons = '';
<?php if ( $this->_properties( 'convertSpeChars' ) ) { ?>
	hab_buttons += '<div id="convertSpeCharsButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return convertSpeChars(1);" title="<?php _e( 'Convert special characters to HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton">&raquo; &amp;amp;</span></a></td></tr></table></div>';
<?php } if ( $this->_properties( 'decodeSpeChars' ) ) { ?>
	hab_buttons += '<div id="decodeSpeCharsButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return convertSpeChars(0);" title="<?php _e( 'Convert HTML entities to special characters', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton">&amp; &laquo;</span></a></td></tr></table></div>';
<?php } if ( $this->_properties( 'htmlEntity' ) ) { ?>
	hab_buttons += '<div id="htmlEntityButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterHtmlEntity(\'\');" title="<?php _e( 'Insert a HTML entitiy', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title="&amp;lt;">&lt;</span></a></td><td><a href="javascript:void();" onclick="return toggleHtmlEntityList();" title="<?php _e( 'Choose HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div>';
<?php } if ( $smiles ) { ?>
	hab_buttons += '<div id="htmlSmilyButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterHtmlSmily(\'\');" title="<?php _e( 'Insert a emoticon', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title=":smile:"><?php echo trim( str_replace( '\'', '"', convert_smilies( ':smile:' ) ) ); ?></span></a></td><td><a href="javascript:void();" onclick="return toggleHtmlSmilyList();" title="<?php _e( 'Choose emoticons', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div>';
<?php } if ( $wpemoji ) { ?>
	hab_buttons += '<div id="wpEmojiButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void()" onclick="return enterWPEmoji(\'\');" title="<?php _e( 'Insert a wpemoji', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title="1f600"><?php echo '&#x1f600;'; ?></span></a></td><td><a href="javascript:void()" onclick="return toggleWPEmojiList();" title="<?php _e( 'Choose wpemoji', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div>';
<?php } if ( $this->_properties( 'postLink' ) ) { ?>
	hab_buttons += '<div id="postLinkButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterPostLink(0);" title="<?php _e( 'Insert most recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span  class="mceActionButton">&nbsp;</span></a></td><td><a href="javascript:void();" onclick="return togglePostLinkList();" title="<?php _e( 'Choose recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span  class="mceOpen"></span></a></td></tr></table></div>';
<?php } if ( $this->_properties( 'place', 'after', false ) ) { ?>
	jQuery( this ).append( hab_buttons );
<?php } else { ?>
	jQuery( this ).prepend( hab_buttons );
<?php } ?>
	// Create HTML entities
	var entitiesContents = '';
	var entities = new Array(
			'&lt;', '&gt;', '&laquo;', '&raquo;', '&quot;', '&copy;', '&reg;', '&#153;',
			'&amp;', '&plusmn;', '&times;', '&divide;', '&radic;', '&cong;', '&asymp;', '&ne;',
			'&prop;', '&infin;', '&ang;', '&deg;', '&cent;', '&pound;', '&euro;', '&yen;',
			'&micro;', '&sect;', '&para;', '&iexcl;', '&iquest;', '&there4;','&hellip;', '&crarr;',
			'&frac14;','&frac12;','&frac34;', '&sup1;', '&sup2;', '&sup3;',
			'&larr;', '&uarr;', '&rarr;', '&darr;', '&harr;',
			'&lArr;', '&uArr;', '&rArr;', '&dArr;', '&hArr;'
			);
	var i=0;
	for ( key in entities ) {
		entitiesContents += '<a href="javascript:void(0)" onclick="return enterHtmlEntity(\''+entities[key].replace( '&', '&amp;' )+'\');">'+entities[key]+'</a>';
		i++;
		if ( i%8 == 0 ) entitiesContents += '<br />';
	}
<?php
		$smillyContents = '';
		if ( $smiles ) {
			$htmlsmiles = array(
				':smile:', ':grin:', ':lol:', ':razz:', ':cool:', ':wink:', ':???:', ':roll:',
				':neutral:', ':shock:', ':eek:', ':sad:', ':cry:', ':mad:', ':evil:', ':twisted:',
				':oops:', ':mrgreen:', ':arrow:', ':idea:', ':!:', ':?:'
			);
			$i = 0;
			foreach ( $htmlsmiles as $smile ) {
				$smilyImg = trim( str_replace( '\'', '\\\'', convert_smilies( $smile ) ) );
				$smillyContents .= '<a href="javascript:void(0)" onclick="return enterHtmlSmily(\\\''.$smile.'\\\', '.($i+1).');" id="htmlsmily_'.($i+1).'" title="'.$smile.'">'.$smilyImg.'</a>';
				$i++;
				if ( $i%8 == 0 ) $smillyContents .= '<br />';
			}
		}
		echo "	smilyContents = '".$smillyContents."';\n";

		$wpemojiContents = '';
		if ( $wpemoji ) {
			$wpemojis = array(
				'1f600' => array(
					'1f600','1f601','1f602','1f603','1f604','1f605','1f606','1f607','1f608','1f609','1f60a','1f60b','1f60c','1f60d','1f60e','1f60f',
					'1f610','1f611','1f612','1f613','1f614','1f615','1f616','1f617','1f618','1f619','1f61a','1f61b','1f61c','1f61d','1f61e','1f61f',
					'1f620','1f621','1f622','1f623','1f624','1f625','1f626','1f627','1f628','1f629','1f62a','1f62b','1f62c','1f62d','1f62e','1f62f',
					'1f630','1f631','1f632','1f633','1f634','1f635','1f636','1f637','1f638','1f639','1f63a','1f63b','1f63c','1f63d','1f63e','1f63f',
					'1f640','263a' ),
				'1f498' => array(
					'1f48f','1f491','1f493','1f494','1f495','1f496','1f497','1f49d','1f49e','1f498','1f48b','1f48c','1f48d','1f48e',
					'1f645','1f646','1f647','1f648','1f649','1f64a','1f64b','1f64c','1f64d','1f64e','1f64f',
					'1f4ac','1f4ad','1f4a4','1f4a2','1f4a5','1f4a6','1f4a7',
					'1f479','1f47a','1f47b','1f47c','1f47d','1f47e','1f47f','1f480','1f46e','1f464' ),
				'1f449' => array(
					'1f440','1f442','1f443','1f444','1f445','1f446','1f447','1f448','1f449','1f44a','1f44b','1f44c','1f44d','1f44e','1f44f','1f450',
					'1f4aa','270a','270b','270c',
					'1f393','1f3a9','1f451','1f452','1f453','1f454','1f455','1f456','1f457','1f458','1f459','1f45a','1f45b','1f45c','1f392','1f45d','1f45e','1f45f',
					'1f460','1f461','1f462','1f463',
					'1f3bd','1f3bf','1f3c2','1f3c4','1f3c7','1f3c3','1f3ca','1f6b4','1f6b5','1f483','1f3be','1f3c0','1f3c8','1f3c9','26bd','26be','1f6a9','1f3c1','1f3c6','1f38a','1f389' ),
				'1f363' => array(
					'1f330','1f33d','1f344','1f345','1f346','1f347','1f348','1f349','1f34a','1f34b','1f34c','1f34d','1f34e','1f34f','1f350','1f351',
					'1f352','1f353',
					'1f354','1f355','1f356','1f357','1f358','1f359','1f35a','1f35b','1f35c','1f35d','1f35e','1f35f','1f360','1f361','1f362','1f363',
					'1f364','1f365','1f366','1f367','1f368','1f369','1f36a','1f36b','1f36c','1f36d','1f36e','1f36f','1f370','1f371','1f372','1f373',
					'1f382','1f375','1f376','1f377','1f378','1f379','1f37a','1f37b','1f37c','2615' ),
				'1f414' => array(
					'1f400','1f401','1f402','1f403','1f404','1f405','1f406','1f407','1f408','1f409','1f40a','1f40b','1f40c','1f40d','1f40e','1f40f',
					'1f410','1f411','1f412','1f413','1f414','1f415','1f416','1f417','1f418','1f419','1f41a','1f41b','1f41c','1f41d','1f41e','1f41f',
					'1f420','1f421','1f422','1f423','1f424','1f425','1f426','1f427','1f428','1f429','1f42a','1f42b','1f42c','1f42d','1f42e','1f42f',
					'1f430','1f431','1f432','1f433','1f434','1f435','1f436','1f437','1f438','1f439','1f43a','1f43b','1f43c','1f43d','1f43e' ),
				'1f310' => array(
					'1f310','1f30d','1f30e','1f30f','1f5fe','1f5fb','1f30b','1f30c',
					'1f301','1f303','1f304','1f305','1f306','1f307','1f308','1f309',
					'1f320','1f4ab','2b50','1f31f','1f31e','2600','1f505','1f506','26c5','2601','1f302','2614','2728','2744','26c4','1f4a8',
					'1f30a','1f300','26a1',
					'1f331','1f332','1f333','1f334','1f335','1f337','1f338','1f339','1f33a','1f33b','1f33c','1f490','1f33e','1f33f','1f340',
					'1f341','1f342','1f343' ),
				'1f550' => array(
					'1f550','1f551','1f552','1f553','1f554','1f555','1f556','1f557','1f558','1f559','1f55a','1f55b','1f55c','1f55d','1f55e','1f55f',
					'1f560','1f561','1f562','1f563','1f564','1f565','1f566','1f567',
					'1f311','1f312','1f313','1f314','1f315','1f316','1f317','1f318','1f319','1f31a','1f31b','1f31c','1f31d',
					'1f0cf','1f383','1f384','1f385','1f386','1f387','1f391','1f38b','1f38c','1f38d','1f38e','1f38f','1f390',
					'23f0','231a','231b','23f3' ),
				'1f696' => array(
					'1f680','1f681','1f682','1f683','1f684','1f685','1f686','1f687','1f688','1f689','1f68a','1f68b','1f68c','1f68d','1f68e','1f68f',
					'1f690','1f691','1f692','1f693','1f694','1f695','1f696','1f697','1f698','1f699','1f69a','1f69b','1f69c','1f69d','1f69e','1f69f',
					'1f6a0','1f6a1','1f6a2','1f6a3','1f6a4','1f6b2','26f5','2708',
					'1f3e0','1f3e1','1f3e2','1f3e3','1f3e4','1f3e5','1f3e6','1f3e7','1f3e8','1f3e9','1f3ea','1f3eb','1f3ec','1f3ed','1f3ee','1f3ef',
					'1f3f0','1f3a0','1f3a1','1f3a2','1f3aa','1f5fc','1f5fd','1f5ff','e50a','26ea','1f492','26f2','26f3','26fa','1f488','26fd','1f4ee','1f6aa' ),
				'1f6ab' => array(
					'2648','2649','264a','264b','264c','264d','264e','264f','2650','2651','2652','2653','2660','2663','2665','2666',
					'26d4','1f6ab','1f4f5','1f51e',
					'1f508','1f509','1f507','1f514','1f515','1f6ac','1f6ad','1f6ae','1f6af','1f6b0','1f6b1','1f6b2','1f6b3','1f6b6','1f6b7','1f6b8',
					'1f6b9','1f6ba','1f6bb','1f6bc','1f6bd','1f6be','1f4a9','1f6bf','1f6c0','1f6c1','2668','2693','1f530', '267f',
					'2934','2935','21a9','21aa','23e9','23ea','23eb','23ec','1f500','1f501','1f502','1f503','1f504',
					'26a0','267b','2705','1f4f6','1f4ae','1f4af' ),
				'1f3b7' => array(
					'1f3a3','1f3a4','1f3a5','1f3a6','1f3a7','1f3b5','1f3b6','1f3a7',
					'1f3ae','1f3af','1f3b0','1f3b1','1f3b2','1f3b3','1f3b4','1f004',
					'1f3b7','1f3b8','1f3b9','1f3ba','1f3bb','1f4ef','1f3bc',
					'1f4de','1f4e0','1f4e2','1f4e3','1f4f7','1f4f9','1f4fa','1f4fb',
					'1f4ba','1f4bb','1f4bc','1f4a1','1f4a3','1f4d1','1f4dc','1f4dd','1f4d6','1f4da','1f4e6',
					'1f50b','1f50c','1f50d','1f50e','1f52a','1f52b','1f52c','1f52d','1f510','1f511','1f512','1f513',
					'1f517','1f526','1f527','1f528','1f529',
					'1f4bf','1f4c0','1f4cb','1f4cc','1f4cd','1f4ce','1f4cf','1f4d0',
					'2702','2709','270f','2712','1f374','1f489','1f48a','1f525','1f484','1f380','1f388','1f381' ),
			);
			$wpemojiContents .= '<div id="wpEmojiTabs_navigation" class="Tabs_navigation">';
			$tab_class = "Tab_active";
			foreach ( array_keys( $wpemojis ) as $head ) {
				if ( !empty( $head ) ) {
					$wpemojiContents .= '<span id="wpEmojiHead_'.$head.'" onclick="changeWPEmojiTab(\\\''.$head.'\\\');" class="'.$tab_class.'">&#x'.$head.';</span>';
					$tab_class = "";
				}
			}
			$wpemojiContents .= '</div>';
			$tab_style = '';
			$emoji_no = 1;
			foreach ( $wpemojis as $head=>$emojis ) {
				if ( !empty( $head ) ) {
					$wpemojiContents .= '<div id="wpEmojiTab_'.$head.'" class="wpEmojiTabs_content"'.$tab_style.'>';
					$i = 0;
					foreach ( $emojis as $emoji ) {
						$wpemojiContents .= '<a href="javascript:void(0)" onclick="return enterWPEmoji(\\\''.$emoji.'\\\', '.$emoji_no.');" id="wpemoji_'.$emoji_no.'" title="'.$emoji.'">&#x'.$emoji.';</a>';
						$i++;
						$emoji_no++;
						$tab_style = ' style="display: none;"';
						if ( $i%10 == 0 ) $wpemojiContents .= '<br />';
					}
					$wpemojiContents .= '</div>';
				}
			}
		}
		echo "	wpemojiContents = '".$wpemojiContents."';\n";
?>
	var postsContents = '<ul>';
	for ( key in posts ) {
		postsContents += '<li><a href="javascript:void(0)" onclick="return enterPostLink('+key+');"><span class="title"><span class="date">'+posts[key]['date']+'</span>'+posts[key]['title']+'</span></a></li>';
	}
	postsContents += '</ul>';
	var pullDownLists = '<div id="htmlEntityList" class="mcePulldownList">'+entitiesContents+'</div>';
	pullDownLists += '<div id="htmlSmilyList" class="mcePulldownList">'+smilyContents+'</div>';
	if ( smilyContents != '' )
		pullDownLists += '<div id="htmlSmilyList" class="mcePulldownList">'+smilyContents+'</div>';
	if ( wpemojiContents != '' )
		pullDownLists += '<div id="wpEmojiList" class="mcePulldownList">'+wpemojiContents+'</div>';
	pullDownLists += '<div id="postLinkList" class="mcePulldownList">'+postsContents+'</div>';
	jQuery( 'body' ).append( pullDownLists );
	jQuery( 'body div' ).mousedown( function() {
		if ( jQuery(this).hasClass( 'htmlAdvancedButton' ) ) {
			keepPulldownList = true;
		} else if ( !jQuery(this).hasClass( 'mcePulldownList' ) ) {
			if ( !keepPulldownList && !( jQuery(this).hasClass( 'Tabs_navigation' ) || jQuery(this).hasClass( 'wpEmojiTabs_content' ) ) ) {
				hiddenPulldownLists();
			}
		}
		return true;
	} );
} );
} );	// jQuery.event.add
function togglePulldown( list, button ) {
	jQuery( list ).each ( function () {
		if ( jQuery( this ).css( 'display' ) == 'block' )
			jQuery( this ).css( 'display', 'none' );
		else {
			if ( jQuery( this ).css( 'left' ) == 'auto' ) {
				var offsetButton = jQuery( button ).offset();
				jQuery( this ).css( 'left', offsetButton.left+'px' ).css( 'top', ( offsetButton.top+jQuery( button ).height() )+'px' );
			}
			jQuery( this ).css( 'display', 'block' );
			keepPulldownList = false;
		}
	} );
	return false;
}
function toggleHtmlEntityList() {
	jQuery( '#postLinkList,#htmlSmilyList,#wpEmojiList' ).css( 'display', 'none' );
	togglePulldown( '#htmlEntityList', '#htmlEntityButton' );
	return false;
}
function toggleHtmlSmilyList() {
	jQuery( '#postLinkList,#htmlEntityList,#wpEmojiList' ).css( 'display', 'none' );
	togglePulldown( '#htmlSmilyList', '#htmlSmilyButton' );
	return false;
}
function toggleWPEmojiList() {
	jQuery( '#postLinkList,#htmlSmilyList,#htmlEntityList' ).css( 'display', 'none' );
	togglePulldown( '#wpEmojiList', '#wpEmojiButton' );
	return false;
}
function togglePostLinkList() {
	jQuery( '#htmlEntityList,#htmlSmilyList,#wpEmojiList' ).css( 'display', 'none' );
	togglePulldown( '#postLinkList', '#postLinkButton' );
	return false;
}
function hiddenPulldownLists() {
	jQuery( '.mcePulldownList' ).each ( function () {
		if ( jQuery( this ).css( 'display' ) == 'block' )
			jQuery( this ).css( 'display', 'none' );
	} );
	return false;
}
function enterHtmlEntity( entity ) {
	if ( entity == '' ) {
		edInsertContent( edCanvas, jQuery( '#htmlEntityButton span.mceActionButton' ).attr( 'title' ) );
	} else {
		jQuery( '#htmlEntityButton span.mceActionButton' ).attr( 'title', entity ).html( entity );
		edInsertContent( edCanvas, entity );
	}
	jQuery( '#htmlEntityList' ).css( 'display', 'none' );
	return false;
}
function enterHtmlSmily( smily, id ) {
	if ( smily == '' ) {
		edInsertContent( edCanvas, ' '+jQuery( '#htmlSmilyButton span.mceActionButton' ).attr( 'title' ) );
	} else {
		jQuery( '#htmlSmilyButton span.mceActionButton' ).attr( 'title', smily ).html( jQuery( '#htmlsmily_'+id ).html() );
		edInsertContent( edCanvas, ' '+smily );
	}
	jQuery( '#htmlSmilyList' ).css( 'display', 'none' );
	return false;
}
function enterWPEmoji( emoji, id ) {
	if ( emoji === '' ) {
		emoji = '&#x'+jQuery( '#wpEmojiButton span.mceActionButton' ).attr( 'title' )+';';
		edInsertContent( edCanvas, emoji );
	} else {
		jQuery( '#wpEmojiButton span.mceActionButton' ).attr( 'title', emoji ).html( jQuery( '#wpemoji_'+id ).html() );
		emoji = '&#x'+emoji+';';
		edInsertContent( edCanvas, emoji );
	}
	jQuery( '#wpEmojiList' ).css( 'display', 'none' );
	return false;
}
function enterPostLink( postno ) {
	edInsertContent( edCanvas, '<a href="'+posts[postno]['url']+'" title="'+posts[postno]['title']+'">'+posts[postno]['title']+'</a>' );
	jQuery( '#postLinkList' ).css( 'display', 'none' );
	return false;
}
function convertSpeChars( encode ) {
	var canvas = document.getElementById( wpActiveEditor );
	if ( !canvas ) return false;

	if ( document.selection ) {
		// for IE
		canvas.focus();
		var sel = document.selection.createRange();
		if ( encode )
			sel.text = sel.text.replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/'/g,"&#039;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
		else
			sel.text = sel.text.replace(/&quot;/g,"\"").replace(/&#039;/g,"'").replace(/&lt;/g,"<").replace(/&gt;/g,">").replace(/&amp;/g,"&");
		canvas.focus();
	} else if ( canvas.selectionStart != canvas.selectionEnd ) {
		// for Firefox, Webkit based, Opera
		var startPos = canvas.selectionStart;
		var endPos = canvas.selectionEnd;
		var scrollTop = canvas.scrollTop;
		var replaced = canvas.value.substring( startPos, endPos );
		if ( encode )
			replaced = replaced.replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/'/g,"&#039;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
		else
			replaced = replaced.replace(/&quot;/g,"\"").replace(/&#039;/g,"'").replace(/&lt;/g,"<").replace(/&gt;/g,">").replace(/&amp;/g,"&");
		canvas.value = canvas.value.substring( 0, startPos ) + replaced + canvas.value.substring( endPos, canvas.value.length );
		canvas.focus();
		canvas.selectionStart = startPos + replaced.length;
		canvas.selectionEnd = startPos + replaced.length;
		canvas.scrollTop = scrollTop;
	}
	return false;
}
function changeWPEmojiTab( head ) {
	jQuery( '.Tab_active' ).removeClass( 'Tab_active' );
	jQuery( '.wpEmojiTabs_content' ).hide();
	jQuery( '#wpEmojiHead_'+head ).addClass( 'Tab_active' );
	jQuery( '#wpEmojiTab_'+head ).show();
	return false;
}
//]]>
</script>
<?php
	}
}
?>