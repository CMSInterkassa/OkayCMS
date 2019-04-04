<form name="payment_interkassa" method="post" action="javascript:selpayIK.selPaysys()" accept-charset="UTF-8">
    {foreach $formData as $key => $value}
        <input type="hidden" name="{$key}" value="{$value|escape}">
    {/foreach}

    <input type="submit" class="button" value="{$lang->form_to_pay}">
</form>
<div class="interkasssa" style="text-align: center;">
    {if is_array($payment_systems) && !empty($payment_systems)}
        {$langIk = [
            'ru' => [
                'sel_payment' => 'Выберите удобный способ оплаты',
                'sel_curr' => 'Укажите валюту',
                'click_pay' => 'Нажмите &laquo;Оплатить&raquo;',
                'pay_through' => 'Оплатить через'
            ],
            'ua' => [
                'sel_payment' => 'Выберіть зручний спосіб оплати',
                'sel_curr' => 'Вкажіть валюту',
                'click_pay' => 'Натисніть &laquo;Оплатити&raquo;',
                'pay_through' => 'Оплатити через'
            ],
            'en' => [
                'sel_payment' => 'Select a convenient payment method',
                'sel_curr' => 'Specify currency',
                'click_pay' => 'Click &laquo;Pay&raquo;',
                'pay_through' => 'Pay through'
            ]
        ]}
        {include 'payment/Interkassa/payments.tpl'}
    {else}
        {$payment_systems}
    {/if}
</div>
<script>
    var idLangIk = '{$langId}';
    var langIk = {
        en : {
            currency_not_select : 'You have not selected a currency',
            something_wrong : 'Something wrong'
        },
        ru : {
            currency_not_select : 'Вы не выбрали валюту',
            something_wrong : 'Что-то пошло не так'
        },
        ua : {
            currency_not_select : 'Ви не вибрали валюту',
            something_wrong : 'Щось пішло не так'
        }
    };
</script>
<script src="/payment/Interkassa/js/interkassa.js"></script>
<link rel="stylesheet" href="/payment/Interkassa/css/interkassa.css">
<style>
    .ik-modal{
        z-index: 9999;
    }
</style>