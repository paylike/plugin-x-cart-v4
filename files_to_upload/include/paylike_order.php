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
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-present Qualiteam software Ltd <info@x-cart.com>
 * @license    https://www.x-cart.com/license-agreement-classic.html X-Cart license agreement
 * @link       https://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

if (empty($mode)) $mode = '';

if (!empty($order['paymentid'])){
    $payment_cc_processor = func_query_first_cell("SELECT processor FROM $sql_tbl[ccprocessors] WHERE paymentid='$order[paymentid]'");
}

if ($payment_cc_processor == 'cc_paylike.php' && !empty($order['extra']['paylike_txnid'])){

    $paylike_trans_ref = $order['extra']['paylike_txnid'];

    require_once $xcart_dir . '/payment/cc_paylike_api/Client.php';

    $module_params = func_query_first("SELECT param01, param02, param03, param04, testmode FROM $sql_tbl[ccprocessors] WHERE paymentid='$order[paymentid]'");

    if ($module_params['testmode'] == 'Y') {
        $App_Key =$module_params['param03'];
        $Public_Key = $module_params['param04'];
    } else {
        $App_Key =$module_params['param01'];
        $Public_Key = $module_params['param02'];
    }

    Paylike\Client::setKey( $App_Key );
    $trans_data = Paylike\Transaction::fetch( $paylike_trans_ref );

    if (!empty($trans_data)){

        if (!in_array($mode, array('paylike_capture', 'paylike_void', 'paylike_refund'))){

           $show_paylike_buttons = array();

           if (
                $trans_data['transaction']['capturedAmount'] > 0 
                && $trans_data['transaction']['capturedAmount'] == $trans_data['transaction']['amount']
                && empty($trans_data['transaction']['refundedAmount'])
           ){
               $show_paylike_buttons['refund'] = 'Y';
           }
           elseif ($trans_data['transaction']['pendingAmount'] > 0 && $trans_data['transaction']['pendingAmount'] == $trans_data['transaction']['amount']){
               $show_paylike_buttons['capture'] = 'Y';
               $show_paylike_buttons['void'] = 'Y';
           }

           $smarty->assign('show_paylike_buttons', $show_paylike_buttons);
        }
        else { 
            // in_array($mode, array('paylike_capture', 'paylike_void', 'paylike_refund')

            $paylike_data = array(
                'amount'   => $trans_data['transaction']['amount'],
                'currency' => $trans_data['transaction']['currency']
            );

            if ($mode == "paylike_void"){
                
                $paylike_transaction_data = Paylike\Transaction::void( $paylike_trans_ref, $paylike_data );

                if (!empty($paylike_transaction_data['transaction']) && $paylike_transaction_data['amount'] == $paylike_transaction_data['voidedAmount']) {
                    $mode = "status_change";
                    $status = "D";
                }
                else {
                    $mode = "paylike_mode_error";
                }        
            }

            if ($mode == "paylike_capture"){
                
                $paylike_transaction_data = Paylike\Transaction::capture( $paylike_trans_ref, $paylike_data );

                if (!empty($paylike_transaction_data['transaction']) && $paylike_transaction_data['amount'] == $paylike_transaction_data['capturedAmount']) {
                    $mode = "status_change";
                    $status = "P";
                }
                else {
                    $mode = "paylike_mode_error";
                }
            }

            if ($mode == "paylike_refund"){

                $paylike_transaction_data = Paylike\Transaction::refund( $paylike_trans_ref, $paylike_data );

                if (!empty($paylike_transaction_data['transaction']) && $paylike_transaction_data['amount'] == $paylike_transaction_data['refundedAmount']) {
                    $mode = "status_change";
                    $status = "R";
                }
                else {
                    $mode = "paylike_mode_error";
                }
            }

            if ($mode == "status_change"){

                func_change_order_status($orderid, $status);

                // must be called after FUnC_change_order_status
                XCOrderTracking::sendNotification();

                $top_message = array(
                    'content' => func_get_langvar_by_name('txt_order_has_been_changed')
                );
            }

            if ($mode == "paylike_mode_error"){
                $top_message = array(
                    'content' => func_get_langvar_by_name('lbl_paylike_order_not_changed')
                );
            }

            func_header_location("order.php?orderid=" . $orderid);
        }

    }
}
?>
