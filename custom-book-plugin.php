<?php
/*
Plugin Name: Custom Book Plugin
Description: A plugin to manage books with custom functionality.
Version: 1.0
Author: Karan Pal
*/
//include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once('C:/xampp/htdocs/assingment/wp-load.php');

/* Code Start for rating tbale  */
function create_books_rating_table() {
    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'books_rating';

    // Define the charset and collate
    $charset_collate = $wpdb->get_charset_collate();

    // Define the SQL query to create the table
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        post_id BIGINT(20) UNSIGNED NOT NULL,
        rating_count FLOAT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        UNIQUE KEY user_post (user_id, post_id)
    ) $charset_collate;";

    // Include the `dbDelta` function to handle table creation/updating
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    // Execute the SQL query
    dbDelta($sql);
}

/* Code End */
/* Code Start for create to add custom stylesheet and script  */
add_action('wp_enqueue_scripts', 'enqueue_book_assets');
function enqueue_book_assets() {
    // Ensure jQuery is enqueued
    wp_enqueue_script('jquery');
    
    // Enqueue custom script
    wp_enqueue_script('book-script', plugins_url('/js/book-script.js', __FILE__), ['jquery'], null, true);

    // Localize script to add ajaxurl for front-end AJAX calls
    wp_localize_script('book-script', 'ajaxurl', admin_url('admin-ajax.php'));

    // Enqueue the custom style
    wp_enqueue_style('book-style', plugins_url('/css/style.css', __FILE__));
}

/* Code End for create to add custom stylesheet and script  */
/* Code Start for ajax genre filter book post type */

// Define the AJAX action for logged-in users
add_action('wp_ajax_filter_books_by_genre', 'filter_books_by_genre');
add_action('wp_ajax_nopriv_filter_books_by_genre', 'filter_books_by_genre');

// AJAX handler function for filtering books by genre
function filter_books_by_genre() {
    $genre_id = isset($_POST['genre_id']) ? $_POST['genre_id'] : '';

    // Query for books based on selected genre
    $args = [
        'post_type' => 'book',
        'post_status' => 'publish',       // Only published posts
        'tax_query' => [
            [
                'taxonomy' => 'genre',
                'field'    => 'id',
                'terms'    => $genre_id,
            ],
        ],
    ];

    $query = new WP_Query($args);
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $genres = get_the_terms(get_the_ID(), 'genre');
            $authors = get_the_terms(get_the_ID(), 'author');
            echo '<div class="book_item">' . get_the_title() . ' - ' . ($genres ? $genres[0]->name : '') . ' - ' . ($authors ? $authors[0]->name : '') . ' <strong>Publication Year:</strong> ' . get_post_meta(get_the_ID(), '_publication_year', true) . '</div>';
        }
    } else {
        echo 'No books found.';
    }

    wp_die(); // End the AJAX request
}

/* Code End for ajax genre filter book post type */
/* Code Start for create book post type */
add_action('init', 'create_book_post_type');
function create_book_post_type() {
    register_post_type('book', [
        'labels' => [
            'name' => __('Book'),
            'singular_name' => __('Book')
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
    ]);
}
/* Code end for create book post tpye */

/* Code Start for create taxonomies post type */
add_action('init', 'create_book_taxonomies');
function create_book_taxonomies() {
    register_taxonomy('genre', 'book', [
        'label' => __('Genre'),
        'hierarchical' => true,
    ]);

    register_taxonomy('author', 'book', [
        'label' => __('Author'),
        'hierarchical' => false,
    ]);
}
/* Code End for create taxonomies post type */

/* Code Start for create meta boxes post type */
// Add the meta boxes
add_action('add_meta_boxes', 'add_book_meta_boxes');
function add_book_meta_boxes() {
    // Meta box for Publication Year
    add_meta_box('publication_year', 'Publication Year & Author Email', 'display_publication_year_email_box', 'book', 'side');
}

// Display the meta box fields
function display_publication_year_email_box($post) {
    // Retrieve saved values
    $publication_year = get_post_meta($post->ID, '_publication_year', true);
    $author_email = get_post_meta($post->ID, '_author_email', true);

    // Render fields in the meta box
    echo '<label for="publication_year">Publication Year:</label>';
    echo '<input type="number" id="publication_year" name="publication_year" value="' . esc_attr($publication_year) . '" style="width:100%;" />';
    
    echo '<label for="author_email" style="margin-top:10px; display:block;">Author Email:</label>';
    echo '<input type="email" id="author_email" name="author_email" value="' . esc_attr($author_email) . '" style="width:100%;" />';
}

// Save the meta box data
add_action('save_post', 'save_book_meta_data');
function save_book_meta_data($post_id) {
     // Save Publication Year

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['publication_year']) || !isset($_POST['author_email'])) return;

    // Save Publication Year
    $publication_year = sanitize_text_field($_POST['publication_year']);
    update_post_meta($post_id, '_publication_year', $publication_year);

    // Save Author Email
    $author_email = sanitize_email($_POST['author_email']);
    update_post_meta($post_id, '_author_email', $author_email);
}

/* Code End for create meta boxes post type */

/* Code Start for create display post short code */
add_shortcode('book_info', 'display_book_info');
function display_book_info($atts) {
    // Fetch all genres for the filter dropdown
    $genres = get_terms([
        'taxonomy' => 'genre',
        'hide_empty' => false,
    ]);

    // Genre dropdown
    $genre_dropdown = '<select id="genre_filter">
        <option value="">Select Genre</option>';
    foreach ($genres as $genre) {
        $genre_dropdown .= '<option value="' . $genre->term_id . '">' . $genre->name . '</option>';
    }
    $genre_dropdown .= '</select>';

    $atts = shortcode_atts(['years' => 5], $atts);

    // Display the genre dropdown above the books
 

    // Default query for books
    $query = new WP_Query([
        'post_type' => 'book',
        'post_status' => 'publish',       // Only published posts
        'meta_query' => [
            [
                'key' => '_publication_year',
                'value' => date('Y') - $atts['years'],
                'compare' => '>=',
            ],
        ],
    ]);

    ob_start();
    if ($query->have_posts()) {
    	echo $genre_dropdown;
    	echo "<div id='append_posttype'>";
        while ($query->have_posts()) {
            $query->the_post();
            $genres = get_the_terms(get_the_ID(), 'genre');
            $authors = get_the_terms(get_the_ID(), 'author');
            echo '<div class="book_item">' . get_the_title() . ' - ' . ($genres ? $genres[0]->name : '') . ' - ' . ($authors ? $authors[0]->name : '') . ' <strong>Publication Year:</strong> ' . get_post_meta(get_the_ID(), '_publication_year', true) . '</div>';
        }
        echo "</div>";
    }
    wp_reset_postdata();
    return ob_get_clean();
}

/* Code End for create display post short code */


/* Code Start to fire email when new book published  */


// Send email notification when a book is published
add_action('save_post', 'send_book_email_notification');
function send_book_email_notification($post_id) {
    // Ensure this is for the "book" post type
    if (get_post_type($post_id) !== 'book') {
        return;
    }

    if (get_post_status($post_id) !== 'publish' || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $post = get_post($post_id);
    if ($post->post_date === $post->post_modified) {

        // Get Author Email
        $author_email = get_post_meta($post_id, '_author_email', true);

        if ($author_email) {
            // Email subject
            $subject = '🎉 Your Book is Live: ' . get_the_title($post_id);

            // Email message
            $message = '
            <html>
            <head>
                <title>Your Book is Published!</title>
            </head>
            <body>
                <h2>Congratulations!</h2>
                <p>Your book, <strong>' . get_the_title($post_id) . '</strong>, has been successfully published on our website.</p>
                <p>Details:</p>
                <ul>
                    <li><strong>Title:</strong> ' . get_the_title($post_id) . '</li>
                    <li><strong>Publication Year:</strong> ' . get_post_meta($post_id, '_publication_year', true) . '</li>
                </ul>
                <p>Visit the book page: <a href="' . get_permalink($post_id) . '">' . get_permalink($post_id) . '</a></p>
                <p>Thank you for using our platform!</p>
            </body>
            </html>';

            // Email headers
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: Your Website <no-reply@yourwebsite.com>',
            ];

            // Send the email
            wp_mail($author_email, $subject, $message, $headers);
        } else {
            error_log('Author email is missing for book ID ' . $post_id);
        }
    }
}

/* Code End to fire email when new book published  */