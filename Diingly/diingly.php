<?php
/*
 * Plugin Name: Diingly
 * Plugin URI: https://jusoft.dev
 * Description: Notifies new WordPress posts on your Discord Server.
 * Version: 1.0
 * Author: juhom205
 * Author URI: https://jusoft.dev
*/
 
function post_to_discord($new_status, $old_status, $post) { 
    if(get_option('discord_webhook_url') == null) 
        return;
      
    if ( $new_status != 'publish' || $old_status == 'publish' || $post->post_type != 'post')
        return;
 
    $webhookURL = get_option('discord_webhook_url');
    $id = $post->ID;
 
    $author = $post->post_author;
    $authorName = get_the_author_meta('display_name', $author);
    $postTitle = $post->post_title;
    $permalink = get_permalink($id);
    $message = "Hello, " . $authorName . " just posted \"" . $postTitle . "\" for your reading pleasure: " . $permalink;
 
    $postData = array('content' => $message);
 
    $curl = curl_init($webhookURL);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");  
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
     
    $response = curl_exec($curl);
    $errors = curl_error($curl);        
     
    log_message($errors);
}
 
function log_message($log) {
      if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
}
 
add_action('transition_post_status', 'post_to_discord', 10, 3);
 
function post_to_discord_section_callback() {
  echo "<p>A valid Discord Webhook URL to the announcements channel is required.";
}
 
function post_to_discord_input_callback() {
 
  echo '<input name="discord_webhook_url" id="discord_webhook_url" type="text" value="' . get_option('discord_webhook_url') . '">';
}
 
function post_to_discord_settings_init() {
 add_settings_section(
   'discord_webhook_url',
   'Post to Discord',
   'post_to_discord_section_callback',
   'general'
 );
 
 add_settings_field(
   'discord_webhook_url',
   'Discord Webhook URL',
   'post_to_discord_input_callback',
   'general',
   'discord_webhook_url'
 );
 
 register_setting( 'general', 'discord_webhook_url' );
}
 
add_action( 'admin_init', 'post_to_discord_settings_init' );