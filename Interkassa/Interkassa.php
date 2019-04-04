<?php

require_once(dirname(dirname(__DIR__)) . '/api/Okay.php');

class Interkassa extends Okay
{
	public static $urlApiIk = 'https://api.interkassa.com/';
	public static $urlSciIk = 'https://sci.interkassa.com/';

	public function checkout_form($order_id)
	{
		$order = $this->orders->get_order((int)$order_id);
		$payment_method = $this->payment->get_payment_method($order->payment_method_id);
		$payment_currency = $this->money->get_currency(intval($payment_method->currency_id));
		$settings = $this->payment->get_payment_settings($payment_method->id);
		
		$price = round($this->money->convert($order->total_price, $payment_method->currency_id, false), 2);

		$success_url = $this->config->root_url.'/order/'.$order->url;
		$callback_url = $this->config->root_url.'/payment/Interkassa/callback.php';

		$data = [
			'ik_co_id' => $settings['cashbox_id'],
			'ik_am'    => $price,
			'ik_cur'   => ($payment_currency->code == 'RUR')? 'RUB' : $payment_currency->code,
			'ik_desc'  => '#'.$order->id,
			'ik_pm_no' => $order_id,
			'ik_ia_u'  => $callback_url,
			'ik_suc_u' => $success_url,
			'ik_pnd_u' => $success_url,
			'ik_fal_u' => $success_url,
		];
		if($settings['test_mode'])
			$data['ik_pw_via'] = 'test_interkassa_test_xts';

		$data['ik_sign'] = self::IkSignFormation($data, $settings['secret_key']);

		if($settings['enableAPI'])
			$payment_systems = $this->getIkPaymentSystems($settings['cashbox_id'], $settings['api_id'], $settings['api_key']);
		else
			$payment_systems = '';

		if(!class_exists('Languages')){
			require_once dirname(dirname(__DIR__)) . '/api/Languages.php';
		}
		$objLang = new Languages();

		$lang = $objLang->get_language($objLang->lang_id());
        $res['langId'] = $lang->label;
        $res['urlRequest'] = self::$urlApiIk;
        $res['formData'] = $data;
        $res['payment_systems'] = $payment_systems;

		return $res;
	}

	public function ajaxRequest()
	{
		header("Pragma: no-cache");
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
		header("Content-type: text/plain");
		$request = $_POST;

		if(empty($request['ik_pm_no'])) {
			die('Bad request!!!');
		}

		$order = $this->orders->get_order((int)$request['ik_pm_no']);
		$payment_method = $this->payment->get_payment_method($order->payment_method_id);
		$settings = $this->payment->get_payment_settings($payment_method->id);

		if (isset($_POST['ik_act']) && $_POST['ik_act'] == 'process'){
			$request['ik_sign'] = self::IkSignFormation($request, $settings['ik_secret_key']);
			$data = $this->getAnswerFromAPI($request);
		}
		else
			$data = self::IkSignFormation($request, $settings['ik_secret_key']);

		echo $data;
		exit;
	}

	public function getAnswerFromAPI($data)
	{
		$ch = curl_init(self::$urlSciIk);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		return $result;
	}
	public static function IkSignFormation($data, $secret_key)
	{
		if (!empty($data['ik_sign'])) unset($data['ik_sign']);
		$dataSet = [];
		foreach ($data as $key => $value) {
			if (!preg_match('/ik_/', $key)) continue;
			$dataSet[$key] = $value;
		}
		ksort($dataSet, SORT_STRING);
		array_push($dataSet, $secret_key);
		$arg = implode(':', $dataSet);
		$ik_sign = base64_encode(md5($arg, true));
		return $ik_sign;
	}

	public function getIkPaymentSystems($ik_cashbox_id, $ik_api_id, $ik_api_key)
	{
		$username = $ik_api_id;
		$password = $ik_api_key;
		$remote_url = self::$urlApiIk . 'v1/paysystem-input-payway?checkoutId=' . $ik_cashbox_id;
		$opts = [
			'http' => [
				'method' => "GET",
				'header' => "Authorization: Basic " . base64_encode("$username:$password")
			]
		];
		$context = stream_context_create($opts);
		$response = file_get_contents($remote_url, false, $context);
		$json_data = json_decode($response);

		if(empty($response))
			return '<strong style="color:red;">Error!!! System response empty!</strong>';

		if ($json_data->status != 'error') {
			$payment_systems = array();
			if(!empty($json_data->data)){
				foreach ($json_data->data as $ps => $info) {
					$payment_system = $info->ser;
					if (!array_key_exists($payment_system, $payment_systems)) {
						$payment_systems[$payment_system] = array();
						foreach ($info->name as $name) {
							if ($name->l == 'en') {
								$payment_systems[$payment_system]['title'] = ucfirst($name->v);
							}
							$payment_systems[$payment_system]['name'][$name->l] = $name->v;
						}
					}
					$payment_systems[$payment_system]['currency'][strtoupper($info->curAls)] = $info->als;
				}
			}

			return !empty($payment_systems)? $payment_systems : '<strong style="color:red;">API connection error or system response empty!</strong>';
		} else {
			if(!empty($json_data->message))
				return '<strong style="color:red;">API connection error!<br>' . $json_data->message . '</strong>';
			else
				return '<strong style="color:red;">API connection error or system response empty!</strong>';
		}
	}

	public function checkIP()
	{
		$ip_stack = [
			'ip_begin' => '151.80.190.97',
			'ip_end' => '151.80.190.104'
		];
		$ip = ip2long($_SERVER['REMOTE_ADDR'])? ip2long($_SERVER['REMOTE_ADDR']) : !ip2long($_SERVER['REMOTE_ADDR']);
		if(($ip >= ip2long($ip_stack['ip_begin'])) && ($ip <= ip2long($ip_stack['ip_end']))){
			return true;
		}
		return false;
	}
}