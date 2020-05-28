<?php

defined('_EXEC') or die;

class Account_model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_account()
	{
		$query = Functions::get_json_decoded_query($this->database->select('accounts', [
			'token',
			'name',
			'path',
			'type',
			'country',
			'city',
			'zip_code',
			'address',
			'time_zone',
			'currency',
			'language',
			'fiscal',
			'contact',
			'logotype',
			'qr',
			'package',
			'operation',
			'reputation',
			'siteminder',
			'zaviapms',
			'sms',
			'settings'
		], [
			'id' => Session::get_value('account')['id']
		]));

		if (!empty($query))
		{
			$query[0]['package'] = $this->database->select('packages', [
				'quantity_end'
			], [
				'id' => $query[0]['package']
			]);

			if (!empty($query[0]['package']))
			{
				$query[0]['package'] = $query[0]['package'][0];

				return $query[0];
			}
			else
				return null;
		}
		else
			return null;
	}

	public function get_countries()
	{
		$query1 = Functions::get_json_decoded_query($this->database->select('countries', [
			'name',
			'code',
			'lada'
		], [
			'priority[>=]' => 1,
			'ORDER' => [
				'priority' => 'ASC'
			]
		]));

		$query2 = Functions::get_json_decoded_query($this->database->select('countries', [
			'name',
			'code',
			'lada'
		], [
			'priority[=]' => null,
			'ORDER' => [
				'name' => 'ASC'
			]
		]));

		return array_merge($query1, $query2);
	}

	public function get_times_zones()
	{
		$query = Functions::get_json_decoded_query($this->database->select('times_zones', [
			'code'
		], [
			'ORDER' => [
				'zone' => 'ASC'
			]
		]));

		return $query;
	}

	public function get_currencies()
	{
		$query1 = Functions::get_json_decoded_query($this->database->select('currencies', [
			'name',
			'code'
		], [
			'priority[>=]' => 1,
			'ORDER' => [
				'priority' => 'ASC'
			]
		]));

		$query2 = Functions::get_json_decoded_query($this->database->select('currencies', [
			'name',
			'code'
		], [
			'priority[=]' => null,
			'ORDER' => [
				'name' => 'ASC'
			]
		]));

		return array_merge($query1, $query2);
	}

	public function get_languages()
	{
		$query1 = $this->database->select('languages', [
			'name',
			'code'
		], [
			'priority[>=]' => 1,
			'ORDER' => [
				'priority' => 'ASC'
			]
		]);

		$query2 = $this->database->select('languages', [
			'name',
			'code'
		], [
			'priority[=]' => null,
			'ORDER' => [
				'name' => 'ASC'
			]
		]);

		return array_merge($query1, $query2);
	}

	public function check_exist_account($field, $value)
	{
		$count = $this->database->count('accounts', [
			'id[!]' => Session::get_value('account')['id'],
			$field => $value
		]);

		return ($count > 0) ? true : false;
	}

	public function edit_logotype($data)
	{
		$data['logotype'] = Functions::uploader($data['logotype']);

		$query = $this->database->update('accounts', [
			'logotype' => $data['logotype']
		], [
			'id' => Session::get_value('account')['id']
		]);

		if (!empty($query))
		{
			Functions::undoloader(Session::get_value('account')['logotype']);

			return $data['logotype'];
		}
		else
			return null;
	}

	public function edit_account($data)
	{
		$query = $this->database->update('accounts', [
			'name' => $data['name'],
			'country' => $data['country'],
			'city' => $data['city'],
			'zip_code' => $data['zip_code'],
			'address' => $data['address'],
			'time_zone' => $data['time_zone'],
			'currency' => $data['currency'],
			'language' => $data['language']
		], [
			'id' => Session::get_value('account')['id']
		]);

		return $query;
	}

	public function edit_billing($data)
	{
		$query = $this->database->update('accounts', [
			'fiscal' => json_encode([
				'id' => $data['fiscal_id'],
				'name' => $data['fiscal_name'],
				'address' => $data['fiscal_address']
			]),
			'contact' => json_encode([
				'firstname' => $data['contact_firstname'],
				'lastname' => $data['contact_lastname'],
				'department' => $data['contact_department'],
				'email' => $data['contact_email'],
				'phone' => [
					'lada' => $data['contact_phone_lada'],
					'number' => $data['contact_phone_number']
				]
			])
		], [
			'id' => Session::get_value('account')['id']
		]);

		return $query;
	}

	public function edit_settings($field, $data)
	{
		$edited1 = Functions::get_json_decoded_query($this->database->select('accounts', [
			'settings'
		], [
			'id' => Session::get_value('account')['id']
		]));

		$edited2 = $edited1[0]['settings'];

		if ($field == 'myvox')
		{
			$edited1[0]['settings']['myvox']['request']['status'] = !empty($data['request_status']) ? true : false;
			$edited1[0]['settings']['myvox']['incident']['status'] = !empty($data['incident_status']) ? true : false;
			$edited1[0]['settings']['myvox']['survey']['status'] = !empty($data['survey_status']) ? true : false;
			$edited1[0]['settings']['myvox']['survey']['title']['es'] = $data['survey_title_es'];
			$edited1[0]['settings']['myvox']['survey']['title']['en'] = $data['survey_title_en'];
			$edited1[0]['settings']['myvox']['survey']['mail']['subject']['es'] = $data['survey_mail_subject_es'];
			$edited1[0]['settings']['myvox']['survey']['mail']['subject']['en'] = $data['survey_mail_subject_en'];
			$edited1[0]['settings']['myvox']['survey']['mail']['description']['es'] = $data['survey_mail_description_es'];
			$edited1[0]['settings']['myvox']['survey']['mail']['description']['en'] = $data['survey_mail_description_en'];

			if (!empty($data['survey_mail_image']['name']))
				$edited1[0]['settings']['myvox']['survey']['mail']['image'] = Functions::uploader($data['survey_mail_image']);

			if (!empty($data['survey_mail_attachment']['name']))
				$edited1[0]['settings']['myvox']['survey']['mail']['attachment'] = Functions::uploader($data['survey_mail_attachment']);

			$edited1[0]['settings']['myvox']['survey']['widget'] = $data['survey_widget'];
		}
		else if ($field == 'reviews')
		{
			$edited1[0]['settings']['reviews']['status'] = !empty($data['status']) ? true : false;
			$edited1[0]['settings']['reviews']['email'] = $data['email'];
			$edited1[0]['settings']['reviews']['phone']['lada'] = $data['phone_lada'];
			$edited1[0]['settings']['reviews']['phone']['number'] = $data['phone_number'];
			$edited1[0]['settings']['reviews']['website'] = $data['website'];
			$edited1[0]['settings']['reviews']['description']['es'] = $data['description_es'];
			$edited1[0]['settings']['reviews']['description']['en'] = $data['description_en'];
			$edited1[0]['settings']['reviews']['seo']['keywords']['es'] = $data['seo_keywords_es'];
			$edited1[0]['settings']['reviews']['seo']['keywords']['en'] = $data['seo_keywords_en'];
			$edited1[0]['settings']['reviews']['seo']['description']['es'] = $data['seo_description_es'];
			$edited1[0]['settings']['reviews']['seo']['description']['en'] = $data['seo_description_en'];
			$edited1[0]['settings']['reviews']['social_media']['facebook'] = $data['social_media_facebook'];
			$edited1[0]['settings']['reviews']['social_media']['instagram'] = $data['social_media_instagram'];
			$edited1[0]['settings']['reviews']['social_media']['twitter'] = $data['social_media_twitter'];
			$edited1[0]['settings']['reviews']['social_media']['linkedin'] = $data['social_media_linkedin'];
			$edited1[0]['settings']['reviews']['social_media']['youtube'] = $data['social_media_youtube'];
			$edited1[0]['settings']['reviews']['social_media']['google'] = $data['social_media_google'];
			$edited1[0]['settings']['reviews']['social_media']['tripadvisor'] = $data['social_media_tripadvisor'];
		}

		$query = $this->database->update('accounts', [
			'settings' => json_encode($edited1[0]['settings'])
		], [
			'id' => Session::get_value('account')['id']
		]);

		if ($field == 'myvox')
		{
			if (!empty($query))
			{
				if (!empty($data['survey_mail_image']['name']) AND !empty($edited2[0]['settings']['myvox']['survey']['mail']['image']))
					Functions::undoloader($edited2[0]['settings']['myvox']['survey']['mail']['image']);

				if (!empty($data['survey_mail_attachment']['name']) AND !empty($edited2[0]['settings']['myvox']['survey']['mail']['attachment']))
					Functions::undoloader($edited2[0]['settings']['myvox']['survey']['mail']['attachment']);
			}
		}

		return $query;
	}
}
