<?php

/**
 * Text widget class
 *
 * @since 2.8.0
 */
class WP_Widget_Text_With_ID extends WP_Widget {

	function WP_Widget_Text_With_ID() {
		$widget_ops = array('classname' => 'widget_text_with_id', 'description' => __('Arbitrary text or HTML'));
		$control_ops = array('width' => 400, 'height' => 350);
		$this->WP_Widget('text_with_id', __('Text With ID'), $widget_ops, $control_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = empty($instance['title']) ? '' : $instance['title'];
		$css_id = apply_filters( 'widget_css_id', empty($instance['css_id']) ? '' : $instance['css_id'], $instance, $this->id_base);
		$text = apply_filters( 'widget_text', $instance['text'], $instance );
		echo $before_widget;
		?>
		<div id="<?php print $css_id?>">
			<?php
			if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
			?>
			<?php echo wpautop($text) ?>
		</div>
		<?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['css_id'] = strip_tags($new_instance['css_id']);
		if ( current_user_can('unfiltered_html') )
			$instance['text'] =  $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '' ) );
		$title = $instance['title'];
		$css_id = strip_tags($instance['css_id']);
		$text = format_to_edit($instance['text']);
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('css_id'); ?>"><?php _e('CSS ID:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('css_id'); ?>" name="<?php echo $this->get_field_name('css_id'); ?>" type="text" value="<?php echo esc_attr($css_id); ?>" /></p>


		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>

		<p><input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />&nbsp;<label for="<?php echo $this->get_field_id('filter'); ?>"><?php _e('Automatically add paragraphs'); ?></label></p>
		<?php
	}
}

function mahimahi_widgets_init() {

	register_widget('WP_Widget_Text_With_ID');

	do_action('widgets_init');


}

add_action('init', 'mahimahi_widgets_init', 1);

