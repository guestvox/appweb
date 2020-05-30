<?php

defined('_EXEC') or die;

// require_once 'plugins/nexmo/vendor/autoload.php';

class Voxes_controller extends Controller
{
	private $lang;

	public function __construct()
	{
		parent::__construct();

		$this->lang = Session::get_value('account')['language'];
	}

	public function index()
	{
		$template = $this->view->render($this, 'index');

		define('_title', 'Guestvox | {$lang.voxes}');

		$tbl_voxes = '';

		foreach ($this->model->get_voxes('open') as $value)
		{
			$tbl_voxes .=
			'<div>
                <div>
					<div class="itm_1">
                        <figure>
                            <img src="' . (($value['origin'] == 'myvox') ? '{$path.images}myvox.png' : (!empty($query['created_user']['avatar']) ? '{$path.uploads}' . $query['created_user']['avatar'] : '{$path.images}avatar.png')) . '">
                        </figure>
                    </div>
                    <div class="itm_2">
						<div>
							<span>';

							if ($value['type'] == 'request')
								$tbl_voxes .= '<i class="fas fa-spa"></i>';
							else if ($value['type'] == 'incident')
								$tbl_voxes .= '<i class="fas fa-exclamation-triangle"></i>';
							else if ($value['type'] == 'workorder')
								$tbl_voxes .= '<i class="fas fa-id-card-alt"></i>';

			$tbl_voxes .=
			'				</span>
						</div>
						<div>
							<h2>' . ((!empty($value['firstname']) AND !empty($value['lastname'])) ? $value['firstname'] . ' ' . $value['lastname'] :  '{$lang.not_name}') . '</h2>
							<span>' . $value['owner']['name'][$this->lang] . (!empty($value['owner']['number']) ? ' #' . $value['owner']['number'] : '') . '</span>
							<span
								data-date-1="' . Functions::get_formatted_date_hour($value['started_date'], $value['started_hour']) . '"
								data-date-2="' . ((!empty($value['completed_date']) AND !empty($value['completed_hour'])) ? Functions::get_formatted_date_hour($value['completed_date'], $value['completed_hour']) : '') . '"
								data-time-zone="' . Session::get_value('account')['time_zone'] . '"
								data-status="' . $value['status'] . '"
								data-elapsed-time></span>
						</div>
                    </div>
                    <div class="itm_3 ' . $value['urgency'] . '">
						<span>' . $value['token'] . '</span>
						<span>' . $value['location']['name'][$this->lang] . '</span>
                        <span>' . $value['opportunity_area']['name'][$this->lang] . '</span>
                        <span>' . $value['opportunity_type']['name'][$this->lang] . '</span>
                    </div>
                    <div class="itm_4">
						<span class="' . (!empty($value['assigned_users']) ? 'active' : '') . '"><i class="fas fa-users"></i></span>
						<span class="' . (!empty($value['comments']) ? 'active' : '') . '"><i class="fas fa-comment"></i></span>
						<span class="' . (!empty($value['attachments']) ? 'active' : '') . '"><i class="fas fa-paperclip"></i></span>
						<span class="' . (($value['type'] == 'incident' AND $value['confidentiality'] == true) ? 'active' : '') . '"><i class="fas fa-key"></i></span>
					</div>
					<a href="/voxes/details/' . $value['id'] . '"></a>
                </div>
            </div>';
		}

		$replace = [
			'{$tbl_voxes}' => $tbl_voxes
		];

		$template = $this->format->replace($replace, $template);

		echo $template;
	}

	// public function create()
	// {
	// 	if (Format::exist_ajax_request() == true)
	// 	{
	// 		if ($_POST['action'] == 'get_opt_owners')
	// 		{
	// 			$html = '<option value="" selected hidden>{$lang.choose}</option>';
	//
	// 			foreach ($this->model->get_owners($_POST['type']) as $value)
	// 				$html .= '<option value="' . $value['id'] . '">' . $value['name'] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_reservation')
	// 		{
	// 			$query = $this->model->get_owner($_POST['owner']);
	//
	// 			if (!empty($query))
	// 			{
	// 				$query = $this->model->get_reservation($query['number']);
	//
	// 				if (!empty($query))
	// 				{
	// 					Functions::environment([
	// 						'status' => 'success',
	// 						'data' => $query
	// 					]);
	// 				}
	// 				else
	// 				{
	// 					Functions::environment([
	// 						'status' => 'error',
	// 						'message' => '{$lang.operation_error}'
	// 					]);
	// 				}
	// 			}
	// 			else
	// 			{
	// 				Functions::environment([
	// 					'status' => 'error',
	// 					'message' => '{$lang.operation_error}'
	// 				]);
	// 			}
	// 		}
	//
	// 		if ($_POST['action'] == 'get_opt_opportunity_areas')
	// 		{
	// 			$html = '<option value="" selected hidden>{$lang.choose}</option>';
	//
	// 			foreach ($this->model->get_opportunity_areas($_POST['type']) as $value)
	// 				$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_opt_opportunity_types')
	// 		{
	// 			$html = '<option value="" selected hidden>{$lang.choose}</option>';
	//
	// 			foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], $_POST['type']) as $value)
	// 				$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_opt_locations')
	// 		{
	// 			$html = '<option value="" selected hidden>{$lang.choose}</option>';
	//
	// 			foreach ($this->model->get_locations($_POST['type']) as $value)
	// 				$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'new_vox')
	// 		{
	// 			$labels = [];
	//
	// 			if (!isset($_POST['type']) OR empty($_POST['type']))
	// 				array_push($labels, ['type','']);
	//
	// 			if (!isset($_POST['owner']) OR empty($_POST['owner']))
	// 				array_push($labels, ['owner','']);
	//
	// 			if (!isset($_POST['opportunity_area']) OR empty($_POST['opportunity_area']))
	// 				array_push($labels, ['opportunity_area','']);
	//
	// 			if (!isset($_POST['opportunity_type']) OR empty($_POST['opportunity_type']))
	// 				array_push($labels, ['opportunity_type','']);
	//
	// 			if (!isset($_POST['started_date']) OR empty($_POST['started_date']))
	// 				array_push($labels, ['started_date','']);
	//
	// 			if (!isset($_POST['started_hour']) OR empty($_POST['started_hour']))
	// 				array_push($labels, ['started_hour','']);
	//
	// 			if (!isset($_POST['location']) OR empty($_POST['location']))
	// 				array_push($labels, ['location','']);
	//
	// 			if (!isset($_POST['urgency']) OR empty($_POST['urgency']))
	// 				array_push($labels, ['urgency','']);
	//
	// 			if (empty($labels))
	// 			{
	// 				$_POST['token'] = Functions::get_random(8);
	// 				$_POST['attachments'] = $_FILES['attachments'];
	//
	// 				$query = $this->model->new_vox($_POST);
	//
	// 				if (!empty($query))
	// 				{
	// 					$_POST['owner'] = $this->model->get_owner($_POST['owner']);
	// 					$_POST['opportunity_area'] = $this->model->get_opportunity_area($_POST['opportunity_area']);
	// 					$_POST['opportunity_type'] = $this->model->get_opportunity_type($_POST['opportunity_type']);
	// 					$_POST['location'] = $this->model->get_location($_POST['location']);
	// 					$_POST['assigned_users'] = $this->model->get_assigned_users($_POST['assigned_users'], $_POST['opportunity_area']['id']);
	//
	// 					$mail = new Mailer(true);
	//
	// 					try
	// 					{
	// 						$mail->isSMTP();
	// 						$mail->setFrom('noreply@guestvox.com', 'Guestvox');
	//
	// 						foreach ($_POST['assigned_users'] as $value)
	// 							$mail->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);
	//
	// 						$mail->isHTML(true);
	// 						$mail->Subject = Lang::general('new', $_POST['type'])[$this->lang];
	// 						$mail->Body =
	// 						'<html>
	// 						    <head>
	// 						        <title>' . Lang::general('new', $_POST['type'])[$this->lang] . '</title>
	// 						    </head>
	// 						    <body>
	// 						        <table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
	// 									<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
	// 										<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 											<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
	// 												<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png" />
	// 											</figure>
	// 										</td>
	// 									</tr>
	// 									<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
	// 										<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 											<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:24px;font-weight:600;text-align:center;color:#212121;">' . Lang::general('new', $_POST['type'])[$this->lang] . '</h4>
	// 											<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('token')[$this->lang] . ': ' . $_POST['token'] . '</h6>
	// 											<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('owner')[$this->lang] . ': ' . $_POST['owner']['name'] . (!empty($_POST['owner']['number']) ? ' #' . $_POST['owner']['number'] : '') . '</h6>
	// 											<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('opportunity_area')[$this->lang] . ': ' . $_POST['opportunity_area']['name'][$this->lang] . '</h6>
	// 					    					<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('opportunity_type')[$this->lang] . ': ' . $_POST['opportunity_type']['name'][$this->lang] . '</h6>
	// 											<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('started_date')[$this->lang] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M, Y') . '</h6>
	// 											<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('started_hour')[$this->lang] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '</h6>
	// 											<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('location')[$this->lang] . ': ' . $_POST['location']['name'][$this->lang] . '</h6>
	// 											<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('urgency')[$this->lang] . ': ' . Lang::general($_POST['urgency'])[$this->lang] . '</h6>';
	//
	// 						if ($_POST['type'] == 'request' OR $_POST['type'] == 'workorder')
	// 							$mail->Body .= '<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:justify;color:#757575;">' . Lang::general('Observations')[$this->lang] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Lang::general('empty')[$this->lang]) . '</p>';
	// 						else if ($vox['type'] == 'incident')
	// 						{
	// 							$mail->Body .=
	// 							'<h6 style="width:100%;margin:0px 0px 5px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('confidentiality')[$this->lang] . ': ' . Lang::general((!empty($_POST['confidentiality']) ? 'yes' : 'not'))[$this->lang] . '</h6>
	// 							<p style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:justify;color:#757575;">' . Lang::general('subject')[$this->lang] . ': ' . (!empty($_POST['subject']) ? $_POST['subject'] : Lang::general('empty')[$this->lang]) . '</p>';
	// 						}
	//
	// 						$mail->Body .=
	// 						'                   <a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:50px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $query . '">' . Lang::general('give_follow_up')[$this->lang] . '</a>
	// 						                </td>
	// 						            </tr>
	// 									<tr style="width:100%;margin:0px;padding:0px;border:0px;">
	// 										<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 											<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
	// 										</td>
	// 									</tr>
	// 						        </table>
	// 						    </body>
	// 						</html>';
	// 						$mail->AltBody = '';
	// 						$mail->send();
	// 					}
	// 					catch (Exception $e) { }
	//
	// 					$sms = $this->model->get_sms();
	//
	// 					if ($sms > 0)
	// 					{
	// 						$sms_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
	// 						$sms_client = new \Nexmo\Client($sms_basic);
	// 						$sms_text = Lang::general('new', $_POST['type'])[$this->lang] . '. ';
	// 						$sms_text .= Lang::general('token')[$this->lang] . ': ' . $_POST['token'] . '. ';
	// 						$sms_text .= Lang::general('owner')[$this->lang] . ': ' . $_POST['owner']['name'] . (!empty($_POST['owner']['number']) ? ' #' . $_POST['owner']['number'] : '') . '. ';
	// 						$sms_text .= Lang::general('opportunity_area')[$this->lang] . ': ' . $_POST['opportunity_area']['name'][$this->lang] . '. ';
	// 						$sms_text .= Lang::general('opportunity_type')[$this->lang] . ': ' . $_POST['opportunity_type']['name'][$this->lang] . '. ';
	// 						$sms_text .= Lang::general('started_date')[$this->lang] . ': ' . Functions::get_formatted_date($_POST['started_date'], 'd M y') . '. ';
	// 						$sms_text .= Lang::general('started_hour')[$this->lang] . ': ' . Functions::get_formatted_hour($_POST['started_hour'], '+ hrs') . '. ';
	// 						$sms_text .= Lang::general('location')[$this->lang] . ': ' . $_POST['location']['name'][$this->lang] . '. ';
	// 						$sms_text .= Lang::general('urgency')[$this->lang] . ': ' . Lang::general($_POST['urgency'])[$this->lang] . '. ';
	//
	// 						if ($_POST['type'] == 'request' OR $_POST['type'] == 'workorder')
	// 							$sms_text .= Lang::general('observations')[$this->lang] . ': ' . (!empty($_POST['observations']) ? $_POST['observations'] : Lang::general('empty')[$this->lang]) . '. ';
	// 						else if ($_POST['type'] == 'incident')
	// 						{
	// 							$sms_text .= Lang::general('confidentiality')[$this->lang] . ': ' . Lang::general((!empty($_POST['confidentiality']) ? 'yes' : 'not'))[$this->lang] . '. ';
	// 							$sms_text .= Lang::general('subject')[$this->lang] . ': ' . (!empty($_POST['subject']) ? $_POST['subject'] : Lang::general('empty')[$this->lang]) . '. ';
	// 						}
	//
	// 						$sms_text .= 'https://' . Configuration::$domain . '/voxes/details/' . $query;
	//
	// 						foreach ($_POST['assigned_users'] as $value)
	// 						{
	// 							if ($sms > 0)
	// 							{
	// 								try
	// 								{
	// 									$sms_client->message()->send([
	// 										'to' => $value['phone']['lada'] . $value['phone']['number'],
	// 										'from' => 'Guestvox',
	// 										'text' => $sms_text
	// 									]);
	//
	// 									$sms = $sms - 1;
	// 								}
	// 								catch (Exception $e) { }
	// 							}
	// 						}
	//
	// 						$this->model->edit_sms($sms);
	// 					}
	//
	// 					Functions::environment([
	// 						'status' => 'success',
	// 						'message' => '{$lang.operation_success}'
	// 					]);
	// 				}
	// 				else
	// 				{
	// 					Functions::environment([
	// 						'status' => 'error',
	// 						'message' => '{$lang.operation_error}'
	// 					]);
	// 				}
	// 			}
	// 			else
	// 			{
	// 				Functions::environment([
	// 					'status' => 'error',
	// 					'labels' => $labels
	// 				]);
	// 			}
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$template = $this->view->render($this, 'create');
	//
	// 		define('_title', 'Guestvox | {$lang.create_vox}');
	//
	// 		$opt_owners = '';
	//
	// 		foreach ($this->model->get_owners('request') as $value)
	// 			$opt_owners .= '<option value="' . $value['id'] . '">' . $value['name'] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';
	//
	// 		$opt_opportunity_areas = '';
	//
	// 		foreach ($this->model->get_opportunity_areas('request') as $value)
	// 			$opt_opportunity_areas .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 		$opt_locations = '';
	//
	// 		foreach ($this->model->get_locations('request') as $value)
	// 			$opt_locations .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 		$opt_guests_treatments = '';
	// 		$opt_guests_types = '';
	// 		$opt_reservations_statuses = '';
	//
	// 		if (Session::get_value('account')['type'] == 'hotel')
	// 		{
	// 			foreach ($this->model->get_guests_treatments() as $value)
	// 				$opt_guests_treatments .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
	//
	// 			foreach ($this->model->get_guests_types() as $value)
	// 				$opt_guests_types .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
	//
	// 			foreach ($this->model->get_reservations_statuses() as $value)
	// 				$opt_reservations_statuses .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
	// 		}
	//
	// 		$opt_users = '';
	//
	// 		foreach ($this->model->get_users() as $value)
	// 			$opt_users .= '<option value="' . $value['id'] . '">' . $value['firstname'] . ' ' . $value['lastname'] . '</option>';
	//
	// 		$replace = [
	// 			'{$opt_owners}' => $opt_owners,
	// 			'{$opt_opportunity_areas}' => $opt_opportunity_areas,
	// 			'{$opt_locations}' => $opt_locations,
	// 			'{$opt_guests_treatments}' => $opt_guests_treatments,
	// 			'{$opt_guests_types}' => $opt_guests_types,
	// 			'{$opt_reservations_statuses}' => $opt_reservations_statuses,
	// 			'{$opt_users}' => $opt_users
	// 		];
	//
	// 		$template = $this->format->replace($replace, $template);
	//
	// 		echo $template;
	// 	}
	// }
	//
	// public function edit($params)
	// {
	// 	$vox = $this->model->get_vox($params[0]);
	//
	// 	if (!empty($vox))
	// 	{
	// 		if (Format::exist_ajax_request() == true)
	// 		{
	// 			if ($_POST['action'] == 'get_opt_owners')
	// 			{
	// 				$html = '<option value="" selected hidden>{$lang.choose}</option>';
	//
	// 				foreach ($this->model->get_owners($_POST['type']) as $value)
	// 					$html .= '<option value="' . $value['id'] . '">' . $value['name'] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';
	//
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'html' => $html
	// 				]);
	// 			}
	//
	// 			if ($_POST['action'] == 'get_reservation')
	// 			{
	// 				$query = $this->model->get_owner($_POST['owner']);
	//
	// 				if (!empty($query))
	// 				{
	// 					$query = $this->model->get_reservation($query['number']);
	//
	// 					if (!empty($query))
	// 					{
	// 						Functions::environment([
	// 							'status' => 'success',
	// 							'data' => $query
	// 						]);
	// 					}
	// 					else
	// 					{
	// 						Functions::environment([
	// 							'status' => 'error',
	// 							'message' => '{$lang.operation_error}'
	// 						]);
	// 					}
	// 				}
	// 				else
	// 				{
	// 					Functions::environment([
	// 						'status' => 'error',
	// 						'message' => '{$lang.operation_error}'
	// 					]);
	// 				}
	// 			}
	//
	// 			if ($_POST['action'] == 'get_opt_opportunity_areas')
	// 			{
	// 				$html = '<option value="" selected hidden>{$lang.choose}</option>';
	//
	// 				foreach ($this->model->get_opportunity_areas($_POST['type']) as $value)
	// 					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'html' => $html
	// 				]);
	// 			}
	//
	// 			if ($_POST['action'] == 'get_opt_opportunity_types')
	// 			{
	// 				$html = '<option value="" selected hidden>{$lang.choose}</option>';
	//
	// 				foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], $_POST['type']) as $value)
	// 					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'html' => $html
	// 				]);
	// 			}
	//
	// 			if ($_POST['action'] == 'get_opt_locations')
	// 			{
	// 				$html = '<option value="" selected hidden>{$lang.choose}</option>';
	//
	// 				foreach ($this->model->get_locations($_POST['type']) as $value)
	// 					$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'html' => $html
	// 				]);
	// 			}
	//
	// 			if ($_POST['action'] == 'edit_vox')
	// 			{
	// 				$labels = [];
	//
	// 				if (!isset($_POST['type']) OR empty($_POST['type']))
	// 					array_push($labels, ['type','']);
	//
	// 				if (!isset($_POST['owner']) OR empty($_POST['owner']))
	// 					array_push($labels, ['owner','']);
	//
	// 				if (!isset($_POST['opportunity_area']) OR empty($_POST['opportunity_area']))
	// 					array_push($labels, ['opportunity_area','']);
	//
	// 				if (!isset($_POST['opportunity_type']) OR empty($_POST['opportunity_type']))
	// 					array_push($labels, ['opportunity_type','']);
	//
	// 				if (!isset($_POST['started_date']) OR empty($_POST['started_date']))
	// 					array_push($labels, ['started_date','']);
	//
	// 				if (!isset($_POST['started_hour']) OR empty($_POST['started_hour']))
	// 					array_push($labels, ['started_hour','']);
	//
	// 				if (!isset($_POST['location']) OR empty($_POST['location']))
	// 					array_push($labels, ['location','']);
	//
	// 				if (!isset($_POST['urgency']) OR empty($_POST['urgency']))
	// 					array_push($labels, ['urgency','']);
	//
	// 				if (empty($labels))
	// 				{
	// 					$_POST['id'] = $vox['id'];
	// 					$_POST['attachments'] = $_FILES['attachments'];
	//
	// 					$query = $this->model->edit_vox($_POST);
	//
	// 					if (!empty($query))
	// 					{
	// 						$_POST['assigned_users'] = $this->model->get_assigned_users($_POST['assigned_users'], $_POST['opportunity_area']);
	//
	// 						$mail = new Mailer(true);
	//
	// 						try
	// 						{
	// 							$mail->isSMTP();
	// 							$mail->setFrom('noreply@guestvox.com', 'Guestvox');
	//
	// 							foreach ($_POST['assigned_users'] as $value)
	// 								$mail->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);
	//
	// 							$mail->isHTML(true);
	// 							$mail->Subject = Lang::general('edited_vox')[$this->lang];
	// 							$mail->Body =
	// 							'<html>
	// 							    <head>
	// 							        <title>' . Lang::general('edited_vox')[$this->lang] . '</title>
	// 							    </head>
	// 							    <body>
	// 							        <table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
	// 										<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
	// 											<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 												<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
	// 													<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png" />
	// 												</figure>
	// 											</td>
	// 										</tr>
	// 										<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
	// 											<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 												<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:24px;font-weight:600;text-align:center;color:#212121;">' . Lang::general('edited_vox')[$this->lang] . '</h4>
	// 												<h6 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('token')[$this->lang] . ': ' . $vox['token'] . '</h6>
	// 												<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:50px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $vox['id'] . '">' . Lang::general('view_details')[$this->lang] . '</a>
	// 							                </td>
	// 							            </tr>
	// 										<tr style="width:100%;margin:0px;padding:0px;border:0px;">
	// 											<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 												<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
	// 											</td>
	// 										</tr>
	// 							        </table>
	// 							    </body>
	// 							</html>';
	// 							$mail->AltBody = '';
	// 							$mail->send();
	// 						}
	// 						catch (Exception $e) { }
	//
	// 						$sms = $this->model->get_sms();
	//
	// 						if ($sms > 0)
	// 						{
	// 							$sms_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
	// 							$sms_client = new \Nexmo\Client($sms_basic);
	// 							$sms_text = Lang::general('edited_vox')[$this->lang] . '. ' . Lang::general('token')[$this->lang] . ': ' . $vox['token'] . '. ' . 'https://' . Configuration::$domain . '/voxes/details/' . $vox['id'];
	//
	// 							foreach ($_POST['assigned_users'] as $value)
	// 							{
	// 								if ($sms > 0)
	// 								{
	// 									try
	// 									{
	// 										$sms_client->message()->send([
	// 											'to' => $value['phone']['lada'] . $value['phone']['number'],
	// 											'from' => 'Guestvox',
	// 											'text' => $sms_text
	// 										]);
	//
	// 										$sms = $sms - 1;
	// 									}
	// 									catch (Exception $e) { }
	// 								}
	// 							}
	//
	// 							$this->model->edit_sms($sms);
	// 						}
	//
	// 						Functions::environment([
	// 							'status' => 'success',
	// 							'path' => '/voxes/details/' . $vox['id'],
	// 							'message' => '{$lang.operation_success}'
	// 						]);
	// 					}
	// 					else
	// 					{
	// 						Functions::environment([
	// 							'status' => 'error',
	// 							'message' => '{$lang.operation_error}'
	// 						]);
	// 					}
	// 				}
	// 				else
	// 				{
	// 					Functions::environment([
	// 						'status' => 'error',
	// 						'labels' => $labels
	// 					]);
	// 				}
	// 			}
	// 		}
	// 		else
	// 		{
	// 			$template = $this->view->render($this, 'edit');
	//
	// 			define('_title', 'Guestvox | {$lang.edit_vox}');
	//
	// 			$frm_edit_vox =
	// 			'<div class="span12">
	// 				<div class="tabs">
	// 					<label>
	// 						<span>{$lang.request}</span>
	// 						<span><i class="fas fa-spa"></i></span>
	// 						<input type="radio" name="type" value="request" ' . (($vox['type'] == 'request') ? 'checked' : '') . '>
	// 					</label>
	// 					<label>
	// 						<span>{$lang.incident}</span>
	// 						<span><i class="fas fa-exclamation-triangle"></i></span>
	// 						<input type="radio" name="type" value="incident" ' . (($vox['type'] == 'incident') ? 'checked' : '') . '>
	// 					</label>
	// 					<label>
	// 						<span>{$lang.workorder}</span>
	// 						<span><i class="fas fa-id-card-alt"></i></span>
	// 						<input type="radio" name="type" value="workorder" ' . (($vox['type'] == 'workorder') ? 'checked' : '') . '>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3">
	// 				<div class="label">
	// 					<label class="success" required>
	// 						<p>{$lang.owner}</p>
	// 						<select name="owner">';
	//
	// 			foreach ($this->model->get_owners($vox['type']) as $value)
	// 				$frm_edit_vox .= '<option value="' . $value['id'] . '" ' . (($vox['owner'] == $value['id']) ? 'selected' : '') . '>' . $value['name'] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';
	//
	// 			$frm_edit_vox .=
	// 			'			</select>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3">
	// 				<div class="label">
	// 					<label class="success" required>
	// 						<p>{$lang.opportunity_area}</p>
	// 						<select name="opportunity_area">';
	//
	// 			foreach ($this->model->get_opportunity_areas($vox['type']) as $value)
	// 				$frm_edit_vox .= '<option value="' . $value['id'] . '"'. (($vox['opportunity_area'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang] . '</option>';
	//
	// 			$frm_edit_vox .=
	// 			'			</select>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3">
	// 				<div class="label">
	// 					<label class="success" required>
	// 						<p>{$lang.opportunity_type}</p>
	// 						<select name="opportunity_type">';
	//
	// 			foreach ($this->model->get_opportunity_types($vox['opportunity_area'], $vox['type']) as $value)
	// 				$frm_edit_vox .= '<option value="' . $value['id'] . '"'. (($vox['opportunity_type'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang] . '</option>';
	//
	// 			$frm_edit_vox .=
	// 			'			</select>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3">
	// 				<div class="label">
	// 					<label class="success" required>
	// 						<p>{$lang.date}</p>
	// 						<input type="date" name="started_date" value="' . $vox['started_date'] . '" />' . '
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3">
	// 				<div class="label">
	// 					<label class="success" required>
	// 						<p>{$lang.hour}</p>
	// 						<input type="time" name="started_hour" value="' . $vox['started_hour'] . '" />
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3">
	// 				<div class="label">
	// 					<label class="success" required>
	// 						<p>{$lang.location}</p>
	// 						<select name="location">';
	//
	// 			foreach ($this->model->get_locations($vox['type']) as $value)
	// 				$frm_edit_vox .= '<option value="' . $value['id'] . '"'. (($vox['location'] == $value['id']) ? 'selected' : '') . '>' . $value['name'][$this->lang] . '</option>';
	//
	// 			$frm_edit_vox .=
	// 			'			</select>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3">
	// 				<div class="label">
	// 					<label class="success" required>
	// 						<p>{$lang.urgency}</p>
	// 						<select name="urgency">
	// 							<option value="low" ' . (($vox['urgency'] == 'low') ? 'selected' : '') . '>{$lang.low}</option>
	// 							<option value="medium" ' . (($vox['urgency'] == 'medium') ? 'selected' : '') . '>{$lang.medium}</option>
	// 							<option value="high" ' . (($vox['urgency'] == 'high') ? 'selected' : '') . '>{$lang.high}</option>
	// 						</select>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3 ' . (($vox['type'] == 'request' OR $vox['type'] == 'workorder') ? '' : 'hidden') . '">
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['observations']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.observations}</p>
	// 						<input type="text" name="observations" value="' . $vox['observations'] . '" maxlength="120" />
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 				<div class="label">
	// 					<label class="' . (($vox['confidentiality'] == true) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.confidentiality}</p>
	// 						<div class="switch">
	// 							<input id="confidentiality" type="checkbox" name="confidentiality" class="switch-input" ' . (($vox['confidentiality'] == true) ? 'checked' : '') . '>
	// 							<label class="switch-label" for="confidentiality"></label>
	// 						</div>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3 ' . (($vox['type'] == 'incident' OR $vox['type'] == 'workorder') ? '' : 'hidden') . '">
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['cost']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.cost}</p>
	// 						<input type="number" name="cost" value="' . $vox['cost'] . '" />
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['subject']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.subject}</p>
	// 						<input type="text" name="subject" value="' . $vox['subject'] . '" maxlength="120" />
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['description']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.description}</p>
	// 						<textarea name="description">' . $vox['description'] . '</textarea>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['action_taken']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.action_taken}</p>
	// 						<textarea name="action_taken">' . $vox['action_taken'] . '</textarea>
	// 					</label>
	// 				</div>
	// 			</div>';
	//
	// 			if (Session::get_value('account')['type'] == 'hotel')
	// 			{
	// 				$frm_edit_vox .=
	// 				'<div class="span3 ' . (($vox['type'] == 'request' OR $vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 					<div class="label">
	// 						<label class="' . (!empty($vox['guest_treatment']) ? 'success' : '') . '" unrequired>
	// 							<p>{$lang.guest_treatment}</p>
	// 							<select name="guest_treatment">
	// 								<option value="" ' . (empty($vox['guest_treatment']) ? 'selected' : '') . '>{$lang.empty}</option>';
	//
	// 				foreach ($this->model->get_guests_treatments() as $value)
	// 					$frm_edit_vox .= '<option value="' . $value['id'] . '"'. (($vox['guest_treatment'] == $value['id']) ? 'selected' : '') . '>' . $value['name'] . '</option>';
	//
	// 				$frm_edit_vox .=
	// 				'			</select>
	// 						</label>
	// 					</div>
	// 				</div>';
	// 			}
	//
	// 			$frm_edit_vox .=
	// 			'<div class="span3 ' . (($vox['type'] == 'request' OR $vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['firstname']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.firstname}</p>
	// 						<input type="text" name="firstname" value="' . $vox['firstname'] . '" />
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3 ' . (($vox['type'] == 'request' OR $vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['lastname']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.lastname}</p>
	// 						<input type="text" name="lastname" value="' . $vox['lastname'] . '" />
	// 					</label>
	// 				</div>
	// 			</div>';
	//
	// 			if (Session::get_value('account')['type'] == 'hotel')
	// 			{
	// 				$frm_edit_vox .=
	// 				'<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 					<div class="label">
	// 						<label class="' . (!empty($vox['guest_type']) ? 'success' : '') . '" unrequired>
	// 							<p>{$lang.guest_type}</p>
	// 							<select name="guest_type">
	// 								<option value="" ' . (empty($vox['guest_type']) ? 'selected' : '') . '>{$lang.empty}</option>';
	//
	// 				foreach ($this->model->get_guests_types() as $value)
	// 					$frm_edit_vox .= '<option value="' . $value['id'] . '"'. (($vox['guest_type'] == $value['id']) ? 'selected' : '') . '>' . $value['name'] . '</option>';
	//
	// 				$frm_edit_vox .=
	// 				'			</select>
	// 						</label>
	// 					</div>
	// 				</div>
	// 				<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 					<div class="label">
	// 						<label class="' . (!empty($vox['guest_id']) ? 'success' : '') . '" unrequired>
	// 							<p>{$lang.guest_id}</p>
	// 							<input type="text" name="guest_id" value="' . $vox['guest_id'] . '" />
	// 						</label>
	// 					</div>
	// 				</div>
	// 				<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 					<div class="label">
	// 						<label class="' . (!empty($vox['reservation_number']) ? 'success' : '') . '" unrequired>
	// 							<p>{$lang.reservation_number}</p>
	// 							<input type="text" name="reservation_number" value="' . $vox['reservation_number'] . '" />
	// 						</label>
	// 					</div>
	// 				</div>
	// 				<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 					<div class="label">
	// 						<label class="' . (!empty($vox['reservation_status']) ? 'success' : '') . '" unrequired>
	// 							<p>{$lang.reservations_statuses}</p>
	// 							<select name="reservation_status">
	// 								<option value="" ' . (empty($vox['reservation_status']) ? 'selected' : '') . '>{$lang.empty}</option>';
	//
	// 				foreach ($this->model->get_reservations_statuses() as $value)
	// 					$frm_edit_vox .= '<option value="' . $value['id'] . '"'. (($vox['reservation_status'] == $value['id']) ? 'selected' : '') . '>' . $value['name'] . '</option>';
	//
	// 				$frm_edit_vox .=
	// 				'			</select>
	// 						</label>
	// 					</div>
	// 				</div>
	// 				<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 					<div class="label">
	// 						<label class="' . (!empty($vox['check_in']) ? 'success' : '') . '" unrequired>
	// 							<p>{$lang.check_in}</p>
	// 							<input type="date" name="check_in" value="' . Functions::get_formatted_date($vox['check_in']) . '" />
	// 						</label>
	// 					</div>
	// 				</div>
	// 				<div class="span3 ' . (($vox['type'] == 'incident') ? '' : 'hidden') . '">
	// 					<div class="label">
	// 						<label class="' . (!empty($vox['check_out']) ? 'success' : '') . '" unrequired>
	// 							<p>{$lang.check_out}</p>
	// 							<input type="date" name="check_out" value="' . Functions::get_formatted_date($vox['check_out']) . '" />
	// 						</label>
	// 					</div>
	// 				</div>';
	// 			}
	//
	// 			$frm_edit_vox .=
	// 			'<div class="span3">
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['assigned_users']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.assigned_users}</p>
	// 						<select name="assigned_users[]" class="chosen-select" multiple>';
	//
	// 			foreach ($this->model->get_users() as $value)
	// 			{
	// 				foreach ($vox['assigned_users'] as $subvalue)
	// 					$frm_edit_vox .= '<option value="' . $value['id'] . '" ' . (($subvalue['id'] == $value['id']) ? 'selected' : '') . '>' . $value['firstname'] . ' ' . $value['lastname'] . '</option>';
	// 			}
	//
	// 			$frm_edit_vox .=
	// 			'			</select>
	// 					</label>
	// 				</div>
	// 			</div>
	// 			<div class="span3">
	// 				<div class="attachments">';
	//
	// 			if (!empty($vox['attachments']))
	// 			{
	// 				foreach ($vox['attachments'] as $value)
	// 				{
	// 					if ($value['status'] == 'success')
	// 					{
	// 						$ext = strtoupper(explode('.', $value['file'])[1]);
	//
	// 						if ($ext == 'JPG' OR $ext == 'JPEG' OR $ext == 'PNG')
	// 							$frm_edit_vox .= '<figure><img src="{$path.uploads}' . $value['file'] . '"><a href="{$path.uploads}' . $value['file'] . '" class="fancybox-thumb" rel="fancybox-thumb"></a></figure>';
	// 						else if ($ext == 'PDF' OR $ext == 'DOC' OR $ext == 'DOCX' OR $ext == 'XLS' OR $ext == 'XLSX')
	// 							$frm_edit_vox .= '<iframe src="https://docs.google.com/viewer?url=https://' . Configuration::$domain . '/uploads/' . $value['file'] . '&embedded=true"></iframe>';
	// 					}
	// 				}
	// 			}
	// 			else
	// 				$frm_edit_vox .= '<p>{$lang.not_attachments}</p>';
	//
	// 			$frm_edit_vox .=
	// 			'	</div>
	// 				<div class="label">
	// 					<label class="' . (!empty($vox['attachments']) ? 'success' : '') . '" unrequired>
	// 						<p>{$lang.attachments}</p>
	// 						<input type="file" name="attachments[]" multiple />
	// 					</label>
	// 				</div>
	// 			</div>';
	//
	// 			$replace = [
	// 				'{$frm_edit_vox}' => $frm_edit_vox
	// 			];
	//
	// 			$template = $this->format->replace($replace, $template);
	//
	// 			echo $template;
	// 		}
	// 	}
	// 	else
	// 		header('Location: /voxes');
	// }
	//
	// public function details($params)
	// {
	// 	$vox = $this->model->get_vox($params[0], true);
	//
	// 	if (!empty($vox))
	// 	{
	// 		if (Format::exist_ajax_request() == true)
	// 		{
	// 			if ($_POST['action'] == 'comment_vox' OR $_POST['action'] == 'complete_vox' OR $_POST['action'] == 'reopen_vox')
	// 			{
	// 				if ($_POST['action'] == 'comment_vox')
	// 					$query = $this->model->comment_vox($vox['id']);
	// 				else if ($_POST['action'] == 'complete_vox')
	// 					$query = $this->model->complete_vox($vox['id']);
	// 				else if ($_POST['action'] == 'reopen_vox')
	// 					$query = $this->model->reopen_vox($vox['id']);
	//
	// 				if (!empty($query))
	// 				{
	// 					$vox['assigned_users'] = $this->model->get_assigned_users($vox['assigned_users'], $vox['opportunity_area']['id']);
	//
	// 					$mail = new Mailer(true);
	//
	// 					if ($_POST['action'] == 'comment_vox')
	// 						$mail_subject = Lang::general('commented_vox')[$this->lang];
	// 					else if ($_POST['action'] == 'complete_vox')
	// 						$mail_subject = Lang::general('completed_vox')[$this->lang];
	// 					else if ($_POST['action'] == 'reopen_vox')
	// 						$mail_subject = Lang::general('reopened_vox')[$this->lang];
	//
	// 					try
	// 					{
	// 						$mail->isSMTP();
	// 						$mail->setFrom('noreply@guestvox.com', 'Guestvox');
	//
	// 						foreach ($vox['assigned_users'] as $value)
	// 							$mail->addAddress($value['email'], $value['firstname'] . ' ' . $value['lastname']);
	//
	// 						$mail->isHTML(true);
	// 						$mail->Subject = $mail_subject;
	// 						$mail->Body =
	// 						'<html>
	// 							<head>
	// 								<title>' . $mail_subject . '</title>
	// 							</head>
	// 							<body>
	// 								<table style="width:600px;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#eee">
	// 									<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
	// 										<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 											<figure style="width:100%;margin:0px;padding:0px;text-align:center;">
	// 												<img style="width:100%;max-width:300px;" src="https://' . Configuration::$domain . '/images/logotype_color.png" />
	// 											</figure>
	// 										</td>
	// 									</tr>
	// 									<tr style="width:100%;margin:0px 0px 10px 0px;padding:0px;border:0px;">
	// 										<td style="width:100%;margin:0px;padding:40px 20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 											<h4 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:24px;font-weight:600;text-align:center;color:#212121;">' . $mail_subject . '</h4>
	// 											<h6 style="width:100%;margin:0px 0px 20px 0px;padding:0px;font-size:14px;font-weight:400;text-align:left;color:#757575;">' . Lang::general('token')[$this->lang] . ': ' . $vox['token'] . '</h6>
	// 											<a style="width:100%;display:block;margin:0px;padding:20px 0px;border-radius:50px;box-sizing:border-box;background-color:#00a5ab;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#fff;" href="https://' . Configuration::$domain . '/voxes/details/' . $vox['id'] . '">' . Lang::general('view_details')[$this->lang] . '</a>
	// 										</td>
	// 									</tr>
	// 									<tr style="width:100%;margin:0px;padding:0px;border:0px;">
	// 										<td style="width:100%;margin:0px;padding:20px;border:0px;box-sizing:border-box;background-color:#fff;">
	// 											<a style="width:100%;display:block;padding:20px 0px;box-sizing:border-box;font-size:14px;font-weight:400;text-align:center;text-decoration:none;color:#757575;" href="https://' . Configuration::$domain . '">' . Configuration::$domain . '</a>
	// 										</td>
	// 									</tr>
	// 								</table>
	// 							</body>
	// 						</html>';
	// 						$mail->AltBody = '';
	// 						$mail->send();
	// 					}
	// 					catch (Exception $e) { }
	//
	// 					$sms = $this->model->get_sms();
	//
	// 					if ($sms > 0)
	// 					{
	// 						$sms_basic  = new \Nexmo\Client\Credentials\Basic('45669cce', 'CR1Vg1bpkviV8Jzc');
	// 						$sms_client = new \Nexmo\Client($sms_basic);
	// 						$sms_text = $mail_subject . '. ' . Lang::general('token')[$this->lang] . ': ' . $vox['token'] . '. ' . 'https://' . Configuration::$domain . '/voxes/details/' . $vox['id'];
	//
	// 						foreach ($vox['assigned_users'] as $value)
	// 						{
	// 							if ($sms > 0)
	// 							{
	// 								try
	// 								{
	// 									$sms_client->message()->send([
	// 										'to' => $value['phone']['lada'] . $value['phone']['number'],
	// 										'from' => 'Guestvox',
	// 										'text' => $sms_text
	// 									]);
	//
	// 									$sms = $sms - 1;
	// 								}
	// 								catch (Exception $e) { }
	// 							}
	// 						}
	//
	// 						$this->model->edit_sms($sms);
	// 					}
	//
	// 					Functions::environment([
	// 						'status' => 'success',
	// 						'message' => '{$lang.operation_success}'
	// 					]);
	// 				}
	// 				else
	// 				{
	// 					Functions::environment([
	// 						'status' => 'error',
	// 						'message' => '{$lang.operation_error}'
	// 					]);
	// 				}
	// 			}
	// 		}
	// 		else
	// 		{
	// 			$template = $this->view->render($this, 'details');
	//
	// 			define('_title', 'Guestvox | {$lang.vox_details}');
	//
	// 			$div_assigned_users = '';
	//
	// 			if (!empty($vox['assigned_users']))
	// 			{
	// 				foreach ($vox['assigned_users'] as $value)
	// 				{
	// 					$div_assigned_users .=
	// 					'<div>
	// 						<figure>
	// 							<img src="' . (!empty($value['avatar']) ? '{$path.uploads}' . $value['avatar'] : '{$path.images}avatar.png') . '" />
	// 						</figure>
	// 						<span>' . $value['firstname'] . ' ' . $value['lastname'] . '</span>
	// 					</div>';
	// 				}
	// 			}
	// 			else
	// 				$div_assigned_users .= '<p>{$lang.not_assigned_users}<p>';
	//
	// 			$div_attachments = '';
	//
	// 			if (!empty($vox['attachments']))
	// 			{
	// 				foreach ($vox['attachments'] as $value)
	// 				{
	// 					if ($value['status'] == 'success')
	// 					{
	// 						$ext = strtoupper(explode('.', $value['file'])[1]);
	//
	// 						if ($ext == 'JPG' OR $ext == 'JPEG' OR $ext == 'PNG')
	// 							$div_attachments .= '<figure><img src="{$path.uploads}' . $value['file'] . '"><a href="{$path.uploads}' . $value['file'] . '" class="fancybox-thumb" rel="fancybox-thumb"></a></figure>';
	// 						else if ($ext == 'PDF' OR $ext == 'DOC' OR $ext == 'DOCX' OR $ext == 'XLS' OR $ext == 'XLSX')
	// 							$div_attachments .= '<iframe src="https://docs.google.com/viewer?url=https://' . Configuration::$domain . '/uploads/' . $value['file'] . '&embedded=true"></iframe>';
	// 					}
	// 				}
	// 			}
	// 			else
	// 				$div_attachments .= '<p>{$lang.not_attachments}<p>';
	//
	// 			$div_viewed_by = '';
	//
	// 			foreach ($vox['viewed_by'] as $value)
	// 			{
	// 				$div_viewed_by .=
	// 				'<div>
	// 					<figure>
	// 						<img src="' . (!empty($value['avatar']) ? '{$path.uploads}' . $value['avatar'] : '{$path.images}avatar.png') . '" />
	// 					</figure>
	// 					<span>' . $value['firstname'] . ' ' . $value['lastname'] . '</span>
	// 				</div>';
	// 			}
	//
	// 			$div_comments = '';
	//
	// 			if (!empty($vox['comments']))
	// 			{
	// 				foreach ($vox['comments'] as $value)
	// 				{
	// 					$div_comments .=
	// 					'<div>
	// 						<div>
	// 							<figure>
	// 								<img src="' . (!empty($value['user']['avatar']) ? '{$path.uploads}' . $value['user']['avatar'] : '{$path.images}avatar.png') . '" />
	// 							</figure>
	// 							<span>' . $value['user']['firstname'] . ' ' . $value['user']['lastname'] . '</span>
	// 							<span>{$lang.say}:</span>
	// 						</div>
	// 						<p>' . $value['message'] . '</p>
	// 						<p>{$lang.cost}: ' . Functions::get_formatted_currency((!empty($value['cost']) ? $value['cost'] : '0'), Session::get_value('account')['currency']) . '</p>
	// 						<div class="attachments">';
	//
	// 					if (!empty($value['attachments']))
	// 					{
	// 						foreach ($value['attachments'] as $subvalue)
	// 						{
	// 							if ($subvalue['status'] == 'success')
	// 							{
	// 								$ext = strtoupper(explode('.', $subvalue['file'])[1]);
	//
	// 								if ($ext == 'JPG' OR $ext == 'JPEG' OR $ext == 'PNG')
	// 									$div_comments .= '<figure><img src="{$path.uploads}' . $subvalue['file'] . '"><a href="{$path.uploads}' . $subvalue['file'] . '" class="fancybox-thumb" rel="fancybox-thumb"></a></figure>';
	// 								else if ($ext == 'PDF' OR $ext == 'DOC' OR $ext == 'DOCX' OR $ext == 'XLS' OR $ext == 'XLSX')
	// 									$div_comments .= '<iframe src="https://docs.google.com/viewer?url=https://guestvox.com/uploads/' . $subvalue['file'] . '&embedded=true"></iframe>';
	// 							}
	// 						}
	// 					}
	// 					else
	// 						$div_comments .= '<p>{$lang.not_attachments}<p>';
	//
	// 					$div_comments .=
	// 					'</div>
	// 					<span>{$lang.commented_at}: ' . Functions::get_formatted_date($value['date'], 'd M, y') . ' {$lang.at} ' . Functions::get_formatted_hour($value['hour'], '+ hrs') . '</span>';
	//
	// 					if ($vox['status'] == 'open')
	// 						$div_comments .= '<a data-response-to="' . $value['user']['username'] . '" data-button-modal="comment_vox" value="' . $vox['type'] . '"><i class="fas fa-reply"></i></a>';
	//
	// 					$div_comments .= '</div>';
	// 				}
	// 			}
	// 			else
	// 				$div_comments .= '<p>{$lang.not_comments}<p>';
	//
	// 			$div_changes_history = '';
	//
	// 			foreach ($vox['changes_history'] as $value)
	// 			{
	// 				$div_changes_history .=
	// 				'<div>
	// 					<div>
	// 						<figure>
	// 							<img src="' . (($value['type'] == 'created' AND $vox['origin'] == 'myvox') ? '{$path.images}myvox.png' : (!empty($value['user']['avatar']) ? '{$path.uploads}' . $value['user']['avatar'] : '{$path.images}avatar.png')) . '" />
	// 						</figure>
	// 						<span>' . (($value['type'] == 'created' AND $vox['origin'] == 'myvox') ? ((!empty($vox['firstname']) AND !empty($vox['lastname'])) ? $vox['firstname'] . ' ' . $vox['lastname'] : 'Myvox') : $value['user']['firstname'] . ' ' . $value['user']['lastname']) . '</span>
	// 					</div>
	// 					<p>{$lang.' . $value['type'] . '} {$lang.the} ' . Functions::get_formatted_date($value['date'], 'd F Y') . ' {$lang.at} ' . Functions::get_formatted_hour($value['hour'], '+ hrs') . '</p>';
	//
	// 				if ($value['type'] == 'edited')
	// 				{
	// 					$div_changes_history .= '<ul>';
	//
	// 					foreach ($value['fields'] as $subvalue)
	// 						$div_changes_history .= '<li>{$lang.' . $subvalue['field'] . '}: ' . $subvalue['before'] . ' <i class="fas fa-arrow-alt-circle-right"></i> ' . $subvalue['after'] . '</li>';
	//
	// 					$div_changes_history .= '</ul>';
	// 				}
	//
	// 				$div_changes_history .= '</div>';
	// 			}
	//
	// 			$replace = [
	// 				'{$type}' => $vox['type'],
	// 				'{$token}' => $vox['token'],
	// 				'{$owner}' => $vox['owner']['name'] . (!empty($vox['owner']['number']) ? ' #' . $vox['owner']['number'] : ''),
	// 				'{$opportunity_area}' => $vox['opportunity_area']['name'][$this->lang],
	// 				'{$opportunity_type}' => $vox['opportunity_type']['name'][$this->lang],
	// 				'{$started_date}' => Functions::get_formatted_date($vox['started_date'], 'd F, Y'),
	// 				'{$started_hour}' => Functions::get_formatted_hour($vox['started_hour'], '+ hrs'),
	// 				'{$h4_date}' => '<h4
	// 					data-date-1="' . Functions::get_formatted_date_hour($vox['started_date'], $vox['started_hour']) . '"
	// 					data-date-2="' . ((!empty($vox['completed_date']) AND !empty($vox['completed_hour'])) ? Functions::get_formatted_date_hour($vox['completed_date'], $vox['completed_hour']) : '') . '"
	// 					data-time-zone="' . Session::get_value('account')['time_zone'] . '"
	// 					data-status="' . $vox['status'] . '"
	// 					data-elapsed-time></h4>',
	// 				'{$location}' => $vox['location']['name'][$this->lang],
	// 				'{$h4_cost}' => ($vox['type'] == 'incident' OR $vox['type'] == 'workorder') ? '<h4>{$lang.cost}: ' . Functions::get_formatted_currency((!empty($vox['cost']) ? $vox['cost'] : '0'), Session::get_value('account')['currency']) . '</h4>' : '',
	// 				'{$urgency}' => $vox['urgency'],
	// 				'{$h4_confidentiality}' => ($vox['type'] == 'incident') ? '<h4>{$lang.confidentiality}: ' . (($vox['confidentiality'] == true) ? '{$lang.yes}' : '{$lang.not}') . '</h4>' : '',
	// 				'{$div_assigned_users}' => $div_assigned_users,
	// 				'{$h4_observations}' => ($vox['type'] == 'request' OR $vox['type'] == 'workorder') ? '<h4>{$lang.observations}: ' . (!empty($vox['observations']) ? $vox['observations'] : '{$lang.not_observations}') . '</h4>' : '',
	// 				'{$h4_subject}' => ($vox['type'] == 'incident') ? '<h4>{$lang.subject}: ' . (!empty($vox['subject']) ? $vox['subject'] : '{$lang.not_subject}') . '</h4>' : '',
	// 				'{$h4_description}' => ($vox['type'] == 'incident') ? '<h4>{$lang.description}: ' . (!empty($vox['description']) ? $vox['description'] : '{$lang.not_description}') . '</h4>' : '',
	// 				'{$h4_action_taken}' => ($vox['type'] == 'incident') ? '<h4>{$lang.action_taken}: ' . (!empty($vox['action_taken']) ? $vox['action_taken'] : '{$lang.not_action_taken}') . '</h4>' : '',
	// 				'{$h4_guest_treatment}' => (($vox['type'] == 'request' OR $vox['type'] == 'incident') AND Session::get_value('account')['type'] == 'hotel') ? '<h4>{$lang.guest_treatment}: ' . (!empty($vox['guest_treatment']) ? $vox['guest_treatment']['name'] : '{$lang.not_guest_treatment}') . '</h4>' : '',
	// 				'{$h4_name}' => ($vox['type'] == 'request' OR $vox['type'] == 'incident') ? '<h4>{$lang.name}: ' . ((!empty($vox['firstname']) AND !empty($vox['lastname'])) ? $vox['firstname'] . ' ' . $vox['lastname'] : '{$lang.not_name}') . '</h4>' : '',
	// 				'{$h4_guest_id}' => ($vox['type'] == 'incident' AND Session::get_value('account')['type'] == 'hotel') ? '<h4>{$lang.guest_id}: ' . (!empty($vox['guest_id']) ? $vox['guest_id'] : '{$lang.not_guest_id}') . '</h4>' : '',
	// 				'{$h4_guest_type}' => ($vox['type'] == 'incident' AND Session::get_value('account')['type'] == 'hotel') ? '<h4>{$lang.guest_type}: ' . (!empty($vox['guest_type']) ? $vox['guest_type']['name'] : '{$lang.not_guest_type}') . '</h4>' : '',
	// 				'{$h4_reservation_number}' => ($vox['type'] == 'incident' AND Session::get_value('account')['type'] == 'hotel') ? '<h4>{$lang.reservation_number}: ' . (!empty($vox['reservation_number']) ? $vox['reservation_number'] : '{$lang.not_reservation_number}') . '</h4>' : '',
	// 				'{$h4_reservation_status}' => ($vox['type'] == 'incident' AND Session::get_value('account')['type'] == 'hotel') ? '<h4>{$lang.reservation_status}: ' . (!empty($vox['reservation_status']) ? $vox['reservation_status']['name'] : '{$lang.not_reservation_status}') . '</h4>' : '',
	// 				'{$h4_check_in}' => ($vox['type'] == 'incident' AND Session::get_value('account')['type'] == 'hotel') ? '<h4>{$lang.check_in}: ' . (!empty($vox['check_in']) ? Functions::get_formatted_date($vox['check_in'], 'd F, Y') : '{$lang.not_check_in}') . '</h4>' : '',
	// 				'{$h4_check_out}' => ($vox['type'] == 'incident' AND Session::get_value('account')['type'] == 'hotel') ? '<h4>{$lang.check_out}: ' . (!empty($vox['check_out']) ? Functions::get_formatted_date($vox['check_out'], 'd F, Y') : '{$lang.not_check_out}') . '</h4>' : '',
	// 				'{$div_attachments}' => $div_attachments,
	// 				'{$div_viewed_by}' => $div_viewed_by,
	// 				'{$div_comments}' => $div_comments,
	// 				'{$btn_comment}' => ($vox['status'] == 'open') ? '<a data-button-modal="comment_vox" value="' . $vox['type'] . '"><i class="fas fa-comment-alt"></i></a>' : '',
	// 				'{$div_changes_history}' => $div_changes_history,
	// 				'{$h4_created_user}' => '<h4>{$lang.created_by} ' . (($vox['origin'] == 'myvox') ? ((!empty($vox['firstname']) AND !empty($vox['lastname'])) ? $vox['firstname'] . ' ' . $vox['lastname'] : 'Myvox') : $vox['created_user']['firstname'] . ' ' . $vox['created_user']['lastname']) . ' {$lang.the} ' . Functions::get_formatted_date($vox['created_date'], 'd F, Y') . ' {$lang.at} ' . Functions::get_formatted_hour($vox['created_hour'], '+ hrs') . '</h4>',
	// 				'{$h4_edited_user}' => '<h4>' . (!empty($vox['edited_user']) ? '{$lang.edited_by} ' . $vox['edited_user']['firstname'] . ' ' . $vox['edited_user']['lastname'] . ' {$lang.the} ' . Functions::get_formatted_date($vox['edited_date'], 'd F, Y') . ' {$lang.at} ' . Functions::get_formatted_hour($vox['edited_hour'], '+ hrs') : '{$lang.not_edited}') . '</h4>',
	// 				'{$h4_completed_user}' => '<h4>' . (($vox['status'] == 'close' AND !empty($vox['completed_user'])) ? '{$lang.completed_by} ' . $vox['completed_user']['firstname'] . ' ' . $vox['completed_user']['lastname'] . ' {$lang.the} ' . Functions::get_formatted_date($vox['completed_date'], 'd F, Y') . ' {$lang.at} ' . Functions::get_formatted_hour($vox['completed_hour'], '+ hrs') : '{$lang.not_completed}') . '</h4>',
	// 				'{$h4_reopened_user}' => '<h4>' . (!empty($vox['reopened_user']) ? '{$lang.reopened_by} ' . $vox['reopened_user']['firstname'] . ' ' . $vox['reopened_user']['lastname'] . ' {$lang.the} ' . Functions::get_formatted_date($vox['reopened_date'], 'd F, Y') . ' {$lang.at} ' . Functions::get_formatted_hour($vox['reopened_hour'], '+ hrs') : '{$lang.not_reopened}') . '</h4>',
	// 				'{$status}' => (($vox['status'] == 'open') ? '{$lang.opened}' : '{$lang.closed}'),
	// 				'{$origin}' => $vox['origin'],
	// 				'{$btn_edit}' => ($vox['status'] == 'open' AND Functions::check_user_access(['{voxes_update}']) == true) ? '<a href="/voxes/edit/' . $vox['id'] . '" class="edit"><i class="fas fa-pen"></i></a>' : '',
	// 				'{$btn_complete}' => ($vox['status'] == 'open' AND Functions::check_user_access(['{voxes_complete}']) == true) ? '<a data-button-modal="complete_vox" class="new"><i class="fas fa-check"></i></a>' : '',
	// 				'{$btn_reopen}' => ($vox['status'] == 'close' AND Functions::check_user_access(['{voxes_reopen}']) == true) ? '<a data-button-modal="reopen_vox"><i class="fas fa-redo-alt"></i></a>' : ''
	// 			];
	//
	// 			$template = $this->format->replace($replace, $template);
	//
	// 			echo $template;
	// 		}
	// 	}
	// 	else
	// 		header('Location: /voxes');
	// }
	//
	// public function reports($params)
	// {
	// 	if (Format::exist_ajax_request() == true)
	// 	{
	// 		if ($_POST['action'] == 'get_vox_report')
	// 		{
	// 			$query = $this->model->get_vox_report($_POST['id']);
	//
	// 			if (!empty($query))
	// 			{
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'data' => $query
	// 				]);
	// 			}
	// 			else
	// 			{
	// 				Functions::environment([
	// 					'status' => 'error',
	// 					'message' => '{$lang.operation_error}'
	// 				]);
	// 			}
	// 		}
	//
	// 		if ($_POST['action'] == 'get_opt_owners')
	// 		{
	// 			$html = '<option value="" selected>{$lang.all}</option>';
	//
	// 			foreach ($this->model->get_owners($_POST['type']) as $value)
	// 				$html .= '<option value="' . $value['id'] . '">' . $value['name'] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_opt_opportunity_areas')
	// 		{
	// 			$html = '<option value="" selected>{$lang.all}</option>';
	//
	// 			foreach ($this->model->get_opportunity_areas($_POST['type']) as $value)
	// 				$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_opt_opportunity_types')
	// 		{
	// 			$html = '<option value="" selected>{$lang.all}</option>';
	//
	// 			foreach ($this->model->get_opportunity_types($_POST['opportunity_area'], $_POST['type']) as $value)
	// 				$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_opt_locations')
	// 		{
	// 			$html = '<option value="" selected>{$lang.all}</option>';
	//
	// 			foreach ($this->model->get_locations($_POST['type']) as $value)
	// 				$html .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_cbx_opportunity_areas')
	// 		{
	// 			$html =
	// 			'<div>
	// 				<input type="checkbox" name="checked_all">
	// 				<span>{$lang.all}</span>
	// 			</div>';
	//
	// 			foreach ($this->model->get_opportunity_areas($_POST['type']) as $value)
	// 			{
	// 				$html .=
	// 				'<div>
	// 					<input type="checkbox" name="opportunity_areas[]" value="' . $value['id'] . '">
	// 					<span>' . $value['name'][$this->lang] . '</span>
	// 				</div>';
	// 			}
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_cbx_vox_report_fields')
	// 		{
	// 			$html =
	// 			'<div>
	// 				<input type="checkbox" name="checked_all">
	// 				<span>{$lang.all}</span>
	// 			</div>';
	//
	// 			foreach ($this->model->get_vox_report_fields($_POST['type']) as $value)
	// 			{
	// 				$html .=
	// 				'<div>
	// 					<input type="checkbox" name="fields[]" value="' . $value['id'] . '">
	// 					<span>{$lang.' . $value['name'] . '}</span>
	// 				</div>';
	// 			}
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'html' => $html
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_lst_vox_report_fields')
	// 		{
	// 			$labels = [];
	//
	// 			if (!isset($_POST['report']) OR empty($_POST['report']))
	// 				array_push($labels, ['report','']);
	//
	// 			if (!isset($_POST['type']) OR empty($_POST['type']))
	// 				array_push($labels, ['type','']);
	//
	// 			if (!isset($_POST['order']) OR empty($_POST['order']))
	// 				array_push($labels, ['order','']);
	//
	// 			if (!isset($_POST['started_date']) OR empty($_POST['started_date']) OR $_POST['started_date'] > Functions::get_current_date() OR $_POST['started_date'] > $_POST['end_date'])
	// 				array_push($labels, ['started_date','']);
	//
	// 			if (!isset($_POST['end_date']) OR empty($_POST['end_date']) OR $_POST['end_date'] > Functions::get_current_date() OR $_POST['end_date'] < $_POST['started_date'])
	// 				array_push($labels, ['end_date','']);
	//
	// 			if (!isset($_POST['fields']) OR empty($_POST['fields']))
	// 				array_push($labels, ['fields[]','']);
	//
	// 			if (empty($labels))
	// 			{
	// 				$query = $this->model->get_voxes('report', $_POST);
	//
	// 				if (!empty($query))
	// 				{
	// 					$html = '';
	//
	// 					foreach ($query as $value)
	// 					{
	// 						$html .= '<div>';
	//
	// 						if (in_array('type', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.type}:</strong> {$lang.' . $value['type'] . '}</p>';
	//
	// 						$html .= '<p><strong>{$lang.token}:</strong> ' . $value['token'] . '</p>';
	//
	// 						if (in_array('owner', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.owner}:</strong>' . $value['owner']['name'] . (!empty($value['owner']['number']) ? ' #' . $value['owner']['number'] : '') . '</p>';
	//
	// 						if (in_array('opportunity_area', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.opportunity_area}:</strong> ' . $value['opportunity_area']['name'][$this->lang] . '</p>';
	//
	// 						if (in_array('opportunity_type', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.opportunity_type}:</strong> ' . $value['opportunity_type']['name'][$this->lang] . '</p>';
	//
	// 						if (in_array('date', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.date}:</strong> ' . Functions::get_formatted_date($value['started_date'], 'd F, Y') . ' ' . Functions::get_formatted_hour($value['started_hour'], '+ hrs') . '</p>';
	//
	// 						if (in_array('location', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.location}:</strong> ' . $value['location']['name'][$this->lang] . '</p>';
	//
	// 						if ($value['type'] == 'incident' OR $value['type'] == 'workorder')
	// 						{
	// 							if (in_array('cost', $_POST['fields']))
	// 								$html .= '<p><strong>{$lang.cost}:</strong> ' . Functions::get_formatted_currency((!empty($value['cost']) ? $value['cost'] : '0'), Session::get_value('account')['currency']) . '</p>';
	// 						}
	//
	// 						if (in_array('urgency', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.urgency}:</strong> {$lang.' . $value['urgency'] . '}</p>';
	//
	// 						if ($value['type'] == 'incident')
	// 						{
	// 							if (in_array('confidentiality', $_POST['fields']))
	// 								$html .= '<p><strong>{$lang.confidentiality}:</strong> {$lang.' . (($value['confidentiality'] == true) ? 'yes' : 'not') . '}</p>';
	// 						}
	//
	// 						if (in_array('assigned_users', $_POST['fields']))
	// 						{
	// 							$str = '';
	//
	// 							if (!empty($value['assigned_users'])
	// 							{
	// 								foreach ($value['assigned_users'] as $subvalue)
	// 									$str .= $subvalue['firstname'] . ' ' . $subvalue['lastname'] . ', ';
	//
	// 								$str = substr($str, 0, -2);
	// 							}
	// 							else
	// 								$str .= '{$lang.empty}';
	//
	// 							$html .= '<p><strong>{$lang.assigned_users}:</strong> ' . $str . '</p>';
	// 						}
	//
	// 						if ($value['type'] == 'request' OR $value['type'] == 'workorder')
	// 						{
	// 							if (in_array('observations', $_POST['fields']))
	// 								$html .= '<p><strong>{$lang.observations}:</strong> ' . (!empty($value['observations']) ? $value['observations'] : '{$lang.empty}') . '</p>';
	// 						}
	//
	// 						if ($value['type'] == 'incident')
	// 						{
	// 							if (in_array('subject', $_POST['fields']))
	// 								$html .= '<p><strong>{$lang.subject}:</strong> ' . (!empty($value['subject']) ? $value['subject'] : '{$lang.empty}') . '</p>';
	//
	// 							if (in_array('description', $_POST['fields']))
	// 								$html .= '<p><strong>{$lang.description}:</strong> ' . (!empty($value['description']) ? $value['description'] : '{$lang.empty}') . '</p>';
	//
	// 							if (in_array('action_taken', $_POST['fields']))
	// 								$html .= '<p><strong>{$lang.action_taken}:</strong> ' . (!empty($value['action_taken']) ? $value['action_taken'] : '{$lang.empty}') . '</p>';
	// 						}
	//
	// 						if (Session::get_value('account')['type'] == 'hotel')
	// 						{
	// 							if ($value['type'] == 'request' OR $value['type'] == 'incident')
	// 							{
	// 								if (in_array('guest_treatment', $_POST['fields']))
	// 									$html .= '<p><strong>{$lang.guest_treatment}:</strong> ' (!empty($value['guest_treatment']) ? $value['guest_treatment']['name'] : '{$lang.empty}') . '</p>';
	// 							}
	// 						}
	//
	// 						if ($value['type'] == 'request' OR $value['type'] == 'incident')
	// 						{
	// 							if (in_array('name', $_POST['fields']))
	// 								$html .= '<p><strong>{$lang.name}:</strong> ' ((!empty($value['firstname']) AND !empty($value['lastname'])) ? $value['firstname'] . ' ' . $value['lastname'] : '{$lang.empty}') . '</p>';
	// 						}
	//
	// 						if (Session::get_value('account')['type'] == 'hotel')
	// 						{
	// 							if ($value['type'] == 'incident')
	// 							{
	// 								if (in_array('guest_id', $_POST['fields']))
	// 									$html .= '<p><strong>{$lang.guest_id}:</strong> ' . (!empty($value['guest_id']) ? $value['guest_id'] : '{$lang.empty}') . '</p>';
	//
	// 								if (in_array('guest_type', $_POST['fields']))
	// 									$html .= '<p><strong>{$lang.guest_type}:</strong> ' . (!empty($value['guest_type']) ? $value['guest_type']['name'] : '{$lang.empty}') . '</p>';
	//
	// 								if (in_array('reservation_number', $_POST['fields']))
	// 									$html .= '<p><strong>{$lang.reservation_number}:</strong> ' . (!empty($value['reservation_number']) ? $value['reservation_number'] : '{$lang.empty}') . '</p>';
	//
	// 								if (in_array('reservation_status', $_POST['fields']))
	// 									$html .= '<p><strong>{$lang.reservation_status}:</strong> ' . (!empty($value['reservation_status']) ? $value['reservation_status']['name'] : '{$lang.empty}') . '</p>';
	//
	// 								if (in_array('staying', $_POST['fields']))
	// 									$html .= '<p><strong>{$lang.staying}:</strong> ' . ((!empty($value['check_in']) AND !empty($value['check_out'])) ? Functions::get_formatted_date($value['check_in'], 'd F, Y') . ' / ' . Functions::get_formatted_date($value['check_out'], 'd F, Y') : '{$lang.empty}') . '</p>';
	// 							}
	// 						}
	//
	// 						if (in_array('attachments', $_POST['fields']))
	// 						{
	// 							$str = '';
	//
	// 							if (!empty($value['attachments']))
	// 							{
	// 								$img = 0;
	// 								$pdf = 0;
	// 								$wrd = 0;
	// 								$exl = 0;
	//
	// 								foreach ($value['attachments'] as $subvalue)
	// 								{
	// 									if ($subvalue['status'] == 'success')
	// 									{
	// 										$ext = strtoupper(explode('.', $subvalue['file'])[1]);
	//
	// 										if ($ext == 'JPG' OR $ext == 'JPEG' OR $ext == 'PNG')
	// 											$img = $img + 1;
	// 										else if ($ext == 'PDF')
	// 											$pdf = $pdf + 1;
	// 										else if ($ext == 'DOC' OR $ext == 'DOCX')
	// 											$wrd = $wrd + 1;
	// 										else if ($ext == 'XLS' OR $ext == 'XLSX')
	// 											$exl = $exl + 1;
	// 									}
	// 								}
	//
	// 								if ($img > 0)
	// 									$str .= '<img src="{$path.images}empty.png">' . $img . ' {$lang.files}, ';
	//
	// 								if ($pdf > 0)
	// 									$str .= '<img src="{$path.images}pdf.png">' . $pdf . ' {$lang.files}, ';
	//
	// 								if ($wrd > 0)
	// 									$str .= '<img src="{$path.images}word.png">' . $wrd . ' {$lang.files}, ';
	//
	// 								if ($exl > 0)
	// 									$str .= '<img src="{$path.images}excel.png">' . $exl . ' {$lang.files}, ';
	//
	// 								$str = substr($str, 0, -2);
	// 							}
	// 							else
	// 								$str .= '{$lang.empty}';
	//
	// 							$html .= '<p><strong>{$lang.attachments}:</strong> ' . $str . '</p>';
	// 						}
	//
	// 						if (in_array('viewed_by', $_POST['fields']))
	// 						{
	// 							$str = '';
	//
	// 							if (!empty($value['viewed_by']))
	// 							{
	// 								foreach ($value['viewed_by'] as $subvalue)
	// 									$str .= $subvalue['firstname'] . ' ' . $subvalue['lastname'] . ', ';
	//
	// 								$str = substr($str, 0, -2);
	// 							}
	// 							else
	// 								$str .= '{$lang.empty}';
	//
	// 							$html .= '<p><strong>{$lang.viewed_by}:</strong> ' . $str . '</p>';
	// 						}
	//
	// 						if (in_array('created', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.created}:</strong> ' . (($value['origin'] == 'myvox') ? ((!empty($value['firstname']) AND !empty($value['lastname'])) ? $value['firstname'] . ' ' . $value['lastname'] : 'Myvox') : $value['created_user']['firstname'] . ' ' . $value['created_user']['lastname']) . ' {$lang.at} ' . Functions::get_formatted_date_hour($value['created_date'], $value['created_hour']) . '</p>';
	//
	// 						if (in_array('edited', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.edited}:</strong> ' . (!empty($value['edited_user']) ? $value['edited_user']['firstname'] . ' ' . $value['edited_user']['lastname'] . ' {$lang.at} ' . Functions::get_formatted_date_hour($value['edited_date'], $value['edited_hour']) : '{$lang.empty}') . '</p>';
	//
	// 						if (in_array('completed', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.completed}:</strong> ' . ($value['status'] == 'close' AND !empty($value['completed_user']) ? $value['completed_user']['firstname'] . ' ' . $value['completed_user']['lastname'] . ' {$lang.at} ' . Functions::get_formatted_date_hour($value['completed_date'], $value['completed_hour']) : '{$lang.empty}') . '</p>';
	//
	// 						if (in_array('reopened', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.reopened}:</strong> ' . (!empty($value['reopened_user']) ? $value['reopened_user']['firstname'] . ' ' . $value['reopened_user']['lastname'] . ' {$lang.at} ' . Functions::get_formatted_date_hour($value['reopened_date'], $value['reopened_hour']) : '{$lang.empty}') . '</p>';
	//
	// 						if (in_array('status', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.status}:</strong> {$lang.' . (($value['status'] == 'open') ? 'opened' : 'closed') . '}</p>';
	//
	// 						if (in_array('origin', $_POST['fields']))
	// 							$html .= '<p><strong>{$lang.origin}:</strong> {$lang.' . $value['origin'] . '}</p>';
	//
	// 						if (in_array('average_resolution', $_POST['fields']))
	// 						{
	// 							$str = '';
	//
	// 							if ($value['status'] == 'close' AND !empty($value['completed_date']) AND !empty($value['completed_hour']))
	// 							{
	// 								$date1 = new DateTime($value['started_date'] . ' ' . $value['started_hour']);
	// 								$date2 = new DateTime($value['completed_date'] . ' ' . $value['completed_hour']);
	// 								$date3 = $date1->diff($date2);
	//
	// 								if ($date3->h == 0 AND $date3->i == 0)
	// 									$str .= $date3->s . ' Seg';
	// 								else if ($date3->h == 0 AND $date3->i > 0)
	// 									$str .= $date3->i . ' Min';
	// 								else if ($date3->h > 0 AND $date3->i == 0)
	// 									$str .= $date3->h . ' Hrs';
	// 								else if ($date3->h > 0 AND $date3->i > 0)
	// 									$str .= $date3->h . ' Hrs ' . $date3->i . ' Min';
	// 							}
	// 							else
	// 								$str .= '{$lang.empty}';
	//
	// 							$html .= '<p><strong>{$lang.average_resolution}:</strong> ' . $str . '</p>';
	// 						}
	//
	// 						if (in_array('comments', $_POST['fields']))
	// 						{
	// 							$str_1 = '';
	//
	// 							if (!empty($value['comments']))
	// 							{
	// 								foreach ($value['comments'] as $subvalue)
	// 								{
	// 									$str_2 = '';
	//
	// 									if (!empty($subvalue['attachments']))
	// 									{
	// 										$img = 0;
	// 										$pdf = 0;
	// 										$wrd = 0;
	// 										$exl = 0;
	//
	// 										foreach ($subvalue['attachments'] as $intvalue)
	// 										{
	// 											if ($intvalue['status'] == 'success')
	// 											{
	// 												$ext = strtoupper(explode('.', $intvalue['file'])[1]);
	//
	// 												if ($ext == 'JPG' OR $ext == 'JPEG' OR $ext == 'PNG')
	// 													$img = $img + 1;
	// 												else if ($ext == 'PDF')
	// 													$pdf = $pdf + 1;
	// 												else if ($ext == 'DOC' OR $ext == 'DOCX')
	// 													$wrd = $wrd + 1;
	// 												else if ($ext == 'XLS' OR $ext == 'XLSX')
	// 													$exl = $exl + 1;
	// 											}
	// 										}
	//
	// 										if ($img > 0 OR $pdf > 0 OR $wrd > 0 OR $exl > 0)
	// 										{
	// 											if ($img > 0)
	// 												$str_2 .= '<img src="{$path.images}empty.png">' . $img . ' {$lang.files}, ';
	//
	// 											if ($pdf > 0)
	// 												$str_2 .= '<img src="{$path.images}pdf.png">' . $pdf . ' {$lang.files}, ';
	//
	// 											if ($wrd > 0)
	// 												$str_2 .= '<img src="{$path.images}word.png">' . $wrd . ' {$lang.files}, ';
	//
	// 											if ($exl > 0)
	// 												$str_2 .= '<img src="{$path.images}excel.png">' . $exl . ' {$lang.files}, ';
	// 										}
	//
	// 										$str_2 = substr($str_2, 0, -2);
	// 									}
	// 									else
	// 										$str_2 .= '{$lang.empty}';
	//
	// 									$str_1 .= '<p><strong>' . $subvalue['user']['firstname'] . ' ' . $subvalue['user']['lastname'] . ':</strong> ' . $subvalue['message'] . '. <strong>{$lang.attachments}:</strong> ' . $str_2 . '</p>';
	// 								}
	// 							}
	// 							else
	// 								$str_1 .= '{$lang.empty}';
	//
	// 							$html .= '<p><strong>{$lang.comments}:</strong></p>' . $str_1;
	// 						}
	//
	// 						$html .= '</div>';
	// 					}
	//
	// 					Functions::environment([
	// 						'status' => 'success',
	// 						'html' => $html
	// 					]);
	// 				}
	// 				else
	// 				{
	// 					Functions::environment([
	// 						'status' => 'error',
	// 						'message' => '{$lang.operation_error}'
	// 					]);
	// 				}
	// 			}
	// 			else
	// 			{
	// 				Functions::environment([
	// 					'status' => 'error',
	// 					'labels' => $labels
	// 				]);
	// 			}
	// 		}
	//
	// 		if ($_POST['action'] == 'new_vox_report' OR $_POST['action'] == 'edit_vox_report')
	// 		{
	// 			$labels = [];
	//
	// 			if (!isset($_POST['name']) OR empty($_POST['name']))
	// 				array_push($labels, ['name','']);
	//
	// 			if (!isset($_POST['type']) OR empty($_POST['type']))
	// 				array_push($labels, ['type','']);
	//
	// 			if (!isset($_POST['order']) OR empty($_POST['order']))
	// 				array_push($labels, ['order','']);
	//
	// 			if (!isset($_POST['time_period']) OR empty($_POST['time_period']) OR !is_numeric($_POST['time_period']) OR $_POST['time_period'] < 1)
	// 				array_push($labels, ['time_period','']);
	//
	// 			if (!isset($_POST['addressed_to']) OR empty($_POST['addressed_to']))
	// 				array_push($labels, ['addressed_to','']);
	//
	// 			if ($_POST['addressed_to'] == 'opportunity_areas')
	// 			{
	// 				if (!isset($_POST['opportunity_areas']) AND empty($_POST['opportunity_areas']))
	// 					array_push($labels, ['opportunity_areas[]','']);
	// 			}
	//
	// 			if (!isset($_POST['fields']) OR empty($_POST['fields']))
	// 				array_push($labels, ['fields[]','']);
	//
	// 			if (empty($labels))
	// 			{
	// 				if ($_POST['action'] == 'new_vox_report')
	// 					$query = $this->model->new_vox_report($_POST);
	// 				else if ($_POST['action'] == 'edit_vox_report')
	// 					$query = $this->model->edit_vox_report($_POST);
	//
	// 				if (!empty($query))
	// 				{
	// 					Functions::environment([
	// 						'status' => 'success',
	// 						'message' => '{$lang.operation_success}'
	// 					]);
	// 				}
	// 				else
	// 				{
	// 					Functions::environment([
	// 						'status' => 'error',
	// 						'message' => '{$lang.operation_error}'
	// 					]);
	// 				}
	// 			}
	// 			else
	// 			{
	// 				Functions::environment([
	// 					'status' => 'error',
	// 					'labels' => $labels
	// 				]);
	// 			}
	// 		}
	//
	// 		if ($_POST['action'] == 'delete_vox_report')
	// 		{
	// 			$query = $this->model->delete_vox_report($_POST['id']);
	//
	// 			if (!empty($query))
	// 			{
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'message' => '{$lang.operation_success}'
	// 				]);
	// 			}
	// 			else
	// 			{
	// 				Functions::environment([
	// 					'status' => 'error',
	// 					'message' => '{$lang.operation_error}'
	// 				]);
	// 			}
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$template = $this->view->render($this, 'reports');
	//
	// 		define('_title', 'Guestvox | {$lang.voxes_reports}');
	//
	// 		$opt_owners = '';
	//
	// 		foreach ($this->model->get_owners() as $value)
	// 			$opt_owners .= '<option value="' . $value['id'] . '">' . $value['name'] . (!empty($value['number']) ? ' #' . $value['number'] : '') . '</option>';
	//
	// 		$opt_opportunity_areas = '';
	//
	// 		foreach ($this->model->get_opportunity_areas() as $value)
	// 			$opt_opportunity_areas .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 		$opt_locations = '';
	//
	// 		foreach ($this->model->get_locations() as $value)
	// 			$opt_locations .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';
	//
	// 		$cbx_opportunity_areas = '';
	//
	// 		foreach ($this->model->get_opportunity_areas() as $value)
	// 		{
	// 			$cbx_opportunity_areas .=
	// 			'<div>
	// 				<input type="checkbox" name="opportunity_areas[]" value="' . $value['id'] . '">
	// 				<span>' . $value['name'][$this->lang] . '</span>
	// 			</div>';
	// 		}
	//
	// 		$cbx_vox_report_fields = '';
	//
	// 		foreach ($this->model->get_vox_report_fields() as $value)
	// 		{
	// 			$cbx_vox_report_fields .=
	// 			'<div>
	// 				<input type="checkbox" name="fields[]" value="' . $value['id'] . '">
	// 				<span>{$lang.' . $value['name'] . '}</span>
	// 			</div>';
	// 		}
	//
	// 		$tbl_voxes_reports = '';
	// 		$opt_voxes_reports = '';
	//
	// 		foreach ($this->model->get_voxes_reports() as $value)
	// 		{
	// 			if ($params[0] == 'saved')
	// 			{
	// 				$tbl_voxes_reports .=
	// 				'<tr>
	// 					<td align="left">' . $value['name'] . '</td>
	// 					' . ((Functions::check_user_access(['{voxes_reports_delete}']) == true) ? '<td align="right" class="icon"><a data-action="delete_vox_report" data-id="' . $value['id'] . '" class="delete"><i class="fas fa-trash"></i></a></td>' : '') . '
	// 					' . ((Functions::check_user_access(['{voxes_reports_update}']) == true) ? '<td align="right" class="icon"><a data-action="edit_vox_report" data-id="' . $value['id'] . '" class="edit"><i class="fas fa-pen"></i></a></td>' : '') . '
	// 				</tr>';
	// 			}
	// 			else if ($params[0] == 'generate')
	// 				$opt_voxes_reports .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
	// 		}
	//
	// 		$replace = [
	// 			'{$opt_opportunity_areas}' => $opt_opportunity_areas,
	// 			'{$opt_owners}' => $opt_owners,
	// 			'{$opt_locations}' => $opt_locations,
	// 			'{$cbx_opportunity_areas}' => $cbx_opportunity_areas,
	// 			'{$cbx_vox_report_fields}' => $cbx_vox_report_fields,
	// 			'{$tbl_voxes_reports}' => $tbl_voxes_reports,
	// 			'{$opt_voxes_reports}' => $opt_voxes_reports
	// 		];
	//
	// 		$template = $this->format->replace($replace, $template);
	//
	// 		echo $template;
	// 	}
	// }
	//
	// public function stats()
	// {
	// 	if (Format::exist_ajax_request() == true)
	// 	{
	// 		if ($_POST['action'] == 'get_v_chart_data')
	// 		{
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'data' => [
	// 					'oa' => $this->model->get_chart_data('v_oa_chart', $_POST, true),
	// 					'o' => $this->model->get_chart_data('v_o_chart', $_POST, true),
	// 					'l' => $this->model->get_chart_data('v_l_chart', $_POST, true)
	// 				]
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_ar_chart_data')
	// 		{
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'data' => [
	// 					'oa' => $this->model->get_chart_data('ar_oa_chart', $_POST, true),
	// 					'o' => $this->model->get_chart_data('ar_o_chart', $_POST, true),
	// 					'l' => $this->model->get_chart_data('ar_l_chart', $_POST, true)
	// 				]
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_c_chart_data')
	// 		{
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'data' => [
	// 					'oa' => $this->model->get_chart_data('c_oa_chart', $_POST, true),
	// 					'o' => $this->model->get_chart_data('c_o_chart', $_POST, true),
	// 					'l' => $this->model->get_chart_data('c_l_chart', $_POST, true)
	// 				]
	// 			]);
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$template = $this->view->render($this, 'stats');
	//
	// 		define('_title', 'Guestvox | {$lang.voxes_stats}');
	//
	// 		$replace = [
	// 			'{$voxes_average_resolution}' => $this->model->get_voxes_average_resolution(),
	// 			'{$voxes_today}' => $this->model->get_voxes_count('today'),
	// 			'{$voxes_week}' => $this->model->get_voxes_count('week'),
	// 			'{$voxes_month}' => $this->model->get_voxes_count('month'),
	// 			'{$voxes_year}' => $this->model->get_voxes_count('year'),
	// 			'{$voxes_total}' => $this->model->get_voxes_count('total')
	// 		];
	//
	// 		$template = $this->format->replace($replace, $template);
	//
	// 		echo $template;
	// 	}
	// }
	//
	// public function charts()
	// {
	// 	header('Content-Type: application/javascript');
	//
	// 	if ($this->lang == 'es')
	// 	{
	// 		$v_oa_chart_data_title = 'Voxes por áreas de oportunidad';
	// 		$v_o_chart_data_title = 'Voxes por propietario';
	// 		$v_l_chart_data_title = 'Voxes por ubicación';
	// 		$ar_oa_chart_data_title = 'Tiempo de resolución por áreas de oportunidad';
	// 		$ar_o_chart_data_title = 'Tiempo de resolución por propietario';
	// 		$ar_l_chart_data_title = 'Tiempo de resolución por ubicación';
	// 		$c_oa_chart_data_title = 'Costos por áreas de oportunidad';
	// 		$c_o_chart_data_title = 'Costos por propietario';
	// 		$c_l_chart_data_title = 'Costos por ubicación';
	// 	}
	// 	else if ($this->lang == 'en')
	// 	{
	// 		$v_oa_chart_data_title = 'Voxes by opportunity areas';
	// 		$v_o_chart_data_title = 'Voxes by owner';
	// 		$v_l_chart_data_title = 'Voxes by location';
	// 		$ar_oa_chart_data_title = 'Resolution average by opportunity areas';
	// 		$ar_o_chart_data_title = 'Resolution average by owner';
	// 		$ar_l_chart_data_title = 'Resolution average by location';
	// 		$c_oa_chart_data_title = 'Costs by opportunity areas';
	// 		$c_o_chart_data_title = 'Costs by owner';
	// 		$c_l_chart_data_title = 'Costs by location';
	// 	}
	//
	// 	$v_oa_chart_data = $this->model->get_chart_data('v_oa_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date(),
	// 		'type' => 'all'
	// 	]);
	//
	// 	$v_o_chart_data = $this->model->get_chart_data('v_o_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date(),
	// 		'type' => 'all'
	// 	]);
	//
	// 	$v_l_chart_data = $this->model->get_chart_data('v_l_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date(),
	// 		'type' => 'all'
	// 	]);
	//
	// 	$ar_oa_chart_data = $this->model->get_chart_data('ar_oa_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date(),
	// 		'type' => 'all'
	// 	]);
	//
	// 	$ar_o_chart_data = $this->model->get_chart_data('ar_o_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date(),
	// 		'type' => 'all'
	// 	]);
	//
	// 	$ar_l_chart_data = $this->model->get_chart_data('ar_l_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date(),
	// 		'type' => 'all'
	// 	]);
	//
	// 	$c_oa_chart_data = $this->model->get_chart_data('c_oa_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date()
	// 	]);
	//
	// 	$c_o_chart_data = $this->model->get_chart_data('c_o_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date()
	// 	]);
	//
	// 	$c_l_chart_data = $this->model->get_chart_data('c_l_chart', [
	// 		'started_date' => Functions::get_past_date(Functions::get_current_date(), '7', 'days'),
	// 		'date_end' => Functions::get_current_date()
	// 	]);
	//
	// 	$js =
	// 	"'use strict';
	//
	// 	var v_oa_chart = {
	//         type: 'pie',
	//         data: {
	// 			labels: [
	//                 " . $v_oa_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $v_oa_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $v_oa_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $v_oa_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var v_o_chart = {
	//         type: 'pie',
	//         data: {
	// 			labels: [
	//                 " . $v_o_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $v_o_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $v_o_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $v_o_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var v_l_chart = {
	//         type: 'pie',
	//         data: {
	// 			labels: [
	//                 " . $v_l_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $v_l_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $v_l_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $v_l_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var ar_oa_chart = {
	//         type: 'horizontalBar',
	//         data: {
	// 			labels: [
	//                 " . $ar_oa_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $ar_oa_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $ar_oa_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $ar_oa_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var ar_o_chart = {
	//         type: 'pie',
	//         data: {
	// 			labels: [
	//                 " . $ar_o_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $ar_o_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $ar_o_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $ar_o_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var ar_l_chart = {
	//         type: 'pie',
	//         data: {
	// 			labels: [
	//                 " . $ar_l_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $ar_l_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $ar_l_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $ar_l_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var c_oa_chart = {
	//         type: 'pie',
	//         data: {
	// 			labels: [
	//                 " . $c_oa_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $c_oa_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $c_oa_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $c_oa_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var c_o_chart = {
	//         type: 'pie',
	//         data: {
	// 			labels: [
	//                 " . $c_o_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $c_o_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $c_o_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $c_o_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var c_l_chart = {
	//         type: 'pie',
	//         data: {
	// 			labels: [
	//                 " . $c_l_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	//                 data: [
	//                     " . $c_l_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $c_l_chart_data['datasets']['colors'] . "
	//                 ],
	//             }],
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				text: '" . $c_l_chart_data_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	window.onload = function()
	// 	{
	// 		v_oa_chart = new Chart(document.getElementById('v_oa_chart').getContext('2d'), v_oa_chart);
	// 		v_o_chart = new Chart(document.getElementById('v_o_chart').getContext('2d'), v_o_chart);
	// 		v_l_chart = new Chart(document.getElementById('v_l_chart').getContext('2d'), v_l_chart);
	// 		ar_oa_chart = new Chart(document.getElementById('ar_oa_chart').getContext('2d'), ar_oa_chart);
	// 		ar_o_chart = new Chart(document.getElementById('ar_o_chart').getContext('2d'), ar_o_chart);
	// 		ar_l_chart = new Chart(document.getElementById('ar_l_chart').getContext('2d'), ar_l_chart);
	// 		c_oa_chart = new Chart(document.getElementById('c_oa_chart').getContext('2d'), c_oa_chart);
	// 		c_o_chart = new Chart(document.getElementById('c_o_chart').getContext('2d'), c_o_chart);
	// 		c_l_chart = new Chart(document.getElementById('c_l_chart').getContext('2d'), c_l_chart);
	// 	};";
	//
	// 	$js = trim(str_replace(array("\t\t\t"), '', $js));
	//
	// 	echo $js;
	// }
}
