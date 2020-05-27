<?php

defined('_EXEC') or die;

class Functions
{
    static public function set_default_timezone()
    {
        if (Session::exists_var('session') == true)
            date_default_timezone_set(Session::get_value('account')['time_zone']);
        else
            date_default_timezone_set(Configuration::$time_zone);
    }

    static public function get_current_date($format = 'Y-m-d')
    {
        Functions::set_default_timezone();

		return date($format);
    }

    static public function get_past_date($date, $number, $lapse, $format = 'Y-m-d')
    {
        if (!empty($date))
        {
            Functions::set_default_timezone();

            return date($format, strtotime($date . ' - ' . $number . ' ' . $lapse));
        }
        else
            return null;
    }

    static public function get_future_date($date, $number, $lapse, $format = 'Y-m-d')
    {
        if (!empty($date))
        {
            Functions::set_default_timezone();

            return date($format, strtotime($date . ' + ' . $number . ' ' . $lapse));
        }
        else
            return null;
    }

    static public function get_diff_date($start_date, $end_date, $lapse, $big_lapse = false)
    {
        if ($lapse == 'days')
        {
            Functions::set_default_timezone();

            $start_date = new DateTime($start_date);
            $end_date = new DateTime($end_date);
            $diff = $start_date->diff($end_date);

            if ($big_lapse == true)
                return ($diff->days) + 1;
            else
                return $diff->days;
        }
        else
            return null;
    }

    static public function get_formatted_date($date, $format = 'Y-m-d')
    {
        if (!empty($date))
        {
            Functions::set_default_timezone();

            return date($format, strtotime($date));
        }
        else
            return null;
    }

    static public function get_current_hour($format = 'H:i:s')
    {
        Functions::set_default_timezone();

		return date($format, time());
    }

    static public function get_formatted_hour($hour, $format = 'H:i:s')
    {
        if (!empty($hour))
        {
            Functions::set_default_timezone();

            if ($format == '+ hrs')
                return $hour . ' Hrs';
            else
        	    return date($format, strtotime($hour));
        }
        else
            return null;
    }

    static public function get_current_date_hour($format = 'Y-m-d H:i:s')
    {
        Functions::set_default_timezone();

		return date($format, time());
    }

    static public function get_formatted_date_hour($date, $hour, $format = 'Y-m-d H:i:s')
    {
        if (!empty($date) AND !empty($hour))
        {
            Functions::set_default_timezone();

            if ($format == '+ hrs')
                return $date . ' ' . $hour . ' Hrs';
            else
                return date($format, strtotime($date . ' ' . $hour));
        }
        else
            return null;
    }

    static public function get_current_week()
    {
        Functions::set_default_timezone();

        return [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))];
    }

    static public function get_current_month()
    {
        Functions::set_default_timezone();

        return [date('Y-m-d', strtotime('first day of this month')), date('Y-m-d', strtotime('last day of this month'))];
    }

    static public function get_current_year()
    {
        Functions::set_default_timezone();

        return [date('Y-m-d', strtotime('first day of January')), date('Y-m-d', strtotime('last day of December'))];
    }

    static public function get_formatted_currency($number = 0, $currency = 'MXN')
    {
        if (!empty($number))
            return '$ ' . number_format($number, 2, '.', ',') . ' ' . $currency;
        else
            return '$ 0.00 ' . $currency;
    }

    static public function get_openssl($action = 'encrypt', $string = false)
    {
        $openssl = false;
        $action = trim($action);
        $encrypt_method = 'AES-256-CBC';
        $secret_key = hash('sha256', Configuration::$secret);
        $encrypt_private_key = substr(hash('sha256', Configuration::$encrypt_private_key), 0, 16);

        if ($action && ($action == 'encrypt' || $action == 'decrypt') && $string)
        {
            $string = trim(strval($string));

            if ($action == 'encrypt')
                $openssl = openssl_encrypt($string, $encrypt_method, $secret_key, 0, $encrypt_private_key);

            if ($action == 'decrypt')
                $openssl = openssl_decrypt($string, $encrypt_method, $secret_key, 0, $encrypt_private_key);
        };

        return $openssl;
    }

    static public function get_encrypt($string)
    {
        $encrypt = '';

        for ($i = 0; $i < strlen($string); $i++)
        {
            $char = substr($string, $i, 1);
            $keychar = substr(Configuration::$secret, ($i % strlen(Configuration::$secret)) -1, 1);
            $char = chr(ord($char) + ord($keychar));
            $encrypt .= $char;
        }

        return base64_encode($encrypt);
    }

    static public function get_decrypt($string)
    {
        $decrypt = '';
        $string = base64_decode($string);

        for ($i = 0; $i < strlen($string); $i++)
        {
            $char = substr($string, $i, 1);
            $keychar = substr(Configuration::$secret, ($i % strlen(Configuration::$secret)) -1, 1);
            $char = chr(ord($char) - ord($keychar));
            $decrypt .= $char;
        }

        return $decrypt;
    }

    static public function check_account_access($params)
    {
        $access = false;

        if (Session::exists_var('session') == true)
        {
            foreach ($params as $value)
            {
                if (Session::get_value('account')[$value] == true)
                    $access = true;
            }
        }

        return $access;
    }

    static public function check_user_access($params)
    {
        $access = false;

        if (Session::exists_var('session') == true)
        {
            foreach ($params as $value)
            {
                if (in_array($value, Session::get_value('user')['permissions']))
                    $access = true;
            }
        }

        return $access;
    }

    static public function check_email($email)
    {
        return (filter_var($email, FILTER_VALIDATE_EMAIL)) ? true : false;
    }

    static public function get_json_decoded_query($query)
    {
        if (is_array($query))
        {
            foreach ($query as $key => $value)
            {
                if (is_array($query[$key]))
                {
                    foreach ($query[$key] as $subkey => $subvalue)
                        $query[$key][$subkey] = (is_array(json_decode($query[$key][$subkey], true)) AND (json_last_error() == JSON_ERROR_NONE)) ? json_decode($query[$key][$subkey], true) : $query[$key][$subkey];
                }
                else
                    $query[$key] = (is_array(json_decode($query[$key], true)) AND (json_last_error() == JSON_ERROR_NONE)) ? json_decode($query[$key], true) : $query[$key];
            }
        }
        else
            $query = (is_array(json_decode($query, true)) AND (json_last_error() == JSON_ERROR_NONE)) ? json_decode($query, true) : $query;

        return $query;
    }

    public static function uploader($file = null, $multiple = false, $upload_directory = PATH_UPLOADS, $valid_extensions = ['png','jpg','jpeg','pdf','doc','docx','xls','xlsx'], $maximum_file_size = 'unlimited')
	{
        if (!empty($file))
        {
            $components = new Components;

            $components->load_component('uploader');

            $upload = new Upload;

            if ($multiple == true)
            {
                foreach ($file as $key => $value)
                {
                    $upload->SetFileName($value['name']);
                    $upload->SetTempName($value['tmp_name']);
                    $upload->SetFileType($value['type']);
                    $upload->SetFileSize($value['size']);
                    $upload->SetUploadDirectory($upload_directory);
                    $upload->SetValidExtensions($valid_extensions);
                    $upload->SetMaximumFileSize($maximum_file_size);

                    $value = $upload->UploadFile();

                    if ($value['status'] == 'success')
                        $file[$key] = $value['file'];
                    else
                        unset($file[$key]);
                }

                $file = array_merge($file);
            }
            else if ($multiple == false)
            {
                $upload->SetFileName($file['name']);
                $upload->SetTempName($file['tmp_name']);
                $upload->SetFileType($file['type']);
                $upload->SetFileSize($file['size']);
                $upload->SetUploadDirectory($upload_directory);
                $upload->SetValidExtensions($valid_extensions);
                $upload->SetMaximumFileSize($maximum_file_size);

                $file = $upload->UploadFile();

                if ($file['status'] == 'success')
                    $file = $file['file'];
                else
                    $file = null;
            }

            return $file;
        }
        else
            return null;
	}

    public static function undoloader($file = null, $upload_directory = PATH_UPLOADS)
    {
        if (!empty($file))
        {
            if (is_array($file))
            {
                foreach ($file as $value)
                    unlink($upload_directory . $value);
            }
            else
                unlink($upload_directory . $file);
        }
    }

    static public function get_lang($inv = false)
    {
        if ($inv == true)
        {
            if (Session::get_value('lang') == 'es')
                return 'en';
            else if (Session::get_value('lang') == 'en')
                return 'es';
        }
        else
            return Session::get_value('lang');
    }

    public static function get_random($length)
    {
        $security = new Security;

        return !empty($length) ? strtoupper($security->random_string($length)) : null;
    }

    static public function environment($return)
    {
        echo json_encode($return, JSON_PRETTY_PRINT);
    }

    static public function api($connection, $access, $method, $option, $params = null)
    {
        if ($connection == 'zaviapms')
        {
            if ($method == 'get')
            {
                $api = curl_init();

                if ($option == 'rooms')
                    curl_setopt($api, CURLOPT_URL, 'https://admin.zaviaerp.com/pms/hotels/api/rooms/?UserName=' . $access['username'] . '&UserPassword=' . $access['password']);

                if ($option == 'room')
                    curl_setopt($api, CURLOPT_URL, 'https://admin.zaviaerp.com/pms/hotels/api/check_room2/?UserName=' . $access['username'] . '&UserPassword=' . $access['username'] . '&RoomNumber=' . $params);

                curl_setopt($api, CURLOPT_RETURNTRANSFER, true);

                $data = Functions::get_json_decoded_query(curl_exec($api));

                curl_close($api);

                return $data;
            }
        }
    }

    public static function shorten_string($string, $length = 400)
	{
		return (strlen(strip_tags($string)) > $length) ? substr(strip_tags($string), 0, $length) . '...' : substr(strip_tags($string), 0, $length);
    }
}
