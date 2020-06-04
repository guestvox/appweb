<?php

defined('_EXEC') or die;

require 'plugins/php_qr_code/qrlib.php';

class Signup_model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_package($type, $quantity, $id = false)
	{
		$query = Functions::get_json_decoded_query($this->database->select('packages', [
			'id',
			'quantity_end',
			'price'
		], [
			'AND' => [
				'type' => $type,
				'quantity_start[<=]' => $quantity,
				'quantity_end[>=]' => $quantity,
				'status' => true
			]
		]));

		return !empty($query) ? (($id == true) ? $query[0]['id'] : $query[0]) : null;
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
		$query = $this->database->select('times_zones', [
			'code'
		], [
			'ORDER' => [
				'zone' => 'ASC'
			]
		]);

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
			$field => $value
		]);

		return ($count > 0) ? true : false;
	}

	public function check_exist_user($field, $value)
	{
		$count = $this->database->count('users', [
			$field => $value
		]);

		return ($count > 0) ? true : false;
	}

	public function new_signup($data)
	{
		$data['token'] = strtolower(Functions::get_random(8));
		$data['path'] = str_replace(' ', '', strtolower($data['path']));
		$data['qr']['filename'] = 'qr_' . $data['path'] . '_' . $data['token'] . '.png';
		$data['qr']['content'] = 'https://' . Configuration::$domain . '/' . $data['path'] . '/myvox';
		$data['qr']['dir'] = PATH_UPLOADS . $data['qr']['filename'];
		$data['qr']['level'] = 'H';
		$data['qr']['size'] = 5;
		$data['qr']['frame'] = 3;

		$this->database->insert('accounts', [
			'token' => $data['token'],
			'name' => $data['name'],
			'path' => $data['path'],
			'type' => $data['type'],
			'country' => $data['country'],
			'city' => $data['city'],
			'zip_code' => $data['zip_code'],
			'address' => $data['address'],
			'time_zone' => $data['time_zone'],
			'currency' => $data['currency'],
			'language' => $data['language'],
			'fiscal' => json_encode([
				'id' => strtoupper($data['fiscal_id']),
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
			]),
			'logotype' => Functions::uploader($data['logotype']),
			'qr' => $data['qr']['filename'],
			'package' => $this->get_package($data['type'], $data['quantity'], true),
			'operation' => !empty($data['operation']) ? true : false,
			'reputation' => !empty($data['reputation']) ? true : false,
			'siteminder' => json_encode([
				'status' => false,
				'username' => '',
				'password' => ''
			]),
			'zaviapms' => json_encode([
				'status' => false,
				'username' => '',
				'password' => ''
			]),
			'sms' => 0,
			'settings' => json_encode([
				'myvox' => [
					'request' => [
						'status' => false,
						'title' => [
							'es' => '',
							'en' => ''
						]
					],
					'incident' => [
						'status' => false,
						'title' => [
							'es' => '',
							'en' => ''
						]
					],
					'menu' => [
						'status' => false,
						'title' => [
							'es' => '',
							'en' => ''
						],
						'currency' => '',
						'opportunity_area' => '',
						'opportunity_type' => ''
					],
					'survey' => [
						'status' => false,
						'title' => [
							'es' => '',
							'en' => ''
						],
						'mail' => [
							'subject' => [
								'es' => '',
								'en' => ''
							],
							'description' => [
								'es' => '',
								'en' => ''
							],
							'image' => '',
							'attachment' => ''
						],
						'widget' => ''
					]
				],
				'reviews' => [
					'status' => false,
					'email' => '',
					'phone' => [
						'lada' => '',
						'number' => ''
					],
					'website' => '',
					'description' => [
						'es' => '',
						'en' => ''
					],
					'seo' => [
						'keywords' => [
							'es' => '',
							'en' => ''
						],
						'description' => [
							'es' => '',
							'en' => ''
						]
					],
					'social_media' => [
						'facebook' => '',
						'instagram' => '',
						'twitter' => '',
						'linkedin' => '',
						'youtube' => '',
						'google' => '',
						'tripadvisor' => ''
					]
				]
			]),
			'payment' => json_encode([
				'type' => ''
			]),
			'signup_date' => Functions::get_current_date(),
			'status' => false
		]);

		QRcode::png($data['qr']['content'], $data['qr']['dir'], $data['qr']['level'], $data['qr']['size'], $data['qr']['frame']);

		$account = $this->database->id();

		if ($data['type'] == 'hotel')
		{
			$data['guests_treatments'] = [
				'es' => [
					'Sr.',
					'Sra.',
					'Srita.'
				],
				'en' => [
					'Mr.',
					'Miss.',
					'Mrs.'
				]
			];

			$this->database->insert('guests_treatments', [
				[
					'account' => $account,
					'name' => $data['guests_treatments'][$data['language']][0],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['guests_treatments'][$data['language']][1],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['guests_treatments'][$data['language']][2],
					'status' => true
				]
			]);

			$data['guests_types'] = [
				'es' => [
					'Club vacacional',
					'Day pass',
					'Externo',
					'Gold',
					'Platinium',
					'Regular',
					'VIP'
				],
				'en' => [
					'Vacational club',
					'Day pass',
					'External',
					'Gold',
					'Platinium',
					'Regular',
					'VIP'
				]
			];

			$this->database->insert('guests_types', [
				[
					'account' => $account,
					'name' => $data['guests_types'][$data['language']][0],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['guests_types'][$data['language']][1],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['guests_types'][$data['language']][2],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['guests_types'][$data['language']][3],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['guests_types'][$data['language']][4],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['guests_types'][$data['language']][5],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['guests_types'][$data['language']][6],
					'status' => true
				]
			]);

			$data['reservations_statuses'] = [
				'es' => [
					'En casa',
					'Fuera de casa',
					'Pre llegada',
					'Llegada',
					'Pre salida',
					'Salida'
				],
				'en' => [
					'In house',
					'Outside of house',
					'Pre arrival',
					'Arrival',
					'Pre departure',
					'Departure'
				]
			];

			$this->database->insert('reservations_statuses', [
				[
					'account' => $account,
					'name' => $data['reservations_statuses'][$data['language']][0],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['reservations_statuses'][$data['language']][1],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['reservations_statuses'][$data['language']][2],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['reservations_statuses'][$data['language']][3],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['reservations_statuses'][$data['language']][4],
					'status' => true
				],
				[
					'account' => $account,
					'name' => $data['reservations_statuses'][$data['language']][5],
					'status' => true
				]
			]);
		}

		$data['users_levels'] = [
			'es' => [
				'Administrador',
				'Director',
				'Gerente',
				'Supervisor',
				'Operador'
			],
			'en' => [
				'Administrator',
				'Director',
				'Manager',
				'Supervisor',
				'Operator'
			],
			'permissions' => []
		];

		$data['users_levels']['permissions'] = [
			'["17","18","4","10","11","65","39","40","41","42","43","44","45","46","47","48","34","35","36","37","38","66","67","68","69","70","71","72","73","74","75","24","25","26","27","28","29","30","31","32","33","19","20","21","22","23","49","50","51","52","53","12","13","14","15","16","59","60","61","62","63","64","54","55","56","57","58","5","6","7","8","9","1"]',
			'["17","18","4","10","11","5","6","7","8","9","1"]',
			'["17","18","4","10","11","5","6","7","8","9","2"]',
			'["17","18","10","11","5","6","7","8","9","2"]',
			'["3"]'
		];

		$this->database->insert('users_levels', [
			[
				'account' => $account,
				'name' => $data['users_levels'][$data['language']][0],
				'permissions' => $data['users_levels']['permissions'][0],
				'status' => true
			],
			[
				'account' => $account,
				'name' => $data['users_levels'][$data['language']][1],
				'permissions' => $data['users_levels']['permissions'][1],
				'status' => true
			],
			[
				'account' => $account,
				'name' => $data['users_levels'][$data['language']][2],
				'permissions' => $data['users_levels']['permissions'][2],
				'status' => true
			],
			[
				'account' => $account,
				'name' => $data['users_levels'][$data['language']][3],
				'permissions' => $data['users_levels']['permissions'][3],
				'status' => true
			],
			[
				'account' => $account,
				'name' => $data['users_levels'][$data['language']][4],
				'permissions' => $data['users_levels']['permissions'][4],
				'status' => true
			]
		]);

		$this->database->insert('users', [
			'account' => $account,
			'firstname' => $data['firstname'],
			'lastname' => $data['lastname'],
			'email' => $data['email'],
			'phone' => json_encode([
				'lada' => $data['phone_lada'],
				'number' => $data['phone_number'],
			]),
			'avatar' => null,
			'username' => $data['username'],
			'password' => $this->security->create_password($data['password']),
			'permissions' => $data['users_levels']['permissions'][0],
			'opportunity_areas' => json_encode([]),
			'status' => false
		]);

		return true;
	}

	public function new_activation($data)
	{
		if ($data[0] == 'account')
		{
			$query = Functions::get_json_decoded_query($this->database->select('accounts', [
				'id',
				'token',
				'language',
				'contact',
				'status'
			], [
				'path' => $data[1]
			]));

			if (!empty($query))
			{
				if ($query[0]['status'] == false)
				{
					$this->database->update('accounts', [
						'status' => true
					], [
						'id' => $query[0]['id']
					]);
				}
			}
		}
		else if ($data[0] == 'user')
		{
			$query = $this->database->select('users', [
				'[>]accounts' => [
					'account' => 'id'
				]
			], [
				'users.id',
				'users.firstname',
				'users.lastname',
				'users.email',
				'users.status',
				'accounts.language'
			], [
				'users.email' => $data[1]
			]);

			if (!empty($query))
			{
				if ($query[0]['status'] == false)
				{
					$this->database->update('users', [
						'status' => true
					], [
						'id' => $query[0]['id']
					]);
				}
			}
		}

		return !empty($query) ? $query[0] : null;
	}
}
