<?php
/*
Plugin Name: HTML entities button
Plugin URI: http://elearn.jp/wpman/column/html-entities-button.html
Description: HTML entities button is a few inserting HTML entities button add to the admin post/page editor.
Author: tmatsuur
Version: 1.3.6
Author URI: http://12net.jp/
*/

/*
    Copyright (C) 2011 tmatsuur (Email: takenori dot matsuura at 12net dot jp)
           This program is licensed under the GNU GPL Version 2.
*/
define( 'HTML_ENTITIES_BUTTON_DOMAIN', 'html-entities-button' );
define( 'HTML_ENTITIES_BUTTON_DB_VERSION_NAME', 'html-entities-button-db-version' );
define( 'HTML_ENTITIES_BUTTON_DB_VERSION', '1.3.5' );

$plugin_html_entities_button = new html_entities_button();
class html_entities_button {
	function html_entities_button() {
		global $pagenow;
		load_plugin_textdomain( HTML_ENTITIES_BUTTON_DOMAIN, false, plugin_basename( dirname( __FILE__ ) ).'/languages' );
		register_activation_hook( __FILE__ , array( &$this , 'init' ) );
		if ( in_array( $pagenow, array( 'post.php', 'post-new.php', 'page.php', 'page-new.php' ) ) ) {
			add_action( 'admin_head', array( &$this, 'style' ) );
			add_action( 'admin_footer', array( &$this, 'setup' ) );
		}
	}
	function init() {
		if ( get_option( HTML_ENTITIES_BUTTON_DB_VERSION_NAME ) != HTML_ENTITIES_BUTTON_DB_VERSION ) {
			update_option( HTML_ENTITIES_BUTTON_DB_VERSION_NAME, HTML_ENTITIES_BUTTON_DB_VERSION );
		}
	}
	function style() {
?>
<style type="text/css">
<!--
.quicktags-toolbar .htmlAdvancedButton { padding: 2px 2px 0px 0px; float: left; }
.quicktags-toolbar .htmlAdvancedButton table { border-collapse: collapse; border-spacing: 0px; }
.quicktags-toolbar .htmlAdvancedButton table td { padding: 0px; text-align: center; vertical-align: top; }
.quicktags-toolbar .htmlAdvancedButton a { border-width: 1px; border-style: solid; border-color: #C3C3C3; line-height: 18px; font-size: 12px; display: inline-block; text-decoration: none; color: #464646; margin: 0px 0px 4px; background: url('./images/fade-butt.png') repeat-x; height: 18px; }
.quicktags-toolbar .htmlAdvancedButton a:hover { border-color: #AAA; background: #DDD; }
.quicktags-toolbar .htmlAdvancedButton a.mceActionButton { width: 16px; padding: 2px 4px; -moz-border-radius-bottomleft: 4px; -webkit-border-bottom-left-radius: 4px; -khtml-border-bottom-left-radius: 4px; border-bottom-left-radius: 4px; -moz-border-radius-topleft: 4px; -webkit-border-top-left-radius: 4px; -khtml-border-top-left-radius: 4px; border-top-left-radius: 4px; }
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
-->
</style>
<?php
	}
	function setup() {
		$smiles = get_option( 'use_smilies' );
		$recent_posts = get_posts( 'numberposts=30' );
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
	var hab_buttons = '<div id="htmlEntityButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterHtmlEntity(\'\');" title="<?php _e( 'Insert a HTML entitiy', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title="&amp;lt;">&lt;</span></a></td><td><a href="javascript:void();" onclick="return toggleHtmlEntityList();" title="<?php _e( 'Choose HTML entities', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div>';
<?php if ( $smiles ) { ?>
	hab_buttons += '<div id="htmlSmilyButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterHtmlSmily(\'\');" title="<?php _e( 'Insert a emoticon', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span class="mceActionButton" title=":smile:"><?php echo trim( str_replace( '\'', '"', convert_smilies( ':smile:' ) ) ); ?></span></a></td><td><a href="javascript:void();" onclick="return toggleHtmlSmilyList();" title="<?php _e( 'Choose emoticons', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span class="mceOpen"></span></a></td></tr></table></div>';
<?php } ?>
	hab_buttons += '<div id="postLinkButton" class="htmlAdvancedButton"><table><tr><td><a href="javascript:void();" onclick="return enterPostLink(0);" title="<?php _e( 'Insert most recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceActionButton"><span  class="mceActionButton">&nbsp;</span></a></td><td><a href="javascript:void();" onclick="return togglePostLinkList();" title="<?php _e( 'Choose recent post link', HTML_ENTITIES_BUTTON_DOMAIN ); ?>" class="mceOpen"><span  class="mceOpen"></span></a></td></tr></table></div>';
	jQuery( this ).prepend( hab_buttons );
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
//]]>
</script>
<?php
	}
}
?>