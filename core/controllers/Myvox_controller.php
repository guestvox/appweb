<?php

defined('_EXEC') or die;

// require_once 'plugins/nexmo/vendor/autoload.php';

class Myvox_controller extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

    public function index($params)
    {
		$break = true;

		$data['account'] = $this->model->get_account($params[0]);

		if (!empty($data['account']))
		{
			Session::set_value('account', $data['account']);

			if (isset($params[1]) AND !empty($params[1]))
			{
				if ($params[1] == 'room')
				{
					$data['room'] = $this->model->get_room($params[2]);

					if (!empty($data['room']))
					{
						$data['room']['guest'] = $this->model->get_guest($data['account']['zaviapms'], $data['room']['number']);

						Session::set_value('room', $data['room']);

						$break = false;
					}
				}
				else if ($params[1] == 'table')
				{
					$data['table'] = $this->model->get_table($params[2]);

					if (!empty($data['table']))
					{
						Session::set_value('table', $data['table']);

						$break = false;
					}
				}
				else if ($params[1] == 'client')
				{
					$data['client'] = $this->model->get_client($params[2]);

					if (!empty($data['client']))
					{
						Session::set_value('client', $data['client']);

						$break = false;
					}
				}
			}
			else
			{
				if ($data['account']['type'] == 'hotel')
				{
					if (Session::exists_var('room') == true AND !empty(Session::get_value('room')['id']))
						$data['room'] = Session::get_value('room')['id'];
					else
						$data['room'] = '';

					$data['room'] = $this->model->get_room($data['room']);

					if (!empty($data['room']))
					{
						$data['room']['guest'] = $this->model->get_guest($data['account']['zaviapms'], $data['room']['number']);

						Session::set_value('room', $data['room']);

						$break = false;
					}
				}
				else if ($data['account']['type'] == 'restaurant')
				{
					if (Session::exists_var('table') == true AND !empty(Session::get_value('table')['id']))
						$data['table'] = Session::get_value('table')['id'];
					else
						$data['table'] = '';

					$data['table'] = $this->model->get_table($data['table']);

					if (!empty($data['table']))
					{
						Session::set_value('table', $data['table']);

						$break = false;
					}
				}
				else if ($data['account']['type'] == 'others')
				{
					if (Session::exists_var('client') == true AND !empty(Session::get_value('client')['id']))
						$data['client'] = Session::get_value('client')['id'];
					else
						$data['client'] = '';

					$data['client'] = $this->model->get_client($data['client']);

					if (!empty($data['client']))
					{
						Session::set_value('client', $data['client']);

						$break = false;
					}
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'get_room')
				{
					$data['room'] = $this->model->get_room($_POST['room']);

					if (!empty($data['room']))
					{
						$data['room']['guest'] = $this->model->get_guest($data['account']['zaviapms'], $data['room']['number']);

						Session::set_value('room', $data['room']);

						Functions::environment([
							'status' => 'success',
							'data' => '{$lang.operation_success}'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'data' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'get_table')
				{
					$data['table'] = $this->model->get_table($_POST['table']);

					if (!empty($data['table']))
					{
						Session::set_value('table', $data['table']);

						Functions::environment([
							'status' => 'success',
							'data' => '{$lang.operation_success}'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'data' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'get_client')
				{
					$data['client'] = $this->model->get_client($_POST['client']);

					if (!empty($data['client']))
					{
						Session::set_value('client', $data['client']);

						Functions::environment([
							'status' => 'success',
							'data' => '{$lang.operation_success}'
						]);
					}
					else
					{
						Functions::environment([
							'status' => 'error',
							'data' => '{$lang.operation_error}'
						]);
					}
				}

				if ($_POST['action'] == 'get_opt_opportunity_types')
				{
					$html = '<option value="" selected hidden>{$lang.choose}</option>';

					foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], $_POST['type']) as $value)
						$html .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

					Functions::environment([
						'status' => 'success',
						'data' => $html
					]);
				}

				if ($_POST['action'] == 'new_request')
				{
					$labels = [];

					if (!isset($params[1]) OR empty($params[1]))
					{
						if (Session::get_value('account')['type'] == 'hotel')
						{
							if (!isset($_POST['room']) OR empty($_POST['room']))
								array_push($labels, ['room','']);
						}

						if (Session::get_value('account')['type'] == 'restaurant')
						{
							if (!isset($_POST['table']) OR empty($_POST['table']))
								array_push($labels, ['table','']);
						}

						if (Session::get_value('account')['type'] == 'others')
						{
							if (!isset($_POST['client']) OR empty($_POST['client']))
								array_push($labels, ['client','']);
						}
					}

					if (!isset($_POST['opportunity_area']) OR empty($_POST['opportunity_area']))
						array_push($labels, ['opportunity_area','']);

					if (!isset($_POST['opportunity_type']) OR empty($_POST['opportunity_type']))
						array_push($labels, ['opportunity_type','']);

					if (!isset($_POST['started_date']) OR empty($_POST['started_date']))
						array_push($labels, ['started_date','']);

					if (!isset($_POST['started_hour']) OR empty($_POST['started_hour']))
						array_push($labels, ['started_hour','']);

					if (!isset($_POST['location']) OR empty($_POST['location']))
						array_push($labels, ['location','']);

					if (!empty($_POST['observations']) AND strlen($_POST['observations']) > 120)
						array_push($labels, ['observations','']);

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);
					}

					if (empty($labels))
					{
						$_POST['account'] = Session::get_value('account');

						if (Session::get_value('account')['type'] == 'hotel')
						{
							$_POST['room'] = Session::get_value('room')['id'];

							if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
								$_POST['firstname'] = Session::get_value('room')['guest']['firstname'];

							if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
								$_POST['lastname'] = Session::get_value('room')['guest']['lastname'];

							$_POST['reservation_number'] = Session::get_value('room')['guest']['reservation_number'];
							$_POST['check_in'] = Session::get_value('room')['guest']['check_in'];
							$_POST['check_out'] = Session::get_value('room')['guest']['check_out'];
						}

						if (Session::get_value('account')['type'] == 'restaurant')
							$_POST['table'] = Session::get_value('table')['id'];

						if (Session::get_value('account')['type'] == 'others')
							$_POST['client'] = Session::get_value('client')['id'];

						$query = $this->model->new_request($_POST);

						if (!empty($query))
						{
							$_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area'], Session::get_value('account')['id']);
							$_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
							$_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
							$_POST['location'] = $this->model->get_location($_POST['location']);

							if (Session::get_value('account')['language'] == 'es')
							{
								$mail_subject = 'Tienes una nueva petición';

								if (Session::get_value('account')['type'] == 'hotel')
									$mail_room = 'Habitación: #';

								if (Session::get_value('account')['type'] == 'restaurant')
									$mail_table = 'Mesa: #';

								if (Session::get_value('account')['type'] == 'others')
									$mail_client = 'Cliente: ';

								$mail_opportunity_area = 'Área de oportunidad: ';
								$mail_opportunity_type = 'Tipo de oportunidad: ';
								$mail_started_date = 'Fecha de inicio: ';
								$mail_started_hour = 'Hora de inicio: ';
								$mail_location = 'Ubicación: ';

								if (Functions::get_current_date_hour() < Functions::get_formatted_date_hour($_POST['started_date'], $_POST['started_hour']))
									$mail_urgency = 'Urgencia: Programada';
								else
									$mail_urgency = 'Urgencia: Media';

								$mail_observations = 'Observaciones: ';
								$mail_give_follow_up = 'Dar seguimiento';
							}
							else if (Session::get_value('account')['language'] == 'en')
							{
								$mail_subject = 'You have a new request';

								if (Session::get_value('account')['type'] == 'hotel')
									$mail_room = 'Room: #';

								if (Session::get_value('account')['type'] == 'restaurant')
									$mail_table = 'Table: #';

								if (Session::get_value('account')['type'] == 'others')
									$mail_client = 'Client: ';

								$mail_opportunity_area = 'Opportunity area: ';
								$mail_opportunity_type = 'Opportunity type: ';
								$mail_started_date = 'Start date: ';
								$mail_started_hour = 'Start hour: ';
								$mail_location = 'Location: ';

								if (Functions::get_current_date_hour() < Functions::get_formatted_date_hour($_POST['started_date'], $_POST['started_hour']))
									$mail_urgency = 'Urgency: Programmed';
								else
									$mail_urgency = 'Urgency: Medium';

								$mail_observations = 'Observations: ';
								$mail_give_follow_up = 'Give follow up';
							}

							$mail = new Mailer(true);

							try
							{
								$mail->isSMTP();
								$mail->setFrom('noreply@guestvox.com', 'GuestVox');

								foreach ($_POST['assigned_users'] as $value)
									$mail->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

								$mail->isHTML(true);
								$mail->Subject = $mail_subject;
								$mail->Body =
								'<html>
									<head>
										<title>' . $mail_subject . '</title>
									</head>
									<body>
										<table style="width:600px;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#eee">
											<tr style="width:100%;margin:0px:margin-bottom:10px;border:0px;padding:0px;">
												<td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
														<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype-color.png" />
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;margin-bottom:10px;border:0px;padding:0px;">
												<td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
													<h4 style="font-size:24px;font-weight:600;text-align:center;color:#212121;margin:0px;margin-bottom:20px;padding:0px;">' . $mail_subject . '</h4>';

								if (Session::get_value('account')['type'] == 'hotel')
									$mail->Body .= '<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_room . Session::get_value('room')['number'] . ' ' . Session::get_value('room')['name'] . '</h6>';

								if (Session::get_value('account')['type'] == 'restaurant')
									$mail->Body .= '<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_table . Session::get_value('table')['number'] . ' ' . Session::get_value('table')['name'] . '</h6>';

								if (Session::get_value('account')['type'] == 'others')
									$mail->Body .= '<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_client . Session::get_value('client')['name'] . '</h6>';

								$mail->Body .=
								'					<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_opportunity_area . $_POST['opportunity_area']['name'][Session::get_value('account')['language']] . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_opportunity_type . $_POST['opportunity_type']['name'][Session::get_value('account')['language']] . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_started_date . Functions::get_formatted_date($_POST['started_date'], 'd M, Y') . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_started_hour . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_location . $_POST['location']['name'][Session::get_value('account')['language']] . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_urgency . '</h6>
													<p style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;padding:0px;">' . $mail_observations . $_POST['observations'] . '</p>
													<a style="width:100%;display:block;margin:15px 0px 20px 0px;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;background-color:#201d33;" href="https://' . Configuration::$domain . '/voxes/view/details/' . $query . '">' . $mail_give_follow_up . '</a>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;border:0px;padding:0px;">
												<td style="width:100%;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#fff;">
													<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#201d33;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
												</td>
											</tr>
										</table>
									</body>
								</html>';
								$mail->AltBody = '';
								$mail->send();
							}
							catch (Exception $e) { }

							if (Session::get_value('account')['sms'] > 0)
							{
								$sms_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
								$sms_client = new \Nexmo\Client($sms_basic);

								$sms_text = $mail_subject . '. ';

								if (Session::get_value('account')['type'] == 'hotel')
									$sms_text .= $mail_room . Session::get_value('room')['number'] . ' ' . Session::get_value('room')['name'] . '. ';

								if (Session::get_value('account')['type'] == 'restaurant')
									$sms_text .= $mail_table . Session::get_value('table')['number'] . ' ' . Session::get_value('table')['name'] . '. ';

								if (Session::get_value('account')['type'] == 'others')
									$sms_text .= $mail_client . Session::get_value('client')['name'] . '. ';

								$sms_text .= $mail_opportunity_area . $_POST['opportunity_area']['name'][Session::get_value('account')['language']] . '. ';
								$sms_text .= $mail_opportunity_type . $_POST['opportunity_type']['name'][Session::get_value('account')['language']] . '. ';
								$sms_text .= $mail_started_date . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
								$sms_text .= $mail_started_hour . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
								$sms_text .= $mail_location . $_POST['location']['name'][Session::get_value('account')['language']] . '. ';
								$sms_text .= $mail_urgency . '. ';
								$sms_text .= $mail_observations . $_POST['observations'] . '. ';
								$sms_text .= 'https://' . Configuration::$domain . '/voxes/view/details/' . $query;

								$sms_account = Session::get_value('account');

								foreach ($_POST['assigned_users'] as $value)
								{
									if ($sms_account['sms'] > 0)
									{
										try {

											$sms_client->message()->send([
												'to' => $value['phone']['lada'] . $value['phone']['number'],
												'from' => 'GuestVox',
												'text' => $sms_text
											]);

											$sms_account['sms'] = $sms_account['sms'] - 1;

										} catch (Exception $e) { }
									}
								}

								$this->model->edit_sms($sms_account['id'], $sms_account['sms']);

								Session::set_value('account', $sms_account);
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_trus_us_vox_send_correctly}'
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

				if ($_POST['action'] == 'new_incident')
				{
					$labels = [];

					if (!isset($params[1]) OR empty($params[1]))
					{
						if (Session::get_value('account')['type'] == 'hotel')
						{
							if (!isset($_POST['room']) OR empty($_POST['room']))
								array_push($labels, ['room','']);
						}

						if (Session::get_value('account')['type'] == 'restaurant')
						{
							if (!isset($_POST['table']) OR empty($_POST['table']))
								array_push($labels, ['table','']);
						}

						if (Session::get_value('account')['type'] == 'others')
						{
							if (!isset($_POST['client']) OR empty($_POST['client']))
								array_push($labels, ['client','']);
						}
					}

					if (!isset($_POST['opportunity_area']) OR empty($_POST['opportunity_area']))
						array_push($labels, ['opportunity_area','']);

					if (!isset($_POST['opportunity_type']) OR empty($_POST['opportunity_type']))
						array_push($labels, ['opportunity_type','']);

					if (!isset($_POST['started_date']) OR empty($_POST['started_date']))
						array_push($labels, ['started_date','']);

					if (!isset($_POST['started_hour']) OR empty($_POST['started_hour']))
						array_push($labels, ['started_hour','']);

					if (!isset($_POST['location']) OR empty($_POST['location']))
						array_push($labels, ['location','']);

					if (!empty($_POST['subject']) AND strlen($_POST['subject']) > 120)
						array_push($labels, ['subject','']);

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);
					}

					if (empty($labels))
					{
						$_POST['account'] = Session::get_value('account');

						if (Session::get_value('account')['type'] == 'hotel')
						{
							$_POST['room'] = Session::get_value('room')['id'];

							if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
								$_POST['firstname'] = Session::get_value('room')['guest']['firstname'];

							if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
								$_POST['lastname'] = Session::get_value('room')['guest']['lastname'];

							$_POST['reservation_number'] = Session::get_value('room')['guest']['reservation_number'];
							$_POST['check_in'] = Session::get_value('room')['guest']['check_in'];
							$_POST['check_out'] = Session::get_value('room')['guest']['check_out'];
						}

						if (Session::get_value('account')['type'] == 'restaurant')
							$_POST['table'] = Session::get_value('table')['id'];

						if (Session::get_value('account')['type'] == 'others')
							$_POST['client'] = Session::get_value('client')['id'];

						$query = $this->model->new_incident($_POST);

						if (!empty($query))
						{
							$_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area'], Session::get_value('account')['id']);
							$_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
							$_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
							$_POST['location'] = $this->model->get_location($_POST['location']);

							if (Session::get_value('account')['language'] == 'es')
							{
								$mail_subject_1 = 'Tienes una nueva incidencia';

								if (Session::get_value('account')['type'] == 'hotel')
									$mail_room = 'Habitación: #';

								if (Session::get_value('account')['type'] == 'restaurant')
									$mail_table = 'Mesa: #';

								if (Session::get_value('account')['type'] == 'others')
									$mail_client = 'Cliente: ';

								$mail_opportunity_area = 'Área de oportunidad: ';
								$mail_opportunity_type = 'Tipo de oportunidad: ';
								$mail_started_date = 'Fecha de inicio: ';
								$mail_started_hour = 'Hora de inicio: ';
								$mail_location = 'Ubicación: ';

								if (Functions::get_current_date_hour() < Functions::get_formatted_date_hour($_POST['started_date'], $_POST['started_hour']))
									$mail_urgency = 'Urgencia: Programada';
								else
									$mail_urgency = 'Urgencia: Media';

								$mail_confidentiality = 'Confidencialidad: No';
								$mail_subject_2 = 'Asunto: ';
								$mail_give_follow_up = 'Dar seguimiento';
							}
							else if (Session::get_value('account')['language'] == 'en')
							{
								$mail_subject_1 = 'You have a new incident';

								if (Session::get_value('account')['type'] == 'hotel')
									$mail_room = 'Room: #';

								if (Session::get_value('account')['type'] == 'restaurant')
									$mail_table = 'Table: #';

								if (Session::get_value('account')['type'] == 'others')
									$mail_client = 'Client: ';

								$mail_opportunity_area = 'Opportunity area: ';
								$mail_opportunity_type = 'Opportunity type: ';
								$mail_started_date = 'Start date: ';
								$mail_started_hour = 'Start hour: ';
								$mail_location = 'Location: ';

								if (Functions::get_current_date_hour() < Functions::get_formatted_date_hour($_POST['started_date'], $_POST['started_hour']))
									$mail_urgency = 'Urgency: Programmed';
								else
									$mail_urgency = 'Urgency: Medium';

								$mail_confidentiality = 'Confidentiality: No';
								$mail_subject_2 = 'Subject: ';
								$mail_give_follow_up = 'Give follow up';
							}

							$mail = new Mailer(true);

							try
							{
								$mail->isSMTP();
								$mail->setFrom('noreply@guestvox.com', 'GuestVox');

								foreach ($_POST['assigned_users'] as $value)
									$mail->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

								$mail->isHTML(true);
								$mail->Subject = $mail_subject_1;
								$mail->Body =
								'<html>
									<head>
										<title>' . $mail_subject_1 . '</title>
									</head>
									<body>
										<table style="width:600px;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#eee">
											<tr style="width:100%;margin:0px:margin-bottom:10px;border:0px;padding:0px;">
												<td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
													<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
														<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype-color.png" />
													</figure>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;margin-bottom:10px;border:0px;padding:0px;">
												<td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
													<h4 style="font-size:24px;font-weight:600;text-align:center;color:#212121;margin:0px;margin-bottom:20px;padding:0px;">' . $mail_subject_1 . '</h4>';

								if (Session::get_value('account')['type'] == 'hotel')
									$mail->Body .= '<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_room . Session::get_value('room')['number'] . ' ' . Session::get_value('room')['name'] . '</h6>';

								if (Session::get_value('account')['type'] == 'restaurant')
									$mail->Body .= '<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_table . Session::get_value('table')['number'] . ' ' . Session::get_value('table')['name'] . '</h6>';

								if (Session::get_value('account')['type'] == 'others')
									$mail->Body .= '<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_client . Session::get_value('client')['name'] . '</h6>';

								$mail->Body .=
								'					<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_opportunity_area . $_POST['opportunity_area']['name'][Session::get_value('account')['language']] . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_opportunity_type . $_POST['opportunity_type']['name'][Session::get_value('account')['language']] . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_started_date . Functions::get_formatted_date($_POST['started_date'], 'd M, Y') . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_started_hour . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_location . $_POST['location']['name'][Session::get_value('account')['language']] . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_urgency . '</h6>
													<h6 style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:5px;padding:0px;">' . $mail_confidentiality . '</h6>
													<p style="font-size:14px;font-weight:400;text-align:center;color:#212121;margin:0px;padding:0px;">' . $mail_subject_2 . $_POST['subject'] . '</p>
													<a style="width:100%;display:block;margin:15px 0px 20px 0px;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;background-color:#201d33;" href="https://' . Configuration::$domain . '/voxes/view/details/' . $query . '">' . $mail_give_follow_up . '</a>
												</td>
											</tr>
											<tr style="width:100%;margin:0px;border:0px;padding:0px;">
												<td style="width:100%;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#fff;">
													<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#201d33;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
												</td>
											</tr>
										</table>
									</body>
								</html>';
								$mail->AltBody = '';
								$mail->send();
							}
							catch (Exception $e) { }

							if (Session::get_value('account')['sms'] > 0)
							{
								$sms_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
								$sms_client = new \Nexmo\Client($sms_basic);

								$sms_text = $mail_subject_1 . '. ';

								if (Session::get_value('account')['type'] == 'hotel')
									$sms_text .= $mail_room . Session::get_value('room')['number'] . ' ' . Session::get_value('room')['name'] . '. ';

								if (Session::get_value('account')['type'] == 'restaurant')
									$sms_text .= $mail_table . Session::get_value('table')['number'] . ' ' . Session::get_value('table')['name'] . '. ';

								if (Session::get_value('account')['type'] == 'others')
									$sms_text .= $mail_client . Session::get_value('client')['number'] . ' ' . Session::get_value('client')['name'] . '. ';

								$sms_text .= $mail_opportunity_area . $_POST['opportunity_area']['name'][Session::get_value('account')['language']] . '. ';
								$sms_text .= $mail_opportunity_type . $_POST['opportunity_type']['name'][Session::get_value('account')['language']] . '. ';
								$sms_text .= $mail_started_date . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
								$sms_text .= $mail_started_hour . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
								$sms_text .= $mail_location . $_POST['location']['name'][Session::get_value('account')['language']] . '. ';
								$sms_text .= $mail_urgency . '. ';
								$sms_text .= $mail_confidentiality . '. ';
								$sms_text .= $mail_subject_2 . $_POST['subject'] . '. ';
								$sms_text .= 'https://' . Configuration::$domain . '/voxes/view/details/' . $query;

								$sms_account = Session::get_value('account');

								foreach ($_POST['assigned_users'] as $value)
								{
									if ($sms_account['sms'] > 0)
									{
										try {

											$sms_client->message()->send([
												'to' => $value['phone']['lada'] . $value['phone']['number'],
												'from' => 'GuestVox',
												'text' => $sms_text
											]);

											$sms_account['sms'] = $sms_account['sms'] - 1;

										} catch (Exception $e) { }
									}
								}

								$this->model->edit_sms($sms_account['id'], $sms_account['sms']);

								Session::set_value('account', $sms_account);
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_trus_us_vox_send_correctly}'
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

				if ($_POST['action'] == 'new_survey_answer')
				{
					$labels = [];

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
						   array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
						   array_push($labels, ['lastname','']);
					}

					if (Session::get_value('account')['type'] == 'others')
					{
						if (empty($_POST['email']) AND Functions::check_email($_POST['email']) == false OR !isset($_POST['email']))
							array_push($labels, ['email','']);
					}

					if (!empty($_POST['phone_lada']) OR !empty($_POST['phone_number']))
					{
						if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
							array_push($labels, ['phone_lada','']);

						if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
							array_push($labels, ['phone_number','']);
					}

					if (empty($labels))
					{
						$_POST['answers'] = $_POST;

						if (Session::get_value('account')['type'] == 'hotel')
							unset($_POST['answers']['room']);

						if (Session::get_value('account')['type'] == 'restaurant')
							unset($_POST['answers']['table']);

						if (Session::get_value('account')['type'] == 'others')
							unset($_POST['answers']['client']);

						unset($_POST['answers']['comment']);
						unset($_POST['answers']['firstname']);
						unset($_POST['answers']['lastname']);
						unset($_POST['answers']['email']);
						unset($_POST['answers']['phone_lada']);
						unset($_POST['answers']['phone_number']);
						unset($_POST['answers']['action']);

						foreach ($_POST['answers'] as $key => $value)
						{
							$explode = explode('-', $key);

							if ($explode[0] == 'pr' OR $explode[0] == 'pt' OR $explode[0] == 'po' OR $explode[0] == 'pc')
							{
								if ($explode[0] == 'pr')
									$explode[0] = 'rate';
								else if ($explode[0] == 'pt')
									$explode[0] = 'twin';
								else if ($explode[0] == 'po')
									$explode[0] = 'open';
								else if ($explode[0] == 'pc')
								{
									$explode[0] = 'check';
									$value = json_encode($value);
								}

								$_POST['answers'][$explode[1]] = [
									'id' => $explode[1],
									'answer' => $value,
									'type' => $explode[0],
									'subanswers' => []
								];

								unset($_POST['answers'][$key]);
							}
							else if ($explode[0] == 'sr' OR $explode[0] == 'st' OR $explode[0] == 'so')
							{
								if (!empty($value))
								{
									if ($explode[0] == 'sr')
										$explode[0] = 'rate';
									else if ($explode[0] == 'st')
										$explode[0] = 'twin';
									else if ($explode[0] == 'so')
										$explode[0] = 'open';

									array_push($_POST['answers'][$explode[1]]['subanswers'], [
										'id' => $explode[2],
										'type' => $explode[0],
										'answer' => $value,
										'subanswers' => []
									]);
								}

								unset($_POST['answers'][$key]);
							}
							else if ($explode[0] == 'ssr' OR $explode[0] == 'sst' OR $explode[0] == 'sso')
							{
								if (!empty($value))
								{
									if ($explode[0] == 'ssr')
										$explode[0] = 'rate';
									else if ($explode[0] == 'sst')
										$explode[0] = 'twin';
									else if ($explode[0] == 'sso')
										$explode[0] = 'open';

									array_push($_POST['answers'][$explode[1]]['subanswers'][$explode[2]]['subanswers'], [
										'id' => $explode[4],
										'type' => $explode[0],
										'answer' => $value
									]);
								}

								unset($_POST['answers'][$key]);

							}
						}

						sort($_POST['answers']);

						$_POST['account'] = Session::get_value('account');
						$_POST['token'] = Functions::get_random(8);

						if (Session::get_value('account')['type'] == 'hotel')
							$_POST['room'] = Session::get_value('room')['id'];

						if (Session::get_value('account')['type'] == 'restaurant')
							$_POST['table'] = Session::get_value('table')['id'];

						if (Session::get_value('account')['type'] == 'others')
							$_POST['client'] = Session::get_value('client')['id'];

						$query = $this->model->new_survey_answer($_POST);

						if (!empty($query))
						{
							if (!isset($params[1]) OR empty($params[1]))
							{
								if ($data['account']['type'] == 'hotel')
									Session::unset_value('room');

								if ($data['account']['type'] == 'restaurant')
									Session::unset_value('table');

								if ($data['account']['type'] == 'others')
									Session::unset_value('client');
							}

							if (!empty($_POST['email']))
							{
								$mail = new Mailer(true);

								try
								{
									$mail_title = Session::get_value('account')['settings']['myvox']['survey_mail']['title'][Session::get_value('account')['language']];
									$mail_subject = Session::get_value('account')['settings']['myvox']['survey_mail']['paragraph'][Session::get_value('account')['language']];
									$mail_file = (!empty(Session::get_value('account')['settings']['myvox']['survey_mail']['attachment']) ? Session::get_value('account')['settings']['myvox']['survey_mail']['attachment']['file'] : '');
									$mail_url = (!empty(Session::get_value('account')['settings']['myvox']['survey_mail']['attachment']) ? file_get_contents('https://' . Configuration::$domain . '/uploads/' . Session::get_value('account')['settings']['myvox']['survey_mail']['attachment']['file']) : '');
									$mail_attachment = (!empty(Session::get_value('account')['settings']['myvox']['survey_mail']['attachment']) ? '<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#039be5;" href="https://' . Configuration::$domain . '/uploads/' . Session::get_value('account')['settings']['myvox']['survey_mail']['attachment']['file'] . '" download="'. Session::get_value('account')['settings']['myvox']['survey_mail']['attachment']['file'] . '">Descargar adjunto</a>' : '');

									$mail->isSMTP();
									$mail->setFrom('noreply@guestvox.com', 'GuestVox');
									$mail->addAddress($_POST['email'], $_POST['firstname'] . ' ' . $_POST['lastname']);
									$mail->isHTML(true);
									$mail->Subject = $mail_title;
									$mail->Body =
									'<html>
										<head>
											<title>' . $mail_title . '</title>
										</head>
										<body>
											<table style="width:600px;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#eee">
												<tr style="width:100%;margin:0px:margin-bottom:10px;border:0px;padding:0px;">
													<td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
														<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
															<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . (!empty(Session::get_value('account')['settings']['myvox']['survey_mail']['image']) ? Session::get_value('account')['settings']['myvox']['survey_mail']['image'] : Session::get_value('account')['logotype']) . '" />
														</figure>
													</td>
												</tr>
												<tr style="width:100%;margin:0px;margin-bottom:10px;border:0px;padding:0px;">
													<td style="width:100%;margin:0px;border:0px;padding:40px 20px;box-sizing:border-box;background-color:#fff;">
														<h4 style="font-size:24px;font-weight:400;text-align:center;color:#212121;margin:0px;margin-bottom:20px;padding:0px;">' . $mail_subject . '</h4>
														<h6 style="font-size:40px;font-weight:600;text-align:center;color:#212121;margin:0px;padding:0px;">' . $_POST['token'] . '</h6>
													</td>
												</tr>
												<tr style="width:100%;margin:0px;border:0px;padding:0px;">
													<td style="width:100%;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#fff;">
														' . $mail_attachment . '
													</td>
												</tr>
												<tr style="width:100%;margin:0px;border:0px;padding:0px;">
													<td style="width:100%;margin:0px;border:0px;padding:20px;box-sizing:border-box;background-color:#fff;">
														<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#201d33;" href="https://' . Configuration::$domain . '">Powered by GuestVox</a>
													</td>
												</tr>
											</table>
										</body>
									</html>';
									$mail->AltBody = '';
									$mail->send();
								}
								catch (Exception $e) { }
							}

							if (!empty($_POST['phone_lada']) AND !empty($_POST['phone_number']))
							{
								if (Session::get_value('account')['sms'] > 0)
								{
									$sms_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									$sms_client = new \Nexmo\Client($sms_basic);

									if (Session::get_value('account')['language'] == 'es')
										$sms_text = 'GuestVox: Gracias por contestar nuestra encuesta. Token: ' . $_POST['token'];
									else if (Session::get_value('account')['language'] == 'en')
										$sms_text = 'GuestVox: Thanks for answers our surver. Token: ' . $_POST['token'];

									$sms_account = Session::get_value('account');

									try {

										$sms_client->message()->send([
											'to' => $_POST['phone_lada'] . $_POST['phone_number'],
											'from' => 'GuestVox',
											'text' => $sms_text
										]);

										$sms_account['sms'] = $sms_account['sms'] - 1;

									} catch (Exception $e) { }

									$this->model->edit_sms($sms_account['id'], $sms_account['sms']);

									Session::set_value('account', $sms_account);
								}
							}

							if (!empty(Session::get_value('account')['settings']['myvox']['survey_widget']))
							{
								$average = $this->model->get_average($query);

								if ($average >= 4)
								{
									$data = [
										'widget' => true,
									];
								}
							}
							else
							{
								$data = [
									'widget' => false
								];
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_for_answering_our_survey}',
								'data' => $data
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
			else
			{
				define('_title', 'GuestVox');

				$template = $this->view->render($this, 'index');

				$a_new_request = '';
				$a_new_incident = '';
				$mdl_new_request = '';
				$mdl_new_incident = '';

				if (Session::get_value('account')['operation'] == true)
				{
					if (Session::get_value('account')['settings']['myvox']['request'] == true)
					{
						$a_new_request .= '<a data-button-modal="new_request">{$lang.make_a_request}</a>';

						$mdl_new_request .=
						'<section class="modal" data-modal="new_request">
							<div class="content">
								<header>
									<h3>{$lang.make_a_request}</h3>
								</header>
								<main>
									<form name="new_request">
										<div class="row">';

						if (!isset($params[1]) OR empty($params[1]))
						{
							if (Session::get_value('account')['type'] == 'hotel')
							{
								$mdl_new_request .=
								'<div class="span12">
									<div class="label">
										<label important>
											<p>{$lang.room}</p>
											<select name="room">
												<option value="" selected hidden>{$lang.choose}</option>';

								foreach ($this->model->get_rooms(Session::get_value('account')['id']) as $value)
									$mdl_new_request .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';

								$mdl_new_request .=
								'			</select>
										</label>
									</div>
								</div>';
							}

							if (Session::get_value('account')['type'] == 'restaurant')
							{
								$mdl_new_request .=
								'<div class="span12">
									<div class="label">
										<label important>
											<p>{$lang.table}</p>
											<select name="table">
												<option value="" selected hidden>{$lang.choose}</option>';

								foreach ($this->model->get_tables(Session::get_value('account')['id']) as $value)
									$mdl_new_request .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';

								$mdl_new_request .=
								'			</select>
										</label>
									</div>
								</div>';
							}

							if (Session::get_value('account')['type'] == 'others')
							{
								$mdl_new_request .=
								'<div class="span12">
									<div class="label">
										<label important>
											<p>{$lang.client}</p>
											<select name="client">
												<option value="" selected hidden>{$lang.choose}</option>';

								foreach ($this->model->get_clients(Session::get_value('account')['id']) as $value)
									$mdl_new_request .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';

								$mdl_new_request .=
								'			</select>
										</label>
									</div>
								</div>';
							}
						}

						$mdl_new_request .=
						'					<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.opportunity_area}</p>
														<select name="opportunity_area" data-type="request">
															<option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_opportunity_areas('request', Session::get_value('account')['id']) as $value)
							$mdl_new_request .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

						$mdl_new_request .=
						'                                </select>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.opportunity_type}</p>
														<select name="opportunity_type" disabled>
															<option value="" selected hidden>{$lang.choose}</option>
														</select>
													</label>
												</div>
											</div>
											<div class="span6">
												<div class="label">
													<label>
														<p>{$lang.date}</p>
														<input type="date" name="started_date" value="' . Functions::get_current_date('Y-m-d') . '">
													</label>
												</div>
											</div>
											<div class="span6">
												<div class="label">
													<label>
														<p>{$lang.hour}</p>
														<input type="time" name="started_hour" value="' . Functions::get_current_hour() . '">
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.location}</p>
														<select name="location">
															<option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_locations('request', Session::get_value('account')['id']) as $value)
							$mdl_new_request .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

						$mdl_new_request .=
						'                                </select>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.observations}</p>
														<input type="text" name="observations" maxlength="120" />
														<p class="description">{$lang.max_120_characters}</p>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.firstname}</p>
														<input type="text" name="firstname" />
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.lastname}</p>
														<input type="text" name="lastname" />
													</label>
												</div>
											</div>
										</div>
									</form>
								</main>
								<footer>
									<div class="action-buttons">
										<button class="btn btn-flat" button-cancel>{$lang.cancel}</button>
										<button class="btn" button-success>{$lang.accept}</button>
									</div>
								</footer>
							</div>
						</section>';
					}

					if (Session::get_value('account')['settings']['myvox']['incident'] == true)
					{
						if (Session::get_value('account')['type'] == 'hotel' OR Session::get_value('account')['type'] == 'restaurant')
							$a_new_incident .= '<a data-button-modal="new_incident">{$lang.i_want_to_leave_a_comment_complaint}</a>';
						else if (Session::get_value('account')['type'] == 'others')
							$a_new_incident .= '<a data-button-modal="new_incident">{$lang.up_incident}</a>';

						$mdl_new_incident .=
						'<section class="modal" data-modal="new_incident">
							<div class="content">
								<header>
									<h3>{$lang.i_want_to_leave_a_comment_complaint}</h3>
								</header>
								<main>
									<form name="new_incident">
										<div class="row">';

						if (!isset($params[1]) OR empty($params[1]))
						{
							if (Session::get_value('account')['type'] == 'hotel')
							{
								$mdl_new_incident .=
								'<div class="span12">
									<div class="label">
										<label important>
											<p>{$lang.room}</p>
											<select name="room">
												<option value="" selected hidden>{$lang.choose}</option>';

								foreach ($this->model->get_rooms(Session::get_value('account')['id']) as $value)
									$mdl_new_incident .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';

								$mdl_new_incident .=
								'			</select>
										</label>
									</div>
								</div>';
							}

							if (Session::get_value('account')['type'] == 'restaurant')
							{
								$mdl_new_incident .=
								'<div class="span12">
									<div class="label">
										<label important>
											<p>{$lang.table}</p>
											<select name="table">
												<option value="" selected hidden>{$lang.choose}</option>';

								foreach ($this->model->get_tables(Session::get_value('account')['id']) as $value)
									$mdl_new_incident .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';

								$mdl_new_incident .=
								'			</select>
										</label>
									</div>
								</div>';
							}

							if (Session::get_value('account')['type'] == 'others')
							{
								$mdl_new_incident .=
								'<div class="span12">
									<div class="label">
										<label important>
											<p>{$lang.client}</p>
											<select name="client">
												<option value="" selected hidden>{$lang.choose}</option>';

								foreach ($this->model->get_clients(Session::get_value('account')['id']) as $value)
									$mdl_new_incident .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';

								$mdl_new_incident .=
								'			</select>
										</label>
									</div>
								</div>';
							}
						}

						$mdl_new_incident .=
						'					<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.opportunity_area}</p>
														<select name="opportunity_area" data-type="incident">
															<option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_opportunity_areas('incident', Session::get_value('account')['id']) as $value)
						   $mdl_new_incident .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

						$mdl_new_incident .=
						'                                </select>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.opportunity_type}</p>
														<select name="opportunity_type" disabled>
															<option value="" selected hidden>{$lang.choose}</option>
														</select>
													</label>
												</div>
											</div>
											<div class="span6">
												<div class="label">
													<label>
														<p>{$lang.date}</p>
														<input type="date" name="started_date" value="' . Functions::get_current_date('Y-m-d') . '">
													</label>
												</div>
											</div>
											<div class="span6">
												<div class="label">
													<label>
														<p>{$lang.hour}</p>
														<input type="time" name="started_hour" value="' . Functions::get_current_hour() . '">
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label important>
														<p>{$lang.location}</p>
														<select name="location">
															<option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_locations('incident', Session::get_value('account')['id']) as $value)
						   $mdl_new_incident .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('lang')] . '</option>';

						$mdl_new_incident .=
						'								</select>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.subject}</p>
														<input type="text" name="subject" maxlength="120" />
														<p class="description">{$lang.max_120_characters}</p>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.firstname}</p>
														<input type="text" name="firstname" />
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.lastname}</p>
														<input type="text" name="lastname" />
													</label>
												</div>
											</div>
										</div>
									</form>
								</main>
								<footer>
									<div class="action-buttons">
										<button class="btn btn-flat" button-cancel>{$lang.cancel}</button>
										<button class="btn" button-success>{$lang.accept}</button>
									</div>
								</footer>
							</div>
						</section>';
					}
				}

				$a_new_survey_answer = '';
				$mdl_new_survey_answer = '';
				$mdl_survey_widget = '';

				if (Session::get_value('account')['reputation'] == true)
				{
					if (Session::get_value('account')['settings']['myvox']['survey'] == true)
					{
						$a_new_survey_answer .= '<a data-button-modal="new_survey_answer" class="survey">' . Session::get_value('account')['settings']['myvox']['survey_title'][Session::get_value('lang')] . '</a>';

						$mdl_new_survey_answer .=
						'<section class="modal" data-modal="new_survey_answer">
							<div class="content">
								<header>
									<h3>{$lang.answer_survey_right_now}</h3>
								</header>
								<main>
									<form name="new_survey_answer">';

						foreach ($this->model->get_survey_questions(Session::get_value('account')['id']) as $value)
						{
							$mdl_new_survey_answer .=
							'<article>
						   		<h6>' . $value['name'][Session::get_value('lang')] . '</h6>';

							if ($value['type'] == 'rate')
							{
								$mdl_new_survey_answer .=
								'<div class="rate">
								   <label><i class="far fa-sad-cry" style="font-size:18px;"></i><input type="radio" name="pr-' . $value['id'] . '" value="1" data-action="open_subquestion"></label>
								   <label><i class="far fa-frown" style="font-size:18px;"></i><input type="radio" name="pr-' . $value['id'] . '" value="2" data-action="open_subquestion"></label>
								   <label><i class="far fa-meh-rolling-eyes" style="font-size:18px;"></i><input type="radio" name="pr-' . $value['id'] . '" value="3" data-action="open_subquestion"></label>
								   <label><i class="far fa-smile" style="font-size:18px;"></i><input type="radio" name="pr-' . $value['id'] . '" value="4" data-action="open_subquestion"></label>
								   <label><i class="far fa-grin-stars" style="font-size:18px;"></i><input type="radio" name="pr-' . $value['id'] . '" value="5" data-action="open_subquestion"></label>
								</div>';
							}
							else if ($value['type'] == 'twin')
							{
								$mdl_new_survey_answer .=
								'<div>
								   <label>{$lang.to_yes}</label>
								   <label><input type="radio" name="pt-' . $value['id'] . '" value="yes" data-action="open_subquestion"></label>
								   <label><input type="radio" name="pt-' . $value['id'] . '" value="no" data-action="open_subquestion"></label>
								   <label>{$lang.to_not}</label>
								</div>';
							}
							else if ($value['type'] == 'open')
							{
								$mdl_new_survey_answer .=
								'<div>
								   <input type="text" name="po-' . $value['id'] . '">
								</div>';
							}
							else if ($value['type'] == 'check')
							{
								foreach ($value['values'] as $subkey => $subvalue)
								{
									$mdl_new_survey_answer .=
									'<div class="checkboxes">
										<input type="checkbox" name="pc-' . $value['id'] . '-values[]" value="' . $subkey . '">
										<span>' . $subvalue[Session::get_value('lang')] . '</span>
									</div>';
								}
							}

							$mdl_new_survey_answer .=
							'</article>';

							if (!empty($value['subquestions']))
							{
								if ($value['type'] == 'rate')
								{
								   $mdl_new_survey_answer .=
								   '<article id="pr-' . $value['id'] . '" class="subquestions hidden">';
								}
								else if ($value['type'] == 'twin')
								{
								   $mdl_new_survey_answer .=
								   '<article id="pt-' . $value['id'] . '" class="subquestions hidden">';
								}

								foreach ($value['subquestions'] as $subkey => $subvalue)
								{
								   	if ($subvalue['status'] == true)
								   	{
									   	$mdl_new_survey_answer .=
									   	'<h6>' . $subvalue['name'][Session::get_value('lang')] . '</h6>';

									   	if ($subvalue['type'] == 'rate')
									   	{
										   	$mdl_new_survey_answer .=
										   	'<div class="rate">
											   	<label><i class="far fa-sad-cry" style="font-size:18px;"></i><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="1" data-action="open_subquestion_sub"></label>
											   	<label><i class="far fa-frown" style="font-size:18px;"></i><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="2" data-action="open_subquestion_sub"></label>
											   	<label><i class="far fa-meh-rolling-eyes" style="font-size:18px;"></i><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="3" data-action="open_subquestion_sub"></label>
											   	<label><i class="far fa-smile" style="font-size:18px;"></i><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="4" data-action="open_subquestion_sub"></label>
											   	<label><i class="far fa-grin-stars" style="font-size:18px;"></i><input type="radio" name="sr-' . $value['id'] . '-' . $subvalue['id'] . '" value="5" data-action="open_subquestion_sub"></label>
										   	</div>';
									   	}
									   	else if ($subvalue['type'] == 'twin')
									   	{
										   	$mdl_new_survey_answer .=
										   	'<div>
											   	<label>{$lang.to_yes}</label>
											   	<label><input type="radio" name="st-' . $value['id'] . '-' . $subvalue['id'] . '" value="yes" data-action="open_subquestion_sub"></label>
											   	<label><input type="radio" name="st-' . $value['id'] . '-' . $subvalue['id'] . '" value="no" data-action="open_subquestion_sub"></label>
											   	<label>{$lang.to_not}</label>
										  	</div>';
									   	}
									   	else if ($subvalue['type'] == 'open')
									   	{
										   	$mdl_new_survey_answer .=
										   	'<div>
											   	<input type="text" name="so-' . $value['id'] . '-' . $subvalue['id'] . '">
										   	</div>';
									   	}

									   	if (!empty($subvalue['subquestions']))
									   	{
										   	if ($subvalue['type'] == 'rate')
			   								{
		   								   		$mdl_new_survey_answer .=
			   								   	'<article id="sr-' . $value['id'] . '-' . $subvalue['id'] . '" class="subquestions-sub hidden">';
			   								}
			   								else if ($subvalue['type'] == 'twin')
			   								{
			   								   	$mdl_new_survey_answer .=
			   								   	'<article id="st-' . $value['id'] . '-' . $subvalue['id'] . '" class="subquestions-sub hidden">';
			   								}

										   	foreach ($subvalue['subquestions'] as $parentkey => $parentvalue)
										   	{
												if ($parentvalue['status'] == true)
												{
													$mdl_new_survey_answer .=
	    									  		'<h6>' . $parentvalue['name'][Session::get_value('lang')] . '</h6>';

												  	if ($parentvalue['type'] == 'rate')
													{
														$mdl_new_survey_answer .=
														'<div class="rate">
														   <label><i class="far fa-sad-cry" style="font-size:18px;"></i><input type="radio" name="ssr-' . $value['id'] . '-' . $subkey . '-' . $subvalue['id'] . '-' . $parentvalue['id'] . '" value="1"></label>
														   <label><i class="far fa-frown" style="font-size:18px;"></i><input type="radio" name="ssr-' . $value['id'] . '-' . $subkey . '-' . $subvalue['id'] . '-' . $parentvalue['id'] . '" value="2"></label>
														   <label><i class="far fa-meh-rolling-eyes" style="font-size:18px;"></i><input type="radio" name="ssr-' . $value['id'] . '-' . $subkey . '-' . $subvalue['id'] . '-' . $parentvalue['id'] . '" value="3"></label>
														   <label><i class="far fa-smile" style="font-size:18px;"></i><input type="radio" name="ssr-' . $value['id'] . '-' . $subkey . '-' . $subvalue['id'] . '-' . $parentvalue['id'] . '" value="4"></label>
														   <label><i class="far fa-grin-stars" style="font-size:18px;"></i><input type="radio" name="ssr-' . $value['id'] . '-' . $subkey . '-' . $subvalue['id'] . '-' . $parentvalue['id'] . '" value="5"></label>
														</div>';
													}
													else if ($parentvalue['type'] == 'twin')
													{
														$mdl_new_survey_answer .=
														'<div>
														   <label>{$lang.to_yes}</label>
														   <label><input type="radio" name="sst-' . $value['id'] . '-' . $subkey . '-' . $subvalue['id'] . '-' . $parentvalue['id'] . '" value="yes"></label>
														   <label><input type="radio" name="sst-' . $value['id'] . '-' . $subkey . '-' . $subvalue['id'] . '-' . $parentvalue['id'] . '" value="no"></label>
														   <label>{$lang.to_not}</label>
														</div>';
													}
													else if ($parentvalue['type'] == 'open')
													{
														$mdl_new_survey_answer .=
														'<div>
														   <input type="text" name="sso-' . $value['id'] . '-' . $subkey . '-' . $subvalue['id'] . '-' . $parentvalue['id'] . '">
														</div>';
													}
												}
										   	}

											$mdl_new_survey_answer .=
			   								'</article>';
								   		}
								   	}
								}

								$mdl_new_survey_answer .=
								'</article>';
							}
					   }

						$mdl_new_survey_answer .=
						'                <div class="row">
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.comments}</p>
														<textarea name="comment"></textarea>
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.firstname}</p>
														<input type="text" name="firstname" />
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label>
														<p>{$lang.lastname}</p>
														<input type="text" name="lastname" />
													</label>
												</div>
											</div>
											<div class="span12">
												<div class="label">
													<label ' . ((Session::get_value('account')['type'] == 'others') ? 'important' : '') . '>
														<p>{$lang.email}</p>
														<input type="email" name="email">
													</label>
												</div>
											</div>
											<div class="span4">
												<div class="label">
													<label>
														<p>{$lang.lada}</p>
														<select name="phone_lada">
															<option value="" selected hidden>{$lang.choose}</option>';

						foreach ($this->model->get_countries() as $value)
							$mdl_new_survey_answer .= '<option value="' . $value['lada'] . '">' . $value['name'][Session::get_value('lang')] . ' (+' . $value['lada'] . ')</option>';

						$mdl_new_survey_answer .=
						'                                </select>
													</label>
												</div>
											</div>
											<div class="span8">
												<div class="label">
													<label>
														<p>{$lang.phone}</p>
														<input type="number" name="phone_number">
													</label>
												</div>
											</div>
										</div>';

										if (!isset($params[1]) OR empty($params[1]))
										{
											if (Session::get_value('account')['type'] == 'hotel')
											{
												$mdl_new_survey_answer .=
												'<div class="label">
													<label>
														<p>{$lang.do_you_know_your_room_number}</p>
														<select name="room">
															<option value="" selected hidden>{$lang.choose}</option>';

												foreach ($this->model->get_rooms(Session::get_value('account')['id']) as $value)
													$mdl_new_survey_answer .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';

												$mdl_new_survey_answer .=
												'		</select>
													</label>
												</div>';
											}

											if (Session::get_value('account')['type'] == 'restaurant')
											{
												$mdl_new_survey_answer .=
												'<div class="label">
													<label>
														<p>{$lang.do_you_know_your_table_number}</p>
														<select name="table">
															<option value="" selected hidden>{$lang.choose}</option>';

												foreach ($this->model->get_tables(Session::get_value('account')['id']) as $value)
													$mdl_new_survey_answer .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';

												$mdl_new_survey_answer .=
												'		</select>
													</label>
												</div>';
											}
										}

							$mdl_new_survey_answer .=
							'		</form>
								</main>
								<footer>
									<div class="action-buttons">
										<button class="btn btn-flat" button-cancel>{$lang.cancel}</button>
										<button class="btn" button-success>{$lang.accept}</button>
									</div>
								</footer>
							</div>
						</section>';

						if (!empty(Session::get_value('account')['settings']['myvox']['survey_widget']))
						{
							$mdl_survey_widget .=
							'<section class="modal" data-modal="survey_widget">
							    <div class="content">
							        <main>' . Session::get_value('account')['settings']['myvox']['survey_widget'] . '</main>
							        <footer>
							            <div class="action-buttons">
							                <button class="btn" button-close>{$lang.accept}</button>
							            </div>
							        </footer>
							    </div>
							</section>';
						}
					}
				}

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('account')['logotype'],
					'{$a_new_request}' => $a_new_request,
					'{$a_new_incident}' => $a_new_incident,
					'{$a_new_survey_answer}' => $a_new_survey_answer,
					'{$mdl_new_request}' => $mdl_new_request,
					'{$mdl_new_incident}' => $mdl_new_incident,
					'{$mdl_new_survey_answer}' => $mdl_new_survey_answer,
					'{$mdl_survey_widget}' => $mdl_survey_widget
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }
}
