<?php

/*
Plugin Name: Como Twitter Feed
Plugin URI: http://www.comocreative.com/
Version: 1.0.3
Author: Como Creative LLC
Description: Plugin designed to embed a custom Twitter Feed by using shortcode: [twitter-feed name=NAME key=KEY secret=SECRET token=TOKEN token_secret=TOKEN_SECRET cache=false get=GET-NUMBER-TWEETS show=SHOW_NUM_TWEETS tweet_length=140 twitter_outer_template='' template=TEMPLATE icon=ICON]
*/

defined('ABSPATH') or die('No Hackers!');

/* Include plugin updater. */
//require_once(trailingslashit( plugin_dir_path( __FILE__ ) ) . 'inc/updater.php');

class TwitterFeed_Shortcode { 
	static $add_script;
	static $add_style;
	static function init() {
		add_shortcode('twitter-feed', array(__CLASS__, 'handle_twitter_shortcode'));
		add_action('init', array(__CLASS__, 'register_twitter_script'));
		add_action('wp_footer', array(__CLASS__, 'print_twitter_script'));
	}
	
	static function handle_twitter_shortcode($atts) {
		self::$add_style = false;
		self::$add_script = false;
		
		$a = shortcode_atts( array(
			'screenname' => (isset($atts['name']) ? $atts['name'] : ''),
			'key' => ($atts['key'] ? $atts['key'] : ''),
			'secret' => (isset($atts['secret']) ? $atts['secret'] : ''),
			'token' => (isset($atts['token']) ? $atts['token'] : ''),
			'token_secret' => (isset($atts['token_secret']) ? $atts['token_secret'] : ''),
			'cache' => (isset($atts['cache']) ? $atts['cache'] : ''),
			'get' => (isset($atts['get']) ? $atts['get'] : 8),
			'show' => (isset($atts['show']) ? $atts['show'] : 4),
			'tweet_length' => (isset($atts['tweet_length']) ? $atts['tweet_length'] : 140),
			'template' => (isset($atts['template']) ? $atts['template'] : ''),
			'outer_template' => (isset($atts['outer_template']) ? $atts['outer_template'] : '<ul id="twitter">{tweets}</ul>'),
			'icon' => (isset($atts['icon']) ? $atts['icon'] : '')
		), $atts );
		
		$cache = $a['cache'] === 'true' ? true : false;
		
		require_once(trailingslashit(plugin_dir_path(__FILE__)) .'inc/TweetPHP.php');
		
		$custIcon = (($a['icon']) ? '<img src="'. $a['icon'] . '" class="feed-icon img-responsive">' :  '<i class="fab fa-twitter"></i>');
		$twitter_template['enable_cache'] = ($cache ? $cache : false);
		$twitter_template['cache_dir'] = get_stylesheet_directory() .'/como-twitter/cache/twitter/'; // Where on the server to save cached tweets
		$twitter_template['cachetime'] = 60 * 60; // Seconds to cache feed (60 * 60 = 1 hour).
		$twitter_template['tweets_to_retrieve'] = ($a['get'] ? $a['get'] : 10); // Specifies the number of tweets to try and fetch, up to a maximum of 200
		$twitter_template['tweets_to_display'] = ($a['cache'] ? $a['show'] : 4); // Number of tweets to display
		$twitter_template['ignore_replies'] = true; // Ignore @replies
		$twitter_template['ignore_retweets'] = true; // Ignore retweets
		$twitter_template['twitter_style_dates'] = false; // Use twitter style dates e.g. 2 hours ago
		$twitter_template['twitter_date_text'] = array('seconds', 'minutes', 'about', 'hour', 'ago');
		$twitter_template['date_format'] = '%b %e%O'; // The defult date format e.g. 12:08 PM Jun 12th.  %I:%M %p %b %e%O See: http://php.net/manual/en/function.strftime.php
		$twitter_template['date_lang'] = null; // Language for date e.g. 'fr_FR'. See: http://php.net/manual/en/function.setlocale.php
		$twitter_template['twitter_template'] = $a['outer_template'];
		
		if ($a['template']) {
			$temp = (is_child_theme() ? get_stylesheet_directory() : get_template_directory() ) . '/como-twitter-feed/'. $a['template'] .'.php';
			if (file_exists($temp)) {
				include($temp);
			} else {
				include(plugin_dir_path( __FILE__ ) .'templates/default.php');
			}
		} else {
			include(plugin_dir_path( __FILE__ ) .'templates/default.php');
		}
		$twitter_template['error_template'] = '<li><span class="status">Our twitter feed is unavailable right now.</span> <span class="meta"><a href="{link}">Follow us on Twitter</a></span></li>';
		$twitter_template['debug'] = false;

		$comoTwitInfo = new TweetPHP(array(
			'consumer_key'          => $a['key'],
			'consumer_secret'       => $a['secret'],
			'access_token'          => $a['token'],
			'access_token_secret'   => $a['token_secret'],
			'twitter_screen_name'   => $a['screenname'],
			'enable_cache'          => $twitter_template['enable_cache'] ,
			'cache_dir'             => $twitter_template['cache_dir'],
			'cachetime'             => $twitter_template['cachetime'],
			'tweets_to_retrieve'    => $twitter_template['tweets_to_retrieve'], 
			'tweets_to_display'     => $twitter_template['tweets_to_display'], 
			'ignore_replies'        => $twitter_template['ignore_replies'], 
			'ignore_retweets'       => $twitter_template['ignore_retweets'], 
			'twitter_style_dates'   => $twitter_template['twitter_style_dates'], 
			'twitter_date_text'     => $twitter_template['twitter_date_text'],
			'date_format'           => $twitter_template['date_format'], 
			'date_lang'             => $twitter_template['date_lang'],
			'tweet_length'			=> $a['tweet_length'],
			'twitter_template'      => $twitter_template['twitter_template'],
			'tweet_template'        => $twitter_template['tweet_template'],
			'error_template'        => $twitter_template['error_template'],
			'debug'                 => $twitter_template['debug'] 
		));
		$twitterFeed = '<div class="social-feed">'. $comoTwitInfo->get_tweet_list() .'</div>';
		return $twitterFeed;
	}
	
	// Register & Print Scripts
	static function register_twitter_script() {
		//wp_register_script('como_map_script', $mapScript, array('jquery'), '1.0', true);
	}
	static function print_twitter_script() {
		if ( ! self::$add_script )
			return;
		//wp_print_scripts('como_map_script');
	}
}
TwitterFeed_Shortcode::init();

// Add Twitter Shortcode Button to CMS
if( !function_exists('add_comotwit_button') ){
    function add_comotwit_button() { ?>
        <script type="text/javascript">
        QTags.addButton( 'twitter-feed', 'Twitter Feed', '[twitter-feed name=NAME key=KEY secret=SECRET token=TOKEN token_secret=TOKEN_SECRET cache=false get=GET-NUMBER-TWEETS show=SHOW_NUM_TWEETS tweet_length=140 template=TEMPLATE icon=ICON]', '' );
        </script>
    <?php }
    add_action('admin_print_footer_scripts',  'add_comotwit_button');
}

function testGlobalvar() {
    global $tritterChars;
    $tritterChars = 140;
}
add_action('after_themGlobalvare_setup', 'testGlobalvar');

if (!function_exists('shorten_tweet')) {
	function shorten_tweet($text, $max_length, $cut_off = '...', $keep_word = false) {
		if(strlen($text) <= $max_length) {
			return $text;
		}
		if(strlen($text) > $max_length) {
			if($keep_word) {
				$text = substr($text, 0, $max_length + 1);
				if($last_space = strrpos($text, ' ')) {
					$text = substr($text, 0, $last_space);
					$text = rtrim($text);
					$text .=  $cut_off;
				}
			} else {
				$text = substr($text, 0, $max_length);
				$text = rtrim($text);
				$text .=  $cut_off;
			}
		}
		return $text;
	}
}

?>