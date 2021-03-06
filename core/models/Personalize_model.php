<?php

defined('_EXEC') or die;

require 'plugins/php_qr_code/qrlib.php';

class Personalize_model extends Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function get_package($type, $rooms_number)
	{
		$query = Functions::get_json_decoded_query($this->database->select('packages', [
			'id',
			'quantity_end',
			'price'
		], [
			'AND' => [
				'type' => $type,
				'quantity_start[<=]' => ($type == 'hotel') ? $rooms_number : 100,
				'quantity_end[>=]' => ($type == 'hotel') ? $rooms_number : 100,
				'status' => true
			]
		]));

		return !empty($query) ? $query[0] : null;
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
		$query1 = $this->database->select('times_zones', [
			'code'
		], [
			'priority[>=]' => 1,
			'ORDER' => [
				'priority' => 'ASC'
			]
		]);

		$query2 = $this->database->select('times_zones', [
			'code'
		], [
			'priority[=]' => null,
			'ORDER' => [
				'zone' => 'ASC'
			]
		]);

		return array_merge($query1, $query2);
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

		$data['menu_delivery_qr']['filename'] = $data['path'] . '_menu_delivery_qr_' . $data['token'] . '.png';
		$data['menu_delivery_qr']['content'] = 'https://' . Configuration::$domain . '/' . $data['path'] . '/myvox/delivery';
		$data['menu_delivery_qr']['dir'] = PATH_UPLOADS . $data['menu_delivery_qr']['filename'];
		$data['menu_delivery_qr']['level'] = 'H';
		$data['menu_delivery_qr']['size'] = 5;
		$data['menu_delivery_qr']['frame'] = 3;

		$data['reviews_qr']['filename'] = $data['path'] . '_reviews_qr_' . $data['token'] . '.png';
		$data['reviews_qr']['content'] = 'https://' . Configuration::$domain . '/' . $data['path'] . '/reviews';
		$data['reviews_qr']['dir'] = PATH_UPLOADS . $data['reviews_qr']['filename'];
		$data['reviews_qr']['level'] = 'H';
		$data['reviews_qr']['size'] = 5;
		$data['reviews_qr']['frame'] = 3;

		$this->database->insert('accounts', [
			'token' => $data['token'],
			'name' => $data['name'],
			'path' => $data['path'],
			'type' => $data['type'],
			'country' => $data['country'],
			'city' => $data['city'],
			'zip_code' => $data['zip_code'],
			'address' => $data['address'],
			'location' => json_encode([
				'lat' => '',
				'lng' => ''
			]),
			'time_zone' => $data['time_zone'],
			'currency' => $data['currency'],
			'language' => $data['language'],
			'fiscal' => json_encode([
				'id' => '',
				'name' => '',
				'address' => ''
			]),
			'contact' => json_encode([
				'firstname' => '',
				'lastname' => '',
				'department' => '',
				'email' => '',
				'phone' => [
					'lada' => '',
					'number' => ''
				]
			]),
			'logotype' => Functions::uploader($data['logotype'], $data['path'] . '_account_logotype_'),
			'qrs' => json_encode([
				'menu_delivery' => $data['menu_delivery_qr']['filename'],
				'reviews' => $data['reviews_qr']['filename']
			]),
			'package' => $this->get_package($data['type'], $data['rooms_number'])['id'],
			'digital_menu' => !empty($data['digital_menu']) ? true : false,
			'operation' => !empty($data['operation']) ? true : false,
			'surveys' => !empty($data['surveys']) ? true : false,
			'zaviapms' => json_encode([
				'status' => false,
				'username' => '',
				'password' => ''
			]),
			'ambit' => json_encode([
				'status' => false,
				'username' => '',
				'password' => '',
				'store_id' => ''
			]),
			'siteminder' => json_encode([
				'status' => false,
				'username' => '',
				'password' => ''
			]),
			'sms' => 0,
			'whatsapp' => 0,
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
						'email' => '',
						'phone' => [
							'lada' => '',
							'number' => ''
						],
						'currency' => '',
						'schedule' => [
							'monday' => [
								'status' => 'close',
								'opening' => '',
								'closing' => ''
							],
							'tuesday' => [
								'status' => 'close',
								'opening' => '',
								'closing' => ''
							],
							'wednesday' => [
								'status' => 'close',
								'opening' => '',
								'closing' => ''
							],
							'thursday' => [
								'status' => 'close',
								'opening' => '',
								'closing' => ''
							],
							'friday' => [
								'status' => 'close',
								'opening' => '',
								'closing' => ''
							],
							'saturday' => [
								'status' => 'close',
								'opening' => '',
								'closing' => ''
							],
							'sunday' => [
								'status' => 'close',
								'opening' => '',
								'closing' => ''
							]
						],
						'delivery' => false,
						'requests' => false,
						'sell_radius' => '',
						'multi' => false
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
				],
				'voxes' => [
					'attention_times' => [
						'request' => [
							'low' => '40',
							'medium' => '20',
							'high' => '10'
						],
						'incident' => [
							'low' => '40',
							'medium' => '20',
							'high' => '10'
						]
					]
				]
			]),
			'payment' => json_encode([
				'status' => false,
				'type' => 'mit',
				'mit' => [
					'code' => '',
					'types' => ''
				],
				'contract' => [
					'status' => 'deactivated',
					'place' => '',
					'date' => '',
					'signature' => '',
					'titular' => [
						'fiscal' => [
							'person' => '',
							'id' => '',
							'name' => '',
							'activity' => ''
						],
						'address' => [
							'street' => '',
							'external_number' => '',
							'internal_number' => '',
							'cp' => '',
							'colony' => '',
							'delegation' => '',
							'city' => '',
							'state' => '',
							'country' => ''
						],
						'bank' => [
							'name' => '',
							'branch' => '',
							'checkbook' => '',
							'clabe' => ''
						],
						'personal_references' => [
							'first' => [
								'name' => '',
								'phone' => [
									'country' => '',
									'number' => ''
								]
							],
							'second' => [
								'name' => '',
								'phone' => [
									'country' => '',
									'number' => ''
								]
							],
							'third' => [
								'name' => '',
								'phone' => [
									'country' => '',
									'number' => ''
								]
							],
							'fourth' => [
								'name' => '',
								'phone' => [
									'country' => '',
									'number' => ''
								]
							]
						],
						'email' => '',
						'phone' => [
							'country' => '',
							'number' => ''
						],
						'tpv' => ''
					],
					'company' => [
						'writing_number' => '',
						'writing_date' => '',
						'public_record_folio' => '',
						'public_record_date' => '',
						'notary_name' => '',
						'notary_number' => '',
						'city' => '',
						'legal_representative' => [
							'name' => '',
							'writing_number' => '',
							'writing_date' => '',
							'notary_name' => '',
							'notary_number' => '',
							'city' => '',
							'card' => [
								'type' => '',
								'number' => '',
								'expedition_date' => '',
								'validity' => ''
							]
						]
					]
				]
			]),
			'signup_date' => Functions::get_current_date(),
			'status' => true
		]);

		QRcode::png($data['menu_delivery_qr']['content'], $data['menu_delivery_qr']['dir'], $data['menu_delivery_qr']['level'], $data['menu_delivery_qr']['size'], $data['menu_delivery_qr']['frame']);
		QRcode::png($data['reviews_qr']['content'], $data['reviews_qr']['dir'], $data['reviews_qr']['level'], $data['reviews_qr']['size'], $data['reviews_qr']['frame']);

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
			'["17","18","4","10","11","65","39","40","41","42","43","44","45","46","47","48","34","35","36","37","38","76","77","78","79","80","66","67","68","69","70","71","72","73","74","75","81","82","83","84","85","24","25","26","27","28","29","30","31","32","33","19","20","21","22","23","49","50","51","52","53","12","13","14","15","16","59","60","61","62","63","64","54","55","56","57","58","5","6","7","8","9","1"]',
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
			// [
			// 	'account' => $account,
			// 	'name' => $data['users_levels'][$data['language']][1],
			// 	'permissions' => $data['users_levels']['permissions'][1],
			// 	'status' => true
			// ],
			// [
			// 	'account' => $account,
			// 	'name' => $data['users_levels'][$data['language']][2],
			// 	'permissions' => $data['users_levels']['permissions'][2],
			// 	'status' => true
			// ],
			// [
			// 	'account' => $account,
			// 	'name' => $data['users_levels'][$data['language']][3],
			// 	'permissions' => $data['users_levels']['permissions'][3],
			// 	'status' => true
			// ],
			// [
			// 	'account' => $account,
			// 	'name' => $data['users_levels'][$data['language']][4],
			// 	'permissions' => $data['users_levels']['permissions'][4],
			// 	'status' => true
			// ]
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
}
