<?php
defined( 'ABSPATH' ) || exit;
?>
<style>
	ul.mgn-tabs {
		padding: 10px 0 0 0;
    	border-bottom: 1px solid #c3c4c7;
    	list-style: none;
		margin-bottom: 0;
	}
	ul.mgn-tabs li {
		border-radius: 3px;
		background: none;
		color: #222;
		display: inline-block;
		cursor: pointer;
		border-top: 1px solid transparent;
		border-left: 1px solid transparent;
		border-right: 1px solid transparent;
		margin-right: 5px;
	}
	ul.mgn-tabs li.current {
		color: #222;
		background: #fff;
		border-top: 1px solid #bbb;
		border-left: 1px solid #bbb;
		border-right: 1px solid #bbb;
		border-bottom: 1px solid #bbb;
	}
	ul.mgn-tabs li a{
		padding: 5px 8px;
		text-decoration: none;
		display: inline-block;
		outline: 0;
		border-radius: 3px;
	}
	ul.mgn-tabs li a:focus{
		box-shadow: none;
		outline: 0;
	}
	ul.mgn-tabs li:not(.current) a:hover{
		color: #043959;
	}
</style>
<h1>Pix Automático com Pagarme</h1>
<p><?php echo wp_kses_post( wpautop( $this->method_description ) ); ?></p>

<ul class="mgn-tabs">
<?php
	foreach( $this->get_tabs() as $key => $value )
	{ 
		$current_class = $current_tab == $key ? ' class="current"' : '';
		echo sprintf('<li%s><a href="%s" aria-current="%s">%s</a></li>', $current_class, add_query_arg( 'mgn_tab', $key, $baseUrl ), $key, $value); 
	}
?>
</ul>
<br class="clear">
