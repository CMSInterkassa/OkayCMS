<?php
require_once('Interkassa.php');
$okayIk = new Interkassa();

if(isset($_GET['pm'])){
	$okayIk->ajaxRequest();
}

if(!$okayIk->checkIP()){
	errRst();
}

$order = $okayIk->orders->get_order(intval($_POST['ik_pm_no']));
if(empty($order))
	errRst('Оплачиваемый заказ не найден');

$method = $okayIk->payment->get_payment_method(intval($order->payment_method_id));

if(empty($method))
	errRst("Неизвестный метод оплаты");

$settings = $okayIk->payment->get_payment_settings($method->id);
$payment_currency = $okayIk->money->get_currency(intval($method->currency_id));

if ($settings['test_mode'])
	$secret_key = $settings['test_key'];
else
	$secret_key = $settings['secret_key'];

$sigIk = $_POST['ik_sign'];
$sign = $okayIk::IkSignFormation($_POST, $secret_key);

if ($_POST['ik_inv_st'] == 'success' && $sigIk === $sign && ($settings['cashbox_id'] === $_POST['ik_co_id'])) {
	// Нельзя оплатить уже оплаченный заказ
	if ($order->paid)
		errRst('Этот заказ уже оплачен');

	if ($_POST['ik_am'] != round($okayIk->money->convert($order->total_price, $method->currency_id, false), 2))
		errRst("incorrect price");

	// Установим статус оплачен
	$okayIk->orders->update_order(intval($order->id), array('paid' => 1));

	// Отправим уведомление на email
	$okayIk->notify->email_order_user(intval($order->id));
	$okayIk->notify->email_order_admin(intval($order->id));
	// Спишем товары
	$okayIk->orders->close(intval($order->id));
} else {
	errRst();
}

function errRst($msg = 'Bad Request!')
{
	header($_SERVER['SERVER_PROTOCOL'].' 400 Bad Request', true, 400);
	die($msg);
}
