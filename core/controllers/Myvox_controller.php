<?php

defined('_EXEC') or die;

// require_once 'plugins/nexmo/vendor/autoload.php';
require_once 'plugins/pusher/vendor/autoload.php';

class Myvox_controller extends Controller
{
	private $lang1;
	private $lang2;

	public function __construct()
	{
		parent::__construct();

		$this->lang1 = Session::get_value('lang');
		$this->lang2 = (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account'])) ? Session::get_value('myvox')['account']['language'] : Session::get_value('lang');
	}

    public function index($params)
    {
		$break = true;

		$account = $this->model->get_account($params[0]);

		if (!empty($account))
		{
			$owner = null;
			$url = '';

			if (!empty($params[1]))
			{
				if ($params[1] == 'owner' AND !empty($params[2]))
					$owner = $params[2];

				$url = $params[1];
			}
			else
			{
				if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['owner']))
					$owner = Session::get_value('myvox')['owner']['id'];

				$url = 'account';
			}

			$owner = $this->model->get_owner($owner, true);

			if (($url == 'owner' AND !empty($owner)) OR ($url == 'delivery' AND $account['type'] == 'restaurant') OR $url == 'account')
			{
				if (!empty($owner) AND $account['type'] == 'hotel')
					$owner['reservation'] = $this->model->get_reservation($owner['number']);

				$myvox = [
					'account' => $account,
					'owner' => $owner,
					'url' => $url
				];

				Session::set_value('myvox', $myvox);

				$break = false;
			}
		}

		if ($break == false)
		{
			if ($url == 'delivery')
				header('Location: /' . $params[0] . '/menu/products');
			else if ($url == 'owner' OR $url == 'account')
			{
				$template = $this->view->render($this, 'index');

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.myvox}');

				$btn_new_request = '';
				$btn_new_incident = '';
				$btn_new_menu_order = '';

				if (Session::get_value('myvox')['account']['operation'] == true)
				{
					if (Session::get_value('myvox')['account']['settings']['myvox']['request']['status'] == true)
						$btn_new_request .= '<a href="/' . $params[0] . '/request">' . Session::get_value('myvox')['account']['settings']['myvox']['request']['title'][$this->lang1] . '</a>';

					if (Session::get_value('myvox')['account']['settings']['myvox']['incident']['status'] == true)
						$btn_new_incident .= '<a href="/' . $params[0] . '/incident">' . Session::get_value('myvox')['account']['settings']['myvox']['incident']['title'][$this->lang1] . '</a>';

					if (Session::get_value('myvox')['account']['type'] == 'hotel' OR Session::get_value('myvox')['account']['type'] == 'restaurant')
					{
						if (Session::get_value('myvox')['account']['settings']['myvox']['menu']['status'] == true)
							$btn_new_menu_order .= '<a href="/' . $params[0] . '/menu/products">' . Session::get_value('myvox')['account']['settings']['myvox']['menu']['title'][$this->lang1] . '</a>';
					}
				}

				$btn_new_survey_answer = '';

				if (Session::get_value('myvox')['account']['reputation'] == true)
				{
					if (Session::get_value('myvox')['account']['settings']['myvox']['survey']['status'] == true)
						$btn_new_survey_answer .= '<a href="/' . $params[0] . '/survey">' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['title'][$this->lang1] . '</a>';
				}

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_new_request}' => $btn_new_request,
					'{$btn_new_incident}' => $btn_new_incident,
					'{$btn_new_menu_order}' => $btn_new_menu_order,
					'{$btn_new_survey_answer}' => $btn_new_survey_answer
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }

    public function request($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['operation'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['request']['status'] == true)
			{
				if (!empty(Session::get_value('myvox')['url']))
				{
					if (Session::get_value('myvox')['url'] == 'account')
						$break = false;
					else if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'get_owner')
				{
					$owner = $this->model->get_owner($_POST['owner']);

					if (!empty($owner))
					{
						if (Session::get_value('myvox')['account']['type'] == 'hotel')
							$owner['reservation'] = $this->model->get_reservation($owner['number']);

						$myvox = Session::get_value('myvox');

						$myvox['owner'] = $owner;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success'
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

				if ($_POST['action'] == 'get_opt_opportunity_types')
				{
					$html = '<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], 'request') as $value)
						$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

					Functions::environment([
						'status' => 'success',
						'html' => $html
					]);
				}

				if ($_POST['action'] == 'new_request')
				{
					$labels = [];

					if (Session::get_value('myvox')['url'] == 'account')
					{
						if (!isset($_POST['owner']) OR empty($_POST['owner']))
							array_push($labels, ['owner','']);
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

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);
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
						$_POST['type'] = 'request';
						$_POST['token'] = strtolower(Functions::get_random(8));

						$query = $this->model->new_vox($_POST);

						if (!empty($query))
						{
							if (!empty($_POST['email']))
							{
								$mail1 = new Mailer(true);

								try
								{
									$mail1->setFrom('noreply@guestvox.com', 'Guestvox');
									$mail1->addAddress($_POST['email'], ((!empty($_POST['firstname']) AND !empty($_POST['lastname'])) ? $_POST['firstname'] . ' ' . $_POST['lastname'] : Languages::email('not_name')[$this->lang1]));
									$mail1->Subject = Languages::email('thanks_received_request')[$this->lang1];
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
															<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
														</figure>
													</td>
												</tr>
												<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
														<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail1->Subject . '</h4>
														<h6 style="width:100%;margin:0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '</h6>
													</td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
														<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">Power by Guestvox</a>
													</td>
												</tr>
											</table>
										</body>
									</html>';
									$mail1->send();
								}
								catch (Exception $e) { }
							}

							if (!empty($_POST['phone_lada']) AND !empty($_POST['phone_number']))
							{
								$sms1 = $this->model->get_sms();

								if ($sms1 > 0)
								{
									$sms1_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									$sms1_client = new \Nexmo\Client($sms1_basic);
									$sms1_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('thanks_received_request')[$this->lang1] . '. ' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '. Power by Guestvox.';

									try
									{
										$sms1_client->message()->send([
											'to' => $_POST['phone_lada'] . $_POST['phone_number'],
											'from' => 'Guestvox',
											'text' => $sms1_text
										]);

										$sms1 = $sms1 - 1;
									}
									catch (Exception $e) { }

									$this->model->edit_sms($sms1);
								}
							}

							$_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
							$_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
							$_POST['location'] = $this->model->get_location($_POST['location']);
							$_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area']['id']);

							$mail2 = new Mailer(true);

							try
							{
								$mail2->setFrom('noreply@guestvox.com', 'Guestvox');

								foreach ($_POST['assigned_users'] as $value)
									$mail2->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

								$mail2->Subject = Languages::email('new', 'request')[$this->lang2];
								$mail2->Body =
								'<html>
									<head>
										<title>' . $mail2->Subject . '</title>
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
													<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd.m.Y') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '</h6>
													<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('observations')[$this->lang2] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Languages::email('not_observations')[$this->lang2]) . '</p>
													<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'] . '">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
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
								$mail2->send();
							}
							catch (Exception $e) { }

							$sms2 = $this->model->get_sms();

							if ($sms2 > 0)
							{
								$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
								$sms2_client = new \Nexmo\Client($sms2_basic);
								$sms2_text = 'Guestvox. ' . Languages::email('new', 'request')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
								$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '. ';
								$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
								$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
								$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('observations')[$this->lang2] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Languages::email('not_observations')[$this->lang2]) . '. ';
								$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];

								foreach ($_POST['assigned_users'] as $value)
								{
									if ($sms2 > 0)
									{
										try
										{
											$sms2_client->message()->send([
												'to' => $value['phone']['lada'] . $value['phone']['number'],
												'from' => 'Guestvox',
												'text' => $sms2_text
											]);

											$sms2 = $sms2 - 1;
										}
										catch (Exception $e) { }
									}
								}

								$this->model->edit_sms($sms2);
							}

							if (Session::get_value('myvox')['url'] == 'account')
							{
								$myvox = Session::get_value('myvox');

								$myvox['owner'] = null;

								Session::set_value('myvox', $myvox);
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_received_request}' . (!empty($_POST['email']) ? ' {$lang.thanks_received_myvox_1} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_received_myvox_2}' : ''),
								'path' => '/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '')
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
				$template = $this->view->render($this, 'request');

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.request}');

				$html =
				'<form name="new_request">
					<div class="row">';

				if (Session::get_value('myvox')['url'] == 'account')
				{
					$html .=
					'<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<select name="owner">
									<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_owners('request') as $value)
						$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

					$html .=
					'			</select>
							</label>
						</div>
					</div>';
				}

				$html .=
				'<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.opportunity_area} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="opportunity_area">
								<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_opportunity_areas('request') as $value)
					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

				$html .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.opportunity_type} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="opportunity_type" disabled>
								<option value="" hidden>{$lang.choose}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.date} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="date" name="started_date" value="' . Functions::get_current_date('Y-m-d') . '">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.hour} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="time" name="started_hour" value="' . Functions::get_current_hour() . '">
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label required>
							<p>{$lang.location} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="location">
								<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_locations('request') as $value)
					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

				$html .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label unrequired>
							<p>{$lang.observations} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<textarea name="observations"></textarea>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.firstname}</p>
							<input type="text" name="firstname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.lastname}</p>
							<input type="text" name="lastname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.email}</p>
							<input type="email" name="email">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.lada}</p>
							<select name="phone_lada">
								<option value="">{$lang.empty} ({$lang.choose})</option>';

				foreach ($this->model->get_countries() as $value)
					$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

				$html .=
				'					</select>
								</label>
							</div>
						</div>
						<div class="span3">
							<div class="label">
								<label unrequired>
									<p>{$lang.phone}</p>
									<input type="number" name="phone_number">
								</label>
							</div>
						</div>
						<div class="span12">
							<div class="buttons">
								<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '" class="delete"><i class="fas fa-times"></i></a>
								<button type="submit" class="new"><i class="fas fa-check"></i></button>
							</div>
						</div>
					</div>
				</form>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-house-user"></i></a>',
					'{$html}' => $html
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }

	public function incident($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['operation'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['incident']['status'] == true)
			{
				if (!empty(Session::get_value('myvox')['url']))
				{
					if (Session::get_value('myvox')['url'] == 'account')
						$break = false;
					else if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'get_owner')
				{
					$owner = $this->model->get_owner($_POST['owner']);

					if (!empty($owner))
					{
						if (Session::get_value('myvox')['account']['type'] == 'hotel')
							$owner['reservation'] = $this->model->get_reservation($owner['number']);

						$myvox = Session::get_value('myvox');

						$myvox['owner'] = $owner;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success'
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

				if ($_POST['action'] == 'get_opt_opportunity_types')
				{
					$html = '<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], 'incident') as $value)
						$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

					Functions::environment([
						'status' => 'success',
						'html' => $html
					]);
				}

				if ($_POST['action'] == 'new_incident')
				{
					$labels = [];

					if (Session::get_value('myvox')['url'] == 'account')
					{
						if (!isset($_POST['owner']) OR empty($_POST['owner']))
							array_push($labels, ['owner','']);
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

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);
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
						$_POST['type'] = 'incident';
						$_POST['token'] = strtolower(Functions::get_random(8));

						$query = $this->model->new_vox($_POST);

						if (!empty($query))
						{
							if (!empty($_POST['email']))
							{
								$mail1 = new Mailer(true);

								try
								{
									$mail1->setFrom('noreply@guestvox.com', 'Guestvox');
									$mail1->addAddress($_POST['email'], ((!empty($_POST['firstname']) AND !empty($_POST['lastname'])) ? $_POST['firstname'] . ' ' . $_POST['lastname'] : Languages::email('not_name')[$this->lang1]));
									$mail1->Subject = Languages::email('thanks_received_incident')[$this->lang1];
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
															<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
														</figure>
													</td>
												</tr>
												<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
														<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail1->Subject . '</h4>
														<h6 style="width:100%;margin:0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '</h6>
													</td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
														<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">Power by Guestvox</a>
													</td>
												</tr>
											</table>
										</body>
									</html>';
									$mail1->send();
								}
								catch (Exception $e) { }
							}

							if (!empty($_POST['phone_lada']) AND !empty($_POST['phone_number']))
							{
								$sms1 = $this->model->get_sms();

								if ($sms1 > 0)
								{
									$sms1_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									$sms1_client = new \Nexmo\Client($sms1_basic);
									$sms1_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('thanks_received_incident')[$this->lang1] . '. ' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '. Power by Guestvox.';

									try
									{
										$sms1_client->message()->send([
											'to' => $_POST['phone_lada'] . $_POST['phone_number'],
											'from' => 'Guestvox',
											'text' => $sms1_text
										]);

										$sms1 = $sms1 - 1;
									}
									catch (Exception $e) { }

									$this->model->edit_sms($sms1);
								}
							}

							$_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
							$_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
							$_POST['location'] = $this->model->get_location($_POST['location']);
							$_POST['assigned_users'] = $this->model->get_assigned_users($_POST['opportunity_area']['id']);

							$mail2 = new Mailer(true);

							try
							{
								$mail2->setFrom('noreply@guestvox.com', 'Guestvox');

								foreach ($_POST['assigned_users'] as $value)
									$mail2->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

								$mail2->Subject = Languages::email('new', 'incident')[$this->lang2];
								$mail2->Body =
								'<html>
									<head>
										<title>' . $mail2->Subject . '</title>
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
													<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd.m.Y') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '</h6>
													<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('confidentiality')[$this->lang2] . ': ' . Languages::email('not')[$this->lang2] . '</h6>
													<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('description')[$this->lang2] . ': ' . (!empty($_POST['description']) ? $_POST['description'] : Languages::email('not_description')[$this->lang2]) . '</p>
													<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'] . '">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
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
								$mail2->send();
							}
							catch (Exception $e) { }

							$sms2 = $this->model->get_sms();

							if ($sms2 > 0)
							{
								$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
								$sms2_client = new \Nexmo\Client($sms2_basic);
								$sms2_text = 'Guestvox. ' . Languages::email('new', 'incident')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
								$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') . '. ';
								$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
								$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
								$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . $_POST['location']['name'][$this->lang2] . '. ';
								$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('confidentiality')[$this->lang2] . ': ' . Languages::email('not')[$this->lang2] . '. ';
								$sms2_text .= Languages::email('description')[$this->lang2] . ': ' . (!empty($_POST['description']) ? $_POST['description'] : Languages::email('not_description')[$this->lang2]) . '. ';
								$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];

								foreach ($_POST['assigned_users'] as $value)
								{
									if ($sms2 > 0)
									{
										try
										{
											$sms2_client->message()->send([
												'to' => $value['phone']['lada'] . $value['phone']['number'],
												'from' => 'Guestvox',
												'text' => $sms2_text
											]);

											$sms2 = $sms2 - 1;
										}
										catch (Exception $e) { }
									}
								}

								$this->model->edit_sms($sms2);
							}

							if (Session::get_value('myvox')['url'] == 'account')
							{
								$myvox = Session::get_value('myvox');

								$myvox['owner'] = null;

								Session::set_value('myvox', $myvox);
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_received_incident}' . (!empty($_POST['email']) ? ' {$lang.thanks_received_myvox_1} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_received_myvox_2}' : ''),
								'path' => '/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '')
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
				$template = $this->view->render($this, 'incident');

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.incident}');

				$html =
				'<form name="new_incident">
					<div class="row">';

				if (Session::get_value('myvox')['url'] == 'account')
				{
					$html .=
					'<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<select name="owner">
									<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_owners('incident') as $value)
						$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

					$html .=
					'			</select>
							</label>
						</div>
					</div>';
				}

				$html .=
				'<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.opportunity_area} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="opportunity_area">
								<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_opportunity_areas('incident') as $value)
					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

				$html .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.opportunity_type} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="opportunity_type" disabled>
								<option value="" hidden>{$lang.choose}</option>
							</select>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.date} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="date" name="started_date" value="' . Functions::get_current_date('Y-m-d') . '">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label required>
							<p>{$lang.hour} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<input type="time" name="started_hour" value="' . Functions::get_current_hour() . '">
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label required>
							<p>{$lang.location} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<select name="location">
								<option value="" hidden>{$lang.choose}</option>';

				foreach ($this->model->get_locations('incident') as $value)
					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

				$html .=
				'			</select>
						</label>
					</div>
				</div>
				<div class="span12">
					<div class="label">
						<label unrequired>
							<p>{$lang.description} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
							<textarea name="description"></textarea>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.firstname}</p>
							<input type="text" name="firstname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.lastname}</p>
							<input type="text" name="lastname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.email}</p>
							<input type="email" name="email">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.lada}</p>
							<select name="phone_lada">
								<option value="">{$lang.empty} ({$lang.choose})</option>';

				foreach ($this->model->get_countries() as $value)
					$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

				$html .=
				'					</select>
								</label>
							</div>
						</div>
						<div class="span3">
							<div class="label">
								<label unrequired>
									<p>{$lang.phone}</p>
									<input type="number" name="phone_number">
								</label>
							</div>
						</div>
						<div class="span12">
							<div class="buttons">
								<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '" class="delete"><i class="fas fa-times"></i></a>
								<button type="submit" class="new"><i class="fas fa-check"></i></button>
							</div>
						</div>
					</div>
				</form>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-house-user"></i></a>',
					'{$html}' => $html
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }

    public function menu($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['operation'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['menu']['status'] == true)
			{
				if (!empty(Session::get_value('myvox')['url']))
				{
					if (Session::get_value('myvox')['url'] == 'account' AND (Session::get_value('myvox')['account']['type'] == 'hotel' OR Session::get_value('myvox')['account']['type'] == 'restaurant'))
						$break = false;
					else if (Session::get_value('myvox')['url'] == 'owner' AND (Session::get_value('myvox')['account']['type'] == 'hotel' OR Session::get_value('myvox')['account']['type'] == 'restaurant') AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
					else if (Session::get_value('myvox')['url'] == 'delivery' AND Session::get_value('myvox')['account']['type'] == 'restaurant')
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (!isset(Session::get_value('myvox')['menu_order']) OR empty(Session::get_value('myvox')['menu_order']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_order'] = [
					'total' => 0,
					'shopping_cart' => []
				];

				Session::set_value('myvox', $myvox);
			}

			if (!isset(Session::get_value('myvox')['menu_categories']) OR empty(Session::get_value('myvox')['menu_categories']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_categories'] = [];

				Session::set_value('myvox', $myvox);
			}

			if (!isset(Session::get_value('myvox')['menu_name']) OR empty(Session::get_value('myvox')['menu_name']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_name'] = '';

				Session::set_value('myvox', $myvox);
			}

			if (!isset(Session::get_value('myvox')['menu_price']) OR empty(Session::get_value('myvox')['menu_price']))
			{
				$myvox = Session::get_value('myvox');

				$myvox['menu_price'] = '';

				Session::set_value('myvox', $myvox);
			}

			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'filter_menu_products_by_categories')
				{
					$myvox = Session::get_value('myvox');

					if (array_key_exists($_POST['id'], $myvox['menu_categories']))
						unset($myvox['menu_categories'][$_POST['id']]);
					else
						$myvox['menu_categories'][$_POST['id']] = $_POST['id'];

					Session::set_value('myvox', $myvox);

					Functions::environment([
						'status' => 'success'
					]);
				}

				if ($_POST['action'] == 'preview_menu_product')
				{
					$query = $this->model->get_menu_product($_POST['id']);

					if (!empty($query))
					{
						$html = '<figure class="' . $query['avatar'] . '" ' . (($query['avatar'] == 'icon') ? 'style="background-color:' . $query['icon_color'] . ';"' : '') . '>';

						if ($query['avatar'] == 'image')
							$html .= '<img src="{$path.uploads}' . $query['image'] . '">';
						else if ($query['avatar'] == 'icon')
							$html .= '<img src="{$path.images}icons/' . $query['icon_type'] . '/' . $query['icon_url'] . '">';

						$html .=
						'</figure>
						<h2>' . $query['name'][$this->lang1] . '</h2>
						<p>' . (!empty($query['description'][$this->lang1]) ? $query['description'][$this->lang1] : '') . '</p>
						' . (!empty($query['price']) ? '<span>' . Functions::get_formatted_currency($query['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . (!empty($query['topics']) ? ' (+ {$lang.topics})' : '') . '</span>' : '');

						if (Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['status'] == 'open' AND Functions::get_current_hour() >= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['opening'] AND Functions::get_current_hour() <= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['closing'])
						{
							$html .= '<form name="add_to_menu_order">';

							foreach ($query['topics'] as $key => $value)
							{
								$html .= '<div class="item">';

								foreach ($value as $subkey => $subvalue)
								{
									$html .=
									'<label>
										<input type="' . $subvalue['selection'] . '" name="topics_' . $key . '[]" value="' . $key . '_' . $subkey . '" data-action="update_menu_product_price" data-id="' . $_POST['id'] . '" ' . (($subvalue['selection'] == 'radio' AND $subkey == 0) ? 'checked' : '') . ' />
										<p>' . $subvalue['name'][$this->lang1] . '</p>
										<span>' . (!empty($subvalue['price']) ? Functions::get_formatted_currency($subvalue['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . ' +' : '') . '</span>
									</label>';
								}

								$html .= '</div>';
							}

							$html .=
							'	<div class="buttons">
									<a class="delete" button-cancel><i class="fas fa-times"></i></a>
									<a data-action="minus_to_menu_order" data-id="' . $_POST['id'] . '"><i class="fas fa-minus"></i></a>
									<input type="text" name="quantity" value="1" min="1">
									<a data-action="plus_to_menu_order" data-id="' . $_POST['id'] . '"><i class="fas fa-plus"></i></a>
									<a class="new" data-action="add_to_menu_order" data-id="' . $_POST['id'] . '"><i class="fas fa-check"></i></a>
								</div>
							</form>';
						}
						else
						{
							$html .=
							'<div class="buttons closed">
								<h6>{$lang.business_closed}</h6>
								<p><strong>{$lang.monday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['monday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['monday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['monday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.tuesday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['tuesday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['tuesday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['tuesday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.wednesday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['wednesday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['wednesday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['wednesday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.thursday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['thursday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['thursday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['thursday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.friday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['friday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['friday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['friday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.saturday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['saturday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['saturday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['saturday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<p><strong>{$lang.sunday}</strong>: ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['sunday']['status'] == 'open') ? '{$lang.open} {$lang.from} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['sunday']['opening'], '+ hrs') . ' {$lang.a} ' . Functions::get_formatted_hour(Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule']['sunday']['closing'], '+ hrs') : '{$lang.close}') . '</p>
								<a class="delete" button-cancel><i class="fas fa-times"></i></a>
							</div>';
						}

						Functions::environment([
							'status' => 'success',
							'html' => $html
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

				if ($_POST['action'] == 'update_menu_product_price')
				{
					$query = $this->model->get_menu_product($_POST['id']);

					if (!empty($query))
					{
						$total = 0;

						foreach ($_POST as $key => $value)
						{
							$key = explode('_', $key);

							if ($key[0] == 'topics')
							{
								foreach ($value as $subkey => $subvalue)
								{
									$subvalue = explode('_', $subvalue);
									$subvalue = $query['topics'][$subvalue[0]][$subvalue[1]];
									$total = ($total + $subvalue['price']);
								}
							}
						}

						$total = (($total + $query['price']) * $_POST['quantity']);
						$total = !empty($total) ? Functions::get_formatted_currency($total, Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . (($total == $query['price'] AND !empty($query['topics'])) ? ' (+ {$lang.topics})' : '') : '';

						Functions::environment([
							'status' => 'success',
							'total' => $total
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

				if ($_POST['action'] == 'add_to_menu_order')
				{
					$query = $this->model->get_menu_product($_POST['id']);

					if (!empty($query))
					{
						$myvox = Session::get_value('myvox');

						$_POST['topics'] = [];

						foreach ($_POST as $key => $value)
						{
							$key = explode('_', $key);

							if ($key[0] == 'topics')
							{
								foreach ($value as $subkey => $subvalue)
								{
									$subvalue = explode('_', $subvalue);

									array_push($_POST['topics'], $query['topics'][$subvalue[0]][$subvalue[1]]);
								}
							}
						}

						if (array_key_exists($_POST['id'], $myvox['menu_order']['shopping_cart']))
						{
							array_push($myvox['menu_order']['shopping_cart'][$_POST['id']], [
								'quantity' => $_POST['quantity'],
								'id' => $_POST['id'],
								'name' => $query['name'],
								'topics' => $_POST['topics'],
								'price' => $query['price'],
								'total' => $this->model->get_menu_order_total($query['price'], $_POST['topics'], $_POST['quantity'])
							]);
						}
						else
						{
							$myvox['menu_order']['shopping_cart'][$_POST['id']] = [
								[
									'quantity' => $_POST['quantity'],
									'id' => $_POST['id'],
									'name' => $query['name'],
									'topics' => $_POST['topics'],
									'price' => $query['price'],
									'total' => $this->model->get_menu_order_total($query['price'], $_POST['topics'], $_POST['quantity'])
								]
							];
						}

						$myvox['menu_order']['total'] = $this->model->get_menu_order_total($myvox['menu_order']['shopping_cart']);

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success',
							'message' => '{$lang.operation_success}'
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

				if ($_POST['action'] == 'remove_to_menu_order')
				{
					$myvox = Session::get_value('myvox');

					unset($myvox['menu_order']['shopping_cart'][$_POST['key']][$_POST['subkey']]);

					if (empty($myvox['menu_order']['shopping_cart'][$_POST['key']]))
						unset($myvox['menu_order']['shopping_cart'][$_POST['key']]);

					$myvox['menu_order']['total'] = $this->model->get_menu_order_total($myvox['menu_order']['shopping_cart']);

					Session::set_value('myvox', $myvox);

					Functions::environment([
						'status' => 'success',
						'message' => '{$lang.operation_success}'
					]);
				}

				if ($_POST['action'] == 'search')
				{
					$labels = [];

					if (!empty($_POST['price']) AND $_POST['price'] < 0)
						array_push($labels, ['price','']);

					if (empty($labels))
					{
						$myvox = Session::get_value('myvox');

						if (isset($_POST['name']) AND !empty($_POST['name']))
							$myvox['menu_name'] = $_POST['name'];

						if (isset($_POST['price']) AND !empty($_POST['price']))
							$myvox['menu_price'] = $_POST['price'];

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success',
							'path' =>  '/' . $params[0] . '/menu/products'
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

				if ($_POST['action'] == 'clear_search')
				{
					$myvox = Session::get_value('myvox');

					$myvox['menu_categories'] = [];
					$myvox['menu_name'] = '';
					$myvox['menu_price'] = '';

					Session::set_value('myvox', $myvox);

					Functions::environment([
						'status' => 'success'
					]);
				}

				if ($_POST['action'] == 'get_owner')
				{
					$owner = $this->model->get_owner($_POST['owner']);

					if (!empty($owner))
					{
						if (Session::get_value('myvox')['account']['type'] == 'hotel')
							$owner['reservation'] = $this->model->get_reservation($owner['number']);

						$myvox = Session::get_value('myvox');

						$myvox['owner'] = $owner;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success'
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

				if ($_POST['action'] == 'new_menu_order')
				{
					$labels = [];

					if (Session::get_value('myvox')['url'] == 'account')
					{
						if (!isset($_POST['owner']) OR empty($_POST['owner']))
							array_push($labels, ['owner','']);
					}

					if (Session::get_value('myvox')['account']['type'] == 'hotel')
					{
						if (!isset($_POST['location']) OR empty($_POST['location']))
							array_push($labels, ['location','']);
					}
					else if (Session::get_value('myvox')['account']['type'] == 'restaurant')
					{
						if (Session::get_value('myvox')['url'] == 'delivery')
						{
							if (!isset($_POST['delivery']) OR empty($_POST['delivery']))
								array_push($labels, ['delivery','']);

							if ($_POST['delivery'] == 'home')
							{
								if (!isset($_POST['address']) OR empty($_POST['address']))
									array_push($labels, ['address','']);
							}

							if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
								array_push($labels, ['firstname','']);

							if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
								array_push($labels, ['lastname','']);

							if (!isset($_POST['phone_lada']) OR empty($_POST['phone_lada']))
								array_push($labels, ['phone_lada','']);

							if (!isset($_POST['phone_number']) OR empty($_POST['phone_number']))
								array_push($labels, ['phone_number','']);
						}
					}

					if (empty($labels))
					{
						$_POST['token'] = strtolower(Functions::get_random(8));
						$_POST['started_date'] = Functions::get_current_date();
						$_POST['started_hour'] = Functions::get_current_hour();

						$query = $this->model->new_menu_order($_POST);

						if (!empty($query))
						{
							// $options = array(
							// 	'cluster' => 'us2',
							// 	'useTLS' => true
							// );

							// $pusher = new Pusher\Pusher(
							// 	'1907b80d942422da0b8e',
							// 	'e565c25c10f7f68fc3fc',
							// 	'1065298',
							// 	$options
							// );

							// $pusher->trigger('menu-orders', 'new-order', 'Pedido enviado');

							$_POST['type'] = 'request';
							$_POST['menu_order'] = $query;

							$query = $this->model->new_vox($_POST, true);

							if (!empty($query))
							{
								if (Session::get_value('myvox')['account']['ambit']['status'] == true)
								{
									$array_payload = [
										'store_id' => 101,
										'client' => [
										  'name' => 'Jorge',
										  'phone' => '+525581053825',
										  'email' => 'jxochihua@hotmail.com',
										  'address' => [
											'client_address' => 'Ayuntamiento 153, 14250, Depto B204, Col. Miguel Hidalgo , Cdmx ',
											'client_address_parts' => [
												'city' => 'Cdmx ',
												'street' => 'Ayuntamiento 153',
												'more_address' => '14250, Depto B204, Col. Miguel Hidalgo ',
										  		],
											],
										],
										'order' => [
										  'id' => 158350180,
										  'type' => 'delivery',
										  'date' => '2020-09-13T19:56:12.000Z',
										  'note' => '',
										  'total' => 413,
										  'store' => '0',
										  'origen' => 'eRest',
										  'formaPago' => '04',
										  'idFormaPago' => 'CARD',
										],
										'Items' => [
										  0 => [
											'price' => 297,
											'type' => '2',
											'note' => '',
											'code' => 'GRI-004',
											'descrip' => 'Bife de lomo',
											'cant' => 1,
											'modifiers' => [
											  0 => [
													'price' => 0,
													'type' => '2',
													'code' => 'MDPLA-056',
													'descrip' => 'Termino 3/4',
													'cant' => 1,
													],
										  		],
											],
										  1 => [
											'price' => 81,
											'type' => '2',
											'note' => '',
											'code' => 'ENS-005',
											'descrip' => 'Ensalada Mixta',
											'cant' => 1,
											'modifiers' => [],
											],
										],
									];

									$post_order = Functions::api('ambit', $array_payload, 'post', '');
								}

								if (Session::get_value('myvox')['account']['type'] == 'restaurant')
								{
									if (Session::get_value('myvox')['url'] == 'delivery')
									{
										if (!empty($_POST['email']))
										{
											$mail1 = new Mailer(true);

											try
											{
												$mail1->setFrom('noreply@guestvox.com', 'Guestvox');
												$mail1->addAddress($_POST['email'], ((!empty($_POST['firstname']) AND !empty($_POST['lastname'])) ? $_POST['firstname'] . ' ' . $_POST['lastname'] : Languages::email('not_name')[$this->lang1]));
												$mail1->Subject = Languages::email('thanks_received_menu_order')[$this->lang1];
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
																		<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
																	</figure>
																</td>
															</tr>
															<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
																<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
																	<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:24px;font-weight:600;text-align:center;color:#212121;">' . $mail1->Subject . '</h4>
																	<h6 style="width:100%;margin:0px 0px 10px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '</h6>';

												foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
												{
													foreach ($value as $subvalue)
													{
														$mail1->Body .= '<p style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">x' . $subvalue['quantity'] . ' ' . $subvalue['name'][$this->lang2];

														if (!empty($subvalue['topics']))
														{
															$mail1->Body .= ' (';

															foreach ($subvalue['topics'] as $parentvalue)
																$mail1->Body .= $parentvalue['name'][$this->lang2] . ', ';

															$mail1->Body .= ')';
														}

														$mail1->Body .= '</p>';
													}
												}

												$mail1->Body .=
												'				</td>
															</tr>
															<tr style="width:100%;margin:0px;padding:0px;border:0px;">
																<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
																	<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">Power by Guestvox</a>
																</td>
															</tr>
														</table>
													</body>
												</html>';
												$mail1->send();
											}
											catch (Exception $e) { }
										}

										$sms1 = $this->model->get_sms();

										if ($sms1 > 0)
										{
											$sms1_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
											$sms1_client = new \Nexmo\Client($sms1_basic);
											$sms1_text = Session::get_value('myvox')['account']['name'] . '. ' . Languages::email('thanks_received_menu_order')[$this->lang1] . '. https://' . Configuration::$domain . '/' . $params[0] . '/menu/' . $_POST['token'] . '. Power by Guestvox.';

											try
											{
												$sms1_client->message()->send([
													'to' => $_POST['phone_lada'] . $_POST['phone_number'],
													'from' => 'Guestvox',
													'text' => $sms1_text
												]);

												$sms1 = $sms1 - 1;
											}
											catch (Exception $e) { }

											$this->model->edit_sms($sms1);
										}
									}
								}

								$_POST['opportunity_area'] = $this->model->get_opportunity_area(Session::get_value('myvox')['account']['settings']['myvox']['menu']['opportunity_area']);
								$_POST['opportunity_type'] = $this->model->get_opportunity_type(Session::get_value('myvox')['account']['settings']['myvox']['menu']['opportunity_type']);

								if (Session::get_value('myvox')['account']['type'] == 'hotel')
									$_POST['location'] = $this->model->get_location($_POST['location']);

								$_POST['assigned_users'] = $this->model->get_assigned_users(Session::get_value('myvox')['account']['settings']['myvox']['menu']['opportunity_area']);

								$mail2 = new Mailer(true);

								try
								{
									$mail2->setFrom('noreply@guestvox.com', 'Guestvox');

									foreach ($_POST['assigned_users'] as $value)
										$mail2->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);

									$mail2->Subject = Languages::email('new', 'request')[$this->lang2];
									$mail2->Body =
									'<html>
										<head>
											<title>' . $mail2->Subject . '</title>
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
														<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail2->Subject . '</h4>
														<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '</h6>
														<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('owner')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel' OR (Session::get_value('myvox')['account']['type'] == 'restaurant' AND (Session::get_value('myvox')['url'] == 'account' OR Session::get_value('myvox')['url'] == 'owner'))) ? Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') : Languages::email('not_owner')[$this->lang2]) . '</h6>
														<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '</h6>
														<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '</h6>
														<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd.m.Y') . '</h6>
														<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
														<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('location')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel') ? $_POST['location']['name'][$this->lang2] : ((Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'delivery') ? $_POST['address'] : Languages::email('not_location')[$this->lang2])) . '</h6>
														' . ((Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'delivery') ? '<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('references')[$this->lang2] . ': ' . (!empty($_POST['references']) ? $_POST['references'] : Languages::email('not_references')[$this->lang2]) . '</h6>' : '') . '
														<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '</h6>
														<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Languages::email('observations')[$this->lang2] . ': ' . Languages::email('not_observations')[$this->lang2] . '</p>';

									foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
									{
										foreach ($value as $subvalue)
										{
											$mail2->Body .= '<p style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">x' . $subvalue['quantity'] . ' ' . $subvalue['name'][$this->lang2];

											if (!empty($subvalue['topics']))
											{
												$mail2->Body .= ' (';

												foreach ($subvalue['topics'] as $parentvalue)
													$mail2->Body .= $parentvalue['name'][$this->lang2] . ', ';

												$mail2->Body .= ')';
											}

											$mail2->Body .= '</p>';
										}
									}

									$mail2->Body .=
									'					<a style="width:100%;display:block;margin:20px 0px 0px 0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'] . '">' . Languages::email('give_follow_up')[$this->lang2] . '</a>
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
									$mail2->send();
								}
								catch (Exception $e) { }

								$whatsapp = $this->model->get_whatsapp();

								if ($whatsapp > 0)
								{
									$whats_text = 'Guestvox. ' . Languages::email('new', 'request')[$this->lang2] . '. ';
									$whats_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
									$whats_text .= Languages::email('owner')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel' OR (Session::get_value('myvox')['account']['type'] == 'restaurant' AND (Session::get_value('myvox')['url'] == 'account' OR Session::get_value('myvox')['url'] == 'owner'))) ? Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') : Languages::email('not_owner')[$this->lang2]) . '. ';
									$whats_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
									$whats_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
									$whats_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
									$whats_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
									$whats_text .= Languages::email('location')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel') ? $_POST['location']['name'][$this->lang2] : ((Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'delivery') ? $_POST['address'] : Languages::email('not_location')[$this->lang2])) . '. ';
									$whats_text .= (Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'delivery') ? Languages::email('references')[$this->lang2] . ': ' . (!empty($_POST['references']) ? $_POST['references'] : Languages::email('not_references')[$this->lang2]) . '. ' : '';
									$whats_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
									$whats_text .= Languages::email('observations')[$this->lang2] . ': ' . Languages::email('not_observations')[$this->lang2] . '. ';

									foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
									{
										foreach ($value as $subvalue)
										{
											$whats_text .= 'x' . $subvalue['quantity'] . ' ' . $subvalue['name'][$this->lang2] . '. ';

											if (!empty($subvalue['topics']))
											{
												$whats_text .= '(';

												foreach ($subvalue['topics'] as $parentvalue)
													$whats_text .= $parentvalue['name'][$this->lang2] . ', ';

												$whats_text .= '). ';
											}
										}
									}

									$whats_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];

									foreach ($_POST['assigned_users'] as $value)
									{
										if ($whatsapp > 0 AND $value['whatsapp'] == true)
										{
											try
											{
												$ch = curl_init();

												$headers = array(
													'Cache-Control: no-cache',
													'Content-Type: application/x-www-form-urlencoded',
													'Accept: text/plain',
													'Apikey: b70ed8d3c58248a6c8581923e3df00fa'
												);

												$message = urlencode($whats_text);

												curl_setopt($ch, CURLOPT_URL, 'https://api.gupshup.io/sm/api/v1/msg');
												curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
												curl_setopt($ch, CURLOPT_POST, 1);
												curl_setopt($ch, CURLOPT_POSTFIELDS, 'channel=whatsapp&source=525532012511&destination=' . $value['phone']['lada'] . '1' . $value['phone']['number'] .'&message=' . $message);
												curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

												$result = curl_exec($ch);

												curl_close($ch);

												$whatsapp = $whatsapp - 1;
											}
											catch (Exception $e) { }
										}
									}

									$this->model->edit_whatsapp($whatsapp);
								}

								$sms2 = $this->model->get_sms();

								if ($sms2 > 0)
								{
									$sms2_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									$sms2_client = new \Nexmo\Client($sms2_basic);
									$sms2_text = 'Guestvox. ' . Languages::email('new', 'request')[$this->lang2] . '. ';
									$sms2_text .= Languages::email('token')[$this->lang2] . ': ' . $_POST['token'] . '. ';
									$sms2_text .= Languages::email('owner')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel' OR (Session::get_value('myvox')['account']['type'] == 'restaurant' AND (Session::get_value('myvox')['url'] == 'account' OR Session::get_value('myvox')['url'] == 'owner'))) ? Session::get_value('myvox')['owner']['name'][$this->lang2] . (!empty(Session::get_value('myvox')['owner']['number']) ? ' #' . Session::get_value('myvox')['owner']['number'] : '') : Languages::email('not_owner')[$this->lang2]) . '. ';
									$sms2_text .= Languages::email('opportunity_area')[$this->lang2] . ': ' . $_POST['opportunity_area']['name'][$this->lang2] . '. ';
									$sms2_text .= Languages::email('opportunity_type')[$this->lang2] . ': ' . $_POST['opportunity_type']['name'][$this->lang2] . '. ';
									$sms2_text .= Languages::email('started_date')[$this->lang2] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
									$sms2_text .= Languages::email('started_hour')[$this->lang2] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
									$sms2_text .= Languages::email('location')[$this->lang2] . ': ' . ((Session::get_value('myvox')['account']['type'] == 'hotel') ? $_POST['location']['name'][$this->lang2] : ((Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'delivery') ? $_POST['address'] : Languages::email('not_location')[$this->lang2])) . '. ';
									$sms2_text .= (Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'delivery') ? Languages::email('references')[$this->lang2] . ': ' . (!empty($_POST['references']) ? $_POST['references'] : Languages::email('not_references')[$this->lang2]) . '. ' : '';
									$sms2_text .= Languages::email('urgency')[$this->lang2] . ': ' . Languages::email('medium')[$this->lang2] . '. ';
									$sms2_text .= Languages::email('observations')[$this->lang2] . ': ' . Languages::email('not_observations')[$this->lang2] . '. ';

									foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $value)
									{
										foreach ($value as $subvalue)
										{
											$sms2_text .= 'x' . $subvalue['quantity'] . ' ' . $subvalue['name'][$this->lang2] . '. ';

											if (!empty($subvalue['topics']))
											{
												$sms2_text .= '(';

												foreach ($subvalue['topics'] as $parentvalue)
													$sms2_text .= $parentvalue['name'][$this->lang2] . ', ';

												$sms2_text .= '). ';
											}
										}
									}

									$sms2_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $_POST['token'];

									foreach ($_POST['assigned_users'] as $value)
									{
										if ($sms2 > 0)
										{
											try
											{
												$sms2_client->message()->send([
													'to' => $value['phone']['lada'] . $value['phone']['number'],
													'from' => 'Guestvox',
													'text' => $sms2_text
												]);

												$sms2 = $sms2 - 1;
											}
											catch (Exception $e) { }
										}
									}

									$this->model->edit_sms($sms2);
								}

								$myvox = Session::get_value('myvox');

								$myvox['menu_order'] = null;

								Session::set_value('myvox', $myvox);

								if (Session::get_value('myvox')['url'] == 'account')
								{
									$myvox = Session::get_value('myvox');

									$myvox['owner'] = null;

									Session::set_value('myvox', $myvox);
								}

								Functions::environment([
									'status' => 'success',
									'message' => '{$lang.thanks_received_menu_order}' . ((Session::get_value('myvox')['account']['type'] == 'restaurant' AND Session::get_value('myvox')['url'] == 'delivery' AND !empty($_POST['email'])) ? ' {$lang.thanks_received_myvox_1} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_received_myvox_2}' : ''),
									'path' => '/' . $params[0] . '/menu/products'
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
				$template = $this->view->render($this, 'menu');

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.menu}');

				$html = '';

				if ($params[1] == 'products')
				{
					$html .= '<section data-menu-categories>';

					foreach ($this->model->get_menu_categories() as $value)
					{
						$html .=
						'<div>
							<figure class="' . (in_array($value['id'], Session::get_value('myvox')['menu_categories']) ? 'selected' : '') . '">
								<img src="{$path.images}icons/' . $value['icon_type'] . '/' . $value['icon_url'] . '">
								<a data-action="filter_menu_products_by_categories" data-id="' . $value['id'] . '"></a>
							</figure>
							<span>' . $value['name'][$this->lang1] . '</span>
						</div>';
					}

					$html .=
				    '</section>
					<section data-menu-products>';

					foreach ($this->model->get_menu_products() as $value)
					{
						if (in_array(Functions::get_current_day(), $value['available']['days']))
						{
							if ((empty($value['available']['start_date']) AND empty($value['available']['end_date'])) OR (!empty($value['available']['start_date']) AND !empty($value['available']['end_date']) AND Functions::get_current_date() >= $value['available']['start_date'] AND Functions::get_current_date() <= $value['available']['end_date']))
							{
								$html .=
								'<div>
									<figure class="' . $value['avatar'] . '" ' . (($value['avatar'] == 'icon') ? 'style="background-color:' . $value['icon_color'] . ';"' : '') . '>';

								if ($value['avatar'] == 'image')
									$html .= '<img src="{$path.uploads}' . $value['image'] . '">';
								else if ($value['avatar'] == 'icon')
									$html .= '<img src="{$path.images}icons/' . $value['icon_type'] . '/' . $value['icon_url'] . '"></figure>';

								$html .=
								'	</figure>
									<div>
										<h2>' . $value['name'][$this->lang1] . '</h2>
										' . (!empty($value['price']) ? '<span>' . Functions::get_formatted_currency($value['price'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . (!empty($value['topics']) ? ' (+ {$lang.topics})' : '') . '</span>' : '') . '
										<a data-action="preview_menu_product" data-id="' . $value['id'] . '"></a>
									</div>
								</div>';
							}
						}
					}

					$html .=
					'</section>
					<section class="modal fullscreen" data-modal="preview_menu_product">
					    <div class="content">
					        <main></main>
					    </div>
					</section>';
				}
				else if ($params[1] == 'search')
				{
					$html .=
					'<section data-search>
						<form name="search">
							<div class="row">
								<div class="span12">
									<div class="label">
										<label unrequired>
											<p>{$lang.what_do_you_want_to_eat}</p>
											<input type="text" name="name" value="' . Session::get_value('myvox')['menu_name'] . '">
										</label>
									</div>
								</div>
								<div class="span12">
									<div class="label">
										<label unrequired>
											<p>{$lang.how_much_is_your_budget}</p>
											<input type="number" name="price" value="' . Session::get_value('myvox')['menu_price'] . '">
										</label>
									</div>
								</div>
								<div class="span12">
									<div data-menu-categories>
										<p>{$lang.choose_your_categories}</p>';

					foreach ($this->model->get_menu_categories() as $value)
					{
						$html .=
						'<div>
							<figure class="' . (in_array($value['id'], Session::get_value('myvox')['menu_categories']) ? 'selected' : '') . '">
								<img src="{$path.images}icons/' . $value['icon_type'] . '/' . $value['icon_url'] . '">
								<a data-action="filter_menu_products_by_categories" data-id="' . $value['id'] . '"></a>
							</figure>
							<span>' . $value['name'][$this->lang1] . '</span>
						</div>';
					}

					$html .=
					'				</div>
								</div>
								<div class="span12">
									<div class="buttons">
										<a data-action="clear_search"><i class="fas fa-sync-alt"></i></a>
										<button class="new" type="submit"><i class="fas fa-search"></i></button>
									</div>
								</div>
							</div>
						</form>
					</section>';
				}
				else if ($params[1] == 'order')
				{
					$html .=
					'<section data-total>
						<span>' . Functions::get_formatted_currency(Session::get_value('myvox')['menu_order']['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span>
					</section>
					<section data-shopping-cart>';

					foreach (Session::get_value('myvox')['menu_order']['shopping_cart'] as $key => $value)
					{
						foreach ($value as $subkey => $subvalue)
						{
							$html .=
							'<div>
								<h2>x' . $subvalue['quantity'] . ' ' . $subvalue['name'][$this->lang1] . '</h2>';

							foreach ($subvalue['topics'] as $parentvalue)
								$html .= '<h3><i class="fas fa-check"></i>' . $parentvalue['name'][$this->lang1] . '</h3>';

							$html .=
								(!empty($subvalue['total']) ? '<span>' . Functions::get_formatted_currency($subvalue['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) . '</span>' : '') . '
								<a class="delete" data-action="remove_to_menu_order" data-key="' . $key . '" data-subkey="' . $subkey . '"><i class="fas fa-trash"></i></a>
							</div>';
						}
					}

					if (((Session::get_value('myvox')['url'] == 'account' OR Session::get_value('myvox')['url'] == 'owner') AND Session::get_value('myvox')['account']['settings']['myvox']['menu']['requests'] == true) OR (Session::get_value('myvox')['url'] == 'delivery' AND Session::get_value('myvox')['account']['settings']['myvox']['menu']['delivery'] == true))
					{
						if (!empty(Session::get_value('myvox')['menu_order']['shopping_cart']))
						{
							$html .=
							'<form name="new_menu_order">
								<div class="row">';

							if (Session::get_value('myvox')['url'] == 'account')
							{
								$html .=
								'<div class="span12">
									<div class="label">
										<label required>
											<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
											<select name="owner">
												<option value="" hidden>{$lang.choose}</option>';

								foreach ($this->model->get_owners('request') as $value)
									$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

								$html .=
								'			</select>
										</label>
									</div>
								</div>';
							}

							if (Session::get_value('myvox')['account']['type'] == 'hotel')
							{
								$html .=
								'<div class="span12">
									<div class="label">
										<label required>
											<p>{$lang.location} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
											<select name="location">
												<option value="" hidden>{$lang.choose}</option>';

								foreach ($this->model->get_locations('request') as $value)
									$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang1] . '</option>';

								$html .=
								'			</select>
										</label>
									</div>
								</div>';
							}

							if (Session::get_value('myvox')['account']['type'] == 'restaurant')
							{
								if (Session::get_value('myvox')['url'] == 'delivery')
								{
									$html .=
									'<div class="span12">
										<div class="label">
											<label required>
												<p>{$lang.where_do_we_deliver_you}</p>
												<select name="delivery">
													<option value="home">{$lang.my_home}</option>
													<option value="restaurant">{$lang.i_want_pick_up_restaurant}</option>
												</select>
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label required>
												<p>{$lang.address} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="address">
											</label>
										</div>
									</div>
									<div class="span12">
										<div class="label">
											<label unrequired>
												<p>{$lang.references} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
												<input type="text" name="references">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>{$lang.firstname}</p>
												<input type="text" name="firstname">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label required>
												<p>{$lang.lastname}</p>
												<input type="text" name="lastname">
											</label>
										</div>
									</div>
									<div class="span6">
										<div class="label">
											<label unrequired>
												<p>{$lang.email}</p>
												<input type="email" name="email">
											</label>
										</div>
									</div>
									<div class="span3">
										<div class="label">
											<label required>
												<p>{$lang.lada}</p>
												<select name="phone_lada">
													<option value="">{$lang.empty} ({$lang.choose})</option>';

									foreach ($this->model->get_countries() as $value)
										$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

									$html .=
									'			</select>
											</label>
										</div>
									</div>
									<div class="span3">
										<div class="label">
											<label required>
												<p>{$lang.phone}</p>
												<input type="number" name="phone_number">
											</label>
										</div>
									</div>';
								}
							}

							if (Session::get_value('myvox')['account']['payment']['status'] == true)
							{
								if (Session::get_value('myvox')['account']['payment']['type'] == 'mit')
								{
									if (Session::get_value('myvox')['menu_order']['total'] > 0)
									{
										$mit = Functions::api('mit', null, 'get', null, [
											'amount' => Session::get_value('myvox')['menu_order']['total'],
											'mit' => Session::get_value('myvox')['account']['payment']['mit']['code'],
											'currency' => Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency'],
											'types' => Session::get_value('myvox')['account']['payment']['mit']['types']
										]);

										$html .= '<div class="span12">';

										if (!empty($mit['code']) AND $mit['code'] == 'success')
											$html .= '<iframe src="' . $mit['url'] . '" width="100%" height="600px"></iframe>';
										else
											$html .= '<p>' . $mit['message'] . '</p>';

										$html .= '</div>';
									}
								}
							}
							else
							{
								$html .=
								'<div class="span12">
									<div class="buttons">
										<button class="new" type="submit">{$lang.generate_order}</button>
									</div>
								</div>';
							}

							$html .=
							'	</div>
							</form>';
						}
					}

					$html .= '</section>';
				}

				$html .=
				'<section class="buttons">
					<a href="/' . $params[0] . '/menu/products" ' . (($params[1] == 'products') ? 'class="active"' : '') . '><i class="fas fa-igloo"></i><span>{$lang.home}</span></a>
					<a href="/' . $params[0] . '/menu/search" ' . (($params[1] == 'search') ? 'class="active"' : '') . '><i class="fas fa-search"></i><span>{$lang.search}</span></a>
					<a ' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['status'] == 'open' AND Functions::get_current_hour() >= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['opening'] AND Functions::get_current_hour() <= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['closing']) ? 'href="/' . $params[0] . '/menu/order" ' . (($params[1] == 'order') ? 'class="active"' : '') . ' data-total' : '') . '><i class="fas fa-shopping-cart"></i><span>' . ((Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['status'] == 'open' AND Functions::get_current_hour() >= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['opening'] AND Functions::get_current_hour() <= Session::get_value('myvox')['account']['settings']['myvox']['menu']['schedule'][Functions::get_current_day()]['closing']) ? Functions::get_formatted_currency(Session::get_value('myvox')['menu_order']['total'], Session::get_value('myvox')['account']['settings']['myvox']['menu']['currency']) : '{$lang.business_closed}') . '</span></a>
				</section>';

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => (Session::get_value('myvox')['url'] == 'account' OR Session::get_value('myvox')['url'] == 'owner') ? '<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-house-user"></i></a>' : '',
					'{$html}' => $html
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }

    public function survey($params)
    {
		$break = true;

		if (Session::exists_var('myvox') == true AND !empty(Session::get_value('myvox')['account']))
		{
			if (Session::get_value('myvox')['account']['reputation'] == true AND Session::get_value('myvox')['account']['settings']['myvox']['survey']['status'] == true)
			{
				if (!empty(Session::get_value('myvox')['url']))
				{
					if (Session::get_value('myvox')['url'] == 'account')
						$break = false;
					else if (Session::get_value('myvox')['url'] == 'owner' AND !empty(Session::get_value('myvox')['owner']))
						$break = false;
				}
			}
		}

		if ($break == false)
		{
			if (Format::exist_ajax_request() == true)
			{
				if ($_POST['action'] == 'get_owner')
				{
					$owner = $this->model->get_owner($_POST['owner']);

					if (!empty($owner))
					{
						if (Session::get_value('myvox')['account']['type'] == 'hotel')
							$owner['reservation'] = $this->model->get_reservation($owner['number']);

						$myvox = Session::get_value('myvox');

						$myvox['owner'] = $owner;

						Session::set_value('myvox', $myvox);

						Functions::environment([
							'status' => 'success'
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

				if ($_POST['action'] == 'new_survey_answer')
				{
					$labels = [];

					if (Session::get_value('myvox')['url'] == 'account')
					{
						if (!isset($_POST['owner']) OR empty($_POST['owner']))
							array_push($labels, ['owner','']);
					}

					if (!empty($_POST['firstname']) OR !empty($_POST['lastname']))
					{
						if (!isset($_POST['firstname']) OR empty($_POST['firstname']))
							array_push($labels, ['firstname','']);

						if (!isset($_POST['lastname']) OR empty($_POST['lastname']))
							array_push($labels, ['lastname','']);
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
						$_POST['token'] = strtolower(Functions::get_random(8));

						$query = $this->model->new_survey_answer($_POST);

						if (!empty($query))
						{
							if (!empty($_POST['email']))
							{
								$mail = new Mailer(true);

								try
								{
									$mail->setFrom('noreply@guestvox.com', 'Guestvox');
									$mail->addAddress($_POST['email'], ((!empty($_POST['firstname']) AND !empty($_POST['lastname'])) ? $_POST['firstname'] . ' ' . $_POST['lastname'] : Languages::email('not_name')[$this->lang1]));
									$mail->Subject = Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['subject'][$this->lang1];
									$mail->Body =
									'<html>
										<head>
											<title>' . $mail->Subject . '</title>
										</head>
										<body>
											<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
												<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
														<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
															<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['logotype'] . '">
														</figure>
													</td>
												</tr>
												<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
														<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:18px;font-weight:600;text-align:center;color:#212121;">' . $mail->Subject . '</h4>
														<h6 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '</h6>
														<p style="width:100%;margin:0px;padding:0px;font-size:14px;font-weight:400;text-align:center;color:#757575;">' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['description'][$this->lang1] . '</p>';

									if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['image']))
									{
										$mail->Body .=
										'<figure style="width:100%;margin:20px 0px 0px 0px;padding:0px;">
											<img style="width:100%;" src="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['image'] . '">
										</figure>';
									}

									if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['attachment']))
										$mail->Body .= '<a style="width:100%;display:block;margin:20px 0px 0px 0px;padding:20px 0px;border-radius:40px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['attachment'] . '" download="https://' . Configuration::$domain . '/uploads/' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['attachment'] . '">' . Languages::email('download_file')[$this->lang1] . '</a>';

									$mail->Body .=
									'				</td>
												</tr>
												<tr style="width:100%;margin:0px;padding:0px;border:0px;">
													<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
														<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">Power by Guestvox</a>
													</td>
												</tr>
											</table>
										</body>
									</html>';
									$mail->send();
								}
								catch (Exception $e) { }
							}

							if (!empty($_POST['phone_lada']) AND !empty($_POST['phone_number']))
							{
								$sms = $this->model->get_sms();

								if ($sms > 0)
								{
									$sms_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
									$sms_client = new \Nexmo\Client($sms_basic);
									$sms_text = Session::get_value('myvox')['account']['name'] . '. ' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['subject'][$this->lang1] . '. ' . Languages::email('token')[$this->lang1] . ': ' . $_POST['token'] . '. ' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['mail']['description'][$this->lang1] . '. Power by Guestvox.';

									try
									{
										$sms_client->message()->send([
											'to' => $_POST['phone_lada'] . $_POST['phone_number'],
											'from' => 'Guestvox',
											'text' => $sms_text
										]);

										$sms = $sms - 1;
									}
									catch (Exception $e) { }

									$this->model->edit_sms($sms);
								}
							}

							$widget = false;

							if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['survey']['widget']))
							{
								$average = $this->model->get_survey_average($query);

								if ($average >= 4)
									$widget = true;
							}

							Functions::environment([
								'status' => 'success',
								'message' => '{$lang.thanks_answering_survey}' . (!empty($_POST['email']) ? ' {$lang.thanks_received_myvox_1} <strong>' . $_POST['email'] . '</strong> {$lang.thanks_received_myvox_2}' : ''),
								'widget' => $widget
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
				$template = $this->view->render($this, 'survey');

				define('_title', Session::get_value('myvox')['account']['name'] . ' | {$lang.survey}');

				$this->model->get_survey_average(1);

				$html =
				'<form name="new_survey_answer">
					<div class="row">';

				if (Session::get_value('myvox')['url'] == 'account')
				{
					$html .=
					'<div class="span12">
						<div class="label">
							<label required>
								<p>{$lang.owner} <a data-action="get_help" data-text=""><i class="fas fa-question-circle"></i></a></p>
								<select name="owner">
									<option value="" hidden>{$lang.choose}</option>';

					foreach ($this->model->get_owners('survey') as $value)
						$html .= '<option value="' . $value['id'] . '" ' . ((!empty(Session::get_value('myvox')['owner']) AND Session::get_value('myvox')['owner']['id'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang1] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';

					$html .=
					'			</select>
							</label>
						</div>
					</div>';
				}

				$html .=
				'<div class="span12">
					<div class="tbl_stl_5" data-table>';

				foreach ($this->model->get_surveys_questions() as $value)
				{
					$html .=
					'<div>
						<div data-level="1">
							<h2>' . $value['name'][$this->lang1] . '</h2>
							<div class="' . $value['type'] . '">';

					if ($value['type'] == 'nps')
					{
						$html .=
						'<div>
							<label><i>1</i><input type="radio" name="' . $value['id'] . '" value="1"></label>
							<label><i>2</i><input type="radio" name="' . $value['id'] . '" value="2"></label>
							<label><i>3</i><input type="radio" name="' . $value['id'] . '" value="3"></label>
							<label><i>4</i><input type="radio" name="' . $value['id'] . '" value="4"></label>
							<label><i>5</i><input type="radio" name="' . $value['id'] . '" value="5"></label>
						</div>
					   	<div>
							<label><i>6</i><input type="radio" name="' . $value['id'] . '" value="6"></label>
							<label><i>7</i><input type="radio" name="' . $value['id'] . '" value="7"></label>
							<label><i>8</i><input type="radio" name="' . $value['id'] . '" value="8"></label>
							<label><i>9</i><input type="radio" name="' . $value['id'] . '" value="9"></label>
							<label><i>10</i><input type="radio" name="' . $value['id'] . '" value="10"></label>
						</div>';
					}
					else if ($value['type'] == 'open')
						$html .= '<input type="text" name="' . $value['id'] . '">';
					else if ($value['type'] == 'rate')
					{
						$html .=
						'<label><i class="fas fa-sad-cry"></i><input type="radio" name="' . $value['id'] . '" value="1"></label>
						<label><i class="fas fa-frown"></i><input type="radio" name="' . $value['id'] . '" value="2"></label>
						<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" name="' . $value['id'] . '" value="3"></label>
						<label><i class="fas fa-smile"></i><input type="radio" name="' . $value['id'] . '" value="4"></label>
						<label><i class="fas fa-grin-stars"></i><input type="radio" name="' . $value['id'] . '" value="5"></label>';
					}
					else if ($value['type'] == 'twin')
					{
						$html .=
						'<label><i class="fas fa-thumbs-up"></i><input type="radio" name="' . $value['id'] . '" value="yes"></label>
						<label><i class="fas fa-thumbs-down"></i><input type="radio" name="' . $value['id'] . '" value="not"></label>';
					}
					else if ($value['type'] == 'check')
					{
						$html .= '<div class="checkboxes stl_3">';

						foreach ($value['values'] as $subvalue)
						{
							$html .=
							'<div>
								<input type="checkbox" name="' . $value['id'] . '[]" value="' . $subvalue['token'] . '">
								<span>' . $subvalue[$this->lang1] . '</span>
							</div>';
						}

						$html .= '</div>';
					}

					$html .=
					'	</div>
					</div>';

					foreach ($this->model->get_surveys_questions($value['id']) as $subvalue)
					{
						$html .=
						'<div data-level="2">
							<h2>' . $subvalue['name'][$this->lang1] . '</h2>
							<div class="' . $subvalue['type'] . '">';

						if ($subvalue['type'] == 'open')
							$html .= '<input type="text" name="' . $subvalue['id'] . '" data-parent="' . $value['id'] . '">';
						else if ($subvalue['type'] == 'rate')
						{
							$html .=
							'<label><i class="fas fa-sad-cry"></i><input type="radio" name="' . $subvalue['id'] . '" value="1" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-frown"></i><input type="radio" name="' . $subvalue['id'] . '" value="2" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" name="' . $subvalue['id'] . '" value="3" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-smile"></i><input type="radio" name="' . $subvalue['id'] . '" value="4" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-grin-stars"></i><input type="radio" name="' . $subvalue['id'] . '" value="5" data-parent="' . $value['id'] . '"></label>';
						}
						else if ($subvalue['type'] == 'twin')
						{
							$html .=
							'<label><i class="fas fa-thumbs-up"></i><input type="radio" name="' . $subvalue['id'] . '" value="yes" data-parent="' . $value['id'] . '"></label>
							<label><i class="fas fa-thumbs-down"></i><input type="radio" name="' . $subvalue['id'] . '" value="not" data-parent="' . $value['id'] . '"></label>';
						}
						else if ($subvalue['type'] == 'check')
						{
							$html .= '<div class="checkboxes stl_3">';

							foreach ($subvalue['values'] as $parentvalue)
							{
								$html .=
								'<div>
									<input type="checkbox" name="' . $subvalue['id'] . '[]" value="' . $parentvalue['token'] . '" data-parent="' . $value['id'] . '">
									<span>' . $parentvalue[$this->lang1] . '</span>
								</div>';
							}

							$html .= '</div>';
						}

						$html .=
						'	</div>
						</div>';

						foreach ($this->model->get_surveys_questions($subvalue['id']) as $parentvalue)
						{
							$html .=
							'<div data-level="3">
								<h2>' . $parentvalue['name'][$this->lang1] . '</h2>
								<div class="' . $parentvalue['type'] . '">';

							if ($parentvalue['type'] == 'open')
								$html .= '<input type="text" name="' . $parentvalue['id'] . '" data-parent="' . $subvalue['id'] . '">';
							else if ($parentvalue['type'] == 'rate')
							{
								$html .=
								'<label><i class="fas fa-sad-cry"></i><input type="radio" name="' . $parentvalue['id'] . '" value="1" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-frown"></i><input type="radio" name="' . $parentvalue['id'] . '" value="2" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" name="' . $parentvalue['id'] . '" value="3" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-smile"></i><input type="radio" name="' . $parentvalue['id'] . '" value="4" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-grin-stars"></i><input type="radio" name="' . $parentvalue['id'] . '" value="5" data-parent="' . $subvalue['id'] . '"></label>';
							}
							else if ($parentvalue['type'] == 'twin')
							{
								$html .=
								'<label><i class="fas fa-thumbs-up"></i><input type="radio" name="' . $parentvalue['id'] . '" value="yes" data-parent="' . $subvalue['id'] . '"></label>
								<label><i class="fas fa-thumbs-down"></i><input type="radio" name="' . $parentvalue['id'] . '" value="not" data-parent="' . $subvalue['id'] . '"></label>';
							}
							else if ($parentvalue['type'] == 'check')
							{
								$html .= '<div class="checkboxes stl_3">';

								foreach ($parentvalue['values'] as $childvalue)
								{
									$html .=
									'<div>
										<input type="checkbox" name="' . $parentvalue['id'] . '[]" value="' . $childvalue['token'] . '" data-parent="' . $subvalue['id'] . '">
										<span>' . $childvalue[$this->lang1] . '</span>
									</div>';
								}

								$html .= '</div>';
							}

							$html .=
							'	</div>
							</div>';
						}
					}

					$html .= '</div>';
				}

				$html .=
				'	</div>
				</div>
				<div class="span12">
					<div class="label">
						<label unrequired>
							<p>{$lang.commentary}</p>
							<textarea name="comment"></textarea>
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.firstname}</p>
							<input type="text" name="firstname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.lastname}</p>
							<input type="text" name="lastname">
						</label>
					</div>
				</div>
				<div class="span6">
					<div class="label">
						<label unrequired>
							<p>{$lang.email}</p>
							<input type="email" name="email">
						</label>
					</div>
				</div>
				<div class="span3">
					<div class="label">
						<label unrequired>
							<p>{$lang.lada}</p>
							<select name="phone_lada">
								<option value="">{$lang.empty} ({$lang.choose})</option>';

				foreach ($this->model->get_countries() as $value)
					$html .= '<option value="' . $value['lada'] . '">' . $value['name'][$this->lang1] . ' (+' . $value['lada'] . ')</option>';

				$html .=
				'					</select>
								</label>
							</div>
						</div>
						<div class="span3">
							<div class="label">
								<label unrequired>
									<p>{$lang.phone}</p>
									<input type="number" name="phone_number">
								</label>
							</div>
						</div>
						<div class="span12">
							<div class="buttons">
								<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '" class="delete"><i class="fas fa-times"></i></a>
								<button type="submit" class="new"><i class="fas fa-check"></i></button>
							</div>
						</div>
					</div>
				</form>';

				$mdl_widget = '';

				if (!empty(Session::get_value('myvox')['account']['settings']['myvox']['survey']['widget']))
				{
					$mdl_widget .=
					'<section class="modal" data-modal="widget">
						<div class="content">
							<main>
								<div>' . Session::get_value('myvox')['account']['settings']['myvox']['survey']['widget'] . '</div>
								<div class="buttons">
									<a class="new" button-close><i class="fas fa-check"></i></a>
								</div>
							</main>
						</div>
					</section>';
				}

				$replace = [
					'{$logotype}' => '{$path.uploads}' . Session::get_value('myvox')['account']['logotype'],
					'{$btn_home}' => '<a href="/' . $params[0] . '/myvox' . ((Session::get_value('myvox')['url'] == 'owner') ? '/owner/' . Session::get_value('myvox')['owner']['token'] : '') . '"><i class="fas fa-house-user"></i></a>',
					'{$html}' => $html,
					'{$mdl_widget}' => $mdl_widget
				];

				$template = $this->format->replace($replace, $template);

				echo $template;
			}
		}
		else
			header('Location: /');
    }
}
