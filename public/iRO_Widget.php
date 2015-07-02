<?php

/**
 * iRO_Widget that displays a javascript filter search box
 */
class iRO_Widget extends WP_Widget {

    function __construct() {
        parent::__construct(
            // Base ID of your widget
            'iro_widget',

            // Widget name will appear in UI
            __('iRO Search Widget', 'iro_connection'),

            // Widget description
            array(
                'description' => __( 'Displays a search box that enables a filter for the joblist', 'iro_connection'),
            )
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );

        // Before
        echo $args['before_widget'];

        if ( ! empty( $title ) )
            echo $args['before_title'] . $title . $args['after_title'];

        // Echo the output of the Shortcode
        echo iRO_Connection_shortcodeJobFilter();

        // After
        echo $args['after_widget'];
    }

    // Widget Backend
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = "";
        }

        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
    <?php
    }

    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
} // Class wpb_widget ends here

// Register and load the widget
function iro_load_widget() {
    register_widget( 'iRO_Widget' );
}
add_action( 'widgets_init', 'iro_load_widget' );