<?php defined( 'ABSPATH' ) or die( 'Restricted access' );
if ( ! class_exists( 'gPluginFormHelper' ) ) { class gPluginFormHelper
{

	/** ---------------------------------------------------------------------------------
						USED FUNCTION: Modyfy with Caution!
	--------------------------------------------------------------------------------- **/

	public static function hideIf( $print = true )
	{
		if ( $print ) 
			echo ' style="display:none;"';
	}
	
	public static function wpSettingsHeaderNav( $settings_uri = '', $active = '', $sub_pages = array(), $class_prefix = 'nav-tab-', $tag = 'h3' )
	{
		if ( ! count( $sub_pages ) )
			return;

		echo '<'.$tag.' class="nav-tab-wrapper">';
			foreach ( $sub_pages as $page_slug => $sub_page ) {
				?><a href="<?php echo esc_url( add_query_arg( 'sub', $page_slug, $settings_uri ) ); ?>"
					class="nav-tab <?php echo $class_prefix.$page_slug.( $page_slug == $active ? ' nav-tab-active' : '' ); ?>">
					<?php echo esc_html( $sub_page ); ?></a><?php
			}
		echo '</'.$tag.'>';
	}
	
	public static function wpNavTabs( $tabs, $active = 'manual', $class_prefix = 'nav-tab-', $tag = 'h3' )
	{
		echo '<'.$tag.' class="nav-tab-wrapper">';
		foreach( $tabs as $tab => $title )
			echo '<a href="#" class="nav-tab '.$class_prefix.$tab.( $active == $tab ? ' nav-tab-active' : '' ).'" rel="'.$tab.'" >'.$title.'</a>';
		echo '</'.$tag.'>';
	}
	
	
	public static function linkStyleSheet( $url )
	{
		echo '<link rel="stylesheet" href="'.esc_url( $url ).'" type="text/css" />'."\n"; 
	}
	

	public static function _tag_open( $tag, $atts, $content = true )
	{
		$html = '<'.$tag;
        foreach( $atts as $key => $att ) {
			if ( is_array( $att ) && count( $att ) )
				$att = implode( ' ', $att );
			if ( 'selected' == $key )	
				$att = ( $att ? 'selected' : false );
			if ( false === $att )
				continue;
			if ( 'class' == $key )
				//$att = sanitize_html_class( $att, false );
				$att = $att;
			else if ( 'href' == $key || 'src' == $key )
				$att = esc_url( $att );
			//else if ( 'input' == $tag && 'value' == $key )
				//$att = $att;
			else 
				$att = esc_attr( $att );
			$html .= ' '.$key.'="'.$att.'"';
		}
		if ( false === $content )
            return $html.' />';
		return $html.'>';
	}

    public static function html( $tag, $atts = array(), $content = false, $sep = '' ) 
	{
		$html = self::_tag_open( $tag, $atts, $content );
		
		if ( false === $content )
			return $html.$sep;
			
        if ( is_null( $content ) )
            return $html.'</'.$tag.'>'.$sep;
			
        return $html.$content.'</'.$tag.'>'.$sep;
	}	
	
    function html_OLD( $tag, $atts = array(), $content = false ) 
    {
        $html = '<'.$tag;
        
        foreach( $atts as $key => $att ) 
            $html .= ' '.$key.'="'.$att.'"';
            
        if ( false === $content )
            return $html.' />';
        else
            $html .= '>';
        
        if ( is_null( $content ) )
            return $html.'</'.$tag.'>';
        
        return $html.$content.'</'.$tag.'>';
    }
	
	public static function select( $list, $atts = array(), $selected = 0, $prop = false, $none = false, $none_val = 0 )
	{
		$html = self::_tag_open( 'select', $atts, true );
		if ( $none ) 
            $html .= '<option value="'.$none_val.'" '.selected( $selected, $none_val, false ).'>'.esc_html( $none ).'</option>';
        foreach( $list as $key => $item )
            $html .= '<option value="'.$key.'" '.selected( $selected, $key, false ).'>'
				.esc_html( ( $prop ? $item[$prop] : $item ) ).'</option>';
        return $html.'</select>';
	}
	

	
	
	

	//must dep
	function prepareFieldOLD( $field, $saved, $name, $prefix, $ref = false, $context = 'admin' )
	{
		if ( ! isset( $field['type'] ) )
			return array();
		
		switch( $field['type'] ) {
            case 'link' : 
				if ( isset( $field['ref'] ) && 'term' == $field['ref'] && $ref )
					return '<a href="'.($context).'" id="'.$prefix.$name.'" name="'.$prefix.$name.'" >'.$ref->name.'</a>';
			break;
			case 'delete' :
				return '<span class="field-delete gpeople-icon" id="'.$prefix.$name.'" name="'.$prefix.$name.'" title="'.$field['title'].'"></span>';
            break;
            case 'textarea' :
                return '<textarea id="'.$prefix.$name.'" name="'.$prefix.$name.'" placeholder="'.$field['title'].'">'.( isset( $saved[$name] ) ? esc_textarea( $saved[$name] ) : '' ).'</textarea>';
            break;
            case 'select' :
				return self::dropdown( $field['values'], $prefix.$name, false, (  isset( $saved[$name] ) ? $saved[$name] : '0' ), $field['none_title'], $field['none_value'] );
            break;
            default :
            case 'text' :
                return '<input type="text" id="'.$prefix.$name.'" name="'.$prefix.$name.'" placeholder="'.$field['title'].'" class="regular-text textInput" value="'.( isset( $saved[$name] ) ? esc_attr( $saved[$name] ) : '' ).'" />';
            break;
        }
        return array();	
	}
	
	//must dep
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

    public static function data_dropdown( $list, $name, $prop = false, $selected = 0, $none = false, $none_val = 0 )
    {
        $data = array(
            'select-id' => $name,
            'select-name' => $name,
            'select-atts' => '',
        );
        
        $options = array();
        
        if ( $none ) 
            $options[] = array(
                'option-val' => $none_val,
                'option-atts' => selected( $selected, $none_val, false ),
                'option-html' => esc_html( $none )
            );
            
        foreach( $list as $key => $item )
            $options[] = array(
                'option-val' => $key,
                'option-atts' => selected( $selected, $key, false ),
                'option-html' => esc_html( ( $prop ? $item->$prop : $item ) )
            );

        $data['options'] = $options;
        return $data;
    }
    
	public static function dropdown( $list, $name, $prop = false, $selected = 0, $none = false, $none_val = 0, $obj = false )
    {
        $html = '<select name="'.$name.'" id="'.$name.'">';
        if ( $none ) 
            $html .= '<option value="'.$none_val.'" '.selected( $selected, $none_val, false ).'>'.esc_html( $none ).'</option>';
        foreach( $list as $key => $item ) {
            $html .= '<option value="'.$key.'" '.selected( $selected, $key, false ).'>'
				.esc_html( ( $prop ? ( $obj ? $item->{$prop} : $item[$prop] ) : $item ) ).'</option>';
		}
        return $html.'</select>';
    }

    function dropdown_e( $list, $name, $prop = false, $selected = 0, $none = false, $none_val = 0 )
    {
        ?><select name="<?php echo $name; ?>" id="<?php echo $name; ?>"><?php
        if ( $none ) 
            echo '<option value="'.$none_val.'" '.selected( $selected, $none_val, false ).'>'.esc_html( $none ).'</option>';
        foreach( $list as $key => $item )
            echo '<option value="'.$key.'" '.selected( $selected, $key, false ).'>'.esc_html( ( $prop ? $item->$prop : $item ) ).'</option>';
        ?></select><?php
    }
    
   	/** ---------------------------------------------------------------------------------
									NOT USED YET
	--------------------------------------------------------------------------------- **/

} }