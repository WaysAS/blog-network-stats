<?php

define('DB_SUFFIX', 'tpn_analytics'); // Suffix for custom db table.

class Top_Network_Posts extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'top_network_posts', // Base ID
            'Top Network Posts', // Name
            array( 'description' => 'Displays top network posts', ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $widget_content = $this->get_widget_content();

        echo $before_widget;
        if ( ! empty( $title ) ) {
            echo $before_title . $title . $after_title;
        }
        echo $widget_content;
        echo $after_widget;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'New title', 'text_domain' );
        }
        ?>
        <p>
        <label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }

    private function get_widget_content() {
        global $wpdb;
        $table_name = $wpdb->base_prefix . DB_SUFFIX;

        $query_result = $wpdb->get_results(
            "
            SELECT blog_id, post_id, COUNT(*) AS count
            FROM $table_name
            GROUP BY blog_id, post_id
            ORDER BY COUNT(*) DESC
            LIMIT 30;
            "
        );

        $result = "<ul class='top-network-posts'>";

        foreach ($query_result as $row) {
            list($post_url, $post_name) = $this->get_post_meta($row->blog_id, $row->post_id);

            $result .=
<<<EOT
    <li><a href="$post_url">$post_name</a> ($row->count)</li>
EOT;
        }

        return $result . "</ul>";
    }

    private function get_post_meta($blog_id, $post_id) {
        global $wpdb;
        $table_name = $wpdb->get_blog_prefix($blog_id) . "posts";
        $result = array(get_blog_details($row->blog_id)->siteurl, "Ukjent tittel");

        $query_result = $wpdb->get_results(
            "
            SELECT post_title, guid AS url
            FROM $table_name
            WHERE ID = $post_id
            LIMIT 1;
            "
        );

        if (count($query_result) > 0) {
            $result = array($query_result[0]->url, $query_result[0]->post_title);
        }

        return $result;
    }

}
