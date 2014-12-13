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

var ajaxSidebar = ( function() {
	var $list    = null;
	var $content = null;

	// initialize with the selector ID
	// of the top-level sections and
	// selector ID of the content area
	var init = function( sectionsID, contentID ) {
		$list = $( "#" + sectionsID );
		$content = $( "#" + contentID );

		$list.on( "click", "> li", sectionClickHandler );
		$list.on( "click", ".sidebar-subsection > li", subsectionClickHandler );
	};

	var sectionClickHandler = function() {
		var $el      = $(this);
		var $sublist = $( "#" + $el.data( "sublist" ) );

		if ($el.hasClass( "current-section" )) {
			$el.removeClass( "current-section" );
			$sublist.velocity( "slideUp", 200);
		} else {
			var $current = $list.find( ".current-section" );
			var $curSubList = $( "#" + $current.data( "sublist" ) );

			$current.removeClass( "current-section" );
			$curSubList.velocity( "slideUp", 200 );

			$el.addClass( "current-section" );
			$sublist.velocity( "slideDown", 200 );
		}
	};

	var subsectionClickHandler = function() {
		var $el      = $(this);
		var pageID   = $el.data("page-id");

		$list.find( ".current-subsection" ).removeClass( "current-subsection" );
		$el.addClass( "current-subsection" );

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
})();