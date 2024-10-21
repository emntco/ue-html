<?php 


/**
 * Salient Child Theme Styles
 * 
 * This set of functions handles the enqueuing of styles for the Salient child theme.
 * It includes:
 * 
 * 1. Enqueuing the child theme's main stylesheet
 * 2. Conditionally enqueuing the RTL stylesheet if needed
 * 3. Using the parent theme's version number for cache busting
 */

function salient_child_enqueue_styles() {
		
		$nectar_theme_version = nectar_get_theme_version();
		wp_enqueue_style( 'salient-child-style', get_stylesheet_directory_uri() . '/style.css', '', $nectar_theme_version );
		
    if ( is_rtl() ) {
   		wp_enqueue_style(  'salient-rtl',  get_template_directory_uri(). '/rtl.css', array(), '1', 'screen' );
		}
}

add_action( 'wp_enqueue_scripts', 'salient_child_enqueue_styles', 100);


/**
 * Embed of the most recent YouTube video.
 * 
 * This set of functions handles fetching and displaying the latest YouTube video.
 * It includes:
 * 
 * 1. Fetching the latest video ID from the YouTube API
 * 2. Generating an iframe for the video
 * 3. Creating a shortcode to easily embed the latest video
 */

function get_latest_youtube_video_id($channel_id) {
    $api_key = getenv('YOUTUBE_API_KEY');
    if (!$api_key) {
        error_log('YouTube API Key not found in environment variables');
        return false;
    }

    $api_url = "https://www.googleapis.com/youtube/v3/search?key=$api_key&channelId=$channel_id&part=snippet,id&order=date&maxResults=1&type=video";

    $response = wp_remote_get($api_url);
    if (is_wp_error($response)) {
        error_log('YouTube API Error: ' . $response->get_error_message());
        return false;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);

    return !empty($data['items'][0]['id']['videoId']) ? $data['items'][0]['id']['videoId'] : false;
}

function get_latest_video_iframe() {
    $channel_id = 'UChI0q9a-ZcbZh7dAu_-J-hg';
    
    $video_id = get_latest_youtube_video_id($channel_id) ?: 'GFq6wH5JR2A';

    return sprintf(
        '<iframe width="560" height="315" src="https://www.youtube.com/embed/%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
        esc_attr($video_id)
    );
}

function latest_video_shortcode() {
    return get_latest_video_iframe();
}
add_shortcode('latest_video', 'latest_video_shortcode');


?>