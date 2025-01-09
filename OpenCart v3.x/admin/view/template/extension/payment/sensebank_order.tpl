<?php if (isset($gateway_order)) { ?>

    <h3>Order # <?php echo $gateway_order['order_id'];?> [<?php echo $gateway_order['gateway_order_reference'];?>]</h3>

    <table id="sensebank-table" class="table table-bordered">
        <tbody>
        <tr id="input-fields">
            <td class="text-right col-sm-2">
                <?php echo $entry_payment_status; ?>
            </td>
            <td class="text-left">
                <div class="col-sm-6">
                <span class="btn btn-info btn-sensebank"
                      data-action="payment_status"><?php echo $button_payment_status; ?></span>
                </div>
            </td>
        </tr>
        <?php if ($gateway_order['status'] == 0) { ?>
            <?php if ($gateway_order['status_deposited'] == 1) { ?> <!-- APPROVED -->
                <tr id="input-fields">
                    <td class="text-right col-sm-2">
                        <?php echo $entry_refund; ?>
                    </td>
                    <td class="text-left">

                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo $gateway_amount;?>" id="user_amount">
                                <span class="input-group-btn">
                                <button class="btn btn-warning btn-sensebank"
                                        data-action="payment_refund_partial"
                                        type="button"><?php echo $button_refund ;?></button>
                                <button class="btn btn-warning btn-sensebank"
                                        data-action="payment_refund_full"
                                        type="button"><?php echo $button_refund_full ?></button>
                            </span>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <?php echo $help_sensebank_amount ?>
                        </div>

                    </td>
                </tr>
            <?php } elseif ($gateway_order['status_deposited'] == 0) { ?> <!-- DEPOSITED -->
                <tr id="input-fields">
                    <td class="text-right col-sm-2">
                        <?php echo $entry_deposit ?>
                    </td>
                    <td class="text-left">

                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="text" class="form-control" value="<?php echo $gateway_amount;?>" id="user_amount">
                                <span class="input-group-btn">
                                <button class="btn btn-success btn-sensebank"
                                        data-action="payment_deposit_partial"
                                        type="button"><?php echo $button_deposit ?></button>
                                <button class="btn btn-success btn-sensebank"
                                        data-action="payment_deposit_full"
                                        type="button"><?php echo $button_deposit_full ?></button>
                            </span>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <?php echo $help_sensebank_amount ?>
                        </div>


                    </td>
                </tr>
            <?php } ?>
            <?php if ($gateway_order['status_reversed'] == 0) { ?>
                <tr id="input-fields">
                    <td class="text-right col-sm-2">
                        <?php echo $entry_reverse ?>
                    </td>
                    <td class="text-left">
                        <div class="col-sm-6">
                        <span class="btn btn-danger btn-sensebank"
                              data-action="payment_reverse"> <?php echo $button_reverse ?></span>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        <?php } ?>
        </tbody>
    </table>
    <script type="text/javascript"><!--
        $('.btn-sensebank').on('click', function () {
            let clickedElement = $(this);
            let orderAction = $(this).data('action');
            let userAmount = 0;

            if (orderAction == 'payment_deposit_partial' || orderAction == 'payment_refund_partial') {
                userAmount = $('#user_amount').val();
            }

            $.ajax({
                url: 'index.php?route=extension/payment/sensebank/gatewayOrderAction&token=<?php echo $token;?>&order_id=<?php echo $order_id;?>',
                type: 'POST',
                data: {
                    'user_amount': userAmount,
                    'order_action': orderAction
                },
                dataType: 'json',
                beforeSend: function (xhr) {
                    clickedElement.append('<i style="margin-left: 10px;" id="sensebank-loading" class="fa fa-refresh"></i>');
                },
                complete: function (xhr) {
                    if ($('#sensebank-loading').length) {
                        $('#sensebank-loading').remove();
                    }
                },
                success: function (json) {

                    if (typeof json.error != 'undefined') {
                        //alert(json.error);
                        $('.alert').remove();

                        if (json['error']) {
                            $('#sensebank-table').before('<div class="alert alert-danger alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['error'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
                        }
                    } else if (typeof json.success != 'undefined') {

                        if (typeof json.history != 'undefined') {
                            addOrderHistory(json.history.order_status_id, json.history.comment, json.history.notify);
                        }

                        //alert(json.success);
                        $('.alert').remove();
                        $('#sensebank-table').before('<div class="alert alert-success alert-dismissible"><i class="fa fa-exclamation-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

                        if (typeof json.redirect != 'undefined') {
                            window.location.reload();
                        }
                    }
                }
            });

            function addOrderHistory(order_status_id, comment, notify) {
                $.ajax({
                    url: '<?php echo $catalog; ?>index.php?route=api/order/history&api_token=<?php echo $api_token; ?>&store_id=<?php echo $store_id; ?>&order_id=<?php echo $order_id; ?>',
                    type: 'post',
                    dataType: 'json',
                    data: 'order_status_id=' + order_status_id + '&notify=' + notify + '&comment=' + comment
                });
            }
        });
        //--></script>
<?php } ?>