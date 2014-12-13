# WordPress Ajax Archive

JQuery module for WordPress that displays a sidebar list of sections with a ajax supported content panel.

## Usage

HTML

```html
<div class="sidebar left">
	<ul id="example-sections">
		<li data-sublist="sublist1">Section 1 <i class="fa fa-caret-right"></i></li>
		<!-- class is important on this ul -->
		<ul id="sublist1" class="sidebar-subsection">
			<li data-page-id="example_page_id">Page Title</li>
		</ul>
		<li data-sublist="sublist2">Section 2 <i class="fa fa-caret-right"></i></li>
		<!-- class is important on this ul -->
		<ul id="sublist2" class="sidebar-subsection">
			<li data-page-id="example_page_id">Page Title</li>
		</ul>
	</ul>
</div>

<div id="example-content" class="right">
	<!-- Initial Page Content -->
</div>

<script>ajaxSidebar.init("example-sections", "example-content");</script>

```

Enqueue your scripts in your theme's function.php file

```php
add_action( 'wp_enqueue_scripts', 'custom_scripts' );
function custom_scripts()
{
	wp_enqueue_script( 'rdm-ajax-sidebar-script', get_template_directory_uri() . '/assets/js/dist/wp-ajax-sidebar.min.js', array(), '20121208', true );

	// This line is of extreme importance. Without localizing this script, ajax will not work on your page.
	wp_localize_script( 'rdm-ajax-sidebar-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));
}
```

Now, include an action and handler in your theme's function.php file

```php
add_action( 'wp_ajax_load_page_content', 'exampleSidebarHandler');
add_action( 'wp_ajax_nopriv_load_page_content', 'exampleSidebarHandler');

function exampleSidebarHandler()
{
	global $wpdb;

	$pageID = $_POST['pageID'];
	$output = "";

	// HTML for your content area
	$output .= '<h3>'.get_the_title( $pageID ).'</h3>';
	$output .= '<div class="l-vmargin"></div>';
	$output .= apply_filters( 'the_content', get_post_field( 'post_content', $pageID ) );

	echo $output;
	die();
}
```

### Author

Todd Miller <todd@rainydaymedia.net>

### License

Copyright (c) <2014> <Rainy Day Media, LLC>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.