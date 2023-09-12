<?php
/**
 * Register the 'Veranstaltungen' custom post type
 */
function custom_events_register_post_type()
{
    $labels = array(
        'name'               => 'Veranstaltungen',
        'singular_name'      => 'Veranstaltung',
        'add_new'            => 'Neue Veranstaltung',
        'add_new_item'       => 'Neue Veranstaltung',
        'edit_item'          => 'Veranstaltung bearbeiten',
        'new_item'           => 'Neue Veranstaltung',
        'view_item'          => 'Veranstaltung Ansehen',
        'search_items'       => 'Suche Veranstaltungen',
        'not_found'          => 'Keine Veranstaltungen gefunden.',
        'not_found_in_trash' => 'Keine Veranstaltungen gefunden.',
        'menu_name'          => 'Veranstaltungen',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'has_archive'         => false,
        'publicly_queryable'  => false,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'events'),
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array('title'),
        'menu_icon'           => 'dashicons-calendar',
    );

    register_post_type('event', $args);
}
add_action('init', 'custom_events_register_post_type');

/**
 * Add custom fields to the 'Veranstaltungen' custom post type
 */
function custom_events_add_custom_fields()
{
    add_meta_box('event_meta', 'Veranstaltung Details', 'custom_events_render_fields', 'event', 'normal', 'high');
}
add_action('add_meta_boxes', 'custom_events_add_custom_fields');

/**
 * Render custom fields
 */
function custom_events_render_fields($post)
{
    // Retrieve saved values (if any)
    $event_date = get_post_meta($post->ID, 'event_date', true);
    $description = get_post_meta($post->ID, 'event_description', true);
    $event_url = get_post_meta($post->ID, 'event_url', true);
    $event_status = get_post_meta($post->ID, 'event_status', true);

    // Check if $event_date is empty and set it to the current date if needed
    if (empty($event_date)) {
        $event_date = date('Y-m-d');
    }

    // Set the post title to the default_title
    $post->post_title = get_option('default_title', '');

    ?>
    <div class="meta_values">
        <div class="meta-field">
            <div class="meta-label"><label for="event_date">Veranstaltungsdatum:</label></div>
            <div class="meta-input"><input type="date" id="event_date" name="event_date" value="<?= esc_attr($event_date); ?>"></div>
        </div>
        <div class="meta-field">
            <div class="meta-label"><label for="event_description">Veranstaltungsbeschreibung:</label></div>
            <div class="meta-input"><textarea id="event_description" name="event_description"><?= esc_textarea($description); ?></textarea></div>
        </div>
        <div class="meta-field">
            <div class="meta-label"><label for="event_url">Ticket-Link</label></div>
            <div class="meta-input"><input type="url" id="event_url" name="event_url" value="<?= esc_url($event_url); ?>"></div>
        </div>
        <div class="meta-field">
            <div class="meta-label"><label for="event_status">Event Status</label></div>
            <div class="meta-input">
                <ul class="meta-radio-list" data-allow_null="0" data-other_choice="0">
                    <li><label><input type="radio" id="geplant" name="event_status" value="geplant" <?= checked(empty($event_status) || $event_status === 'geplant', true); ?>>Geplant</label></li>
                    <li><label><input type="radio" id="vvk" name="event_status" value="vvk" <?= checked($event_status, 'vvk'); ?>>VVK</label></li>
                    <li><label><input type="radio" id="no_vvk" name="event_status" value="no_vvk" <?= checked($event_status, 'no_vvk'); ?>>Nur Abendkasse</label></li>
                    <li><label><input type="radio" id="ausverkauft" name="event_status" value="ausverkauft" <?= checked($event_status, 'ausverkauft'); ?>>Ausverkauft</label></li>
                    <li><label><input type="radio" id="abgesagt" name="event_status" value="abgesagt" <?= checked($event_status, 'abgesagt'); ?>>Abgesagt</label></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Save custom field values when the post is saved
 */
function custom_events_save_custom_fields($post_id)
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Check if "vvk" is selected in "event_status"
    if (isset($_POST['event_status']) && $_POST['event_status'] === 'vvk') {
        // Check if the "event_url" is empty
        if (empty($_POST['event_url'])) {
            // The "Ticket-Link" is required when "VVK" is selected in "Event Status"
            // You can add an error message or handle it as needed
            // For example, you can display an admin notice
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>The "Ticket-Link" is required when "VVK" is selected in "Event Status".</p></div>';
            });
            // Prevent saving the post
            return;
        }
    }

    // Save custom fields
    $custom_fields = array('event_date', 'event_description', 'event_url', 'event_status');
    foreach ($custom_fields as $field) {
        if (isset($_POST[$field])) {
            // If the field is 'event_date', convert it to the date format
            if ($field === 'event_date') {
                $date_value = sanitize_text_field($_POST[$field]);
                // Check if it's a valid date before saving
                if (strtotime($date_value)) {
                    $date_value = date('Y-m-d', strtotime($date_value));
                } else {
                    // Handle invalid dates or leave it empty
                    $date_value = '';
                }
                update_post_meta($post_id, $field, $date_value);
            } else {
                $value = sanitize_text_field($_POST[$field]);
                update_post_meta($post_id, $field, $value);
            }
        }
    }
}
add_action('save_post', 'custom_events_save_custom_fields');

/**
 * Add custom columns to the admin overview table
 */
function custom_events_columns($columns)
{
    // Define a new array for reordered columns
    $new_columns = array(
        'event_date'   => 'Veranstaltungsdatum', // Event Date as the first column
        'title'        => 'Title', // Default Title column
        'event_status' => 'Event Status',
    );

    // Merge the new columns with the existing columns
    $columns = array_merge($new_columns, $columns);

    return $columns;
}
add_filter('manage_edit-event_columns', 'custom_events_columns');


/**
 * Make the "Veranstaltungsdatum" and "Event Status" columns sortable
 */
function custom_events_sortable_columns($sortable_columns)
{
    $sortable_columns['event_date'] = 'event_date'; // Sort by date
    $sortable_columns['event_status'] = 'event_status'; // Sort by text
    return $sortable_columns;
}
add_filter('manage_edit-event_sortable_columns', 'custom_events_sortable_columns');

/**
 * Modify the query to handle sorting for "Veranstaltungsdatum" and "Event Status"
 */
function custom_events_custom_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('post_type') === 'event') {
        $orderby = $query->get('orderby');

        // Sort by "Veranstaltungsdatum"
        if ('event_date' === $orderby) {
            $query->set('meta_key', 'event_date');
            $query->set('orderby', 'meta_value');
        }

        // Sort by "Event Status"
        if ('event_status' === $orderby) {
            $query->set('meta_key', 'event_status');
            $query->set('orderby', 'meta_value');
        }
    }
}
add_action('pre_get_posts', 'custom_events_custom_orderby');

/**
 * Populate the custom columns with data
 */
function custom_events_custom_column($column, $post_id)
{
    switch ($column) {
        case 'event_date':
            $event_date = get_post_meta($post_id, 'event_date', true);
            $formatted_date = date('d.m.y', strtotime($event_date));
            echo esc_html($formatted_date);
            break;

        case 'event_status':
            $event_status = get_post_meta($post_id, 'event_status', true);

            switch ($event_status) {
                case 'geplant':
                    echo esc_html(get_option('default_geplant', ''));
                    break;

                case 'vvk':
                    echo esc_html(get_option('default_vvk', ''));
                    break;

                case 'ausverkauft':
                    echo esc_html(get_option('default_ausverkauft', ''));
                    break;

                case 'abgesagt':
                    echo esc_html(get_option('default_abgesagt', ''));
                    break;

                case 'no_vvk':
                    echo esc_html(get_option('default_no_vvk', ''));
                    break;

                default:
                    echo ''; // Handle unknown status values
                    break;
            }
            break;
    }
}
add_action('manage_event_posts_custom_column', 'custom_events_custom_column', 10, 2);

/**
 * Custom Post Einstellungen
 */
function custom_events_settings_menu()
{
    add_submenu_page(
        'edit.php?post_type=event', // Replace 'event' with your custom post type name
        'Event Einstellungen',
        'Event Einstellungen',
        'manage_options',
        'custom-events-settings',
        'custom_events_render_settings_page'
    );
}
add_action('admin_menu', 'custom_events_settings_menu');

function custom_events_render_settings_page()
{
    ?>
    <div class="wrap">
        <h2>Event Einstellungen</h2>
        <form method="post" action="options.php">
            <?php settings_fields('custom_events_settings_group'); ?>
            <?php do_settings_sections('custom-events-settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

function custom_events_settings_init()
{
    // Register a section for your settings
    add_settings_section(
        'custom_events_settings_section',
        'Event Default Values',
        'custom_events_settings_section_callback',
        'custom-events-settings'
    );

    // Define an array of settings and their labels
    $settings = array(
        'default_title'       => 'Standard-Titel',
        'default_geplant'     => 'Standard-Wert für "Geplante Veranstaltungen"',
        'default_vvk'         => 'Standard-Wert für "VVK"',
        'default_no_vvk'      => 'Standard-Wert für "Keinen VVK"',
        'default_ausverkauft' => 'Standard-Wert für "Ausverkauft"',
        'default_abgesagt'    => 'Standard-Wert für "Abgesagt"',
        'default_no_events'   => 'Standard-Wert für "Keine Veranstaltungen"',
    );

    // Loop through the settings array and register each setting and field
    foreach ($settings as $setting_key => $setting_label) {
        register_setting('custom_events_settings_group', $setting_key);

        add_settings_field(
            $setting_key,
            $setting_label,
            'custom_events_default_setting_callback',
            'custom-events-settings',
            'custom_events_settings_section',
            array('setting_key' => $setting_key)
        );
    }
}
add_action('admin_init', 'custom_events_settings_init');

function custom_events_settings_section_callback()
{
    echo '<p>Standard-Werte für das Plugin.</p>';
}

function custom_events_default_setting_callback($args)
{
    $setting_key = $args['setting_key'];
    $setting_value = get_option($setting_key, '');

    ?>
    <input type="text" name="<?php echo $setting_key; ?>" value="<?php echo esc_attr($setting_value); ?>" />
    <?php
}

// Retrieve the default event values using a loop
$default_settings = array(
    'default_title',
    'default_geplant',
    'default_vvk',
    'default_no_vvk',
    'default_ausverkauft',
    'default_abgesagt',
    'default_no_events',
);

$default_values = array();

foreach ($default_settings as $setting) {
    $default_values[$setting] = get_option($setting, '');
}

// Now, you can access the default values using $default_values['setting_name']
