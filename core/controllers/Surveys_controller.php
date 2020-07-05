<?php

defined('_EXEC') or die;

class Surveys_controller extends Controller
{
	private $lang;

	public function __construct()
	{
		parent::__construct();

		$this->lang = Session::get_value('account')['language'];
	}

	public function questions()
	{
		if (Format::exist_ajax_request() == true)
		{
			if ($_POST['action'] == 'get_survey_question')
			{
				$query = $this->model->get_survey_question($_POST['id']);

                if (!empty($query))
                {
                    Functions::environment([
    					'status' => 'success',
    					'data' => $query
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

			if ($_POST['action'] == 'new_survey_question' OR $_POST['action'] == 'edit_survey_question')
			{
				$labels = [];

				if (!isset($_POST['name_es']) OR empty($_POST['name_es']))
					array_push($labels, ['name_es','']);

				if (!isset($_POST['name_en']) OR empty($_POST['name_en']))
					array_push($labels, ['name_en','']);

				if (!isset($_POST['type']) OR empty($_POST['type']))
					array_push($labels, ['type','']);

				if ($_POST['type'] == 'check')
				{
					$_POST['values'] = json_decode($_POST['values'], true);

					if (!isset($_POST['values']) OR empty($_POST['values']))
					{
						array_push($labels, ['value_es','']);
						array_push($labels, ['value_en','']);
					}
				}

				if (empty($labels))
				{
					if ($_POST['action'] == 'new_survey_question')
						$query = $this->model->new_survey_question($_POST);
					else if ($_POST['action'] == 'edit_survey_question')
						$query = $this->model->edit_survey_question($_POST);

					if (!empty($query))
					{
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
				else
				{
					Functions::environment([
						'status' => 'error',
						'labels' => $labels
					]);
				}
			}

			if ($_POST['action'] == 'deactivate_survey_question' OR $_POST['action'] == 'activate_survey_question' OR $_POST['action'] == 'delete_survey_question')
			{
				if ($_POST['action'] == 'deactivate_survey_question')
					$query = $this->model->deactivate_survey_question($_POST['id']);
				else if ($_POST['action'] == 'activate_survey_question')
					$query = $this->model->activate_survey_question($_POST['id']);
				else if ($_POST['action'] == 'delete_survey_question')
					$query = $this->model->delete_survey_question($_POST['id']);

				if (!empty($query))
				{
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
		}
		else
		{
			$template = $this->view->render($this, 'questions');

			define('_title', 'Guestvox | {$lang.surveys_questions}');

			$tbl_surveys_questions = '';

			foreach ($this->model->get_surveys_questions() as $value)
			{
				$tbl_surveys_questions .=
				'<div>
					<div data-level="1">
						<h2>' . $value['name'][$this->lang] . '</h2>
						<div class="' . $value['type'] . '">';

				if ($value['type'] == 'nps')
				{
					$tbl_surveys_questions .=
					'<div>
						<label><i>1</i><input type="radio" disabled></label>
					   	<label><i>2</i><input type="radio" disabled></label>
					   	<label><i>3</i><input type="radio" disabled></label>
					   	<label><i>4</i><input type="radio" disabled></label>
					   	<label><i>5</i><input type="radio" disabled></label>
					</div>
				   	<div>
						<label><i>6</i><input type="radio" disabled></label>
						<label><i>7</i><input type="radio" disabled></label>
						<label><i>8</i><input type="radio" disabled></label>
						<label><i>9</i><input type="radio" disabled></label>
						<label><i>10</i><input type="radio" disabled></label>
					</div>';
				}
				else if ($value['type'] == 'open')
					$tbl_surveys_questions .= '<input type="text" disabled>';
				else if ($value['type'] == 'rate')
				{
					$tbl_surveys_questions .=
					'<label><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
					<label><i class="fas fa-frown"></i><input type="radio" disabled></label>
					<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
					<label><i class="fas fa-smile"></i><input type="radio" disabled></label>
					<label><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
				}
				else if ($value['type'] == 'twin')
				{
					$tbl_surveys_questions .=
					'<label><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>
					<label><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>';
				}
				else if ($value['type'] == 'check')
				{
					$tbl_surveys_questions .= '<div class="checkboxes stl_3">';

					foreach ($value['values'] as $subvalue)
					{
						$tbl_surveys_questions .=
						'<div>
							<input type="checkbox" disabled>
							<span>' . $subvalue[$this->lang] . '</span>
						</div>';
					}

					$tbl_surveys_questions .= '</div>';
				}

				$tbl_surveys_questions .= '</div>';

				if ($value['system'] == false)
				{
					$tbl_surveys_questions .=
					'<div class="buttons">
						' . ((Functions::check_user_access(['{surveys_questions_deactivate}','{surveys_questions_activate}']) == true) ? '<a data-action="' . (($value['status'] == true) ? 'deactivate_survey_question' : 'activate_survey_question') . '" data-id="' . $value['id'] . '">' . (($value['status'] == true) ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>') . '</a>' : '') . '
						' . ((Functions::check_user_access(['{surveys_questions_update}']) == true) ? '<a class="edit" data-action="edit_survey_question" data-id="' . $value['id'] . '"><i class="fas fa-pen"></i></a>' : '') . '
						' . ((Functions::check_user_access(['{surveys_questions_delete}']) == true) ? '<a class="delete" data-action="delete_survey_question" data-id="' . $value['id'] . '"><i class="fas fa-trash"></i></a>' : '') . '
					</div>';
				}

				$tbl_surveys_questions .= '</div>';

				foreach ($this->model->get_surveys_questions('all', $value['id']) as $subvalue)
				{
					$tbl_surveys_questions .=
					'<div data-level="2">
						<h2>' . $subvalue['name'][$this->lang] . '</h2>
						<div class="' . $subvalue['type'] . '">';

					if ($subvalue['type'] == 'open')
						$tbl_surveys_questions .= '<input type="text" disabled>';
					else if ($subvalue['type'] == 'rate')
					{
						$tbl_surveys_questions .=
						'<label><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
						<label><i class="fas fa-frown"></i><input type="radio" disabled></label>
						<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
						<label><i class="fas fa-smile"></i><input type="radio" disabled></label>
						<label><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
					}
					else if ($subvalue['type'] == 'twin')
					{
						$tbl_surveys_questions .=
						'<label><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>
						<label><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>';
					}
					else if ($subvalue['type'] == 'check')
					{
						$tbl_surveys_questions .= '<div class="checkboxes stl_3">';

						foreach ($subvalue['values'] as $parentvalue)
						{
							$tbl_surveys_questions .=
							'<div>
								<input type="checkbox" disabled>
								<span>' . $parentvalue[$this->lang] . '</span>
							</div>';
						}

						$tbl_surveys_questions .= '</div>';
					}

					$tbl_surveys_questions .= '</div>';

					if ($subvalue['system'] == false)
					{
						$tbl_surveys_questions .=
						'<div class="buttons">
							' . ((Functions::check_user_access(['{surveys_questions_deactivate}','{surveys_questions_activate}']) == true) ? '<a data-action="' . (($subvalue['status'] == true) ? 'deactivate_survey_question' : 'activate_survey_question') . '" data-id="' . $subvalue['id'] . '">' . (($subvalue['status'] == true) ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>') . '</a>' : '') . '
							' . ((Functions::check_user_access(['{surveys_questions_update}']) == true) ? '<a class="edit" data-action="edit_survey_question" data-id="' . $subvalue['id'] . '"><i class="fas fa-pen"></i></a>' : '') . '
							' . ((Functions::check_user_access(['{surveys_questions_delete}']) == true) ? '<a class="delete" data-action="delete_survey_question" data-id="' . $subvalue['id'] . '"><i class="fas fa-trash"></i></a>' : '') . '
						</div>';
					}

					$tbl_surveys_questions .= '</div>';

					foreach ($this->model->get_surveys_questions('all', $subvalue['id']) as $parentvalue)
					{
						$tbl_surveys_questions .=
						'<div data-level="3">
							<h2>' . $parentvalue['name'][$this->lang] . '</h2>
							<div class="' . $parentvalue['type'] . '">';

						if ($parentvalue['type'] == 'open')
							$tbl_surveys_questions .= '<input type="text" disabled>';
						else if ($parentvalue['type'] == 'rate')
						{
							$tbl_surveys_questions .=
							'<label><i class="fas fa-sad-cry"></i><input type="radio" disabled></label>
							<label><i class="fas fa-frown"></i><input type="radio" disabled></label>
							<label><i class="fas fa-meh-rolling-eyes"></i><input type="radio" disabled></label>
							<label><i class="fas fa-smile"></i><input type="radio" disabled></label>
							<label><i class="fas fa-grin-stars"></i><input type="radio" disabled></label>';
						}
						else if ($parentvalue['type'] == 'twin')
						{
							$tbl_surveys_questions .=
							'<label><i class="fas fa-thumbs-down"></i><input type="radio" disabled></label>
							<label><i class="fas fa-thumbs-up"></i><input type="radio" disabled></label>';
						}
						else if ($parentvalue['type'] == 'check')
						{
							$tbl_surveys_questions .= '<div class="checkboxes stl_3">';

							foreach ($parentvalue['values'] as $childvalue)
							{
								$tbl_surveys_questions .=
								'<div>
									<input type="checkbox" disabled>
									<span>' . $childvalue[$this->lang] . '</span>
								</div>';
							}

							$tbl_surveys_questions .= '</div>';
						}

						$tbl_surveys_questions .= '</div>';

						if ($parentvalue['system'] == false)
						{
							$tbl_surveys_questions .=
							'<div class="buttons">
								' . ((Functions::check_user_access(['{surveys_questions_deactivate}','{surveys_questions_activate}']) == true) ? '<a data-action="' . (($parentvalue['status'] == true) ? 'deactivate_survey_question' : 'activate_survey_question') . '" data-id="' . $parentvalue['id'] . '">' . (($parentvalue['status'] == true) ? '<i class="fas fa-ban"></i>' : '<i class="fas fa-check"></i>') . '</a>' : '') . '
								' . ((Functions::check_user_access(['{surveys_questions_update}']) == true) ? '<a class="edit" data-action="edit_survey_question" data-id="' . $parentvalue['id'] . '"><i class="fas fa-pen"></i></a>' : '') . '
								' . ((Functions::check_user_access(['{surveys_questions_delete}']) == true) ? '<a class="delete" data-action="delete_survey_question" data-id="' . $parentvalue['id'] . '"><i class="fas fa-trash"></i></a>' : '') . '
							</div>';
						}

						$tbl_surveys_questions .= '</div>';
					}
				}

				$tbl_surveys_questions .= '</div>';
			}

			$opt_surveys_questions = '';

			foreach ($this->model->get_surveys_questions('actives') as $value)
			{
				$opt_surveys_questions .= '<option value="' . $value['id'] . '">' . $value['name'][$this->lang] . '</option>';

				foreach ($this->model->get_surveys_questions('actives', $value['id']) as $subvalue)
					$opt_surveys_questions .= '<option value="' . $subvalue['id'] . '">- ' . $subvalue['name'][$this->lang] . '</option>';
			}

			$replace = [
				'{$tbl_surveys_questions}' => $tbl_surveys_questions,
				'{$opt_surveys_questions}' => $opt_surveys_questions
			];

			$template = $this->format->replace($replace, $template);

			echo $template;
		}
	}

	// public function answers()
	// {
	// 	if (Format::exist_ajax_request() == true)
	// 	{
	// 		if ($_POST['action'] == 'get_filter_survey_answer')
	// 		{
	// 			$query = $this->model->get_survey_answers($_POST['owner'], [$_POST['started_date'], $_POST['end_date']]);
	//
	// 			$data = '';
	//
	// 			if (!empty($query))
	// 			{
	// 				$data = '';
	// 				$data_count = 0;
	//
	// 				foreach ($query as $value)
	// 				{
	// 					$value['count'] = $value['count'] - $data_count;
	//
	// 					$data .=
	// 					'<tr>
	// 						<td align="left">' . $value['count'] . '</td>
	// 						<td align="left">' . $value['token'] . '</td>';
	//
	// 					if (Session::get_value('account')['type'] == 'hotel')
	// 						$data .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 					if (Session::get_value('account')['type'] == 'restaurant')
	// 						$data .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 					if (Session::get_value('account')['type'] == 'others')
	// 						$data .= '<td align="left">' . (!empty($value['owner']) ? $value['owner']['name'] : '') . '</td>';
	//
	// 					$data .=
	// 					'	<td align="left">' . ((Session::get_value('account')['zaviapms']['status'] == true AND !empty($value['guest']['zaviapms']['firstname']) AND !empty($value['guest']['zaviapms']['lastname'])) ? $value['guest']['zaviapms']['firstname'] . ' ' . $value['guest']['zaviapms']['lastname'] : $value['guest']['guestvox']['firstname'] . ' ' . $value['guest']['guestvox']['lastname']) . '</td>
	// 						<td align="left">' . Functions::get_formatted_date($value['date'], 'd.m.Y') . '</td>
	// 						<td align="left"><i class="fas fa-star" style="margin-right:5px;color:#ffeb3b;"></i>' . $value['rate'] . '</td>
	// 						<td align="right" class="icon"><a data-action="view_survey_answer" data-id="' . $value['id'] . '"><i class="fas fa-bars"></i></a></td>
	// 					</tr>';
	//
	// 					$data_count = $data_count + 1;
	// 				}
	//
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'data' => $data
	// 				]);
	// 			}
	// 			else
	// 			{
	// 				Functions::environment([
	// 					'status' => 'error',
	// 					'data' => $data
	// 				]);
	// 			}
	// 		}
	//
	// 		if ($_POST['action'] == 'get_survey_answer')
	// 		{
	// 			$query = $this->model->get_survey_answer($_POST['id']);
	//
	// 			if (!empty($query))
	// 			{
	// 				$data = '<span><strong>{$lang.token}:</strong> ' . $query['token']  . '</span>';
	//
	// 				if (Session::get_value('account')['type'] == 'hotel')
	// 					$data .= '<span><strong>{$lang.owner}:</strong> ' . (!empty($query['owner']) ? '#' . $query['owner']['number'] . ' ' . $query['owner']['name'] : '') . '</span>';
	//
	// 				if (Session::get_value('account')['type'] == 'restaurant')
	// 					$data .= '<span><strong>{$lang.owner}:</strong> ' . (!empty($query['owner']) ? '#' . $query['owner']['number'] . ' ' . $query['owner']['name'] : '') . '</span>';
	//
	// 				if (Session::get_value('account')['type'] == 'others')
	// 					$data .= '<span><strong>{$lang.owner}:</strong> ' . (!empty($query['owner']) ? $query['owner']['name'] : '') . '</span>';
	//
	// 				$data .=
	// 				'<div>
	// 					<h2>{$lang.survey_datas}</h2>
	// 					<span><strong>{$lang.guest}:</strong> ' . $query['guest']['guestvox']['firstname'] . ' ' . $query['guest']['guestvox']['lastname'] . '</span>
	// 					<span><strong>{$lang.email}:</strong> ' . $query['guest']['guestvox']['email'] . '</span>
	// 					<span><strong>{$lang.phone}:</strong> ' . $query['guest']['guestvox']['phone']['lada'] . ' ' . $query['guest']['guestvox']['phone']['number'] . '</span>
	// 					<span><strong>{$lang.date}:</strong> ' . Functions::get_formatted_date($query['date'], 'd.m.Y')  . '</span>
	// 					<span><strong>{$lang.rate}:</strong> <i class="fas fa-star" style="margin-right:5px;color:#ffeb3b;"></i>' . $query['rate']  . '</span>
	// 				</div>';
	//
	// 				if (Session::get_value('account')['zaviapms']['status'] == true)
	// 				{
	// 					$data .=
	// 					'<div>
	// 						<h2>{$lang.zaviapms_datas}</h2>
	// 						<span><strong>{$lang.guest}:</strong> ' . $query['guest']['zaviapms']['firstname'] . ' ' . $query['guest']['zaviapms']['lastname'] . '</span>
	// 						<span><strong>{$lang.reservation_number}:</strong> ' . $query['guest']['zaviapms']['reservation_number'] . '</span>
	// 						<span><strong>{$lang.check_in}:</strong> ' . Functions::get_formatted_date($query['guest']['zaviapms']['check_in'], 'd.m.Y') . '</span>
	// 						<span><strong>{$lang.check_out}:</strong> ' . Functions::get_formatted_date($query['guest']['zaviapms']['check_out'], 'd.m.Y') . '</span>
	// 						<span><strong>{$lang.nationality}:</strong> ' . $query['guest']['zaviapms']['nationality'] . '</span>
	// 						<span><strong>{$lang.input_channel}:</strong> ' . $query['guest']['zaviapms']['input_channel'] . '</span>
	// 						<span><strong>{$lang.traveler_type}:</strong> ' . $query['guest']['zaviapms']['traveler_type'] . '</span>
	// 						<span><strong>{$lang.age_group}:</strong> ' . $query['guest']['zaviapms']['age_group'] . '</span>
	// 					</div>';
	// 				}
	//
	// 				foreach ($query['answers'] as $value)
	//                 {
	// 					$data .=
	// 					'<article>
	// 						<h6>' . $value['question'][Session::get_value('account')['language']] . '</h6>
	// 						<div>';
	//
	// 					if ($value['type'] == 'nps')
	// 					{
	// 						$data .=
	// 						'<label><span>1</i><input type="radio" ' . (($value['answer'] == 1) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>2</i><input type="radio" ' . (($value['answer'] == 2) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>3</i><input type="radio" ' . (($value['answer'] == 3) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>4</i><input type="radio" ' . (($value['answer'] == 4) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>5</i><input type="radio" ' . (($value['answer'] == 5) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>6</i><input type="radio" ' . (($value['answer'] == 6) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>7</i><input type="radio" ' . (($value['answer'] == 7) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>8</i><input type="radio" ' . (($value['answer'] == 8) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>9</i><input type="radio" ' . (($value['answer'] == 9) ? 'checked' : '') . ' disabled></label>
	// 					   <label><span>10</i><input type="radio" ' . (($value['answer'] == 10) ? 'checked' : '') . ' disabled></label>';
	// 					}
	//
	//                     if ($value['type'] == 'rate')
	// 					{
	// 						$data .=
	// 	                    '<label><i class="far fa-thumbs-down"></i></label>
    //                         <label><input type="radio" ' . (($value['answer'] == 1) ? 'checked' : '') . ' disabled></label>
    //                         <label><input type="radio" ' . (($value['answer'] == 2) ? 'checked' : '') . ' disabled></label>
    //                         <label><input type="radio" ' . (($value['answer'] == 3) ? 'checked' : '') . ' disabled></label>
    //                         <label><input type="radio" ' . (($value['answer'] == 4) ? 'checked' : '') . ' disabled></label>
    //                         <label><input type="radio" ' . (($value['answer'] == 5) ? 'checked' : '') . ' disabled></label>
    //                         <label><i class="far fa-thumbs-up"></i></label>';
	// 					}
	// 					else if ($value['type'] == 'twin')
	// 					{
	// 						$data .=
	// 						'<label>{$lang.to_yes}</label>
	// 						<label><input type="radio" ' . (($value['answer'] == 'yes') ? 'checked' : '') . ' disabled></label>
	// 						<label><input type="radio" ' . (($value['answer'] == 'no') ? 'checked' : '') . ' disabled></label>
	// 						<label>{$lang.to_not}</label>';
	// 					}
	// 					else if ($value['type'] == 'open')
	// 						$data .= '<p>' . $value['answer'] . '</p>';
	// 					else if ($value['type'] == 'check')
	// 					{
	// 						foreach ($value['values'] as $subkey => $subvalue)
	// 						{
	// 							$data .=
	// 							'<div class="checkboxes">
	// 								<input type="checkbox" ' . (in_array($subkey, $value['answer']) ? 'checked' : '') . ' disabled>
	// 								<span>' . $subvalue[Session::get_value('account')['language']] . '</span>
	// 							</div>';
	// 						}
	// 					}
	//
	// 					$data .= '</div>';
	//
	// 					if (!empty($value['subanswers']))
	// 					{
	// 						$data .= '<article>';
	//
	// 						foreach ($value['subanswers'] as $subkey => $subvalue)
	// 						{
	// 							$data .=
	// 							'<h6>' . $subvalue['question'][Session::get_value('account')['language']] . '</h6>
	// 							<div>';
	//
	// 							if ($subvalue['type'] == 'rate')
	// 							{
	// 								$data .=
	// 								'<label><i class="far fa-thumbs-down"></i></label>
	// 								<label><input type="radio" ' . (($subvalue['answer'] == 1) ? 'checked' : '') . ' disabled></label>
	// 								<label><input type="radio" ' . (($subvalue['answer'] == 2) ? 'checked' : '') . ' disabled></label>
	// 								<label><input type="radio" ' . (($subvalue['answer'] == 3) ? 'checked' : '') . ' disabled></label>
	// 								<label><input type="radio" ' . (($subvalue['answer'] == 4) ? 'checked' : '') . ' disabled></label>
	// 								<label><input type="radio" ' . (($subvalue['answer'] == 5) ? 'checked' : '') . ' disabled></label>
	// 								<label><i class="far fa-thumbs-up"></i></label>';
	// 							}
	// 							else if ($subvalue['type'] == 'twin')
	// 							{
	// 								$data .=
	// 								'<label>{$lang.to_yes}</label>
	// 								<label><input type="radio" ' . (($subvalue['answer'] == 'yes') ? 'checked' : '') . ' disabled></label>
	// 								<label><input type="radio" ' . (($subvalue['answer'] == 'no') ? 'checked' : '') . ' disabled></label>
	// 								<label>{$lang.to_not}</label>';
	// 							}
	// 							else if ($subvalue['type'] == 'open')
	// 								$data .= '<p>' . $subvalue['answer'] . '</p>';
	//
	// 							$data .= '</div>';
	//
	// 							if (!empty($subvalue['subanswers']))
	// 							{
	// 								$data .= '<article class="sub">';
	//
	// 								foreach ($subvalue['subanswers'] as $parentkey => $parentvalue)
	// 								{
	// 									$data .=
	// 									'<h6>' . $parentvalue['question'][Session::get_value('account')['language']] . '</h6>
	// 									<div>';
	//
	// 									if ($parentvalue['type'] == 'rate')
	// 									{
	// 										$data .=
	// 										'<label><i class="far fa-thumbs-down"></i></label>
	// 										<label><input type="radio" ' . (($parentvalue['answer'] == 1) ? 'checked' : '') . ' disabled></label>
	// 										<label><input type="radio" ' . (($parentvalue['answer'] == 2) ? 'checked' : '') . ' disabled></label>
	// 										<label><input type="radio" ' . (($parentvalue['answer'] == 3) ? 'checked' : '') . ' disabled></label>
	// 										<label><input type="radio" ' . (($parentvalue['answer'] == 4) ? 'checked' : '') . ' disabled></label>
	// 										<label><input type="radio" ' . (($parentvalue['answer'] == 5) ? 'checked' : '') . ' disabled></label>
	// 										<label><i class="far fa-thumbs-up"></i></label>';
	// 									}
	// 									else if ($parentvalue['type'] == 'twin')
	// 									{
	// 										$data .=
	// 										'<label>{$lang.to_yes}</label>
	// 										<label><input type="radio" ' . (($parentvalue['answer'] == 'yes') ? 'checked' : '') . ' disabled></label>
	// 										<label><input type="radio" ' . (($parentvalue['answer'] == 'no') ? 'checked' : '') . ' disabled></label>
	// 										<label>{$lang.to_not}</label>';
	// 									}
	// 									else if ($parentvalue['type'] == 'open')
	// 										$data .= '<p>' . $parentvalue['answer'] . '</p>';
	//
	// 									$data .= '</div>';
	// 								}
	//
	// 								$data .= '</article>';
	// 							}
	// 						}
	//
	// 						$data .= '</article>';
	// 					}
	//
	// 					$data .= '</article>';
	//                 }
	//
	// 				$data .=
	// 				'<div class="label">
	// 					<label>
	// 						<p>{$lang.comments}</p>
	// 						<textarea disabled>' . $query['comment'] . '</textarea>
	// 					</label>
	// 				</div>';
	//
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'data' => $data
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
	// 		define('_title', 'GuestVox');
	//
	// 		$template = $this->view->render($this, 'answers');
	//
	// 		$opt_owners = '';
	//
	// 		if (Session::get_value('account')['type'] == 'hotel')
	// 		{
	// 			foreach ($this->model->get_owners() as $value)
	// 			{
	// 				if ($value['status'] == true)
	// 					$opt_owners .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
	// 				else
	// 					$opt_owners .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';
	// 			}
	// 		}
	//
	// 		$tbl_survey_answers = '';
	// 		$tbl_survey_answers_count = 0;
	//
	// 		foreach ($this->model->get_survey_answers('', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]) as $value)
	// 		{
	// 			$value['count'] = $value['count'] - $tbl_survey_answers_count;
	//
	// 			$tbl_survey_answers .=
	// 			'<tr>
	// 				<td align="left">' . $value['count'] . '</td>
	// 				<td align="left">' . $value['token'] . '</td>';
	//
	// 			if (Session::get_value('account')['type'] == 'hotel')
	// 				$tbl_survey_answers .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 			if (Session::get_value('account')['type'] == 'restaurant')
	// 				$tbl_survey_answers .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 			if (Session::get_value('account')['type'] == 'others')
	// 				$tbl_survey_answers .= '<td align="left">' . (!empty($value['owner']) ? $value['owner']['name'] : '') . '</td>';
	//
	// 			$tbl_survey_answers .=
	// 			'	<td align="left">' . ((Session::get_value('account')['zaviapms']['status'] == true AND !empty($value['guest']['zaviapms']['firstname']) AND !empty($value['guest']['zaviapms']['lastname'])) ? $value['guest']['zaviapms']['firstname'] . ' ' . $value['guest']['zaviapms']['lastname'] : $value['guest']['guestvox']['firstname'] . ' ' . $value['guest']['guestvox']['lastname']) . '</td>
	// 				<td align="left">' . Functions::get_formatted_date($value['date'], 'd.m.Y') . '</td>
	// 				<td align="left"><i class="fas fa-star" style="margin-right:5px;color:#ffeb3b;"></i>' . $value['rate'] . '</td>
	// 				<td align="right" class="icon"><a data-action="view_survey_answer" data-id="' . $value['id'] . '"><i class="fas fa-bars"></i></a></td>
	// 			</tr>';
	//
	// 			$tbl_survey_answers_count = $tbl_survey_answers_count + 1;
	// 		}
	//
	// 		$replace = [
	// 			'{$opt_owners}' => $opt_owners,
	// 			'{$tbl_survey_answers}' => $tbl_survey_answers
	// 		];
	//
	// 		$template = $this->format->replace($replace, $template);
	//
	// 		echo $template;
	// 	}
	// }
	//
	// public function comments()
	// {
	// 	if (Format::exist_ajax_request() == true)
	// 	{
    //         if ($_POST['action'] == 'get_filter_survey_comments')
	// 		{
	// 			$query = $this->model->get_survey_answers($_POST['owner'], [$_POST['started_date'], $_POST['end_date']]);
	//
	// 			$data = '';
	//
	// 			if (!empty($query))
	// 			{
	// 				foreach ($query as $value)
	// 				{
	// 					if (!empty($value['comment']))
    //                     {
    //                             $data .=
    //                             '<tr>
    //                                     <td align="left">' . $value['token'] . '</td>';
	//
    //                             if (Session::get_value('account')['type'] == 'hotel')
    //                                     $data .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
    //                             if (Session::get_value('account')['type'] == 'restaurant')
    //                                     $data .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
    //                             if (Session::get_value('account')['type'] == 'others')
    //                                     $data .= '<td align="left">' . (!empty($value['owner']) ? $value['owner']['name'] : '') . '</td>';
	//
    //                             $data .=
    //                             '	<td align="left">' . ((Session::get_value('account')['zaviapms']['status'] == true AND !empty($value['guest']['zaviapms']['firstname']) AND !empty($value['guest']['zaviapms']['lastname'])) ? $value['guest']['zaviapms']['firstname'] . ' ' . $value['guest']['zaviapms']['lastname'] : $value['guest']['guestvox']['firstname'] . ' ' . $value['guest']['guestvox']['lastname']) . '</td>
    //                                     <td align="left" class="comment"> ' . $value['comment'] . '</td>
    //                                     <td align="right" class="icon">' . (($value['status'] == true) ? '<a data-action="deactivate_comment" data-id="' . $value['id'] . '"><i class="fas fa-ban"></i></a>' : '<a data-action="activate_comment" data-id="' . $value['id'] . '"><i class="fas fa-check"></i></a>') . '</td>
    //                             </tr>';
	//
    //                     }
	// 				}
	//
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'data' => $data
	// 				]);
	// 			}
	// 			else
	// 			{
	// 				Functions::environment([
	// 					'status' => 'error',
	// 					'data' => $data
	// 				]);
	// 			}
	// 		}
	//
	// 		if ($_POST['action'] == 'deactivate_comment')
	// 		{
	// 			$query = $this->model->deactivate_comment($_POST['id']);
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
	//
	// 		if ($_POST['action'] == 'activate_comment')
	// 		{
	// 			$query = $this->model->activate_comment($_POST['id']);
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
	// 		define('_title', 'GuestVox');
	//
	// 		$template = $this->view->render($this, 'comments');
	//
	// 		$opt_owners="";
	//
    //     	if (Session::get_value('account')['type'] == 'hotel')
	// 		{
	// 			foreach ($this->model->get_owners() as $value)
	// 			{
	// 				if ($value['status'] == true)
	// 					$opt_owners .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
	// 				else
	// 					$opt_owners .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';
	// 			}
	// 		}
	//
	// 		$tbl_survey_comments = '';
	//
	// 		foreach ($this->model->get_survey_answers('', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]) as $value)
	// 		{
	// 			if (!empty($value['comment']))
	// 			{
	// 				$tbl_survey_comments .=
	// 				'<tr>
	// 					<td align="left">' . $value['token'] . '</td>';
	//
	// 				if (Session::get_value('account')['type'] == 'hotel')
	// 					$tbl_survey_comments .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 				if (Session::get_value('account')['type'] == 'restaurant')
	// 					$tbl_survey_comments .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 				if (Session::get_value('account')['type'] == 'others')
	// 					$tbl_survey_comments .= '<td align="left">' . (!empty($value['owner']) ? $value['owner']['name'] : '') . '</td>';
	//
	// 				$tbl_survey_comments .=
	// 				'	<td align="left">' . ((Session::get_value('account')['zaviapms']['status'] == true AND !empty($value['guest']['zaviapms']['firstname']) AND !empty($value['guest']['zaviapms']['lastname'])) ? $value['guest']['zaviapms']['firstname'] . ' ' . $value['guest']['zaviapms']['lastname'] : $value['guest']['guestvox']['firstname'] . ' ' . $value['guest']['guestvox']['lastname']) . '</td>
	// 					<td align="left" class="comment"> ' . $value['comment'] . '</td>
	// 					<td align="right" class="icon">' . (($value['status'] == true) ? '<a data-action="deactivate_comment" data-id="' . $value['id'] . '"><i class="fas fa-ban"></i></a>' : '<a data-action="activate_comment" data-id="' . $value['id'] . '"><i class="fas fa-check"></i></a>') . '</td>
	// 				</tr>';
	//
	// 			}
	// 		}
	//
	// 		$replace = [
	// 			'{$opt_owners}' => $opt_owners,
	// 			'{$tbl_survey_comments}' => $tbl_survey_comments
	// 		];
	//
	// 		$template = $this->format->replace($replace, $template);
	//
	// 		echo $template;
	// 	}
	// }
	//
	// public function contacts()
	// {
	// 	if (Format::exist_ajax_request() == true)
	// 	{
	// 		if ($_POST['action'] == 'get_filter_survey_contacts')
	// 		{
	// 			$query = $this->model->get_survey_answers($_POST['owner'], []);
	//
	// 			if (!empty($query))
	// 			{
	// 				$data = '';
	//
	// 				foreach ($query as $value)
	// 				{
	// 					if (!empty($value['guest']['guestvox']['email']) AND !empty($value['guest']['guestvox']['phone']['number']))
	// 					{
	// 						$data .=
	// 						'<tr>
	// 							<td align="left">' . $value['token'] . '</td>';
	//
	// 						if (Session::get_value('account')['type'] == 'hotel')
	// 							$data .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 						if (Session::get_value('account')['type'] == 'restaurant')
	// 							$data .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 						if (Session::get_value('account')['type'] == 'others')
	// 							$data .= '<td align="left">' . (!empty($value['owner']) ? $value['owner']['name'] : '') . '</td>';
	//
	// 						$data .=
	// 						'	<td align="left">' . ((Session::get_value('account')['zaviapms']['status'] == true AND !empty($value['guest']['zaviapms']['firstname']) AND !empty($value['guest']['zaviapms']['lastname'])) ? $value['guest']['zaviapms']['firstname'] . ' ' . $value['guest']['zaviapms']['lastname'] : $value['guest']['guestvox']['firstname'] . ' ' . $value['guest']['guestvox']['lastname']) . '</td>
	// 							<td align="left"> ' . $value['guest']['guestvox']['email'] .  '</td>
	// 							<td align="left"> ' . $value['guest']['guestvox']['phone']['lada'] . $value['guest']['guestvox']['phone']['number'] .  '</td>
	// 						</tr>';
	// 					}
	// 				}
	//
	// 				Functions::environment([
	// 					'status' => 'success',
	// 					'data' => $data
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
	// 		define('_title', 'GuestVox');
	//
	// 		$template = $this->view->render($this, 'contacts');
	//
	// 		$opt_owners="";
	//
    //     	if (Session::get_value('account')['type'] == 'hotel')
	// 		{
	// 			foreach ($this->model->get_owners() as $value)
	// 			{
	// 				if ($value['status'] == true)
	// 					$opt_owners .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
	// 				else
	// 					$opt_owners .= '<option value="' . $value['id'] . '">#' . $value['number'] . ' ' . $value['name'] . '</option>';
	// 			}
	// 		}
	//
	// 		$tbl_survey_contacts = '';
	//
	// 		foreach ($this->model->get_survey_answers('', []) as $value)
	// 		{
	// 			if (!empty($value['guest']['guestvox']['email']) AND !empty($value['guest']['guestvox']['phone']['number']))
	// 			{
	// 				$tbl_survey_contacts .=
	// 				'<tr>
	// 					<td align="left">' . $value['token'] . '</td>';
	//
	// 				if (Session::get_value('account')['type'] == 'hotel')
	// 					$tbl_survey_contacts .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 				if (Session::get_value('account')['type'] == 'restaurant')
	// 					$tbl_survey_contacts .= '<td align="left">' . (!empty($value['owner']) ? '#' . $value['owner']['number'] . ' ' . $value['owner']['name'] : '') . '</td>';
	//
	// 				if (Session::get_value('account')['type'] == 'others')
	// 					$tbl_survey_contacts .= '<td align="left">' . (!empty($value['owner']) ? $value['owner']['name'] : '') . '</td>';
	//
	// 				$tbl_survey_contacts .=
	// 				'	<td align="left">' . ((Session::get_value('account')['zaviapms']['status'] == true AND !empty($value['guest']['zaviapms']['firstname']) AND !empty($value['guest']['zaviapms']['lastname'])) ? $value['guest']['zaviapms']['firstname'] . ' ' . $value['guest']['zaviapms']['lastname'] : $value['guest']['guestvox']['firstname'] . ' ' . $value['guest']['guestvox']['lastname']) . '</td>
	// 					<td align="left"> ' . $value['guest']['guestvox']['email'] .  '</td>
	// 					<td align="left"> ' . $value['guest']['guestvox']['phone']['lada'] . $value['guest']['guestvox']['phone']['number'] .  '</td>
	// 				</tr>';
	// 			}
	// 		}
	//
	// 		$replace = [
	// 			'{$opt_owners}' => $opt_owners,
	// 			'{$tbl_survey_contacts}' => $tbl_survey_contacts
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
	// 		if ($_POST['action'] == 'get_view_all')
	// 		{
	// 			$general_average_rate = $this->model->get_general_average_rate($_POST['action'], []);
	//
	// 			$h4_general_average_rate = '';
	//
	// 			if ($general_average_rate >= 0 AND $general_average_rate < 1.8)
	// 				$h4_general_average_rate = '<h4 style="color:#f44336;">' . $general_average_rate . '</h4>';
	// 			else if ($general_average_rate >= 1.8 AND $general_average_rate < 2.8)
	// 				$h4_general_average_rate = '<h4 style="color:#ffc107;">' . $general_average_rate . '</h4>';
	// 			else if ($general_average_rate >= 2.8 AND $general_average_rate < 3.8)
	// 				$h4_general_average_rate = '<h4 style="color:#ffeb3b;">' . $general_average_rate . '</h4>';
	// 			else if ($general_average_rate >= 3.8 AND $general_average_rate < 4.8)
	// 				$h4_general_average_rate = '<h4 style="color:#4caf50;">' . $general_average_rate . '</h4>';
	// 			else if ($general_average_rate >= 4.8 AND $general_average_rate <= 5)
	// 				$h4_general_average_rate = '<h4 style="color:#00a5ab;">' . $general_average_rate . '</h4>';
	//
	// 			$spn_general_avarage_rate =
	// 			'<span>
	// 				' . (($general_average_rate >= 0 AND $general_average_rate < 1.8) ? '<i class="fas fa-sad-cry" style="font-size:50px;color:#f44336;"></i>' : '<i class="far fa-sad-cry"></i>') . '
	// 				' . (($general_average_rate >= 1.8 AND $general_average_rate < 2.8) ? '<i class="fas fa-frown" style="font-size:50px;color:#ffc107;"></i>' : '<i class="far fa-frown"></i>') . '
	// 				' . (($general_average_rate >= 2.8 AND $general_average_rate < 3.8) ? '<i class="fas fa-meh-rolling-eyes" style="font-size:50px;color:#ffeb3b;"></i>' : '<i class="far fa-meh-rolling-eyes"></i>') . '
	// 				' . (($general_average_rate >= 3.8 AND $general_average_rate < 4.8) ? '<i class="fas fa-smile" style="font-size:50px;color:#4caf50;"></i>' : '<i class="far fa-smile"></i>') . '
	// 				' . (($general_average_rate >= 4.8 AND $general_average_rate <= 5) ? '<i class="fas fa-grin-stars" style="font-size:50px;color:#00a5ab;"></i>' : '<i class="far fa-grin-stars"></i>') . '
	// 			</span>';
	//
	// 			$rate_general = '';
	// 			$rate_general = $h4_general_average_rate . $spn_general_avarage_rate;
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'data' => [
	// 					'rate_general' => $rate_general,
	// 					'five_percentage_rate' => $this->model->get_percentage_rate($_POST['action'], 'five', []),
	// 					'four_percentage_rate' => $this->model->get_percentage_rate($_POST['action'], 'four', []),
	// 					'tree_percentage_rate' => $this->model->get_percentage_rate($_POST['action'], 'tree', []),
	// 					'two_percentage_rate' => $this->model->get_percentage_rate($_POST['action'], 'two', []),
	// 					'one_percentage_rate' => $this->model->get_percentage_rate($_POST['action'], 'one', []),
	// 					]
	// 			]);
	// 		}
	//
	// 		if ($_POST['action'] == 'get_charts_by_date_filter')
	// 		{
	// 			$general_average_rate = $this->model->get_general_average_rate('', [$_POST['started_date'], $_POST['end_date']]);
	//
	// 			$h4_general_average_rate = '';
	//
	// 			if ($general_average_rate >= 0 AND $general_average_rate < 1.8)
	// 				$h4_general_average_rate = '<h4 style="color:#f44336;">' . $general_average_rate . '</h4>';
	// 			else if ($general_average_rate >= 1.8 AND $general_average_rate < 2.8)
	// 				$h4_general_average_rate = '<h4 style="color:#ffc107;">' . $general_average_rate . '</h4>';
	// 			else if ($general_average_rate >= 2.8 AND $general_average_rate < 3.8)
	// 				$h4_general_average_rate = '<h4 style="color:#ffeb3b;">' . $general_average_rate . '</h4>';
	// 			else if ($general_average_rate >= 3.8 AND $general_average_rate < 4.8)
	// 				$h4_general_average_rate = '<h4 style="color:#4caf50;">' . $general_average_rate . '</h4>';
	// 			else if ($general_average_rate >= 4.8 AND $general_average_rate <= 5)
	// 				$h4_general_average_rate = '<h4 style="color:#00a5ab;">' . $general_average_rate . '</h4>';
	//
	// 			$spn_general_avarage_rate =
	// 			'<span>
	// 				' . (($general_average_rate >= 0 AND $general_average_rate < 1.8) ? '<i class="fas fa-sad-cry" style="font-size:50px;color:#f44336;"></i>' : '<i class="far fa-sad-cry"></i>') . '
	// 				' . (($general_average_rate >= 1.8 AND $general_average_rate < 2.8) ? '<i class="fas fa-frown" style="font-size:50px;color:#ffc107;"></i>' : '<i class="far fa-frown"></i>') . '
	// 				' . (($general_average_rate >= 2.8 AND $general_average_rate < 3.8) ? '<i class="fas fa-meh-rolling-eyes" style="font-size:50px;color:#ffeb3b;"></i>' : '<i class="far fa-meh-rolling-eyes"></i>') . '
	// 				' . (($general_average_rate >= 3.8 AND $general_average_rate < 4.8) ? '<i class="fas fa-smile" style="font-size:50px;color:#4caf50;"></i>' : '<i class="far fa-smile"></i>') . '
	// 				' . (($general_average_rate >= 4.8 AND $general_average_rate <= 5) ? '<i class="fas fa-grin-stars" style="font-size:50px;color:#00a5ab;"></i>' : '<i class="far fa-grin-stars"></i>') . '
	// 			</span>';
	//
	// 			$rate_general = '';
	// 			$rate_general = $h4_general_average_rate . $spn_general_avarage_rate;
	//
	// 			Functions::environment([
	// 				'status' => 'success',
	// 				'data' => [
	// 					'rate_general' => $rate_general,
	// 					'five_percentage_rate' => $this->model->get_percentage_rate('', 'five', [$_POST['started_date'], $_POST['end_date']]),
	// 					'four_percentage_rate' => $this->model->get_percentage_rate('', 'four', [$_POST['started_date'], $_POST['end_date']]),
	// 					'tree_percentage_rate' => $this->model->get_percentage_rate('', 'tree', [$_POST['started_date'], $_POST['end_date']]),
	// 					'two_percentage_rate' => $this->model->get_percentage_rate('', 'two', [$_POST['started_date'], $_POST['end_date']]),
	// 					'one_percentage_rate' => $this->model->get_percentage_rate('', 'one', [$_POST['started_date'], $_POST['end_date']]),
	// 					's1_chart_data' => $this->model->get_chart_data('s1_chart', [$_POST['started_date'], $_POST['end_date']], true),
	// 					's2_chart_data' => $this->model->get_chart_data('s2_chart', [$_POST['started_date'], $_POST['end_date'], $_POST['question']], true),
	// 					's5_chart_data' => $this->model->get_chart_data('s5_chart', [$_POST['started_date'], $_POST['end_date']], true),
	// 					's6_chart_data' => $this->model->get_chart_data('s6_chart', [$_POST['started_date'], $_POST['end_date']], true),
	// 					's7_chart_data' => $this->model->get_chart_data('s7_chart', [$_POST['started_date'], $_POST['end_date']], true),
	// 					's8_chart_data' => $this->model->get_chart_data('s8_chart', [$_POST['started_date'], $_POST['end_date']], true)
	// 				]
	// 			]);
	// 		}
	// 	}
	// 	else
	// 	{
	// 		define('_title', 'GuestVox');
	//
	// 		$template = $this->view->render($this, 'stats');
	//
	// 		$general_average_rate = $this->model->get_general_average_rate('', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]);
	//
	// 		$h4_general_average_rate = '';
	//
	// 		if ($general_average_rate >= 0 AND $general_average_rate < 1.8)
	// 			$h4_general_average_rate = '<h4 style="color:#f44336;">' . $general_average_rate . '</h4>';
	// 		else if ($general_average_rate >= 1.8 AND $general_average_rate < 2.8)
	// 			$h4_general_average_rate = '<h4 style="color:#ffc107;">' . $general_average_rate . '</h4>';
	// 		else if ($general_average_rate >= 2.8 AND $general_average_rate < 3.8)
	// 			$h4_general_average_rate = '<h4 style="color:#ffeb3b;">' . $general_average_rate . '</h4>';
	// 		else if ($general_average_rate >= 3.8 AND $general_average_rate < 4.8)
	// 			$h4_general_average_rate = '<h4 style="color:#4caf50;">' . $general_average_rate . '</h4>';
	// 		else if ($general_average_rate >= 4.8 AND $general_average_rate <= 5)
	// 			$h4_general_average_rate = '<h4 style="color:#00a5ab;">' . $general_average_rate . '</h4>';
	//
	// 		$spn_general_avarage_rate =
	// 		'<span>
	// 			' . (($general_average_rate >= 0 AND $general_average_rate < 1.8) ? '<i class="fas fa-sad-cry" style="font-size:50px;color:#f44336;"></i>' : '<i class="far fa-sad-cry"></i>') . '
	// 			' . (($general_average_rate >= 1.8 AND $general_average_rate < 2.8) ? '<i class="fas fa-frown" style="font-size:50px;color:#ffc107;"></i>' : '<i class="far fa-frown"></i>') . '
	// 			' . (($general_average_rate >= 2.8 AND $general_average_rate < 3.8) ? '<i class="fas fa-meh-rolling-eyes" style="font-size:50px;color:#ffeb3b;"></i>' : '<i class="far fa-meh-rolling-eyes"></i>') . '
	// 			' . (($general_average_rate >= 3.8 AND $general_average_rate < 4.8) ? '<i class="fas fa-smile" style="font-size:50px;color:#4caf50;"></i>' : '<i class="far fa-smile"></i>') . '
	// 			' . (($general_average_rate >= 4.8 AND $general_average_rate <= 5) ? '<i class="fas fa-grin-stars" style="font-size:50px;color:#00a5ab;"></i>' : '<i class="far fa-grin-stars"></i>') . '
	// 		</span>';
	//
	// 		$opt_surveys_questions = '';
	//
	// 		foreach ($this->model->get_surveys_questions(true) as $value)
	// 			$opt_surveys_questions .= '<option value="' . $value['id'] . '">' . $value['name'][Session::get_value('account')['language']] . '</option>';
	//
	// 		$replace = [
	// 			'{$general_nps}' => $this->model->get_survey_nps(),
	// 			'{$h4_general_average_rate}' => $h4_general_average_rate,
	// 			'{$spn_general_avarage_rate}' => $spn_general_avarage_rate,
	// 			'{$five_percentage_rate}' => $this->model->get_percentage_rate('', 'five', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]),
	// 			'{$four_percentage_rate}' => $this->model->get_percentage_rate('', 'four', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]),
	// 			'{$tree_percentage_rate}' => $this->model->get_percentage_rate('', 'tree', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]),
	// 			'{$two_percentage_rate}' => $this->model->get_percentage_rate('', 'two', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]),
	// 			'{$one_percentage_rate}' => $this->model->get_percentage_rate('', 'one', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]),
	// 			'{$count_answered_total}' => $this->model->get_count('answered_total'),
	// 			'{$count_answered_today}' => $this->model->get_count('answered_today'),
	// 			'{$count_answered_week}' => $this->model->get_count('answered_week'),
	// 			'{$count_answered_month}' => $this->model->get_count('answered_month'),
	// 			'{$count_answered_year}' => $this->model->get_count('answered_year'),
	// 			'{$opt_surveys_questions}' => $opt_surveys_questions
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
	// 	$s1_chart_data = $this->model->get_chart_data('s1_chart', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]);
	// 	$nps_chart_data = $this->model->get_chart_data('nps_chart', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date(), 'all']);
	// 	$s2_chart_data = $this->model->get_chart_data('s2_chart', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date(), 'all']);
	//
	// 	if (Session::get_value('account')['zaviapms']['status'] == true)
	// 	{
	// 		$s5_chart_data = $this->model->get_chart_data('s5_chart', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]);
	// 		$s6_chart_data = $this->model->get_chart_data('s6_chart', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]);
	// 		$s7_chart_data = $this->model->get_chart_data('s7_chart', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]);
	// 		$s8_chart_data = $this->model->get_chart_data('s8_chart', [Functions::get_past_date(Functions::get_current_date(), '7', 'days'), Functions::get_current_date()]);
	// 	}
	//
	// 	if (Session::get_value('account')['language'] == 'es')
	// 	{
	// 		if (Session::get_value('account')['type'] == 'hotel')
	// 			$s1_chart_title = 'Por habitación';
	// 		else if (Session::get_value('account')['type'] == 'restaurant')
	// 			$s1_chart_title = 'Por mesa';
	// 		else if (Session::get_value('account')['type'] == 'others')
	// 			$s1_chart_title = 'Por cliente';
	//
	// 		$nps_chart_title = 'NPS';
	// 		$s2_chart_title = 'Valoración';
	//
	// 		if (Session::get_value('account')['zaviapms']['status'] == true)
	// 		{
	// 			$s5_chart_title = 'Nacionalidad';
	// 			$s6_chart_title = 'Canal de entrada';
	// 			$s7_chart_title = 'Tipo de viajero';
	// 			$s8_chart_title = 'Grupo de edad';
	// 		}
	// 	}
	// 	else if (Session::get_value('account')['language'] == 'en')
	// 	{
	// 		if (Session::get_value('account')['type'] == 'hotel')
	// 			$s1_chart_title = 'Per owner';
	// 		else if (Session::get_value('account')['type'] == 'restaurant')
	// 			$s1_chart_title = 'Per owner';
	// 		else if (Session::get_value('account')['type'] == 'others')
	// 			$s1_chart_title = 'Per owner';
	//
	// 		$nps_chart_title = 'Rating';
	// 		$s2_chart_title = 'Rating';
	//
	// 		if (Session::get_value('account')['zaviapms']['status'] == true)
	// 		{
	// 			$s5_chart_title = 'Nationality';
	// 			$s6_chart_title = 'Input channel';
	// 			$s7_chart_title = 'Traveler';
	// 			$s8_chart_title = 'Age group';
	// 		}
	// 	}
	//
	// 	$js =
	// 	"'use strict';
	//
	// 	var s1_chart = {
	//         type: 'doughnut',
	//         data: {
	// 			labels: [
	//                 " . $s1_chart_data['labels'] . "
	//             ],
	// 			datasets: [{
	// 				data: [
	//                     " . $s1_chart_data['datasets']['data'] . "
	//                 ],
	//                 backgroundColor: [
	//                     " . $s1_chart_data['datasets']['colors'] . "
	//                 ]
	//             }]
	//         },
	//         options: {
	// 			title: {
	// 				display: true,
	// 				position: 'bottom',
	// 				text: '" . $s1_chart_title . "'
	// 			},
	// 			legend: {
	// 				display: false
	// 			},
	//             responsive: true
    //         }
    //     };
	//
	// 	var nps_chart = {
	// 	    type: 'bar',
	// 		data: {
	// 			labels: [
	//                 " . $nps_chart_data['labels'] . "
	//             ],
	// 			datasets: [
	// 				" . $nps_chart_data['datasets'] . "
	// 			]
	//         },
	// 		options: {
	// 			title: {
	// 				display: true,
	// 				position: 'top',
	// 				text: '" . $nps_chart_title . "'
	// 			},
	// 			legend: {
	// 				display: true
	// 			},
	// 			tooltips: {
	// 				mode: 'index',
	// 				intersect: false,
	// 			},
	// 			hover: {
	// 				mode: 'nearest',
	// 				intersect: true
	// 			},
	//             responsive: true
    //         }
	// 	};
	//
	// 	var s2_chart = {
	// 	    type: 'line',
	// 		data: {
	// 			labels: [
	//                 " . $s2_chart_data['labels'] . "
	//             ],
	// 			datasets: [
	// 				" . $s2_chart_data['datasets'] . "
	// 			]
	//         },
	// 		options: {
	// 			title: {
	// 				display: true,
	// 				position: 'top',
	// 				text: '" . $s2_chart_title . "'
	// 			},
	// 			legend: {
	// 				display: true
	// 			},
	// 			tooltips: {
	// 				mode: 'index',
	// 				intersect: false,
	// 			},
	// 			hover: {
	// 				mode: 'nearest',
	// 				intersect: true
	// 			},
	// 			elements: {
	// 				line: {
	// 					tension: 0
	// 				}
	// 			},
	//             responsive: true
    //         }
	// 	};";
	//
	// 	if (Session::get_value('account')['zaviapms']['status'] == true)
	// 	{
	// 		$js .=
	// 		"var s5_chart = {
	// 	        type: 'pie',
	// 	        data: {
	// 				labels: [
	// 	                " . $s5_chart_data['labels'] . "
	// 	            ],
	// 				datasets: [{
	// 					data: [
	// 	                    " . $s5_chart_data['datasets']['data'] . "
	// 	                ],
	// 	                backgroundColor: [
	// 	                    " . $s5_chart_data['datasets']['colors'] . "
	// 	                ]
	// 	            }]
	// 	        },
	// 	        options: {
	// 				title: {
	// 					display: true,
	// 					position: 'top',
	// 					text: '" . $s5_chart_title . "'
	// 				},
	// 				legend: {
	// 					display: false
	// 				},
	// 	            responsive: true
	//             }
	//         };
	//
	// 		var s6_chart = {
	// 	        type: 'pie',
	// 	        data: {
	// 				labels: [
	// 	                " . $s6_chart_data['labels'] . "
	// 	            ],
	// 				datasets: [{
	// 					data: [
	// 	                    " . $s6_chart_data['datasets']['data'] . "
	// 	                ],
	// 	                backgroundColor: [
	// 	                    " . $s6_chart_data['datasets']['colors'] . "
	// 	                ]
	// 	            }]
	// 	        },
	// 	        options: {
	// 				title: {
	// 					display: true,
	// 					position: 'top',
	// 					text: '" . $s6_chart_title . "'
	// 				},
	// 				legend: {
	// 					display: false
	// 				},
	// 	            responsive: true
	//             }
	//         };
	//
	// 		var s7_chart = {
	// 	        type: 'pie',
	// 	        data: {
	// 				labels: [
	// 	                " . $s7_chart_data['labels'] . "
	// 	            ],
	// 				datasets: [{
	// 					data: [
	// 	                    " . $s7_chart_data['datasets']['data'] . "
	// 	                ],
	// 	                backgroundColor: [
	// 	                    " . $s7_chart_data['datasets']['colors'] . "
	// 	                ]
	// 	            }]
	// 	        },
	// 	        options: {
	// 				title: {
	// 					display: true,
	// 					position: 'top',
	// 					text: '" . $s7_chart_title . "'
	// 				},
	// 				legend: {
	// 					display: false
	// 				},
	// 	            responsive: true
	//             }
	//         };
	//
	// 		var s8_chart = {
	// 	        type: 'pie',
	// 	        data: {
	// 				labels: [
	// 	                " . $s8_chart_data['labels'] . "
	// 	            ],
	// 				datasets: [{
	// 					data: [
	// 	                    " . $s8_chart_data['datasets']['data'] . "
	// 	                ],
	// 	                backgroundColor: [
	// 	                    " . $s8_chart_data['datasets']['colors'] . "
	// 	                ]
	// 	            }]
	// 	        },
	// 	        options: {
	// 				title: {
	// 					display: true,
	// 					position: 'top',
	// 					text: '" . $s8_chart_title . "'
	// 				},
	// 				legend: {
	// 					display: false
	// 				},
	// 	            responsive: true
	//             }
	//         };";
	// 	}
	//
	// 	$js .=
	// 	"window.onload = function()
	// 	{
	// 		s1_chart = new Chart(document.getElementById('s1_chart').getContext('2d'), s1_chart);
	// 		nps_chart = new Chart(document.getElementById('nps_chart').getContext('2d'), nps_chart);
	// 		s2_chart = new Chart(document.getElementById('s2_chart').getContext('2d'), s2_chart);";
	//
	// 		if (Session::get_value('account')['zaviapms']['status'] == true)
	// 		{
	// 			$js .=
	// 			"s5_chart = new Chart(document.getElementById('s5_chart').getContext('2d'), s5_chart);
	// 			s6_chart = new Chart(document.getElementById('s6_chart').getContext('2d'), s6_chart);
	// 			s7_chart = new Chart(document.getElementById('s7_chart').getContext('2d'), s7_chart);
	// 			s8_chart = new Chart(document.getElementById('s8_chart').getContext('2d'), s8_chart);";
	// 		}
	//
	// 	$js .=
	// 	"};";
	//
	// 	$js = trim(str_replace(array("\t\t\t"), '', $js));
	//
	// 	echo $js;
	// }
}
