<?php

defined('_EXEC') or die;

class Personalize_controller extends Controller
{
	private $lang;

	public function __construct()
	{
		parent::__construct();

		$this->lang = Session::get_value('lang');
	}

	public function index()
	{
		if (Format::exist_ajax_request() == true)
		{
			if ($_POST['action'] == 'get_total')
			{
				$data = [
					'price' => [
						'digital_menu' => 0,
						'operation' => 0,
						'surveys' => 0
					],
					'total' => 0
				];

				$query = $this->model->get_package($_POST['type'], $_POST['rooms_number']);

				if (!empty($query))
				{
					$data['price']['digital_menu'] = $query['price']['digital_menu'];
					$data['price']['operation'] = $query['price']['operation'];
					$data['price']['surveys'] = $query['price']['surveys'];

					if (!empty($_POST['digital_menu']))
						$data['total'] = $data['total'] + $query['price']['digital_menu'];

					if (!empty($_POST['operation']))
						$data['total'] = $data['total'] + $query['price']['operation'];

					if (!empty($_POST['surveys']))
						$data['total'] = $data['total'] + $query['price']['surveys'];
				}

				$data['price']['digital_menu'] = Functions::get_formatted_currency($data['price']['digital_menu'], 'MXN');
				$data['price']['operation'] = Functions::get_formatted_currency($data['price']['operation'], 'MXN');
				$data['price']['surveys'] = Functions::get_formatted_currency($data['price']['surveys'], 'MXN');
				$data['total'] = Functions::get_formatted_currency($data['total'], 'MXN');

				Functions::environment([
					'status' => 'success',
					'data' => $data
				]);
			}

			if ($_POST['action'] == 'next')
			{
				if ($_POST['step'] == '1')
				{
					$labels = [];

					if (!isset($_FILES['logotype']['name']) OR empty($_FILES['logotype']['name']))
						array_push($labels, ['logotype','']);

					if (!isset($_POST['name']) OR empty($_POST['name']) OR $this->model->check_exist_account('name', $_POST['name']) == true)
						array_push($labels, ['name','']);

					if (!isset($_POST['path']) OR empty($_POST['path']) OR $this->model->check_exist_account('path', $_POST['path']) == true)
						array_push($labels, ['path','']);

					if (!isset($_POST['type']) OR empty($_POST['type']))
						array_push($labels, ['type','']);

					if ($_POST['type'] == 'hotel')
					{
						if (!isset($_POST['rooms_number']) OR empty($_POST['rooms_number']) OR !is_numeric($_POST['rooms_number']) OR $_POST['rooms_number'] < 1)
							array_push($labels, ['rooms_number','']);
					}

					if (!isset($_POST['country']) OR empty($_POST['country']))
						array_push($labels, ['country','']);

					if (!isset($_POST['city']) OR empty($_POST['city']))
						array_push($labels, ['city','']);

					if (!isset($_POST['zip_code']) OR empty($_POST['zip_code']))
						array_push($labels, ['zip_code','']);

					if (!isset($_POST['address']) OR empty($_POST['address']))
						array_push($labels, ['address','']);

					if (!isset($_POST['time_zone']) OR empty($_POST['time_zone']))
						array_push($labels, ['time_zone','']);

					if (!isset($_POST['currency']) OR empty($_POST['currency']))
						array_push($labels, ['currency','']);

					if (!isset($_POST['language']) OR empty($_POST['language']))
						array_push($labels, ['language','']);

					if (empty($labels))
					{
						Functions::environment([
							'status' => 'success'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'labels' => $labels
						]);
					}
				}

				if ($_POST['step'] == '2')
				{
					$labels = [];

					if ((!isset($_POST['digital_menu']) OR empty($_POST['digital_menu'])) AND (!isset($_POST['operation']) OR empty($_POST['operation'])) AND (!isset($_POST['surveys']) OR empty($_POST['surveys'])))
					{
						array_push($labels, ['digital_menu','']);
						array_push($labels, ['operation','']);
						array_push($labels, ['surveys','']);
					}

					if (empty($labels))
					{
						Functions::environment([
							'status' => 'success'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'labels' => $labels
						]);
					}
				}

				if ($_POST['step'] == '3')
				{
					$labels = [];

					if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
						array_push($labels, ['firstname','']);

					if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
						array_push($labels, ['lastname','']);

					if (!isset($_POST['email']) OR empty($_POST['email']) OR Functions::check_email($_POST['email']) == false)
						array_push($labels, ['email','']);

					if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
						array_push($labels, ['phone_lada','']);

					if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
						array_push($labels, ['phone_number','']);

					if (!isset($_POST['username']) OR empty($_POST['username']) OR $this->model->check_exist_user('username', $_POST['username']) == true)
						array_push($labels, ['username','']);

					if (!isset($_POST['password']) OR empty($_POST['password']))
						array_push($labels, ['password','']);

					if (empty($labels))
					{
						$_POST['logotype'] = $_FILES['logotype'];

						$query = $this->model->new_signup($_POST);

						if (!empty($query))
						{
							$mail1 = new Mailer(true);

							try
							{
								$mail1->setFrom('daniel@guestvox.com', 'Daniel Basurto');
								$mail1->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']);
								$mail1->Subject = Languages::email('thanks_signup_guestvox')[$_POST['language']];
								$mail1->Body =
								'<html>
									<head>
										<title>' . $mail1->Subject . '</title>
									</head>
									<body>
										<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
														<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png">
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail1->Subject . '</h4>
													<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Languages::email('validate_signup_user')[$_POST['language']] . '</p>
													<a style="width:100%;display:block;margin:5px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/activar/' . $_POST['username'] . '">' . Languages::email('active_user')[$_POST['language']] . '</a>
													<a style="width:100%;display:block;margin:0px;padding:0px;box-sizing:border-box;background:none;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '/terminos-y-condiciones">' . Languages::email('terms_and_conditions')[$_POST['language']] . '</a>
													<a style="width:100%;display:block;margin:0px;padding:0px;box-sizing:border-box;background:none;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '/politicas-de-privacidad">' . Languages::email('privacy_policies')[$_POST['language']] . '</a>
												</td>
											</tr>
											<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:40px 0px;border:0px;box-sizing:border-box;text-align:center;">
														<img style="width:150px;height:150px;border-radius:50%;" src="https://' . Configuration::$domain . '/images/index/stl_6_image_1.png">
														<span style="display:block;color:#757575;font-size:18px;">Daniel Basurto</span>
														<span style="display:block;color:#757575;font-size:18px;">CEO</span>
														<span style="display:block;color:#757575;font-size:18px;">daniel@guestvox.com</span>
														<span style="display:block;color:#757575;font-size:18px;">+52 (998) 845 28 43</span>
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;padding:0px;border:0px;">
												<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
													<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
												</td>
											</tr>
										</table>
									</body>
								</html>';
								$mail1->send();
							}
							catch (Exception $e) { }

							$mail2 = new Mailer(true);

							try
							{
								$mail2->setFrom('noreply@guestvox.com', 'Guestvox');
								$mail2->addAddress('contacto@guestvox.com', 'Guestvox');
								$mail2->Subject = 'Guestvox | Nuevo registro';
								$mail2->Body =
								'Nombre: ' . $_POST['name'] . '<br>
								Tipo: ' . Languages::email($_POST['type'])['es'] . '<br>
								' . (($_POST['type'] == 'hotel') ? 'Número de habitaciones: ' . $_POST['rooms_number'] . '<br>' : '') . '
								Páis: ' . $_POST['country'] . ' - ' . $_POST['city'] . ' - ' . $_POST['zip_code'] . '<br>
								Administrador: ' . $_POST['firstname'] . ' ' . $_POST['lastname'] . ' - ' . $_POST['email'] . ' + (' . $_POST['phone_lada'] . ') ' . $_POST['phone_number'];
								$mail2->send();
							}
							catch (Exception $e) { }

							Functions::environment([
								'status' => 'success',
								'path' => '/',
								'message' => '{$lang.thanks_signup_guestvox_1} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_signup_guestvox_2}'
							]);
						}
						else
						{
							Functions::environment([
								'status' => 'error',
								'message' => '{$lang.operation_error}'
							]);
						}
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'labels' => $labels
						]);
					}
				}
			}
		}
		else
		{
			$template = $this->view->render($this, 'index');

			define('_title', 'Guestvox | {$lang.personalize}');

			$opt_countries = '';

			foreach ($this->model->get_countries() as $value)
				$opt_countries .= '<option value="' . $value['code'] . '">' . $value['name'][$this->lang] . '</option>';

			$opt_times_zones = '';

			foreach ($this->model->get_times_zones() as $value)
				$opt_times_zones .= '<option value="' . $value['code'] . '">' . $value['code'] . '</option>';

			$opt_currencies = '';

			foreach ($this->model->get_currencies() as $value)
				$opt_currencies .= '<option value="' . $value['code'] . '">' . $value['name'][$this->lang] . ' (' . $value['code'] . ')</option>';

			$opt_languages = '';

			foreach ($this->model->get_languages() as $value)
				$opt_languages .= '<option value="' . $value['code'] . '">' . $value['name'] . '</option>';

			$opt_ladas = '';

			foreach ($this->model->get_countries() as $value)
				$opt_ladas .= '<option value="' . $value['lada'] . '">(+' . $value['lada'] . ') ' . $value['name'][$this->lang] . '</option>';

			$replace = [
				'{$opt_countries}' => $opt_countries,
				'{$opt_times_zones}' => $opt_times_zones,
				'{$opt_currencies}' => $opt_currencies,
				'{$opt_languages}' => $opt_languages,
				'{$opt_ladas}' => $opt_ladas
			];

			$template = $this->format->replace($replace, $template);

			echo $template;
		}
	}
}
