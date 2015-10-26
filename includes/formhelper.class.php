<?php defined( 'ABSPATH' ) or die( 'Restricted access' );

if ( ! class_exists( 'gPluginFormHelper' ) ) { class gPluginFormHelper extends gPluginClassCore
{

	public static function hideIf( $print = TRUE )
	{
		if ( $print )
			echo ' style="display:none;"';
	}

	// FIXME: DEPRECATED : use gPluginFormHelper::headerNav()
	public static function wpSettingsHeaderNav( $settings_uri = '', $active = '', $sub_pages = array(), $class_prefix = 'nav-tab-', $tag = 'h2' )
	{
		self::headerNav( $settings_uri, $active, $sub_pages, $class_prefix, $tag );
	}

	public static function headerNav( $uri = '', $active = '', $subs = array(), $prefix = 'nav-tab-', $tag = 'h2' )
	{
		if ( ! count( $subs ) )
			return;

		$html = '';

		foreach ( $subs as $slug => $page )
			$html .= self::html( 'a', array(
				'class' => 'nav-tab '.$prefix.$slug.( $slug == $active ? ' nav-tab-active' : '' ),
				'href'  => add_query_arg( 'sub', $slug, $uri ),
			), $page );

		echo self::html( $tag, array(
			'class' => 'nav-tab-wrapper',
		), $html );
	}

	public static function headerTabs( $tabs, $active = 'manual', $prefix = 'nav-tab-', $tag = 'h2' )
	{
		if ( ! count( $tabs ) )
			return;

		$html = '';

		foreach ( $tabs as $tab => $title )
			$html .= self::html( 'a', array(
				'class'    => 'nav-tab '.$prefix.$tab.( $tab == $active ? ' nav-tab-active' : '' ),
				'href'     => '#',
				'data-tab' => $tab,
				'rel'      => $tab, // back comp
			), $title );

		echo self::html( $tag, array(
			'class' => 'nav-tab-wrapper',
		), $html );
	}

	// DEPRECATED: use gPluginFormHelper::headerTabs()
	public static function wpNavTabs( $tabs, $active = 'manual', $class_prefix = 'nav-tab-', $tag = 'h2' )
	{
		echo '<'.$tag.' class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $title )
			echo '<a href="#" class="nav-tab '.$class_prefix.$tab.( $active == $tab ? ' nav-tab-active' : '' ).'" rel="'.$tab.'" >'.$title.'</a>';
		echo '</'.$tag.'>';
	}

	public static function linkStyleSheet( $url, $version = GPLUGIN_VERSION, $media = FALSE )
	{
		echo "\t".self::html( 'link', array(
			'rel'   => 'stylesheet',
			'href'  => add_query_arg( 'ver', $version, $url ),
			'type'  => 'text/css',
			'media' => $media,
		) )."\n";
	}

	private static function _tag_open( $tag, $atts, $content = TRUE )
	{
		$html = '<'.$tag;

		foreach ( $atts as $key => $att ) {

			if ( is_array( $att ) && count( $att ) ) {

				if ( 'data' == $key ) {
					foreach ( $att as $data_key => $data_val ) {
						if ( is_array( $data_val ) )
							$html .= ' data-'.$data_key.'=\''.wp_json_encode( $data_val ).'\'';
						else
							$html .= ' data-'.$data_key.'="'.esc_attr( $data_val ).'"';
					}
					continue;

				} else {
					$att = implode( ' ', array_unique( $att ) );
				}
			}

			if ( 'selected' == $key )
				$att = ( $att ? 'selected' : FALSE );

			if ( 'checked' == $key )
				$att = ( $att ? 'checked' : FALSE );

			if ( 'readonly' == $key )
				$att = ( $att ? 'readonly' : FALSE );

			if ( 'disabled' == $key )
				$att = ( $att ? 'disabled' : FALSE );

			if ( FALSE === $att )
				continue;

			if ( 'class' == $key )
				//$att = sanitize_html_class( $att, FALSE );
				$att = $att;

			else if ( 'href' == $key && '#' != $att )
				$att = esc_url( $att );

			else if ( 'src' == $key )
				$att = esc_url( $att );

			// else if ( 'input' == $tag && 'value' == $key )
			// 	$att = $att;

			else
				$att = esc_attr( $att );

			$html .= ' '.$key.'="'.$att.'"';
		}

		if ( FALSE === $content )
			return $html.' />';

		return $html.'>';
	}

	public static function html( $tag, $atts = array(), $content = FALSE, $sep = '' )
	{
		if ( is_array( $atts ) )
			$html = self::_tag_open( $tag, $atts, $content );
		else
			return '<'.$tag.'>'.$atts.'</'.$tag.'>'.$sep;

		if ( FALSE === $content )
			return $html.$sep;

		if ( is_null( $content ) )
			return $html.'</'.$tag.'>'.$sep;

		return $html.$content.'</'.$tag.'>'.$sep;
	}

	public static function select( $list, $atts = array(), $selected = 0, $prop = FALSE, $none = FALSE, $none_val = 0 )
	{
		$html = self::_tag_open( 'select', $atts, TRUE );

		if ( $none )
			$html .= '<option value="'.$none_val.'" '.selected( $selected, $none_val, FALSE ).'>'.esc_html( $none ).'</option>';

		foreach ( $list as $key => $item )
			$html .= '<option value="'.$key.'" '.selected( $selected, $key, FALSE ).'>'
				.esc_html( ( $prop ? $item[$prop] : $item ) ).'</option>';

		return $html.'</select>';
	}

	// must dep
	function getFieldTitle( $column )
	{
		if ( isset( $field['type'] ) ) {
			if ( 'delete' == $field['type'] )
				return '<span class="field-delete-all" title="'.$field['title'].'"></span>';
		}
		return ( isset( $field['title'] ) ? $field['title'] : '' );
	}

	// must dep / use : gPluginUtils::reKey()
	function reKey( $list, $key )
	{
		if ( ! empty( $list ) ) {
			$ids = wp_list_pluck( $list, $key );
			$list = array_combine( $ids, $list );
		}
		return $list;
	}

	public static function data_dropdown( $list, $name, $prop = FALSE, $selected = 0, $none = FALSE, $none_val = 0 )
	{
		$data = array(
			'select-id'   => $name,
			'select-name' => $name,
			'select-atts' => '',
		);

		$options = array();

		if ( $none )
			$options[] = array(
				'option-val'  => $none_val,
				'option-atts' => selected( $selected, $none_val, FALSE ),
				'option-html' => esc_html( $none )
			);

		foreach ( $list as $key => $item )
			$options[] = array(
				'option-val'  => $key,
				'option-atts' => selected( $selected, $key, FALSE ),
				'option-html' => esc_html( ( $prop ? $item->$prop : $item ) )
			);

		$data['options'] = $options;
		return $data;
	}

	public static function dropdown( $list, $name, $prop = FALSE, $selected = 0, $none = FALSE, $none_val = 0, $obj = FALSE )
	{
		$html = '<select name="'.$name.'" id="'.$name.'">';

		if ( $none )
			$html .= '<option value="'.$none_val.'" '.selected( $selected, $none_val, FALSE ).'>'.esc_html( $none ).'</option>';

		foreach ( $list as $key => $item ) {
			$html .= '<option value="'.$key.'" '.selected( $selected, $key, FALSE ).'>'
				.esc_html( ( $prop ? ( $obj ? $item->{$prop} : $item[$prop] ) : $item ) ).'</option>';
		}

		return $html.'</select>';
	}

	function dropdown_e( $list, $name, $prop = FALSE, $selected = 0, $none = FALSE, $none_val = 0 )
	{
		?><select name="<?php echo $name; ?>" id="<?php echo $name; ?>"><?php
		if ( $none )
			echo '<option value="'.$none_val.'" '.selected( $selected, $none_val, FALSE ).'>'.esc_html( $none ).'</option>';
		foreach ( $list as $key => $item )
			echo '<option value="'.$key.'" '.selected( $selected, $key, FALSE ).'>'.esc_html( ( $prop ? $item->$prop : $item ) ).'</option>';
		?></select> <?php
	}
} }
