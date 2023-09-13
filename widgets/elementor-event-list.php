<?php
class Event_List_Widget extends \Elementor\Widget_Base
{
    // Widget properties
    public function get_name()
    {
        return 'event_list_widget';
    }

    public function get_style_depends()
    {
        return ['event-list-style'];
    }

    public function get_title()
    {
        return esc_html__('Schneuers Event List Widget', 'elementor-addon');
    }

    public function get_icon()
    {
        return 'eicon-calendar'; 
    }

    public function get_categories()
    {
        return ['basic'];
    }

    public function get_keywords()
    {
        return ['hello', 'world'];
    }

    protected function render()
    {
        // Get today's date
        $today = date('Y-m-d');
        $row_number = 1;
        $background_color = get_option('background_color'); // Default to white if not set
        $text_color = get_option('text_color'); // Default to black if not set
        $button_color = get_option('button_color'); // Default to a blue color if not set
        $link_color = get_option('link_color'); // Default to white if not set
        $info_text_color = get_option('info_text_color'); // Default to black if not set

        // Query custom posts
        $args = array(
            'numberposts' => -1,
            'post_type'   => 'event',
            'meta_key'    => 'event_date',
            'orderby'     => 'meta_value',
            'order'       => 'ASC',
            'meta_query'  => array(
                array(
                    'key'       => 'event_date',
                    'value'     => $today,
                    'compare'   => '>=',
                    'type'      => 'DATE',
                ),
            ),
        );
        $custom_posts = get_posts($args);

        // Check if custom posts are found
        if (!empty($custom_posts)) {
            // Start rendering the section

                // Output inline CSS
            echo '<style>';
            echo '.dates { color: ' . esc_attr($text_color) . '; }';
            echo ' a.button.ticket { color: ' . esc_attr($link_color) . '; };';
            echo '.info{ color: ' . esc_attr($info_text_color) . '; }';

            echo '</style>';
            echo '<section id="dates" class="section bg2 dates" role="region" aria-label="dates"><div class="section_wrapper"><div class="section_content">';
            echo '<div id="dates_blocks" class="dates_blocks clearfix">';
            echo '<div class="dateslist_holder">';
            echo '<ul class="dateslist">';

            // Loop through custom posts
            foreach ($custom_posts as $custom_post) {
                $event_status = get_post_meta($custom_post->ID, 'event_status', true);
                $event_url = get_post_meta($custom_post->ID, 'event_url', true);
                $event_description = get_post_meta($custom_post->ID, 'event_description', true);

                // Determine the item class based on event status
                $item_class = ($event_status === 'abgesagt') ? 'item row' . $row_number . ' canceled-item' : 'item row' . $row_number;

                echo '<li class="' . esc_attr($item_class) . '">';

                // Render event URL link if it exists and status is 'vvk'
                if ($event_url && $event_status === 'vvk') {
                    echo '<a href="' . $event_url . '" class="deeplink" title="Show more details">&nbsp;</a>';
                }

                echo '<div class="date">' . date('d.m.y', strtotime(get_post_meta($custom_post->ID, 'event_date', true))) . '</div>';
                echo '<div class="title">' . esc_html($custom_post->post_title);

                if ($event_description) {
                    echo '<div class="info">' . $event_description . '</div></div>';
                } else {
                    echo '</div>';
                }

                echo '<div class="buttons">';
                // Render appropriate button based on event status
                switch ($event_status) {
                    case 'geplant':
                        echo '<span class="planned">' . get_option('default_geplant', '') . '</span>';
                        break;

                    case 'vvk':
                        echo '<a data-umg-type="Tickets" class="button ticket ticket' . $row_number . '" href="' . $event_url . '" target="_blank" rel="noopener">' . get_option('default_vvk', '') . '</a>';
                        break;

                    case 'ausverkauft':
                        echo '<span class="ausverkauft">' . get_option('default_ausverkauft', '') . '</span>';
                        break;

                    case 'abgesagt':
                        echo '<span>' . get_option('default_abgesagt', '') . '</span>';
                        break;

                    case 'no_vvk':
                        echo '<span>' . get_option('default_no_vvk', '') . '</span>';
                        break;

                    default:
                        echo ''; // Handle unknown status values
                        break;
                }

                echo '</div>';
                echo '</li>';

                $row_number++;
            }

            // End rendering the section
            echo '</ul>';
            echo '</div>';
            echo '</div>';
            echo '</div></div></section>';
        } else {
            // Display message when no events found
            echo '<p>' . get_option('default_no_events', '') . '</p>';
        }
    }
}
