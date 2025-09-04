<?php

/**
 * @author Kopf & Hand
 * @package Kopf & Hand Theme
 * @copyright 25.01.2025
 * @link https://kopf-hand.de
 */

// Sicherheitsprüfung
if (!defined('ABSPATH')) {
    exit; // Beendet die Ausführung des Skripts, wenn ABSPATH nicht definiert ist
}

// Prefetch-Link überarbeiten
add_action('template_redirect', function () {
    ob_start(function ($buffer) {
        return str_replace('<link rel="prefetch" href="http://0.0.0.1/" />', '<link rel="prefetch" href="' . home_url() . '" />', $buffer);
    });
});

// Styles
function enqueue_child_theme_styles() {
    $parent_style = 'divi-style';
    wp_enqueue_style($parent_style, get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array($parent_style));
    
    // Font Awesome laden (wie in style.css erwähnt)
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', array(), '6.0.0');
    
    // Performance: Critical resources preloaden
    echo '<link rel="preload" href="' . get_stylesheet_uri() . '" as="style">';
    echo '<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" as="style">';
}
add_action('wp_enqueue_scripts', 'enqueue_child_theme_styles', 10);



// Scripts
function enqueue_child_theme_scripts() {
    $script_uri = get_stylesheet_directory_uri() . '/script.js';
    $nav_uri = get_stylesheet_directory_uri() . '/main-nav.js';

    if (file_exists(get_stylesheet_directory() . '/script.js')) {
        wp_enqueue_script('child-script', $script_uri, array('jquery'), null, true);
    }
    if (file_exists(get_stylesheet_directory() . '/main-nav.js')) {
        wp_enqueue_script('main-nav', $nav_uri, array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_child_theme_scripts', 20);


//META -- Barrierefrei ZOOM
function custom_meta_viewport() {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">' . "\n";
}
remove_action('wp_head', 'et_add_viewport_meta'); // Entfernt Divis eigene Viewport-Einstellung
add_action('wp_head', 'custom_meta_viewport', 1);



// Absendername ändern
function custom_wp_mail_from_name($original_from_name) {
    return 'nautimo'; // Dein gewünschter Absendername
}
add_filter('wp_mail_from_name', 'custom_wp_mail_from_name');

// Absender-E-Mail-Adresse ändern
function custom_wp_mail_from($original_email_address) {
    return 'info@nautimo.de';
}
add_filter('wp_mail_from', 'custom_wp_mail_from');




// Custom Post Types hinzufügen
function register_custom_post_types() {
    $post_types = [
        'mitternachtssauna' => 'Mitternachtssauna',
        'jobs' => 'Jobs',
        'spa-angebote' => 'Spa-Angebote',
        'spa-wellness' => 'Spa-Wellness' 
    ];
    
    foreach ($post_types as $slug => $name) {
        register_post_type($slug, [
            'labels' => [
                'name' => $name,
                'singular_name' => $name,
                'add_new_item' => "Neues $name hinzufügen",
                'edit_item' => "$name bearbeiten",
                'new_item' => "Neues $name",
                'view_item' => "$name ansehen",
                'search_items' => "$name durchsuchen",
                'not_found' => "Keine $name gefunden",
                'not_found_in_trash' => "Keine $name im Papierkorb gefunden",
            ],
            'public' => true,
            'publicly_queryable' => true,
            'has_archive' => true,
            'menu_position' => 5,
            'taxonomies'  => ['category'], 
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'rewrite' => ['slug' => $slug],
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'graphql_single_name' => str_replace('-', '_', $slug),
            'graphql_plural_name' => str_replace('-', '_', $slug) . 's',
            'capability_type' => 'post',
        ]);
    }
}
add_action('init', 'register_custom_post_types');

// Kategorien zu Custom Post Types hinzufügen
function add_categories_to_custom_post_types() {
    $post_types = ['mitternachtssauna', 'jobs', 'spa-angebote', 'spa-wellness'];
    
    foreach ($post_types as $slug) {
        register_taxonomy_for_object_type('category', $slug);
    }
}
add_action('init', 'add_categories_to_custom_post_types');

// Custom Taxonomien hinzufügen
function register_custom_taxonomies() {
    $taxonomies = [
        'saunathemen' => ['mitternachtssauna'],
        'job-kategorien' => ['jobs'],
        'spa-kategorien' => ['spa-angebote'], 
        'spa-wellness-kategorien' => ['spa-wellness']
    ];
    
    foreach ($taxonomies as $slug => $post_types) {
        register_taxonomy($slug, $post_types, [
            'labels' => [
                'name' => ucwords(str_replace('-', ' ', $slug)),
                'singular_name' => ucwords(str_replace('-', ' ', $slug)),
                'search_items' => ucwords(str_replace('-', ' ', $slug)) . ' durchsuchen',
                'all_items' => 'Alle ' . ucwords(str_replace('-', ' ', $slug)),
                'edit_item' => ucwords(str_replace('-', ' ', $slug)) . ' bearbeiten',
                'update_item' => ucwords(str_replace('-', ' ', $slug)) . ' aktualisieren',
                'add_new_item' => 'Neue ' . ucwords(str_replace('-', ' ', $slug)) . ' hinzufügen',
                'new_item_name' => 'Neuer Name für ' . ucwords(str_replace('-', ' ', $slug))
            ],
            'hierarchical' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => $slug],
        ]);
    }
}
add_action('init', 'register_custom_taxonomies');

// Theme Support für Thumbnails
function theme_setup() {
    if (function_exists('add_theme_support')) {
        add_theme_support('post-thumbnails');
    }
}
add_action('after_setup_theme', 'theme_setup');






function enable_tickets_for_spa_custom_posts($post_types) {
    $post_types[] = 'spa-angebote'; 
    $post_types[] = 'spa-wellness';
    return $post_types;
}
add_filter('tribe_tickets_post_types', 'enable_tickets_for_spa_custom_posts');



// Google Fonts entfernen
function remove_google_fonts() {
    wp_dequeue_style('divi-fonts');
}
add_action('wp_enqueue_scripts', 'remove_google_fonts', 20);




// Divi umbenennen
function rename_divi_menu() {
    global $menu;
    if (!is_array($menu)) return;
    
    foreach ($menu as $key => $value) {
        if (isset($value[0]) && $value[0] === 'Divi') {
            $menu[$key][0] = 'Kopf & Hand';
        }
    }
}
add_action('admin_init', 'rename_divi_menu', 99);

// Icon für Divi-Menü anpassen
function customize_divi_menu_icon_css() {
    echo '<style>
        #adminmenu .menu-icon-divi div.wp-menu-image {
            background: url("https://kopf-hand.de/wp-content/uploads/2023/07/thumbnail-20x20-1.png") no-repeat center !important;
            background-size: contain !important;
        }
        #adminmenu .menu-icon-divi div.wp-menu-image img {
            display: none;
        }
    </style>';
}
add_action('admin_head', 'customize_divi_menu_icon_css');



// Mixed Content Fix für HTTPS
function force_https_content($content) {
    if (is_ssl()) {
        $home_url = home_url('/', 'http');
        $content = str_replace($home_url, home_url('/', 'https'), $content);
    }
    return $content;
}
add_filter('the_content', 'force_https_content', 99);



//SVG
function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

function sanitize_svg($file, $filename, $mimes) {
    if (strpos($filename, '.svg') !== false) {
        $file['type'] = 'image/svg+xml';
    }
    return $file;
}
add_filter('wp_check_filetype_and_ext', 'sanitize_svg', 10, 3);



//
//
//
//
//BEITRÄGE
//
//
//
function get_latest_post_permalink() {
    $latest_post = get_posts(['numberposts' => 1]);
    if (!empty($latest_post)) {
        return get_permalink($latest_post[0]->ID);
    }
    return home_url();
}

// Speichert die URL des neuesten Blogbeitrags als Theme-Option
function update_latest_post_url() {
    $latest_post = get_posts(['numberposts' => 1]);
    if (!empty($latest_post)) {
        update_option('latest_post_url', get_permalink($latest_post[0]->ID));
    } else {
        update_option('latest_post_url', home_url());
    }
}
// Aktualisiert die URL bei jedem neuen Beitrag
add_action('publish_post', 'update_latest_post_url');
add_action('save_post', 'update_latest_post_url');

// TITEL DES AKTUELLEN BEITRAGS
function latest_post_title_simple() {
    $post = get_posts(['numberposts' => 1]);
    return !empty($post) ? get_the_title($post[0]->ID) : '';
  }
  add_shortcode('latest_post_title', 'latest_post_title_simple');
  
// Shortcode für Divi, um die gespeicherte URL abzurufen
function latest_post_url() {
    $latest_url = get_transient('latest_post_url');

    if ($latest_url === false) { 
        $latest_post = new WP_Query([
            'post_type'      => 'post',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'fields'         => 'ids'
        ]);

        if ($latest_post->have_posts()) {
            $latest_url = get_permalink($latest_post->posts[0]);
        } else {
            $latest_url = home_url();
        }

        set_transient('latest_post_url', $latest_url, 10 * MINUTE_IN_SECONDS);
    }

    return $latest_url;
}
add_shortcode('latest_post', 'latest_post_url');


function clear_latest_post_url_cache() {
    delete_transient('latest_post_url');
}
add_action('publish_post', 'clear_latest_post_url_cache');




//
//
//
//
//KURSE
//
//
//
// Helper: nächstes Event holen
function kh_get_next_event() {
    $args = [
        'posts_per_page' => 1,
        'post_type'      => 'tribe_events',
        'meta_key'       => '_EventStartDate',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => '_EventStartDate',
                'value'   => current_time( 'Y-m-d H:i:s' ),
                'compare' => '>=',
                'type'    => 'DATETIME',
            ],
        ],
    ];
    $events = tribe_get_events( $args );
    return ! empty( $events ) ? $events[0] : null;
}

// Shortcode: URL der nächsten Veranstaltung
function kh_latest_event_url() {
    $cache_key = 'latest_event_url';
    if ( false !== ( $url = get_transient( $cache_key ) ) ) {
        return $url;
    }

    $event = kh_get_next_event();
    if ( $event ) {
        $url = get_permalink( $event->ID );
        set_transient( $cache_key, $url, 5 * MINUTE_IN_SECONDS );
        return $url;
    }

    return home_url();
}
add_shortcode( 'latest_event', 'kh_latest_event_url' );

// Shortcode: Titel der nächsten Veranstaltung
function kh_latest_event_title() {
    $cache_key = 'latest_event_title';
    if ( false !== ( $title = get_transient( $cache_key ) ) ) {
        return $title;
    }

    $event = kh_get_next_event();
    if ( $event ) {
        $title = get_the_title( $event->ID );
        set_transient( $cache_key, $title, 5 * MINUTE_IN_SECONDS );
        return $title;
    }

    return 'Keine Veranstaltung gefunden';
}
add_shortcode( 'latest_event_title', 'kh_latest_event_title' );

// Inline-Style im <head> mit Thumbnail-Hintergrund
function kh_latest_event_thumb_style() {
    $cache_key = 'latest_event_thumb_url';
    if ( false === ( $url = get_transient( $cache_key ) ) ) {
        $event = kh_get_next_event();
        if ( $event && has_post_thumbnail( $event->ID ) ) {
            $url = get_the_post_thumbnail_url( $event->ID, 'full' );
            set_transient( $cache_key, $url, 5 * MINUTE_IN_SECONDS );
        }
    }

    if ( ! empty( $url ) ) {
        echo "<style>
            .kurse-bg-column {
                background-image: linear-gradient(180deg,rgba(34,30,34,0.25) 0%,rgba(34,30,34,0.5) 100%), url('". esc_url( $url ) ."') !important;
                background-size: cover;
                background-position: center;
            }
        </style>";
    }
}
add_action( 'wp_head', 'kh_latest_event_thumb_style' );

// Shortcode: Zeit der nächsten Veranstaltung (nur Uhrzeiten)
function kh_latest_event_time_simple() {
    $cache_key = 'latest_event_time_simple';
    if ( false !== ( $time = get_transient( $cache_key ) ) ) {
        return $time;
    }

    $event = kh_get_next_event();
    if ( $event ) {
        // tribe_get_start_date($event, $display_time, $format)
        $start = tribe_get_start_date( $event, false, 'H:i' );
        $end   = tribe_get_end_date(   $event, false, 'H:i' );
        $time  = sprintf( '%s – %s', $start, $end );
        set_transient( $cache_key, $time, 5 * MINUTE_IN_SECONDS );
        return $time;
    }

    return '';
}
add_shortcode( 'latest_event_time', 'kh_latest_event_time_simple' );

// Shortcode: Tag der nächsten Veranstaltung (relativ + Datum)
function kh_latest_event_day_relative() {
    $cache_key = 'latest_event_day_relative';
    if ( false !== ( $day = get_transient( $cache_key ) ) ) {
        return $day;
    }

    $event = kh_get_next_event();
    if ( $event ) {
        $start_raw = get_post_meta( $event->ID, '_EventStartDate', true );
        $start_dt  = new DateTime( $start_raw );
        $today     = new DateTime( current_time( 'Y-m-d' ) );
        $tomorrow  = (clone $today)->modify('+1 day');

        if ( $start_dt->format('Y-m-d') === $today->format('Y-m-d') ) {
            $day = 'Heute';
        } elseif ( $start_dt->format('Y-m-d') === $tomorrow->format('Y-m-d') ) {
            $day = 'Morgen';
        } else {
            $day = $start_dt->format('d.m.Y');
        }

        set_transient( $cache_key, $day, 5 * MINUTE_IN_SECONDS );
        return $day;
    }

    return '';
}
add_shortcode( 'latest_event_day', 'kh_latest_event_day_relative' );


// Cache leeren, wenn Events erstellt, aktualisiert oder gelöscht werden
function kh_clear_event_cache() {
    delete_transient( 'latest_event_url' );
    delete_transient( 'latest_event_title' );
    delete_transient( 'latest_event_thumb_url' );
}
add_action( 'tribe_events_event_created', 'kh_clear_event_cache' );
add_action( 'tribe_events_event_updated', 'kh_clear_event_cache' );
add_action( 'save_post_tribe_events',     'kh_clear_event_cache' );
add_action( 'delete_post',                'kh_clear_event_cache' );








//
//
//
//
//EVENT SAUNA
//
//
//
//Shortcode EVent Kategorie = BUTTON = [latest_event_category] // Title = [latest_event_category_title]
function latest_upcoming_event_in_category_url() {
    $args = [
        'posts_per_page' => 1,
        'post_type'      => 'tribe_events',
        'meta_key'       => '_EventStartDate',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => '_EventEndDate',
                'value'   => current_time('Y-m-d H:i:s'),
                'compare' => '>=', // Nur Events, deren Enddatum in der Zukunft liegt
                'type'    => 'DATETIME'
            ]
        ],
        'tax_query'      => [
            [
                'taxonomy' => 'tribe_events_cat', // The Events Calendar Kategorie
                'field'    => 'slug',
                'terms'    => 'mitternachtssauna', // Hier den Slug der Kategorie anpassen
            ],
        ],
    ];

    $events = tribe_get_events($args);

    if (!empty($events)) {
        return get_permalink($events[0]->ID);
    }

    return home_url(); // Falls keine Veranstaltung existiert, zur Startseite weiterleiten
}
add_shortcode('latest_event_category', 'latest_upcoming_event_in_category_url');

function latest_upcoming_event_in_category_title() {
    $args = [
        'posts_per_page' => 1,
        'post_type'      => 'tribe_events',
        'meta_key'       => '_EventStartDate',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => '_EventEndDate',
                'value'   => current_time('Y-m-d H:i:s'),
                'compare' => '>=', // Nur Events, deren Enddatum in der Zukunft liegt
                'type'    => 'DATETIME'
            ]
        ],
        'tax_query'      => [
            [
                'taxonomy' => 'tribe_events_cat',
                'field'    => 'slug',
                'terms'    => 'mitternachtssauna', // Hier den Slug der Kategorie anpassen
            ],
        ],
    ];

    $events = tribe_get_events($args);

    if (!empty($events)) {
        return get_the_title($events[0]->ID);
    }

    return 'Keine Veranstaltung gefunden';
}
add_shortcode('latest_event_category_title', 'latest_upcoming_event_in_category_title');

// SAUNA BILD Helper: holt das nächste Event-Objekt
function koh_get_next_event( $category_slug = 'mitternachtssauna' ) {
    $args = [
        'posts_per_page' => 1,
        'post_type'      => 'tribe_events',
        'meta_key'       => '_EventStartDate',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [[
            'key'     => '_EventEndDate',
            'value'   => current_time('Y-m-d H:i:s'),
            'compare' => '>=',
            'type'    => 'DATETIME',
        ]],
        'tax_query'      => [[
            'taxonomy' => 'tribe_events_cat',
            'field'    => 'slug',
            'terms'    => $category_slug,
        ]],
    ];
    $events = tribe_get_events( $args );
    return ! empty( $events ) ? $events[0] : false;
}

// Shortcode: gibt nur URL zurück
function koh_latest_event_image_url( $atts ) {
    $atts = shortcode_atts( ['size' => 'large'], $atts, 'latest_event_image_url' );
    $event = koh_get_next_event();
    if ( ! $event ) {
        return '';
    }
    return esc_url( get_the_post_thumbnail_url( $event->ID, $atts['size'] ) );
}
add_shortcode( 'latest_event_image_url', 'koh_latest_event_image_url' );

add_action('wp_enqueue_scripts', function(){
    wp_register_style('child-style', get_stylesheet_uri());
    wp_enqueue_style('child-style');
    $url = do_shortcode('[latest_event_image_url size="large"]');
    wp_add_inline_style('child-style', "
      .event-bg-column {
        background-image: linear-gradient(180deg,rgba(34,30,34,0.25) 0%,rgba(34,30,34,0.5) 100%), url('{$url}') !important;
      }
    ");
  });

  // Shortcode: Tag der nächsten Veranstaltung in Kategorie (Heute, Morgen, sonst Datum)
function koh_latest_event_day_relative( $atts ) {
    // Optional: Kategorie per Attribut übergeben, Standard ist 'mitternachtssauna'
    $atts = shortcode_atts( ['category' => 'mitternachtssauna'], $atts, 'latest_event_category_day' );
    $cache_key = 'latest_event_day_rel_' . sanitize_key( $atts['category'] );
    
    if ( false !== ( $day = get_transient( $cache_key ) ) ) {
        return $day;
    }

    // Holt das nächste Event in der gewünschten Kategorie
    $event = koh_get_next_event( $atts['category'] );
    if ( ! $event ) {
        return '';
    }

    $start_raw = get_post_meta( $event->ID, '_EventStartDate', true );
    $start_dt  = new DateTime( $start_raw );
    $today     = new DateTime( current_time( 'Y-m-d' ) );
    $tomorrow  = (clone $today)->modify('+1 day');

    if ( $start_dt->format('Y-m-d') === $today->format('Y-m-d') ) {
        $day = 'Heute';
    } elseif ( $start_dt->format('Y-m-d') === $tomorrow->format('Y-m-d') ) {
        $day = 'Morgen';
    } else {
        $day = $start_dt->format('d.m.Y');
    }

    set_transient( $cache_key, $day, 5 * MINUTE_IN_SECONDS );
    return $day;
}
add_shortcode( 'latest_event_category_day', 'koh_latest_event_day_relative' );

  





//Übersetzung
function custom_translate_event_tickets( $translated_text, $text, $domain ) {
    if ( $domain === 'event-tickets' ) {
        $translations = array(
            'First and last name' => 'Vor- und Nachname',
            'Your email address is required' => 'Deine E-Mail-Adresse ist erforderlich',
            'Email address' => 'E-Mail-Adresse',
            'Your first and last names are required' => 'Dein Vor- und Nachname ist erforderlich',
            'Your tickets will be sent to this email address' => 'Dein(e) Ticket(s) werden an diese E-Mail-Adresse gesendet',
            'attendee-phone' => 'Telefonnummer',

        );

        if ( isset( $translations[$text] ) ) {
            return $translations[$text];
        }

        // Dynamischer Teil: z. B. "Attendee 1"
        if ( strpos( $text, 'Attendee ' ) === 0 ) {
            return str_replace( 'Attendee', 'Teilnehmer', $text );
        }
    }
    return $translated_text;
}
add_filter( 'gettext', 'custom_translate_event_tickets', 10, 3 );

add_filter( 'gettext_with_context', 'custom_translate_event_tickets_with_context', 10, 4 );

function custom_translate_event_tickets_with_context( $translated_text, $text, $context, $domain ) {
    if ( $domain === 'event-tickets' && $text === 'Attendee' && $context === 'ticket-form' ) {
        return 'Teilnehmer';
    }
    return $translated_text;
}

add_filter( 'tribe_tickets_attendee_registration_field_label', function( $label, $field ) {
    if ( isset( $field['slug'] ) && $field['slug'] === 'attendee-phone' ) {
        return 'Telefonnummer';
    }
    return $label;
}, 10, 2 );




//Platzhalter
function add_placeholder_script() {
    ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let emailInputs = document.querySelectorAll("input[type='email']");
            emailInputs.forEach(input => input.placeholder = "z. B. beispiel@e-mail.de");

            let nameInputs = document.querySelectorAll("input[name='your-name']");
            nameInputs.forEach(input => input.placeholder = "z. B. Maxime Mustermensch");
        });
    </script>
    <?php
}
add_action('wp_footer', 'add_placeholder_script');



//Events nicht mit Divi
function disable_divi_for_events($enabled, $post_type) {
    if ($post_type === 'tribe_events') { // Oder 'event' falls dein Post Type anders heißt
        return false;
    }
    return $enabled;
}
add_filter('et_builder_should_load_for_post_type', 'disable_divi_for_events', 10, 2);



function custom_tribe_price_format($formatted_price, $cost, $post_id) {
    if (!empty($formatted_price)) {
        $formatted_price = preg_replace('/(\d)(€)/', '$1 €', $formatted_price); // Sicherstellen, dass ein Leerzeichen vor € eingefügt wird
    }
    return $formatted_price;
}
add_filter('tribe_get_formatted_cost', 'custom_tribe_price_format', 10, 3);


//ALLE € Leerzeichen
function fix_currency_spacing_final() {
    ?>
    <script>
        function fixCurrencySpacing() {
            // 1. Falls Preis & Währung im gleichen Element stehen (.decm_price)
            document.querySelectorAll(".decm_price").forEach(function(el) {
                el.innerHTML = el.innerHTML.replace(/(\d)(€)/g, "$1 €");
            });

            // 2. Falls Währung in separatem <span class="tribe-currency-symbol"> steht
            document.querySelectorAll(".tribe-currency-symbol").forEach(function(el) {
                if (el.innerHTML.trim().charAt(0) !== " ") {
                    el.innerHTML = " " + el.innerHTML.trim();
                }
            });

            // 3. Falls Preis & Währung im Checkout (tribe-tickets) stehen
            document.querySelectorAll(".tribe-tickets__commerce-checkout-cart-item-subtotal").forEach(function(el) {
                el.innerHTML = el.innerHTML.replace(/(\d)(€)/g, "$1 €");
            });

            // 4. Falls das neue Element .tec-tickets-price.amount betroffen ist
            document.querySelectorAll(".tec-tickets-price.amount").forEach(function(el) {
                el.innerHTML = el.innerHTML.replace(/(\d)(€)/g, "$1 €");
            });

            // 5. Falls irgendwo noch "123,45€" direkt im <span> oder <div> steht
            document.querySelectorAll("span, div").forEach(function(el) {
                if (el.innerHTML.match(/^\d+,\d+€$/)) { 
                    el.innerHTML = el.innerHTML.replace(/(\d)(€)/g, "$1 €");
                }
            });
        }

        // Direkt beim Laden der Seite ausführen
        document.addEventListener("DOMContentLoaded", fixCurrencySpacing);

        // Falls AJAX von The Events Calendar genutzt wird, auch nach DOM-Updates ausführen
        document.addEventListener("tribe-events-bar-rendered", fixCurrencySpacing);
        document.addEventListener("tribe-events-bar-updated", fixCurrencySpacing);
    </script>
    <?php
}
add_action('wp_footer', 'fix_currency_spacing_final');

//ZOOM Barrierefrei /* Danke nexTab */
// === VORSCHLAG 1: Einheitliches Viewport-Meta & Zoom ===
function kh_custom_viewport_meta() {
    // Divis default entfernen
    remove_action('wp_head', 'et_add_viewport_meta');
    // eigenes, barrierefreies Meta
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.1, maximum-scale=10.0, user-scalable=yes" />' . "\n";
}
add_action('wp_head', 'kh_custom_viewport_meta', 1);


//Standard Title als AltBild falls keins vorhanden
function nxt_auto_title_attr($content) {
    return preg_replace_callback('/<a (.*?)>(.*?)<\/a>/', function ($matches) {
        if (strpos($matches[1], 'title=') === false) {
            return '<a ' . $matches[1] . ' title="' . strip_tags($matches[2]) . '">' . $matches[2] . '</a>';
        }
        return $matches[0];
    }, $content);
}
add_filter('the_content', 'nxt_auto_title_attr');


//ARIA Landmarks
add_action('wp_head', function() {
    ob_start(function($output) {
        return str_replace(
            ['<header', '<nav', '<main', '<aside', '<footer'],
            ['<header role="banner"', '<nav role="navigation"', '<main role="main"', '<aside role="complementary"', '<footer role="contentinfo"'],
            $output
        );
    });
});



//Automatische Untertitel
function nxt_force_youtube_subtitles($content) {
    return str_replace('youtube.com/embed/', 'youtube.com/embed/?cc_load_policy=1&', $content);
}
add_filter('the_content', 'nxt_force_youtube_subtitles');

//Verbesserung der Screenreader
function nxt_screenreader_text() {
    echo '<style>
        .screen-reader-text {
            position: absolute !important;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            border: 0;
        }
    </style>';
}
add_action('wp_head', 'nxt_screenreader_text');

//Ticker Meldungen
function create_ticker_post_type() {
    register_post_type('ticker', array(
        'labels' => array(
            'name'                  => __('Ticker‑Meldungen'),
            'singular_name'         => __('Ticker‑Meldung'),
            'menu_name'             => __('Ticker‑Meldungen'),
            'all_items'             => __('Alle Ticker‑Meldungen'),
            'add_new'               => __('Ticker hinzufügen'),
            'add_new_item'          => __('Neue Ticker‑Meldung hinzufügen'),
            'edit_item'             => __('Ticker‑Meldung bearbeiten'),
            'new_item'              => __('Neue Ticker‑Meldung'),
            'view_item'             => __('Ticker‑Meldung ansehen'),
            'search_items'          => __('Ticker‑Meldungen durchsuchen'),
            'not_found'             => __('Keine Ticker‑Meldungen gefunden'),
            'not_found_in_trash'    => __('Keine Ticker‑Meldungen im Papierkorb'),
        ),
        'public'        => true,
        'has_archive'   => false,
        'menu_position' => 5,
        'supports'      => array('title'),
    ));
}
add_action('init', 'create_ticker_post_type');


function ticker_meldungen_shortcode() {
    ob_start(); // Startet Output-Buffering

    $args = array('post_type' => 'ticker', 'posts_per_page' => -1);
    $query = new WP_Query($args);

    echo '<div class="stock-ticker"><ul>';
    while ($query->have_posts()) : $query->the_post();
        echo '<li class="company plus"><i class="fa-solid fa-circle-info" style="margin-right:1rem"></i>' . esc_html(get_the_title()) . '</li>';
    endwhile;
    echo '</ul></div>';

    wp_reset_postdata(); // Setzt die Query zurück

    return ob_get_clean(); // Gibt den Inhalt zurück
}
add_shortcode('ticker_meldungen', 'ticker_meldungen_shortcode');



//DOM Flashing
function prevent_dom_flashing() {
    ?>
    <style>
        html {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.documentElement.style.opacity = "1";
            document.documentElement.style.visibility = "visible";
        });
    </script>
    <?php
}

add_action('wp_head', 'prevent_dom_flashing', 1); // Früh laden


//Bestellung abgeschlossen
add_filter('tribe_tickets_email_headers', function ($headers, $template_type, $attendee, $post_id) {

    $post_type = get_post_type($post_id);

    if (in_array($post_type, ['spa-angebote', 'spa-wellness'])) {
        // Mail für Spa-Angebote → spa-nautimo@nautimo.de
        $headers[] = 'Bcc: felixjfischer@gmail.com';
    }

    if (in_array($post_type, ['tribe_events', 'mitternachtssauna'])) {
        // Mail für Sauna-Events → sauna@nautimo.de
        $headers[] = 'Bcc: felixjfischer@gmail.com';
    }

    return $headers;

}, 10, 4);


// WOMO BILD
function display_womo_webcam() {
    $version = date('YmdH'); // ändert sich jede Stunde
    $img_url = site_url('/wp-content/uploads/womo/webcam/WoMo-Bild-1h.jpg') . '?v=' . $version;
    return '<img id="womo-cam" src="' . esc_url($img_url) . '" alt="WoMo Webcam">';
}
add_shortcode('womo_webcam', 'display_womo_webcam');




function kh_add_ticket_manager_role() {
    $caps = [
        // — Basis —
        'read'                      => true,
        'edit_posts'                => true,
        'publish_posts'             => true,
        'delete_posts'              => true,
        'edit_others_posts'         => true,
        'edit_published_posts'      => true,
        'delete_published_posts'    => true,

        // — EVENTS CALENDAR (komplett) —
        'edit_tribe_events'              => true,
        'edit_others_tribe_events'       => true,
        'edit_published_tribe_events'    => true,
        'publish_tribe_events'           => true,
        'delete_tribe_events'            => true,
        'delete_others_tribe_events'     => true,
        'delete_published_tribe_events'  => true,
        'read_private_tribe_events'      => true,

        // — EVENT TICKETS (Classic) —
        'edit_tribe_tickets'             => true,
        'edit_others_tribe_tickets'      => true,
        'edit_published_tribe_tickets'   => true,
        'publish_tribe_tickets'          => true,
        'delete_tribe_tickets'           => true,
        'delete_others_tribe_tickets'    => true,
        'delete_published_tribe_tickets' => true,
        'read_private_tribe_tickets'     => true,

        // — EVENT TICKETS COMMERCE (tec_tc_ticket) —
        'edit_tec_tc_ticket'              => true,
        'edit_others_tec_tc_ticket'       => true,
        'edit_published_tec_tc_ticket'    => true,
        'publish_tec_tc_ticket'           => true,
        'delete_tec_tc_ticket'            => true,
        'delete_others_tec_tc_ticket'     => true,
        'delete_published_tec_tc_ticket'  => true,
        'read_private_tec_tc_ticket'      => true,

        // — TICKETS‐ORDERS —
        'edit_tribe_tickets_order'             => true,
        'edit_others_tribe_tickets_order'      => true,
        'edit_published_tribe_tickets_order'   => true,
        'publish_tribe_tickets_order'          => true,
        'delete_tribe_tickets_order'           => true,
        'delete_others_tribe_tickets_order'    => true,
        'delete_published_tribe_tickets_order' => true,
        'read_private_tribe_tickets_order'     => true,

        // — ATTENDEES (Event Tickets Commerce) —
        'edit_tec_tc_attendee'           => true,
        'edit_others_tec_tc_attendee'    => true,
        'publish_tec_tc_attendee'        => true,
        'read_private_tec_tc_attendee'   => true,
        

        // — Dashboard & User-Profile —
        'view_tribe_tickets_dashboard' => true,
        'view_tribe_events_dashboard'    => true,
        'view_tribe_tickets_dashboard'   => true,
        'list_users'                   => true,
        'edit_user'                    => true,
    ];


    $role = get_role('ticket_manager');
    if ( ! $role ) {
        add_role( 'ticket_manager', 'Ticket-Manager', $caps );
    } else {
        foreach ( $caps as $cap => $grant ) {
            $grant ? $role->add_cap( $cap ) : $role->remove_cap( $cap );
        }
    }
}
add_action( 'init', 'kh_add_ticket_manager_role', 20 );


/**
 * 2) Admin-Menüs einschränken (inkl. Veranstaltungen & Tickets)
 */
function kh_restrict_menus_for_ticket_managers() {
    if ( ! current_user_can('ticket_manager') ) {
        return;
    }
    global $menu;

    $allowed = [
        'index.php',                              // Dashboard
        'edit.php?post_type=ticker',              // Ticker-Meldungen
        'edit.php?post_type=tribe_events',        // Veranstaltungen
        'edit.php?post_type=tribe_tickets',       // Event Tickets Classic
        'edit.php?post_type=tec_tc_ticket',       // Event Tickets Commerce
        'edit.php?post_type=tribe_tickets_order', // Ticket-Bestellungen
        'users.php',                              // Benutzer-Liste
        'profile.php',
        'edit.php?post_type=tickets-attendees', // neu
        'users.php',
        'profile.php',                            // eigenes Profil
    ];

    foreach ( $menu as $key => $item ) {
        if ( ! in_array( $item[2], $allowed, true ) ) {
            remove_menu_page( $item[2] );
        }
    }
}
add_action( 'admin_menu', 'kh_restrict_menus_for_ticket_managers', 999 );

// 2) Top-Level-Menü „Teilnehmer“ hinzufügen
add_action( 'admin_menu', function() {
    if ( ! current_user_can('ticket_manager') ) {
        return;
    }
    // der echte CPT-Slug für Commerce-Attendees
    $pt = 'tec_tc_attendee';
    if ( post_type_exists( $pt ) ) {
        add_menu_page(
            'Teilnehmer Übersicht',          // page title
            'Teilnehmer',                    // menu title
            'edit_tec_tc_attendee',          // capability
            'edit.php?post_type=' . $pt,     // link
            '',                              // callback
            'dashicons-groups',              // icon
            26                                // Position
        );
    }
}, 1002 );


// 3) Menü-Einschränkung: nur das Teilnehmer-Menü plus deine anderen CPTs
add_action( 'admin_menu', function() {
    if ( ! current_user_can('ticket_manager') ) return;
    global $menu;

    $allowed = [
        'index.php',
        'edit.php?post_type=ticker',
        'edit.php?post_type=tribe_tickets_order',
        'edit.php?post_type=tribe_tickets',
        'edit.php?post_type=tec_tc_ticket',
        'edit.php?post_type=tec_tc_attendee', // hier der richtige Eintrag
        'users.php',
        'profile.php',
    ];

    foreach ( $menu as $key => $item ) {
        if ( ! in_array( $item[2], $allowed, true ) ) {
            remove_menu_page( $item[2] );
        }
    }
}, 999 );


// 4) Direkt-URL-Checks (pre_get_posts) nur auf den Attendee-CPT beschränken
add_action( 'pre_get_posts', function( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() || ! current_user_can('ticket_manager') ) {
        return;
    }
    $screen = get_current_screen();
    $allowed_types = [
        'ticker',
        'tribe_tickets_order',
        'tribe_tickets',
        'tec_tc_ticket',
        'tec_tc_attendee',               // richtiges Post-Type-Slug
    ];

    if ( in_array( $screen->post_type, $allowed_types, true ) ) {
        return;
    }

    wp_safe_redirect( admin_url('index.php') );
    exit;
} );



// Erlaubt benutzerdefinierten Rollen den CSV-Export der Teilnehmer
add_filter( 'tec_tickets_attendees_user_can_export_csv', function( $can_export ) {
    // Ersetze 'ticket_manager' durch deinen tatsächlichen Rollen-Slug
    if ( current_user_can( 'ticket_manager' ) ) {
        return true;
    }
    return $can_export;
}, 10, 1 );



add_filter( 'tribe_tickets_email_headers', function( $headers, $ticket_id ) {
// Add BCC email address
$bcc_email = 'felix@kopf-hand.de';
$headers[] = 'Bcc: ' . $bcc_email;
// Optional: log to check headers
error_log( 'BCC added to email headers: ' . print_r( $headers, true ) );

return $headers;
}, 10, 2 );








//latest event slug shortcode
// 1) Generische Helfer-Funktion, die für einen Kategorie-Slug die URL der nächsten Veranstaltung zurückgibt
function kh_latest_event_url_for_category( $category_slug ) {
    $args = [
        'posts_per_page' => 1,
        'post_type'      => 'tribe_events',
        'meta_key'       => '_EventStartDate',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [
            [
                'key'     => '_EventEndDate',
                'value'   => current_time( 'Y-m-d H:i:s' ),
                'compare' => '>=',       // nur zukünftige Events
                'type'    => 'DATETIME',
            ],
        ],
        'tax_query'      => [
            [
                'taxonomy' => 'tribe_events_cat',
                'field'    => 'slug',
                'terms'    => $category_slug,
            ],
        ],
    ];

    $events = tribe_get_events( $args );
    if ( ! empty( $events ) ) {
        return get_permalink( $events[0]->ID );
    }
    return home_url(); // Fallback
}

// 2) Liste Deiner Kategorie-Slugs
$aquakategorien = [
    'aqua-fitness',
    'aqua-gymnastik',
    'aqua-jogging',
    'aqua-spinning',
    'hydro-power',
    'rueckbildungskurse',
    'schwangerenschwimmen',
    'babyschwimmen',
    'bronze',
    'gold',
    'mini-club',
    'seepferdchen',
    'oeffentliche-wassergymnastik',
    'wasserfloehe',
    'rueckbildungskurs-im-wasser',
    'wassergymnastik-fuer-schwangere',
];

// 3) Für jede Kategorie einen Shortcode [latest_event_{slug}] registrieren
foreach ( $aquakategorien as $slug ) {
    add_shortcode( 'latest_event_' . $slug, function() use ( $slug ) {
        return kh_latest_event_url_for_category( $slug );
    } );
}





//
//

function kh_clean_et_cache_folders() {
    $cache_dir = WP_CONTENT_DIR . '/et-cache/';
    $max_age   = 7 * DAY_IN_SECONDS;

    if (is_dir($cache_dir)) {
        $folders = glob($cache_dir . '*', GLOB_ONLYDIR);
        foreach ($folders as $folder) {
            if (filemtime($folder) < (time() - $max_age)) {
                // Löscht Ordner rekursiv
                kh_rrmdir($folder);
            }
        }
    }
}

// Hilfsfunktion: rekursives Löschen eines Ordners
function kh_rrmdir($dir) {
    if (!file_exists($dir)) return;
    if (is_file($dir)) return unlink($dir);

    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? kh_rrmdir($path) : unlink($path);
    }

    rmdir($dir);
}

// Täglicher Cronjob
add_action('kh_daily_etcache_cleanup', 'kh_clean_et_cache_folders');

if (!wp_next_scheduled('kh_daily_etcache_cleanup')) {
    wp_schedule_event(time(), 'daily', 'kh_daily_etcache_cleanup');
}


//
//
//
//
//
//




?>