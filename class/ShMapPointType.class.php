<?php

class ShMapPointType
{
	static function init()
	{
		add_action('init',				array(__CLASS__, 'register_all'), 11 );
		add_action( 'parent_file',		array(__CLASS__, 'tax_menu_correction'), 1);	
		add_action( 'admin_menu', 		array(__CLASS__, 'tax_add_admin_menus'), 11);
		add_filter("manage_edit-".SHM_POINT_TYPE."_columns", array( __CLASS__,'ctg_columns')); 
		add_filter("manage_".SHM_POINT_TYPE."_custom_column",array( __CLASS__,'manage_ctg_columns'), 11.234, 3);
		add_action( SHM_POINT_TYPE.'_add_form_fields', 		array( __CLASS__, 'new_ctg'), 10, 2 );
		add_action( SHM_POINT_TYPE.'_edit_form_fields', 	array( __CLASS__, 'add_ctg'), 2, 2 );
		add_action( 'edit_'.SHM_POINT_TYPE, 				array( __CLASS__, 'save_ctg'), 10);  
		add_action( 'create_'.SHM_POINT_TYPE, 				array( __CLASS__, 'save_ctg'), 10);
		add_action( 'before_delete_post',  					array( __CLASS__, 'before_delete_post') );

	}
	
	function before_delete_post( $post_id ) 
	{
		global $wpdb;
		$query = "
		DELETE FROM " . $wpdb->prefix . "point_map 
		WHERE point_id=$post_id;";
		$wpdb->query($query);
	}
	static function register_all()
	{
		//Map marker type
		$labels = array(
			'name'              => __("Map marker type", SHMAPPER),
			'singular_name'     => __("Map marker type", SHMAPPER),
			'search_items'      => __("Search Map marker type", SHMAPPER),
			'all_items'         => __("all Map marker types", SHMAPPER),
			'view_item '        => __("view Map marker type", SHMAPPER),
			'parent_item'       => __("parent Map marker type", SHMAPPER),
			'parent_item_colon' => __("parent Map marker type:", SHMAPPER),
			'edit_item'         => __("edit Map marker type", SHMAPPER),
			'update_item'       => __("update Map marker type", SHMAPPER),
			'add_new_item'      => __("add Map marker type", SHMAPPER),
			'new_item_name'     => __("new Map marker type name", SHMAPPER),
			'menu_name'         => __("Map marker type", SHMAPPER),
		);
		register_taxonomy(SHM_POINT_TYPE, [ ], 
		[
			'label'                 => '', // определяется параметром $labels->name
			'labels'                => $labels,
			'description'           => __('Unique type of every Map markers', SHMAPPER), // описание таксономии
			'public'                => true,
			'hierarchical'          => false,
			'update_count_callback' => '',
			'show_in_nav_menus'     => true,
			'rewrite'               => true,
			'capabilities'          => array(),
			'meta_box_cb'           => "post_categories_meta_box", // callback функция. Отвечает за html код метабокса (с версии 3.8): post_categories_meta_box или post_tags_meta_box. Если указать false, то метабокс будет отключен вообще
			'show_admin_column'     => true, // Позволить или нет авто-создание колонки таксономии в таблице ассоциированного типа записи. (с версии 3.5)
			'_builtin'              => false,
			'show_in_quick_edit'    => true, // по умолчанию значение show_ui
		] );
	}
	static function tax_menu_correction($parent_file) 
	{
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
		if ( $taxonomy == SHM_POINT_TYPE )
			$parent_file = 'shm_page';
		return $parent_file;
	}
	static function tax_add_admin_menus() 
	{
		add_submenu_page( 
			'shm_page', 
			__("Map marker types", SHMAPPER), 
			__("Map marker types", SHMAPPER), 
			'manage_options', 
			'edit-tags.php?taxonomy=' . SHM_POINT_TYPE
		);
    }
	static function ctg_columns($theme_columns) 
	{
		$new_columns = array
		(
			'cb' 			=> ' ',
			'id' 			=> 'id',
			'name' 			=> __('Name'),
			'icon' 			=> __('Icon', SHMAPPER)
		);
		return $new_columns;
	}
	static function manage_ctg_columns($out, $column_name, $term_id) 
	{
		switch ($column_name) {
			case 'id':
				$out 		.= $term_id;
				break;
			case 'icon': 
				$icon = get_term_meta( $term_id, 'icon', true ); 
				$color = get_term_meta( $term_id, 'color', true ); 
				$logo = wp_get_attachment_image_src($icon, "full")[0];
				echo "<div>
					<img src='$logo' style='width:auto; height:60px; margin:10px;' />
					<div style='width:80px;height:5px;background-color:$color;'></div>
				</div>";
				break;
			default:
				break;
		}
		return $out;    
	}
	static function new_ctg( $tax_name )
	{
		require_once(SHM_REAL_PATH."tpl/input_file_form.php");
		?>
		<div class="form-field term-description-wrap">
			<label for="color">
				<?php echo __("Color", SHMAPPER);  ?>
			</label> 
			<div class="bfh-colorpicker" data-name="color" data-color="<?php echo $color ?>">
			</div>
			<input type="color" name="color" value="<?php echo $color ?>" />
		</div>
		<div class="form-field term-description-wrap">
			<label for="height">
				<?php echo __("Height", SHMAPPER);  ?>
			</label> 
			<input type="number" name="height" value="<?php echo $height ?>" />
		</div>
		<div class="form-field term-description-wrap">
			<label for="width">
				<?php echo __("Width", SHMAPPER);  ?>
			</label> 
			<input type="number" name="width" value="<?php echo $width ?>" />
		</div>
		<div class="form-field term-description-wrap">
			<label for="icon">
				<?php echo __("Icon", SHMAPPER);  ?>
			</label> 
			<div class='shm-flex'>
			<?php
				echo get_input_file_form2( "icon", $icon, "icon", 0 );
			?>
			</div>
		</div>
		
		<?php
	}
	static function add_ctg( $term, $tax_name )
	{
		require_once(SHM_REAL_PATH."tpl/input_file_form.php");
		if($term)
		{
			$term_id = $term->term_id;
			$icon = get_term_meta($term_id, "icon", true);
			$color = get_term_meta($term_id, "color", true);
			$height = get_term_meta($term_id, "height", true);
			$height = !$height ? 30 : $height;
			$width = get_term_meta($term_id, "width", true);
			$width = !$width ? 30 : $width;
		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="color">
					<?php echo __("Color", SHMAPPER);  ?>
				</label> 
			</th>
			<td>
				<div class="bfh-colorpicker" data-name="color" data-color="<?php echo $color ?>">
				</div>
				<input type="color" name="color" value="<?php echo $color ?>" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="height">
					<?php echo __("Height", SHMAPPER);  ?>
				</label> 
			</th>
			<td>
				<input type="number" name="height" value="<?php echo $height ?>" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="width">
					<?php echo __("Width", SHMAPPER);  ?>
				</label> 
			</th>
			<td>
				<input type="number" name="width" value="<?php echo $width ?>" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="icon">
					<?php echo __("Icon", SHMAPPER);  ?>
				</label> 
			</th>
			<td>
				<?php
					echo get_input_file_form2( "icon", $icon, "icon", 0 );
				?>
			</td>
		</tr>
		<?php
	}
	static function save_ctg( $term_id ) 
	{
	    update_term_meta($term_id, "icon", 	sanitize_text_field($_POST['icon0']));
	    update_term_meta($term_id, "color", sanitize_hex_color($_POST['color']));
	    update_term_meta($term_id, "height", sanitize_text_field($_POST['height']));
	    update_term_meta($term_id, "width", sanitize_text_field($_POST['width']));
	}
	static function get_icon($term, $is_locked=false)
	{
		
		$color 		= get_term_meta($term->term_id, "color", true);
		$icon  		= (int)get_term_meta($term->term_id, "icon", true);
		$d 			= wp_get_attachment_image_src($icon, array(100, 100));
		$cur_bgnd 	= $d[0];
		$class		= $is_locked ? " shm-muffle " : "";
		return "
		<div class='ganre_picto $class' term='". SHM_POINT_TYPE ."' term_id='$term->term_id' >
			<div 
				class='shm_type_icon' 
				style='background-color:$color; background-image:url($cur_bgnd);'
				>
			</div>
			<div class='ganre_label'>" . $term->name . "</div>
		</div>";
	}
	static function get_all_ids()
	{
		return get_terms([
			"taxonomy" 		=> SHM_POINT_TYPE,
			"hide_empty"	=> false,
			"fields"		=> "ids"
			
		]);
	}
	static function wp_dropdown($params=-1)
	{
		if(!is_array($params))
			$params=[ "id" => "ganres", "name" => "ganres", "class"=> "form-control", "taxonomy"=> SHM_POINT_TYPE];
		$all = get_terms(['taxonomy' => $params['taxonomy'], 'hide_empty' => false ]);
		$multiple = $params['multiple'] ? " multiple " : "" ;
		$selector =$params['selector']  ? " selector='" . $params['selector'] . "' " : " s='ee' ";
		$html = "<select name='".$params['name']."' id='".$params['id']."' $multiple class='".$params['class']."' $selector>";
		foreach($all as $term)
		{
			$selected = in_array($term->term_id, $params['selected']) ? "selected" : "";
			$html .= "<option value='" . $term->term_id . "' $selected >" . $term->name . "</option>";
		}
		$html .="</select>";
		return $html;
	}
	static function get_icon_src($term_id, $size=-1)
	{
		$size 		= $size == -1 ? get_term_meta( $term_id, "height", true ) : $size;
		$icon 		= get_term_meta( $term_id, "icon", true );
		$d 			= wp_get_attachment_image_src( $icon, array($size, $size) );
		return $d;
	}
	static function get_ganre_swicher($params = -1, $type="checkbox", $form_factor="large")
	{
		if(!is_array($params))
			$params = ["prefix" =>"ganre" ];
		$selected = is_array($params['selected']) ?  $params['selected'] : explode(",", $params['selected']);
		$includes = $params['includes'] ;
		$row_class = isset($params['row_class']) ? $params['row_class'] : "" ;
		$row_style = isset($params['row_style']) ? $params['row_style'] : ""; ;
		$ganres	= get_terms(["taxonomy" => SHM_POINT_TYPE, 'hide_empty' => false ]);
		$html 	= "<div class='shm-row point_type_swicher $row_class' style='$row_style'>";
		switch($params['col_width'])
		{
			case 12:
				$col_width	= "shm-1";
				break;
			case 6:
				$col_width	= "shm-2";
				break;
			case 4:
				$col_width	= "shm-3";
				break;
			case 3:
				$col_width	= "shm-4";
				break;
			default:
			case 2:
				$col_width	= "shm-6";
				break;
			
		}
		foreach($ganres as $ganre)
		{
			if( is_array($includes) && !in_array( $ganre->term_id, $includes ) ) continue;
						
			$icon 		= get_term_meta($ganre->term_id, "icon", true);
			$color 		= get_term_meta($ganre->term_id, "color", true);
			$d 			= wp_get_attachment_image_src($icon, array(100, 100));
			$cur_bgnd 	= $d[0];
			$before 	= "";
			$after 		= "";
			switch( $form_factor )
			{
				case "large":
					$class = "ganre_checkbox";
					$before = "<div class='$col_width'>";
					$after = "
						<label for='" . $params['prefix'] . "_" . $ganre->term_id . "'>
							" . $ganre->name . 
							($cur_bgnd ? "<img src='$cur_bgnd' alt='' />" : "<div class='shm-clr' style='background:$color;'></div>") .
						"</label>
					</div>";
					break;
				case "stroke":
					$class = "ganre_checkbox2";
					$after = "
						<label for='" . $params['prefix'] . "_" . $ganre->term_id . "' title='" . $ganre->name . "'>".
							($cur_bgnd ? "<img src='$cur_bgnd' alt='' />" : "<div class='shm-clr-little' style='background:$color;'></div>").
						"</label>";
					break;
				default:
					$class = "ganre_checkbox";
					break;
			}
			$html .= "
				$before
				<input 
					type='$type' 
					name='" . $params['prefix'] . ($type == "checkbox" ?  "[]'" : "'").
					"id='" . $params['prefix'] . "_" . $ganre->term_id . "'
					term_id='" . $ganre->term_id . "'
					class='$class'
					value='" . $ganre->term_id . "' ".
					checked(1, in_array( $ganre->term_id, $selected) ? 1 : 0, false).
				"/>
				$after";
		}
		
		if( isset($params['default_none'])	)
		{
			$html .= "
			<div class='$col_width'>
				<input 
					type='$type' 
					name='" . $params['prefix'] . ($type == "checkbox" ?  "[]'" : "'").
					"id='" . $params['prefix'] . "_" . 0 . "'
					term_id='" . 0 . "'
					class='$class'
					value='" . 0 . "' ".
					checked(1, in_array( 0, $selected) ? 1 : 0, false).
				"/>
				<label for='" . $params['prefix'] . "_" . 0 . "'>" . 
					__("None", SHMAPPER) . 
					"<div class='shm-clr' style='background:#ffffff;'></div>" .
				"</label>
			</div>";
		}	
		
		$html .= "
			<input type='hidden' id='" . $params['prefix'] . "pointtype' name='" . $params['name'] . "' point='' value='" . $params['selected'] . "' />
		</div>";
		return $html;
	}
}