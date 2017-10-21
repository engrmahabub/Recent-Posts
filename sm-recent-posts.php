<?php
/*
Plugin Name: SM Recent Posts
Plugin URI: https://wordpress.org/plugins/sm-recent-posts/
Author: Mahabubur Rahman
Author URI: http://mahabub.me
Description: Recent post widget plugin for wordpress site sidebar. In author can view author recent post or all recent post.
Version: 1.0.0
*/
define('SM_RECENT_POSTS_URL',plugin_dir_url( __FILE__ ));
class SMRecentPost_Widget extends WP_Widget {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'SMRecentPost_Widget',
            'description' => 'SM Recent Post is for display recent post on sidebar.In author page you can display only author recent post or all recent posts.',
        );
        parent::__construct( 'SMRecentPost_Widget', 'SM Recent Posts', $widget_ops );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {
        // outputs the content of the widget
        extract( $args );

        if ( ! isset( $args['widget_id'] ) ) {
            $args['widget_id'] = $this->id;
        }

        $title              = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Posts' );
        $title              = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        $number_of_posts    = ( ! empty( $instance['number_of_posts'] ) ) ? absint( $instance['number_of_posts'] ) : 5;
        if ( ! $number_of_posts )
            $number_of_posts = 5;
        $date_show          = isset( $instance['date_show'] ) ? $instance['date_show'] : false;
        $author_post_only          = isset( $instance['author_post_only'] ) ? $instance['author_post_only'] : false;

        echo $before_widget;
        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        $args=array(
                'posts_per_page'    => $number_of_posts,
                'post_status'       => 'publish',
            );
        if ( is_author() AND $author_post_only  ) {
            $author = get_user_by( 'slug', get_query_var( 'author_name' ) );
            $args['author'] =   $author->ID;
        }
        $the_query = new WP_Query( $args );

        if ( $the_query->have_posts() ) : ?>
            <style>
                #sm_recent_post{
                    list-style: none;
                }
                #sm_recent_post li {
                    padding: 5px 0px;
                    border-bottom: 1px dashed #ace9ff;
                }
            </style>
            <ul id='sm_recent_post'>
            <?php
            while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
                <li>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <?php the_post_thumbnail(array(70,70)); ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
                            <img src="<?=SM_RECENT_POSTS_URL?>assets/img/no-thumbnail.png" width="70" height="70">
                        </a>
                    <?php endif; ?>
                    <a href="<?php the_permalink();?>"><?php the_title(); ?></a>
                    <?php if($date_show): ?>
                    <span class="post-date"><?=get_the_date( 'F j, Y' );?></span>
                    <?php endif; ?>
                </li>
            <?php
            endwhile;
            echo "</ul>";
            wp_reset_postdata();
        else :
            ?>
            <p><?php esc_html_e( 'Sorry, no posts matched your criteria.' ); ?></p>
        <?php
        endif;
        
        echo $after_widget;
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {
        // outputs the options form on admin
        $title      	        = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number_of_posts        = isset( $instance['number_of_posts'] ) ? absint( $instance['number_of_posts'] ) : 5;
        $date_show              = isset( $instance['date_show'] ) ? (bool) $instance['date_show'] : false;
        $author_post_only       = isset( $instance['author_post_only'] ) ? (bool) $instance['author_post_only'] : false;

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number_of_posts'); ?>"><?php _e('Number of posts to show:'); ?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number_of_posts'); ?>" name="<?php echo $this->get_field_name('number_of_posts'); ?>" type="number" min="3" value="<?php echo $number_of_posts; ?>"/>
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked( $date_show); ?> id="<?php echo $this->get_field_id( 'date_show' ); ?>" name="<?php echo $this->get_field_name( 'date_show' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'date_show' ); ?>">Display post date?</label>
        </p>

        <hr>
        <p>
            <h3>Author Page Settings :</h3>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $author_post_only ); ?> id="<?php echo $this->get_field_id( 'author_post_only' ); ?>" name="<?php echo $this->get_field_name( 'author_post_only' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'author_post_only' ); ?>">Author post show only ?</label>
        </p>

        <?php
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    public function update( $new_instance, $old_instance ) {
        // processes widget options to be saved
        $instance = $old_instance;

        $instance['title']              = sanitize_text_field( $new_instance['title'] );
        $instance['number_of_posts']    = (int) $new_instance['number_of_posts'];
        $instance['date_show']          = isset( $new_instance['date_show'] ) ? (bool) $new_instance['date_show'] : false;
        $instance['author_post_only']   = isset( $new_instance['author_post_only'] ) ? (bool) $new_instance['author_post_only'] : false;

        return $instance;
    }
}

add_action( 'widgets_init', function(){
    register_widget( 'SMRecentPost_Widget' );
});