<?php

defined('_EXEC') or die;

require 'plugins/php-qr-code/qrlib.php';

class Index_model extends Model
{
	public function __construct()
	{
		parent::__construct();
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

	public function get_time_zones()
	{
		$query = $this->database->select('time_zones', [
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

	public function get_room_package($rooms_number)
	{
		$query = Functions::get_json_decoded_query($this->database->select('room_packages', [
			'id',
			'quantity_end',
			'price'
		], [
			'AND' => [
				'quantity_start[<=]' => $rooms_number,
				'quantity_end[>=]' => $rooms_number
			]
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function get_table_package($tables_number)
	{
		$query = Functions::get_json_decoded_query($this->database->select('table_packages', [
			'id',
			'quantity_end',
			'price'
		], [
			'AND' => [
				'quantity_start[<=]' => $tables_number,
				'quantity_end[>=]' => $tables_number
			]
		]));

		return !empty($query) ? $query[0] : null;
	}

	public function get_client_package($clients_number)
	{
		$query = Functions::get_json_decoded_query($this->database->select('client_packages', [
			'id',
			'quantity_end',
			'price'
		], [
			'AND' => [
				'quantity_start[<=]' => $clients_number,
				'quantity_end[>=]' => $clients_number
			]
		]));

		return !empty($query) ? $query[0] : null;
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
		$data['path'] = str_replace(' ', '', strtolower($data['path']));
		$data['token'] = Functions::get_random(8);
		$data['qr']['filename'] = 'qr_' . $data['path'] . '_' . $data['token'] . '.png';
		$data['qr']['content'] = 'https://' . Configuration::$domain . '/' . $data['path'] . '/myvox';
		$data['qr']['dir'] = PATH_UPLOADS . $data['qr']['filename'];
		$data['qr']['level'] = 'H';
		$data['qr']['size'] = 5;
		$data['qr']['frame'] = 3;

		$this->database->insert('accounts', [
			'token' => strtoupper($data['token']),
			'name' => $data['name'],
			'path' => $data['path'],
			'type' => $data['type'],
			'zip_code' => $data['zip_code'],
			'country' => $data['country'],
			'city' => $data['city'],
			'address' => $data['address'],
			'time_zone' => $data['time_zone'],
			'currency' => $data['currency'],
			'language' => $data['language'],
			'room_package' => ($data['type'] == 'hotel') ? $this->get_room_package($data['rooms_number'])['id'] : null,
			'table_package' => ($data['type'] == 'restaurant') ? $this->get_table_package($data['tables_number'])['id'] : null,
			'client_package' => ($data['type'] == 'others') ? $this->get_client_package($data['clients_number'])['id'] : null,
			'logotype' => Functions::uploader($data['logotype']),
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
			'payment' => json_encode([
				'type' => 'demo'
			]),
			'qr' => $data['qr']['filename'],
			'operation' => !empty($data['operation']) ? true : false,
			'reputation' => !empty($data['reputation']) ? true : false,
			'zaviapms' => json_encode([
				'status' => false,
				'username' => '',
				'password' => '',
			]),
			'sms' => 0,
			'settings' => json_encode([
				'myvox' => [
					'request' => !empty($data['operation']) ? true : false,
					'incident' => !empty($data['operation']) ? true : false,
					'survey' => !empty($data['reputation']) ? true : false,
					'survey_title' => [
						'es' => 'Responder encuesta',
						'en' => 'Answer survey'
					]
				]
			]),
			'signup_date' => Functions::get_current_date(),
			'status' => false
		]);

		QRcode::png($data['qr']['content'], $data['qr']['dir'], $data['qr']['level'], $data['qr']['size'], $data['qr']['frame']);

		$account = $this->database->id();

		if ($data['type'] == 'hotel')
		{
			$data['guest_treatments'] = [
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

			$this->database->insert('guest_treatments', [
				[
					'account' => $account,
					'name' => $data['guest_treatments'][$data['language']][0]
				],
				[
					'account' => $account,
					'name' => $data['guest_treatments'][$data['language']][1]
				],
				[
					'account' => $account,
					'name' => $data['guest_treatments'][$data['language']][2]
				]
			]);

			$data['guest_types'] = [
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

			$this->database->insert('guest_types', [
				[
					'account' => $account,
					'name' => $data['guest_types'][$data['language']][0]
				],
				[
					'account' => $account,
					'name' => $data['guest_types'][$data['language']][1]
				],
				[
					'account' => $account,
					'name' => $data['guest_types'][$data['language']][2]
				],
				[
					'account' => $account,
					'name' => $data['guest_types'][$data['language']][3]
				],
				[
					'account' => $account,
					'name' => $data['guest_types'][$data['language']][4]
				],
				[
					'account' => $account,
					'name' => $data['guest_types'][$data['language']][5]
				],
				[
					'account' => $account,
					'name' => $data['guest_types'][$data['language']][6]
				]
			]);

			$data['reservation_statuses'] = [
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

			$this->database->insert('reservation_statuses', [
				[
					'account' => $account,
					'name' => $data['reservation_statuses'][$data['language']][0]
				],
				[
					'account' => $account,
					'name' => $data['reservation_statuses'][$data['language']][1]
				],
				[
					'account' => $account,
					'name' => $data['reservation_statuses'][$data['language']][2]
				],
				[
					'account' => $account,
					'name' => $data['reservation_statuses'][$data['language']][3]
				],
				[
					'account' => $account,
					'name' => $data['reservation_statuses'][$data['language']][4]
				],
				[
					'account' => $account,
					'name' => $data['reservation_statuses'][$data['language']][5]
				]
			]);
		}

		$data['user_permissions'] = [
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
			'ids' => []
		];

		if ($data['type'] == 'hotel')
		{
			$data['user_permissions']['ids'] = [
				'["46","47","25","39","38","40","29","30","31","32","33","34","10","11","12","4","5","6","7","8","9","35","36","37","13","14","15","42","43","44","45","48","16","17","19","20","21","18","22","23","24","41","49","50","26","1","2","3"]',
				'["46","47","25","39","41","38","26","1","2","3"]',
				'["46","47","25","39","41","38","28","1","2","3"]',
				'["46","47","39","41","38","28","1","2","3"]',
				'["27","1","2","3"]'
			];
		}
		else if ($data['type'] == 'restaurant')
		{
			$data['user_permissions']['ids'] = [
				'["46","47","25","39","38","40","29","30","31","32","33","34","10","11","12","4","5","6","7","8","9","35","36","37","51","52","53","42","43","44","45","48","16","17","19","20","21","18","22","23","24","41","49","50","26","1","2","3"]',
				'["46","47","25","39","41","38","26","1","2","3"]',
				'["46","47","25","39","41","38","28","1","2","3"]',
				'["46","47","39","41","38","28","1","2","3"]',
				'["27","1","2","3"]'
			];
		}
		else if ($data['type'] == 'others')
		{
			$data['user_permissions']['ids'] = [
				'["46","47","25","39","38","40","29","30","31","32","33","34","10","11","12","4","5","6","7","8","9","35","36","37","54","55","56","42","43","44","45","48","16","17","19","20","21","18","22","23","24","41","49","50","26","1","2","3"]',
				'["46","47","25","39","41","38","26","1","2","3"]',
				'["46","47","25","39","41","38","28","1","2","3"]',
				'["46","47","39","41","38","28","1","2","3"]',
				'["27","1","2","3"]'
			];
		}

		$this->database->insert('user_levels', [
			[
				'account' => $account,
				'name' => $data['user_permissions'][$data['language']][0],
				'user_permissions' => $data['user_permissions']['ids'][0]
			],
			[
				'account' => $account,
				'name' => $data['user_permissions'][$data['language']][1],
				'user_permissions' => $data['user_permissions']['ids'][1]
			],
			[
				'account' => $account,
				'name' => $data['user_permissions'][$data['language']][2],
				'user_permissions' => $data['user_permissions']['ids'][2]
			],
			[
				'account' => $account,
				'name' => $data['user_permissions'][$data['language']][3],
				'user_permissions' => $data['user_permissions']['ids'][3]
			],
			[
				'account' => $account,
				'name' => $data['user_permissions'][$data['language']][4],
				'user_permissions' => $data['user_permissions']['ids'][4]
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
			'user_permissions' => $data['user_permissions']['ids'][0],
			'opportunity_areas' => json_encode([]),
			'status' => false
		]);

		$user = $this->database->id();

		return [
			'account' => $account,
			'user' => $user
		];
	}

	public function get_login($data)
	{
		$query = Functions::get_json_decoded_query($this->database->select('users', [
			'[>]accounts' => [
				'account' => 'id'
			],
			'[>]room_packages' => [
				'accounts.room_package' => 'id'
			],
			'[>]table_packages' => [
				'accounts.table_package' => 'id'
			],
			'[>]client_packages' => [
				'accounts.client_package' => 'id'
			]
		], [
			'users.id(user_id)',
			'users.firstname(user_firstname)',
			'users.lastname(user_lastname)',
			'users.avatar(user_avatar)',
			'users.username(user_username)',
			'users.password(user_password)',
			'users.user_permissions(user_user_permissions)',
			'users.opportunity_areas(user_opportunity_areas)',
			'users.status(user_status)',
			'accounts.id(account_id)',
			'accounts.name(account_name)',
			'accounts.path(account_path)',
			'accounts.type(account_type)',
			'accounts.time_zone(account_time_zone)',
			'accounts.currency(account_currency)',
			'accounts.language(account_language)',
			'accounts.logotype(account_logotype)',
			'accounts.operation(account_operation)',
			'accounts.reputation(account_reputation)',
			'accounts.zaviapms(account_zaviapms)',
			'accounts.sms(account_sms)',
			'accounts.status(account_status)',
			'room_packages.id(room_package_id)',
			'room_packages.quantity_end(room_package_quantity_end)',
			'table_packages.id(table_package_id)',
			'table_packages.quantity_end(table_package_quantity_end)',
			'client_packages.id(client_package_id)',
			'client_packages.quantity_end(client_package_quantity_end)'
		], [
			'AND' => [
				'OR' => [
					'users.email' => $data['username'],
					'users.username' => $data['username']
				]
			]
		]));

		if (!empty($query))
		{
			foreach ($query[0]['user_user_permissions'] as $key => $value)
			{
				$value = $this->database->select('user_permissions', [
					'code'
				], [
					'id' => $value
				]);

				if (!empty($value))
					$query[0]['user_user_permissions'][$key] = $value[0]['code'];
				else
					unset($query[0]['user_user_permissions'][$key]);
			}

			$data = [
				'user' => [
					'id' => $query[0]['user_id'],
					'firstname' => $query[0]['user_firstname'],
					'lastname' => $query[0]['user_lastname'],
					'avatar' => $query[0]['user_avatar'],
					'username' => $query[0]['user_username'],
					'password' => $query[0]['user_password'],
					'user_permissions' => $query[0]['user_user_permissions'],
					'opportunity_areas' => $query[0]['user_opportunity_areas'],
					'status' => $query[0]['user_status']
				],
				'account' => [
					'id' => $query[0]['account_id'],
					'name' => $query[0]['account_name'],
					'path' => $query[0]['account_path'],
					'type' => $query[0]['account_type'],
					'time_zone' => $query[0]['account_time_zone'],
					'currency' => $query[0]['account_currency'],
					'language' => $query[0]['account_language'],
					'logotype' => $query[0]['account_logotype'],
					'operation' => $query[0]['account_operation'],
					'reputation' => $query[0]['account_reputation'],
					'zaviapms' => $query[0]['account_zaviapms'],
					'sms' => $query[0]['account_sms'],
					'status' => $query[0]['account_status']
				]
			];

			if ($query[0]['account_type'] == 'hotel')
			{
				$data['account']['room_package'] = [
					'id' => $query[0]['room_package_id'],
					'quantity_end' => $query[0]['room_package_quantity_end']
				];
			}
			else if ($query[0]['account_type'] == 'restaurant')
			{
				$data['account']['table_package'] = [
					'id' => $query[0]['table_package_id'],
					'quantity_end' => $query[0]['table_package_quantity_end']
				];
			}
			else if ($query[0]['account_type'] == 'others')
			{
				$data['account']['client_package'] = [
					'id' => $query[0]['client_package_id'],
					'quantity_end' => $query[0]['client_package_quantity_end']
				];
			}

			return $data;
		}
		else
			return null;
	}

	public function new_validation($data)
	{
		if ($data[0] == 'account')
		{
			$query = Functions::get_json_decoded_query($this->database->select('accounts', [
				'name',
				'language',
				'contact',
				'status'
			], [
				'id' => $data[1]
			]));

			if (!empty($query))
			{
				if ($query[0]['status'] == false)
				{
					$this->database->update('accounts', [
						'status' => true
					], [
						'id' => $data[1]
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
				'users.firstname',
				'users.lastname',
				'users.email',
				'users.username',
				'users.status',
				'accounts.language'
			], [
				'users.id' => $data[1]
			]);

			if (!empty($query))
			{
				if ($query[0]['status'] == false)
				{
					$this->database->update('users', [
						'status' => true
					], [
						'id' => $data[1]
					]);
				}
			}
		}

		return !empty($query) ? $query[0] : null;
	}
}
