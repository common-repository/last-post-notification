<?php
/*
Plugin Name: Last Post Notification
Plugin URI: http://smheart.org/last-post-notification
Description: When each post is published this plugin schedules three notifications (one week, three days, tomorrow) based on the last scheduled future post time.
Author: Matthew Phillips
Version: 1.0.3
Author URI: http://smheart.org

Copyright 2009 SMHeart Inc, Matthew Phillips  (email : matthew@smheart.org)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

http://www.gnu.org/licenses/gpl.txt


Version
        1.0.3 - 28 December 2009
        1.0.2 - 10 December 2009
        1.0.1 - 06 December 2009
        1.0 - 30 November 2009

*/

add_action('admin_menu', 'last_post_notification_menu');
add_action('admin_head', 'last_post_notification_styles');
add_action('transition_post_status', 'last_post_notification');
add_action('last_post_in_one_week','one_week_notification');
add_action('last_post_in_three_days','three_day_notification');
add_action('last_post_tomorrow','tomorrow_notification');
register_activation_hook(__FILE__, 'last_post_notification_install');
register_activation_hook(__FILE__, 'last_post_notification');

function last_post_notification_menu() {
	add_options_page('Last Post Notification Options', 'Last Post Notification', 8, __FILE__, 'last_post_notification_options');
	}

function last_post_notification_styles() {
	?>
 	<link rel="stylesheet" href="/wp-content/plugins/last-post-notification/last-post-notification.css" type="text/css" media="screen" charset="utf-8"/>
	<?php
	}

function last_post_notification(){
	global $wpdb;
        $last_post_date = $wpdb->get_row("SELECT post_date FROM $wpdb->posts WHERE post_date != '0000-00-00 00:00:00' AND post_status = 'future' ORDER BY post_date DESC LIMIT 1");
	$oldestpost = strtotime($last_post_date -> post_date);
	wp_clear_scheduled_hook('last_post_in_one_week');
	wp_clear_scheduled_hook('last_post_in_three_days');
	wp_clear_scheduled_hook('last_post_tomorrow');
	$in_one_week=$oldestpost-604800;
	$in_three_days=$oldestpost-259200;
	$tomorrow=$oldestpost-86400;
	$timestamp = strtotime(current_time('mysql',0));
	if ($in_one_week > $timestamp){wp_schedule_single_event($in_one_week, 'last_post_in_one_week');}
	if ($in_three_days > $timestamp){wp_schedule_single_event($in_three_days, 'last_post_in_three_days');}
	if ($tomorrow > $timestamp){wp_schedule_single_event($tomorrow, 'last_post_tomorrow');}
	}

function one_week_notification(){
	$headers = "From:  ".get_option('lpn_from_address');
	mail(get_option("lpn_recipient_address"),'One week until posts run out',get_option("lpn_one_week_message"),$headers);
	}

function three_day_notification(){
	$headers = "From:  ".get_option('lpn_from_address');
	mail(get_option("lpn_recipient_address"),'Three Days until posts run out',get_option("lpn_three_day_message"),$headers);
	}

function tomorrow_notification(){
	$headers = "From:  ".get_option('lpn_from_address');
	mail(get_option("lpn_recipient_address"),'Posts run out TOMORROW!',get_option("lpn_tomorrow_message"),$headers);
	}

function last_post_notification_install() {
	if (!is_blog_installed()) return;
	add_option('lpn_one_week_message', 'Your last scheduled post for '.get_bloginfo("name").' will publish in a week', '', 'no');
	add_option('lpn_three_day_message', 'Your last scheduled post for '.get_bloginfo("name").' will publish in three days', '', 'no');
	add_option('lpn_tomorrow_message', 'Your last scheduled post for '.get_bloginfo("name").' will publish tomorrow', '', 'no');
	add_option('lpn_recipient_address',get_option("admin_email"), '', 'no');
	add_option('lpn_from_address',get_option("admin_email"), '', 'no');
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	}

function last_post_notification_options() {
	?>
	<div class="wrap">
		<h2>Last Post Notification V1.0.3</h2>
		<div id="lpn_main">
			<div id="lpn_left_wrap">
				<div id="lpn_left_inside">
					<h3>Donate</h3>
					<p><em>If you like this plugin and find it useful, help keep this plugin free and actively developed by clicking the <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10178829" target="paypal"><strong>donate</strong></a> button or send me a gift from my <a href="http://amzn.com/w/11GK2Q9X1JXGY" target="amazon"><strong>Amazon wishlist</strong></a>.  Also follow me on <a href="http://twitter.com/kestrachern/" target="twitter"><strong>Twitter</strong></a>.</em></p>
					<a target="paypal" title="Paypal Donate"href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10178829"><img src="/wp-content/plugins/last-post-notification/paypal.jpg" alt="Donate with PayPal" /></a>
					<a target="amazon" title="Amazon Wish List" href="http://amzn.com/w/11GK2Q9X1JXGY"><img src="/wp-content/plugins/last-post-notification/amazon.jpg" alt="My Amazon wishlist" /> </a>
					<a target="Twitter" title="Follow me on Twitter" href="http://twitter.com/kestrachern/"><img src="/wp-content/plugins/last-post-notification/twitter.jpg" alt="Twitter" /></a>	
				</div>
			</div>
			<div id="lpn_right_wrap">
				<div id="lpn_right_inside">
				<h3>About the Plugin</h3>
				<p> This plugin is designed for sites that set future dates for content.  It will notify an individual via email then there is one week, three days, and one day before the last post will be automatically published.</p>
				</div>
			</div>
		</div>
	<div style="clear:both;"></div>
	<fieldset class="options"><legend>Notification Schedule</legend> 
	<form method="post" action="options.php">
		<?php echo wp_nonce_field('update-options'); ?>
		<table class="form-table">
			<tr valign="top">
				<?php
					echo '<td>From Address  <br/><input type="text" name="lpn_from_address" value="'.get_option('lpn_from_address').'" /></td>';
					echo '<td>Recipient Address  <br/><input type="text" name="lpn_recipient_address" value="'.get_option('lpn_recipient_address').'" /></td>';
					echo '</tr><tr valign="top">';
					echo '<td colspan="2">One Week Message ';
					$datetime = get_option('date_format') . ' ' . get_option('time_format');
					$next_cron = wp_next_scheduled('last_post_in_one_week');
					if ( ! empty( $next_cron ) ) :
						echo '<span id="last-scheduled-time-wrap">';
						printf('Scheduled: <span id="next-last-scheduled-time">' . gmdate($datetime, $next_cron + (get_option('gmt_offset') * 3600)) . '</span></span>');
						endif;
					echo '<br/>';
					echo '<textarea name="lpn_one_week_message" cols="85">'.get_option('lpn_one_week_message').'</textarea>';
					echo '</td>';
					echo '</tr><tr valign="top">';
					echo '<td colspan="2">Three Day Message ';
					$datetime = get_option('date_format') . ' ' . get_option('time_format');
					$next_cron = wp_next_scheduled('last_post_in_three_days');
					if ( ! empty( $next_cron ) ) :
						echo '<span id="last-scheduled-time-wrap">';
						printf('Scheduled: <span id="next-last-scheduled-time">' . gmdate($datetime, $next_cron + (get_option('gmt_offset') * 3600)) . '</span></span>');
						endif;
					echo '<br/>';
					echo '<textarea name="lpn_three_day_message" cols="85">'.get_option('lpn_three_day_message').'</textarea>';
					echo '</td>';
					echo '</tr><tr valign="top">';
					echo '<td colspan="2">Tomorrow Message ';
					$datetime = get_option('date_format') . ' ' . get_option('time_format');
					$next_cron = wp_next_scheduled('last_post_tomorrow');
					if ( ! empty( $next_cron ) ) :
						echo '<span id="last-scheduled-time-wrap">';
						printf('Scheduled: <span id="next-last-scheduled-time">' . gmdate($datetime, $next_cron + (get_option('gmt_offset') * 3600)) . '</span></span>');
						endif;
					echo '<br/>';
					echo '<textarea name="lpn_tomorrow_message" cols="85">'.get_option('lpn_tomorrow_message').'</textarea>';
					echo '</td>';
				?>
			</tr>
		</table>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="lpn_recipient_address,lpn_from_address,lpn_one_week_message,lpn_three_day_message,lpn_tomorrow_message" />
		<p class="submit">
			<input type="submit" name="Submit" value="Schedule Notifications" />
		</p>
	</form>
	</fieldset>	
	<div style="clear:both;"></div>			
	<fieldset class="options"><legend>Feature Suggestion/Bug Report</legend> 
	<?php if ($_SERVER['REQUEST_METHOD'] != 'POST'){
      		$me = $_SERVER['PHP_SELF'].'?page=last-post-notification/last-post-notification.php';
		?>
		<form name="form1" method="post" action="<?php echo $me;?>">
		<table border="0" cellspacing="0" cellpadding="2">
		<tr>
			<td>
				Make a:
			</td>
			<td>
				<select name="MessageType">
				<option value="Feature Suggestion">Feature Suggestion</option>
				<option value="Bug Report">Bug Report</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Name:
			</td>
			<td>
				<input type="text" name="Name">
			</td>
		</tr>
		<tr>
			<td>
				Your email:
			</td>
			<td>
				<input type="text" name="Email" value="<?php echo(get_option('admin_email')) ?>" />
			</td>
		</tr>
		<tr>
			<td valign="top">
				Message:
			</td>
			<td>
				<textarea name="MsgBody">
				</textarea>
			</td>
		</tr>
		<tr>
			<td>
				&nbsp;
			</td>
			<td>
				<input type="submit" name="Submit" value="Send">
			</td>
		</tr>
	</table>
</form>
<?php
   } else {
      error_reporting(0);
	$recipient = 'support@smheart.org';
	$subject = stripslashes($_POST['MessageType']).'- Last Post Notification Plugin';
	$name = stripslashes($_POST['Name']);
	$email = stripslashes($_POST['Email']);
	if ($from == "") {
		$from = get_option('admin_email');
	}
	$header = "From: ".$name." <".$from.">\r\n."."Reply-To: ".$from." \r\n"."X-Mailer: PHP/" . phpversion();
	$msg = stripslashes($_POST['MsgBody']);
      if (mail($recipient, $subject, $msg, $header))
         echo nl2br("<h2>Message Sent:</h2>
         <strong>To:</strong> Last Post Notification Plugin Support
         <strong>Subject:</strong> $subject
         <strong>Message:</strong> $msg");
      else
         echo "<h2>Message failed to send</h2>";
}
?>
	</fieldset>
</div>
<?php
	}
?>