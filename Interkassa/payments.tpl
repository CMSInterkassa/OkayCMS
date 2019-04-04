<button type="button" class="sel-ps-ik btn btn-info btn-lg" data-toggle="modal" data-target="#InterkassaModal" style="display: none;">
    Select Payment Method
</button>
<div id="InterkassaModal" class="ik-modal fade" role="dialog">
    <div class="ik-modal-dialog ik-modal-lg">
        <div class="ik-modal-content" id="plans">
            <div class="container">
                <h3>
                    1. {$langIk[$langId]['sel_payment']}<br>
                    2. {$langIk[$langId]['sel_curr']}<br>
                    3. {$langIk[$langId]['click_pay']}<br>
                </h3>
                <div class="ik-row">
                    {foreach $payment_systems as $ps => $info}
                    <div class="col-sm-3 text-center payment_system">
                        <div class="panel panel-warning panel-pricing">
                            <div class="panel-heading">
                                <div class="panel-image">
                                    <img src="/payment/Interkassa/images/{$ps}.png"
                                         alt="{$info['title']}">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="radioBtn btn-group">
                                        {foreach $info['currency'] as $currency => $currencyAlias}
                                        <a class="btn btn-primary btn-sm notActive"
                                           data-toggle="fun"
                                           data-title="{$currencyAlias}">{$currency}</a>
                                        {/foreach}
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <a class="btn btn-lg btn-block btn-success ik-payment-confirmation"
                                   data-title="{$ps}"
                                   href="#">{$langIk[$langId]['pay_through']}<br>
                                    <strong>{$info['title']}</strong>
                                </a>
                            </div>
                        </div>
                    </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>