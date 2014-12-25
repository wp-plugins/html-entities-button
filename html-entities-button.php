<?php
/*
Plugin Name: HTML entities button
Plugin URI: http://elearn.jp/wpman/column/html-entities-button.html
Description: HTML entities button is a few inserting HTML entities button add to the admin post/page editor.
Author: tmatsuur
Version: 1.5.2
Author URI: http://12net.jp/
*/

/*
    Copyright (C) 2011-2014 tmatsuur (Email: takenori dot matsuura at 12net dot jp)
           This program is licensed under the GNU GPL Version 2.
*/
define( 'HTML_ENTITIES_BUTTON_DOMAIN', 'html-entities-button' );
define( 'HTML_ENTITIES_BUTTON_DB_VERSION_NAME', 'html-entities-button-db-version' );
define( 'HTML_ENTITIES_BUTTON_DB_VERSION', '1.5.2' );

$plugin_html_entities_button = new html_entities_button();
class html_entities_button {
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
			} else if ( $_pagenow == 'options-general.php' && $_GET['page'] == self::PROPERTIES_PAGE_NAME ) {
				add_action( 'admin_head', array( $this, 'style' ) );
			}
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
	function properties() {
		global $wp_version;
		$message = '';
		$properties = get_option( 'html_entities_button', array( 'place'=>'front', 'convertSpeChars'=>true, 'decodeSpeChars'=>true, 'htmlEntity'=>true, 'htmlSmily'=>true, 'postLink'=>true ) );
		if ( isset( $_POST['properties'] ) ) {
			$properties['place'] = in_array( $_POST['properties']['place'], array( 'front', 'after' ) )? $_POST['properties']['place']: 'front';
			$properties['convertSpeChars'] = isset( $_POST['properties']['convertSpeChars'] );
			$properties['decodeSpeChars'] = isset( $_POST['properties']['decodeSpeChars'] );
			$properties['htmlEntity'] = isset( $_POST['properties']['htmlEntity'] );
			$properties['htmlSmily'] = isset( $_POST['properties']['htmlSmily'] );
			$properties['postLink'] = isset( $_POST['properties']['postLink'] );
			update_option( 'html_entities_button', $properties );
			$message = __( 'Settings saved.' );
		}
?>
<div id="<?php echo self::PROPERTIES_PAGE_NAME; ?>" class="wrap">
<div id="icon-options-general" class="icon32"><br /></div>
<h2><?php echo __( 'Settings' ); ?></h2>
<?php if ( $message != '' ) { ?>
<?php if ( version_compare( $wp_version, '3.5', '>=' ) ) { ?>
<div id="setting-error-settings_updated" class="updated settings-error"><p><strong><?php echo $message; ?></strong></p></div>
<?php } else { ?>
<div id="message" class="update fade"><p><?php echo $message; ?></p></div>
<?php } } ?>

<form method="post" id="form-properties">
<table class="form-table">
<tr valign="top">
<th><?php _e( 'Buttons', HTML_ENTITIES_BUTTON_DOMAIN ); ?></th>
<td class="quicktags-toolbar">
<input type="checkbox" name="properties[convertSpeChars]" value="1" <?php checked( $properties['convertSpeChars'] ); ?> />&nbsp;<div id="convertSpeCharsButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" title="<?php _e( 'Convert special characters to HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton">&raquo; &amp;amp;</span></a></td></tr></table></div><br />
<input type="checkbox" name="properties[decodeSpeChars]" value="1" <?php checked( $properties['decodeSpeChars'] ); ?> />&nbsp;<div id="decodeSpeCharsButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" title="<?php _e( 'Convert HTML entities to special characters', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton">&amp; &laquo;</span></a></td></tr></table></div><br />
<input type="checkbox" name="properties[htmlEntity]" value="1" <?php checked( $properties['htmlEntity'] ); ?> />&nbsp;<div id="htmlEntityButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" title="<?php _e( 'Insert a HTML entitiy', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title="&amp;lt;">&lt;</span></a></td><td><a href="javascript:void();" title="<?php _e( 'Choose HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div><br />
<input type="checkbox" name="properties[htmlSmily]" value="1" <?php checked( $properties['htmlSmily'] ); ?> />&nbsp;<div id="htmlSmilyButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" title="<?php _e( 'Insert a emoticon', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title=":smile:"><?php echo trim( str_replace( '\'', '"', convert_smilies( ':smile:' ) ) ); ?></span></a></td><td><a href="javascript:void();" title="<?php _e( 'Choose emoticons', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div><br />
<input type="checkbox" name="properties[postLink]" value="1" <?php checked( $properties['postLink'] ); ?> />&nbsp;<div id="postLinkButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" title="<?php _e( 'Insert most recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span  class="mceActionButton">&nbsp;</span></a></td><td><a href="javascript:void();" title="<?php _e( 'Choose recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span  class="mceOpen"></span></a></td></tr></table></div><br />
</td>
</tr>
<tr valign="top">
<th><?php _e( 'Placement', HTML_ENTITIES_BUTTON_DOMAIN ); ?></th>
<td>
<input type="radio" name="properties[place]" id="place_prev" value="front" <?php checked( $properties['place'] != 'after' ); ?> />&nbsp;<label for="place_prev"><?php _e( 'It arranges in front of standard buttons.', HTML_ENTITIES_BUTTON_DOMAIN ); ?></label><br />
<input type="radio" name="properties[place]" id="place_after" value="after" <?php checked( $properties['place'] == 'after' ); ?> />&nbsp;<label for="place_after"><?php _e( 'It arranges after standard buttons. ', HTML_ENTITIES_BUTTON_DOMAIN ); ?></label><br />
</td>
</tr>
<tr valign="top">
<td colspan="2">
<input type="submit" name="save" value="<?php _e( 'Save' ); ?>" class="button-primary" />
</td>
</tr>
</table>
</form>
<?php
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

.mcePulldownList { position: absolute; display: none; z-index: 200000; border: 1px solid #AAA; background-color: #EEEEEE; padding: 5px; }
#htmlEntityList a { display: inline-block; padding: 2px 4px 2px 4px; width: 1.5em; text-align: center; text-decoration: none; }
#htmlEntityList a:hover, #htmlSmilyList a:hover { background-color: #FFFFFF; }
#htmlSmilyList a { display: inline-block; padding: 4px 4px 4px 4px; width: 15px; text-align: center; text-decoration: none; }
#postLinkList { width: 25em; max-height: 15.2em; overflow: auto; }
#postLinkList ul { -webkit-margin-before: 0; margin-left: 0; }
#postLinkList li { float: left; clear: both; margin-bottom: 2px; line-height: 120%; }
#postLinkList li:hover { background-color: #FFFFFF; }
#postLinkList li a { text-decoration: none; }
#postLinkList li span { display: inline-block; line-height: 120%; }
#postLinkList li span.date { width: 7em; }
#postLinkList li span.title { width: 24.75em; white-space: nowrap; overflow: hidden; padding: 4px 4px 2px 4px; }

td.quicktags-toolbar input[type=checkbox] { vertical-align: 0.3em; }

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
		$properties = get_option( 'html_entities_button', array( 'place'=>'front', 'convertSpeChars'=>true, 'decodeSpeChars'=>true, 'htmlEntity'=>true, 'htmlSmily'=>true, 'postLink'=>true ) );

		$smiles = get_option( 'use_smilies' );
		if ( !isset( $properties["postLink"] ) || $properties["postLink"] )
			$recent_posts = get_posts( 'numberposts=30' );
		else
			$recent_posts = array();
?>
<script type="text/javascript">
//<![CDATA[
var keepPulldownList = false;
// Create recent post list
var posts = new Array( <?php echo count( $recent_posts ); ?>);
<?php
	foreach ( $recent_posts as $id=>$recent_post ) {
		echo "\tposts[$id] = {id:".$recent_post->ID.", title: '".htmlspecialchars( $recent_post->post_title, ENT_QUOTES )."', date:'".date( 'm/d h:i', strtotime( $recent_post->post_date ) )."', url:'".get_permalink( $recent_post->ID )."'};\n";
	}
?>
jQuery.event.add( window, 'load', function( ) {
jQuery( '#ed_toolbar' ).each( function() {
	var hab_buttons = '';
<?php if ( !isset( $properties["convertSpeChars"] ) || $properties["convertSpeChars"] ) { ?>
	hab_buttons += '<div id="convertSpeCharsButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return convertSpeChars(1);" title="<?php _e( 'Convert special characters to HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton">&raquo; &amp;amp;</span></a></td></tr></table></div>';
<?php } if ( !isset( $properties["decodeSpeChars"] ) || $properties["decodeSpeChars"] ) { ?>
	hab_buttons += '<div id="decodeSpeCharsButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return convertSpeChars(0);" title="<?php _e( 'Convert HTML entities to special characters', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton">&amp; &laquo;</span></a></td></tr></table></div>';
<?php } if ( !isset( $properties["htmlEntity"] ) || $properties["htmlEntity"] ) { ?>
	hab_buttons += '<div id="htmlEntityButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterHtmlEntity(\'\');" title="<?php _e( 'Insert a HTML entitiy', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title="&amp;lt;">&lt;</span></a></td><td><a href="javascript:void();" onclick="return toggleHtmlEntityList();" title="<?php _e( 'Choose HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div>';
<?php } if ( $smiles && ( !isset( $properties["htmlSmily"] ) || $properties["htmlSmily"] ) ) { ?>
	hab_buttons += '<div id="htmlSmilyButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterHtmlSmily(\'\');" title="<?php _e( 'Insert a emoticon', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title=":smile:"><?php echo trim( str_replace( '\'', '"', convert_smilies( ':smile:' ) ) ); ?></span></a></td><td><a href="javascript:void();" onclick="return toggleHtmlSmilyList();" title="<?php _e( 'Choose emoticons', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div>';
<?php } if ( !isset( $properties["postLink"] ) || $properties["postLink"] ) { ?>
	hab_buttons += '<div id="postLinkButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterPostLink(0);" title="<?php _e( 'Insert most recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span  class="mceActionButton">&nbsp;</span></a></td><td><a href="javascript:void();" onclick="return togglePostLinkList();" title="<?php _e( 'Choose recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span  class="mceOpen"></span></a></td></tr></table></div>';
<?php } if ( isset( $properties["place"] ) && $properties["place"] == 'after' ) { ?>
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
		entitiesContents += '<a href="javascript:;" onclick="return enterHtmlEntity(\''+entities[key].replace( '&', '&amp;' )+'\');">'+entities[key]+'</a>';
		i++;
		if ( i%8 == 0 ) entitiesContents += '<br />';
	}
	<?php
			if ( $smiles ) {
				$htmlsmiles = array(
					':smile:', ':grin:', ':lol:', ':razz:', ':cool:', ':wink:', ':???:', ':roll:',
					':neutral:', ':shock:', ':eek:', ':sad:', ':cry:', ':mad:', ':evil:', ':twisted:',
					':oops:', ':mrgreen:', ':arrow:', ':idea:', ':!:', ':?:'
					);
				$i = 0;
				$smillyContents = '';
				foreach ( $htmlsmiles as $smile ) {
					$smilyImg = trim( str_replace( '\'', '\\\'', convert_smilies( $smile ) ) );
					$smillyContents .= '<a href="javascript:void(0);" onclick="return enterHtmlSmily(\\\''.$smile.'\\\', '.($i+1).');" id="htmlsmily_'.($i+1).'" title="'.$smile.'">'.$smilyImg.'</a>';
					$i++;
					if ( $i%8 == 0 ) $smillyContents .= '<br />';
				}
				echo "	smilyContents = '".$smillyContents."';\n";
			} else {
				echo "	smilyContents = '';\n";
			}
		?>
	var postsContents = '<ul>';
	for ( key in posts ) {
		postsContents += '<li><a href="javascript:void(0);" onclick="return enterPostLink('+key+');"><span class="title"><span class="date">'+posts[key]['date']+'</span>'+posts[key]['title']+'</span></a></li>';
	}
	postsContents += '</ul>';
	var pullDownLists = '<div id="htmlEntityList" class="mcePulldownList">'+entitiesContents+'</div>';
	pullDownLists += '<div id="htmlSmilyList" class="mcePulldownList">'+smilyContents+'</div>';
	if ( smilyContents != '' )
		pullDownLists += '<div id="htmlSmilyList" class="mcePulldownList">'+smilyContents+'</div>';
	pullDownLists += '<div id="postLinkList" class="mcePulldownList">'+postsContents+'</div>';
	jQuery( 'body' ).append( pullDownLists );
	jQuery( 'body div' ).mousedown( function() {
		if ( jQuery(this).hasClass( 'htmlAdvancedButton' ) ) {
			keepPulldownList = true;
		} else if ( !jQuery(this).hasClass( 'mcePulldownList' ) ) {
			if ( !keepPulldownList ) hiddenPulldownLists();
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
	jQuery( '#postLinkList' ).css( 'display', 'none' );
	jQuery( '#htmlSmilyList' ).css( 'display', 'none' );
	togglePulldown( '#htmlEntityList', '#htmlEntityButton' );
	return false;
}
function toggleHtmlSmilyList() {
	jQuery( '#postLinkList' ).css( 'display', 'none' );
	jQuery( '#htmlEntityList' ).css( 'display', 'none' );
	togglePulldown( '#htmlSmilyList', '#htmlSmilyButton' );
	return false;
}
function togglePostLinkList() {
	jQuery( '#htmlEntityList' ).css( 'display', 'none' );
	jQuery( '#htmlSmilyList' ).css( 'display', 'none' );
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
//]]>
</script>
<?php
	}
}
?>