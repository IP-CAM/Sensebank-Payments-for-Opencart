<?php echo $header; ?><?php echo $column_left; ?>

<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-sensebank" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
            </div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="container-fluid">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_settings; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-sensebank" class="form-horizontal">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_status; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_status" class="form-control">
                                <option value="1" <?php echo $sensebank_status == 1 ? 'selected="selected"' : ''; ?>><?php echo $status_enabled; ?></option>
                                <option value="0" <?php echo $sensebank_status == 0 ? 'selected="selected"' : ''; ?>><?php echo $status_disabled; ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="">
                            <?php echo $entry_merchantLogin; ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" name="sensebank_merchantLogin" value="<?php echo $sensebank_merchantLogin; ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="">
                            <?php echo $entry_merchantPassword; ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="password" name="sensebank_merchantPassword" value="<?php echo $sensebank_merchantPassword; ?>" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_mode; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_mode" class="form-control">
                                <option value="test" <?php echo $sensebank_mode == 'test' ? 'selected="selected"' : ''; ?>><?php echo $mode_test; ?></option>
                                <option value="prod" <?php echo $sensebank_mode == 'prod' ? 'selected="selected"' : ''; ?>><?php echo $mode_prod; ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_stage; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_stage" class="form-control">
                                <option value="one" <?php echo $sensebank_stage == 'one' ? 'selected="selected"' : ''; ?>><?php echo $stage_one; ?></option>
                                <option value="two" <?php echo $sensebank_stage == 'two' ? 'selected="selected"' : ''; ?>><?php echo $stage_two; ?></option>
                            </select>
                        </div>
                    </div>

                    <?php if ($sensebank_enable_sensebank_cacert_option) { ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_enable_sensebank_cacert; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_enable_sensebank_cacert" class="form-control">
                                <option value="1" <?php echo $sensebank_enable_sensebank_cacert == 1 ? 'selected="selected"' : ''; ?>><?php echo $sensebank_cacert_enabled; ?></option>
                                <option value="0" <?php echo $sensebank_enable_sensebank_cacert == 0 ? 'selected="selected"' : ''; ?>><?php echo $sensebank_cacert_disabled; ?></option>
                            </select>
                        </div>
                    </div>
                    <?php }?>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="input-order-status-before"><?php echo $entry_order_status_before; ?></label>
                        <div class="col-sm-9">
                            <select name="sensebank_order_status_before_id" id="input-order-status-before" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $sensebank_order_status_before_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="input-order-status-completed"><?php echo $entry_order_status_completed; ?></label>
                        <div class="col-sm-9">
                            <select name="sensebank_order_status_completed_id" id="input-order-status-completed" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $sensebank_order_status_completed_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

<?php if ($sensebank_enable_refund_options == true) { ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="input-order-status-reversed"><?php echo $entry_order_status_reversed; ?></label>
                        <div class="col-sm-9">
                            <select name="sensebank_order_status_reversed_id" id="input-order-status-reversed" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $sensebank_order_status_reversed_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="input-order-status-refunded"><?php echo $entry_order_status_refunded; ?></label>
                        <div class="col-sm-9">
                            <select name="sensebank_order_status_refunded_id" id="input-order-status-refunded" class="form-control">
                                <?php foreach ($order_statuses as $order_status) { ?>
                                <?php if ($order_status['order_status_id'] == $sensebank_order_status_refunded_id) { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                                <?php } else { ?>
                                <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                                <?php } ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
<?php } ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="sensebank_payment_sort_order">
                            <?php echo $entry_sortOrder; ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" name="sensebank_payment_sort_order" id="sensebank_payment_sort_order" value="<?php echo $sensebank_payment_sort_order; ?>" class="form-control" />
                        </div>
                    </div>

<?php if ($enable_back_url_settings == true) { ?>
                    <!-- backToShopURL -->
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="">
                            <?php echo $entry_backToShopURL; ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" name="sensebank_backToShopURL" id="sensebank_backToShopURL" value="<?php echo $sensebank_backToShopURL; ?>" class="form-control" />
                        </div>
                    </div>
<?php } ?>


                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_logging; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_logging" class="form-control">
                                <option value="1" <?php echo $sensebank_logging == 1 ? 'selected="selected"' : ''; ?>><?php echo $logging_enabled; ?></option>
                                <option value="0" <?php echo $sensebank_logging == 0 ? 'selected="selected"' : ''; ?>><?php echo $logging_disabled; ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_currency; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_currency" class="form-control">
                                <?php foreach ($currency_list as $currency) { ?>
                                    <option value="<?php echo $currency['numeric']; ?>" <?php echo $currency['numeric'] == $sensebank_currency ? 'selected="selected"' : '';?>>
                                        <?php echo $currency['numeric'] == 0 ? $currency['alphabetic'] : $currency['alphabetic'] . ' (' . $currency['numeric'] . ')'; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <?php if ($sensebank_enable_fiscale_options == true) { ?>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_ofdStatus; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_ofd_status" class="form-control">
                                <option value="1" <?php echo $sensebank_ofd_status == 1 ? 'selected="selected"' : ''; ?>><?php echo $entry_ofd_enabled; ?></option>
                                <option value="0" <?php echo $sensebank_ofd_status == 0 ? 'selected="selected"' : ''; ?>><?php echo $entry_ofd_disabled; ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_FFDVersionFormat; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_FFDVersion" class="form-control">
                                <?php foreach ($FFDVersionList as $FFD_version) { ?>
                                <option value="<?php echo $FFD_version['value']; ?>" <?php echo $FFD_version['value'] == $sensebank_FFDVersion ? 'selected="selected"' : '';?>>
                                <?php echo $FFD_version['value'] == 0 ? $FFD_version['title'] : $FFD_version['title']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_taxSystem; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_taxSystem" class="form-control">
                                <?php foreach ($taxSystem_list as $taxSystem) { ?>
                                <option value="<?php echo $taxSystem['numeric']; ?>" <?php echo $taxSystem['numeric'] == $sensebank_taxSystem ? 'selected="selected"' : '';?>>
                                <?php echo $taxSystem['numeric'] == 0 ? $taxSystem['alphabetic'] : $taxSystem['alphabetic']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_taxType; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_taxType" class="form-control">
                                <?php foreach ($taxType_list as $taxType) { ?>
                                <option value="<?php echo $taxType['numeric']; ?>" <?php echo $taxType['numeric'] == $sensebank_taxType ? 'selected="selected"' : '';?>>
                                <?php echo $taxType['numeric'] == 0 ? $taxType['alphabetic'] : $taxType['alphabetic']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_paymentMethod; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_paymentMethodType" class="form-control">
                                <?php foreach ($ffd_paymentMethodTypeList as $ffd_paymentMethodType) { ?>
                                <option value="<?php echo $ffd_paymentMethodType['numeric']; ?>" <?php echo $ffd_paymentMethodType['numeric'] == $sensebank_paymentMethodType ? 'selected="selected"' : '';?>>
                                <?php echo $ffd_paymentMethodType['numeric'] == 0 ? $ffd_paymentMethodType['alphabetic'] : $ffd_paymentMethodType['alphabetic']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- delivery method -->
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_paymentMethodDelivery; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_deliveryPaymentMethodType" class="form-control">
                                <?php foreach ($ffd_paymentMethodTypeList as $ffd_paymentMethodType) { ?>
                                <option value="<?php echo $ffd_paymentMethodType['numeric']; ?>" <?php echo $ffd_paymentMethodType['numeric'] == $sensebank_deliveryPaymentMethodType ? 'selected="selected"' : '';?>>
                                <?php echo $ffd_paymentMethodType['numeric'] == 0 ? $ffd_paymentMethodType['alphabetic'] : $ffd_paymentMethodType['alphabetic']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <!-- object -->
                    <div class="form-group">
                        <label class="col-sm-3 control-label">
                            <?php echo $entry_paymentObject; ?>
                        </label>
                        <div class="col-sm-9">
                            <select name="sensebank_paymentObjectType" class="form-control">
                                <?php foreach ($ffd_paymentObjectTypeList as $ffd_paymentObjectType) { ?>
                                <option value="<?php echo $ffd_paymentObjectType['numeric']; ?>" <?php echo $ffd_paymentObjectType['numeric'] == $sensebank_paymentObjectType ? 'selected="selected"' : '';?>>
                                <?php echo $ffd_paymentObjectType['numeric'] == 0 ? $ffd_paymentObjectType['alphabetic'] : $ffd_paymentObjectType['alphabetic']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>

                </form>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>