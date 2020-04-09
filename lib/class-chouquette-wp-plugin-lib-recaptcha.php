<?php

class Chouquette_WP_Plugin_Lib_Recaptcha_Exception extends Exception
{
}

/**
 * Helpers for recaptcha.
 *
 * @since        1.0.0
 * @package        Chouquette_WP_Plugin
 * @subpackage    Chouquette_WP_Plugin/lib
 * @author        Fabrice Douchant <fabrice.douchant@gmail.com>
 */
class Chouquette_WP_Plugin_Lib_Recaptcha
{

	public static $recaptcha_verify_url = 'https://www.google.com/recaptcha/api/siteverify';
	public static $recaptcha_secret = '6LeGzZoUAAAAAF35rYtWWthF9Wb_WDB1QPJ3hYG4';

	/**
	 * Validate a given recaptcha token
	 *
	 * @param string $recaptcha_token the token to validate
	 * @param float $min_score_success the minimum score of success accepted
	 * @return bool true|false if recaptcha is validated
	 * @throws Chouquette_WP_Plugin_Lib_Recaptcha_Exception if recaptcha couldn't be challenged
	 */
	public static function validateRecaptchaToken(string $recaptcha_token, float $min_score_success = 0.5)
	{
		// Make and decode POST request
		$recaptcha = file_get_contents(Chouquette_WP_Plugin_Lib_Recaptcha::$recaptcha_verify_url . '?secret=' . Chouquette_WP_Plugin_Lib_Recaptcha::$recaptcha_secret . '&response=' . $recaptcha_token);
		$recaptcha = json_decode($recaptcha);

		// Take action based on the score returned:
		if (!$recaptcha->success) {
			throw new Chouquette_WP_Plugin_Lib_Recaptcha_Exception('Erreur recaptcha : ' . join(', ', $recaptcha->{'error-codes'}));
		}
		if (isset($recaptcha->score) && $recaptcha->score >= $min_score_success) {
			return true;
		} else {
			return false;
		}
	}

}
