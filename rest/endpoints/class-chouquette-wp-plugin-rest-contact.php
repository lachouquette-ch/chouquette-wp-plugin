<?php

class Chouquette_WP_Plugin_Rest_Contact extends WP_REST_Controller
{

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes()
	{
		$version = '1';
		$namespace = 'chouquette/v' . $version;
		$base = 'contact';
		register_rest_route($namespace, '/' . $base, array(
			array(
				'methods' => WP_REST_Server::CREATABLE,
				'callback' => array($this, 'send_message')
			)
		));
	}

	/**
	 * Send message to Chouquette recipient
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function send_message($request)
	{
		if (!$request->has_param('recaptcha') || !$request->has_param('name') || !$request->has_param('email') || !$request->has_param('subject') || !$request->has_param('to') || !$request->has_param('message')) {
			return new WP_Error(
				'rest_contact_params_required',
				__("Should contain 'recaptcha', 'name', 'email', 'subject', 'to' and 'message'."),
				array('status' => 400)
			);
		}

		if (!Chouquette_WP_Plugin_Lib_Recaptcha::validateRecaptchaToken($request->get_param('recaptcha'))) {
			return new WP_Error(
				'rest_contact_recaptcha_invalid',
				__("Le filtre anti-spam (recaptcha) n'a pas accepté ton message. Merci de re-essayer."),
				array('status' => 412)
			);
		}

		switch ($request->get_param('to')) {
			case "hello":
				$contact_mail = "hello@lachouquette.ch";
				break;
			case "communication":
				$contact_mail = "communication@lachouquette.ch";
				break;
			case "webmaster":
				$contact_mail = "webmaster@lachouquette.ch";
				break;
			default:
				return new WP_Error(
					'rest_contact_to_invalid',
					__("Le destinataire n'est pas valide."),
					array('status' => 400)
				);
		};

		print_r($request);
		echo($contact_mail);

		$result = Chouquette_WP_Plugin_Lib_Email::send_mail(
			$request->get_param('name'),
			$request->get_param('email'),
			$contact_mail,
			$request->get_param('subject'),
			$request->get_param('message'));
		if ($result) {
			return new WP_REST_Response(json_encode(__('Ton message à bien été envoyé')));
		} else {
			return new WP_Error(
				'rest_contact_send',
				json_encode(__("Ton email n\'a pas pu être envoyé. Merci de réessayé plus tard ou de nous contact si l\'erreur persiste. On est désolé, snif !")),
				array('status' => 500)
			);
		}

	}

}
