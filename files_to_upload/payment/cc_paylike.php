<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-present Qualiteam software Ltd <info@x-cart.com>         |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE  |
| AT THE FOLLOWING URL: https://www.x-cart.com/license-agreement-classic.html |
|                                                                             |
| THIS AGREEMENT EXPRESSES THE TERMS AND CONDITIONS ON WHICH YOU MAY USE THIS |
| SOFTWARE PROGRAM AND ASSOCIATED DOCUMENTATION THAT QUALITEAM SOFTWARE LTD   |
| (hereinafter referred to as "THE AUTHOR") OF REPUBLIC OF CYPRUS IS          |
| FURNISHING OR MAKING AVAILABLE TO YOU WITH THIS AGREEMENT (COLLECTIVELY,    |
| THE "SOFTWARE"). PLEASE REVIEW THE FOLLOWING TERMS AND CONDITIONS OF THIS   |
| LICENSE AGREEMENT CAREFULLY BEFORE INSTALLING OR USING THE SOFTWARE. BY     |
| INSTALLING, COPYING OR OTHERWISE USING THE SOFTWARE, YOU AND YOUR COMPANY   |
| (COLLECTIVELY, "YOU") ARE ACCEPTING AND AGREEING TO THE TERMS OF THIS       |
| LICENSE AGREEMENT. IF YOU ARE NOT WILLING TO BE BOUND BY THIS AGREEMENT, DO |
| NOT INSTALL OR USE THE SOFTWARE. VARIOUS COPYRIGHTS AND OTHER INTELLECTUAL  |
| PROPERTY RIGHTS PROTECT THE SOFTWARE. THIS AGREEMENT IS A LICENSE AGREEMENT |
| THAT GIVES YOU LIMITED RIGHTS TO USE THE SOFTWARE AND NOT AN AGREEMENT FOR  |
| SALE OR FOR TRANSFER OF TITLE. THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY  |
| GRANTED BY THIS AGREEMENT.                                                  |
+-----------------------------------------------------------------------------+
\*****************************************************************************/

/**
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @license    https://www.x-cart.com/license-agreement-classic.html X-Cart license agreement
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

require_once __DIR__.'/cc_paylike_api/Client.php';

if ($module_params['testmode'] == 'Y') {
    $App_Key =$module_params['param03'];
    $Public_Key = $module_params['param04'];
} else {
    $App_Key =$module_params['param01'];
    $Public_Key = $module_params['param02'];
}

Paylike\Client::setKey( $App_Key );
$trans_data = Paylike\Transaction::fetch( $paylike_trans_ref );

$paylike_log = '';
$paylike_error = '';

if ( is_null( $trans_data ) ) {
    $paylike_log = 'Invalid transaction data. Unable to authorize transaction.';
    $paylike_error = 'error_invalid_transaction_data';
}

if ( is_array( $trans_data ) && isset( $trans_data['error'] ) && ! is_null( $trans_data['error'] ) && $trans_data['error'] == 1 ) {
    $paylike_log = 'Transaction error returned: ' . $trans_data['message'];
    $paylike_error = 'error_transaction_error_returned';
} elseif ( is_array( $trans_data ) && isset( $trans_data[0]['message'] ) && ! is_null( $trans_data[0]['message'] ) ) {
    $paylike_log = 'Transaction error returned: ' . $trans_data[0]['message'] ;
    $paylike_error = 'error_transaction_error_returned';
}

if ( isset( $trans_data['transaction'] ) ) {
    if ( isset( $trans_data['transaction']['successful'] ) ) {

        $order_captured = false;

        if ( $module_params['use_preauth'] != 'Y' ) {
            $data = array(
                'amount'   => $trans_data['transaction']['amount'],
                'currency' => $trans_data['transaction']['currency']
            );
            $capture_data = Paylike\Transaction::capture( $paylike_trans_ref, $data );
            if ( ! isset( $capture_data['transaction'] ) ) {
                $paylike_log ='Unable to capture.';
            } else {
                $paylike_log = 'Transaction finished. Captured.';
                $order_captured = true;
            }
        } else {
            $paylike_log = 'Transaction authorized.';
        }
    }
}
else {
    $paylike_log = 'Transaction error. Empty transaction results.';
    $paylike_error = 'error_invalid_transaction_data';
}

$extra_order_data = array();
$extra_order_data['paylike_txnid'] = $paylike_trans_ref;

if (!empty($paylike_error)){
    $bill_output['code'] = 2;
    $bill_output['billmes'] = "Failed. " . $paylike_log . " Paylike Transaction: " . $paylike_trans_ref . ".";

    $extra_order_data['capture_status'] = 'F';
}
else {
    $bill_output['code'] = 1;
    $bill_output['billmes'] = $paylike_log . " Paylike Transaction: " . $paylike_trans_ref . ".";

    if (!$order_captured){
        $bill_output['is_preauth'] = 'Y';
        $extra_order_data['capture_status'] = 'A';
    }
    else {
        $extra_order_data['capture_status'] = 'C';
    }
}
?>
