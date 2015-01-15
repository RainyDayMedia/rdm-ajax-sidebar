/**
 * WordPress Ajax Sidebar with Content
 * JQuery module that builds an ajax supported sidebar
 * with a content panel.
 *
 * Author: Todd Miller <todd@rainydaymedia.net>
 * Homepage: https://github.com/RainyDayMedia/wp-ajax-sidebar
 * Repo: https://github.com/RainyDayMedia/wp-ajax-sidebar.git
 * License: Copyright (c) <2014> <Rainy Day Media, LLC>
 */

var rdmAjaxSidebar = ( function( $ ) {
	var $list    = null;
	var $content = null;

	// initialize with the selector ID
	// of the top-level sections and
	// selector ID of the content area
	var init = function( menuID, contentID ) {
		$list = $( "#" + menuID );
		$content = $( "#" + contentID );

		$list.on( "click", ".rdm-ajax-sidebar-parent", parentClickHandler );
		$list.on( "click", ".rdm-ajax-sidebar-child", childClickHandler );
	};

	var parentClickHandler = function( e ) {
		var $el    = $(this);
		//var child  = $el.data( "child" );
		var $child  = $( "#" + $el.data( "child" ) );
		var pageID = $el.data( "page-id" );
		//alert($child);

		$current = closeCurrentParent();
		
		// mostly working, but make sure external pages don't get the current parent class
		if ( !$el.is( $current ) ) {
			$el.addClass( "rdm-ajax-sidebar-current-parent" );
			//$list.find( ".rdm-ajax-sidebar-current-child" ).removeClass( "rdm-ajax-sidebar-current-child" );

			if ( !$child.length ) {
				if ( typeof pageID !== "undefined" ) {
					e.preventDefault();
					doAjaxAction( pageID );
				}

				return;
			}

			$child.slideDown( 200 );
		}
	};

	var closeCurrentParent = function() {
		var $current  = $list.find( ".rdm-ajax-sidebar-current-parent" );

		if ( !$current.length ) {
			return false;
		}
		$current.removeClass( "rdm-ajax-sidebar-current-parent" );

		var $curChild = $( "#" + $current.data( "child" ) );

		if ( $curChild.length ) {
			//$child.velocity( "slideUp", 200);
			$curChild.slideUp( 200 );
		}

		return $current;
	};

	var childClickHandler = function( e ) {
		var $el      = $(this);
		var pageID   = $el.data("page-id");

		if ( typeof pageID === "undefined" ) {
			return;
		}

		e.preventDefault();

		$list.find( ".rdm-ajax-sidebar-current-child" ).removeClass( "rdm-ajax-sidebar-current-child" );
		$el.addClass( "rdm-ajax-sidebar-current-child" );

		doAjaxAction( pageID );
	};

	var doAjaxAction = function( pageID ) {
		var data = {
			'action': 'load_page_content',
			'pageID': pageID
		};

		$.post(ajax_object.ajax_url, data, function(response) {
			$content.html(response);
		});
	};

	// public API
	return {
		init: init
	};
})( jQuery );