<?php 

/* Salient */

// Loads the child theme's stylesheets and handles RTL support.
function salient_child_enqueue_styles() {
    $nectar_theme_version = nectar_get_theme_version();
    wp_enqueue_style( 'salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version );
    
    if ( is_rtl() ) {
        wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
    }
}
add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);


/* Get and cache the latest YouTube video ID using transients. */

// Fetches and caches the most recent video ID from a YouTube channel.
function get_latest_youtube_video_id($channel_id) {
    // Check cache first
    $cached_video_id = get_transient('latest_youtube_video_id');
    
    if (false !== $cached_video_id) {
        return $cached_video_id;
    }

    // Get API key from environment.
    $api_key = getenv('YOUTUBE_API_KEY');
    if (!$api_key) {
        error_log('YouTube API Key not found in environment variables');
        return 'NVlDLVfhFL0'; // Fallback video ID
    }

    // Make API request.
    $api_url = "https://www.googleapis.com/youtube/v3/search?key=$api_key&channelId=$channel_id&part=snippet,id&order=date&maxResults=1&type=video";

    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        error_log('YouTube API Error: ' . $response->get_error_message());
        return 'NVlDLVfhFL0';
    }

    // Process response and cache result.
    $data = json_decode(wp_remote_retrieve_body($response), true);
    $video_id = !empty($data['items'][0]['id']['videoId']) ? $data['items'][0]['id']['videoId'] : 'NVlDLVfhFL0';

    set_transient('latest_youtube_video_id', $video_id, 86400); // Cache for 24 hours

    return $video_id;
}

// Generate Video Iframe HTML.
function get_latest_video_iframe() {
    $channel_id = 'UChI0q9a-ZcbZh7dAu_-J-hg';
    $video_id = get_latest_youtube_video_id($channel_id);

    return sprintf(
        '<iframe width="560" height="315" src="https://www.youtube.com/embed/%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
        esc_attr($video_id)
    );
}

// Creates [latest_video] shortcode for use in posts/pages.
function latest_video_shortcode() {
    return get_latest_video_iframe();
}
add_shortcode('latest_video', 'latest_video_shortcode');

?>
