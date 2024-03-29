<?php
/*
 * Copyright (c) 2023 by Fabrice Douchant <fabrice.douchant@gmail.com>.
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * PHPMailer configuration
 */
if (!function_exists('chouquette_wp_plugin_smtp_config')) :
	function chouquette_wp_plugin_smtp_config($phpmailer)
	{
		$phpmailer->isSMTP();
		$phpmailer->Host = SMTP_HOST;
		$phpmailer->SMTPAuth = SMTP_AUTH;
		$phpmailer->Port = SMTP_PORT;
		$phpmailer->SMTPSecure = SMTP_SECURE;
		$phpmailer->Username = SMTP_USERNAME;
		$phpmailer->Password = SMTP_PASSWORD;
		$phpmailer->From = SMTP_FROM;
		$phpmailer->FromName = SMTP_FROMNAME;
	}

	add_action('phpmailer_init', 'chouquette_wp_plugin_smtp_config');
endif;

/**
 * Helpers for emails.
 *
 * @since        1.0.0
 * @package       Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Lib_Email
{

	const LOGO_ID = 18066;

	/**
	 * Send email. Wrapper on https://developer.wordpress.org/reference/functions/wp_mail with fallback, from and BCC trick
	 *
	 * Wraps message (and subject) with Chouquette information.
	 * Set proper header (BCC, Reply-To)
	 *
	 * @param $from_name string name of sender
	 * @param $from string email of sender
	 * @param $to string dest. email
	 * @param $subject string email subject
	 * @param $message string email content
	 *
	 * @return true/false if the mail was property sent
	 */
	public static function send_mail(string $from_name, string $from, string $to, string $subject, string $message)
	{
		/* Message body */
		$body_template = <<<EOT
            <html lang="fr">
            <head>
                <title>%s</title>
            </head>
            <body aria-readonly="false">
                <p>%s</p>
                <p><em>Cet email vous a été envoyé depuis </em><a href="%s">%s</a></p>
                <div style="text-align: center">
                    <a href="%s" name="%s">
                    <img src="%s" alt="%s" />
                    <p>%s</p>
                    </a>
                </div>
            </body>
            </html>
EOT;

		$logo_url = wp_get_attachment_url(self::LOGO_ID);
		$body = sprintf($body_template,
			$subject,
			nl2br(stripslashes($message)),
            CQ_FRONTEND_DOMAIN, CQ_FRONTEND_DOMAIN,
            CQ_FRONTEND_DOMAIN, get_bloginfo('name'),
			$logo_url, get_bloginfo('name'),
			get_bloginfo('description'));

		/* Headers */
		$headers = array(
			"Content-Type: text/html; charset=UTF-8",
			"From: {$from}",
			"Reply-To: {$from_name} <{$from}>"
		);
		if (MAIL_BCC_FALLBACK) {
			$headers[] = 'Bcc: ' . MAIL_FALLBACK;
		}
		if (!MAIL_ACTIVATE) { // security for development. Send email to fallback instead of real dest.
			$to = MAIL_FALLBACK;
		}

        /**
         * Display errors
         */
        if ( ! function_exists('debug_wpmail') ) :

            function debug_wpmail( $result = false ) {

                if ( $result )
                    return;

                global $ts_mail_errors, $phpmailer;

                if ( ! isset($ts_mail_errors) )
                    $ts_mail_errors = array();

                if ( isset($phpmailer) )
                    $ts_mail_errors[] = $phpmailer->ErrorInfo;

                error_log('<pre>');
                error_log(implode (" - ", $ts_mail_errors));
                error_log('</pre>');
            }
        endif;

		$res = wp_mail($to, $subject, $body, $headers);
        if (!$res) debug_wpmail($res); // write to error log
		return $res;
	}

}
