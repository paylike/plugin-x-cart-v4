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
 * This script implements checkout facility
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Cart
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-present Qualiteam software Ltd <info@x-cart.com>
 * @license    https://www.x-cart.com/license-agreement-classic.html X-Cart license agreement
 * @version    func.paylike.php
 * @link       https://www.x-cart.com/
 * @see        ____file_see____
 */

function func_get_paylike_settings_if_enabled(){
    global $cart, $sql_tbl, $smarty, $CLIENT_IP;

    if (!empty($cart['paymentid'])){
        $processor_file = func_query_first_cell("SELECT processor FROM $sql_tbl[ccprocessors] WHERE paymentid='$cart[paymentid]'");
        if ($processor_file == 'cc_paylike.php'){
            $paylike_module_params = func_query_first("SELECT param02, param04, param06, param07, param08, testmode FROM $sql_tbl[ccprocessors] WHERE processor='$processor_file'");
            if ($paylike_module_params['testmode'] == 'Y'){
                $paylike_payment_cc_data['Public_Key'] = $paylike_module_params['param04'];
            } else {
                $paylike_payment_cc_data['Public_Key'] = $paylike_module_params['param02'];
            }

            #$paylike_payment_cc_data['popup_title'] = $paylike_module_params['param06'];
            $paylike_payment_cc_data['popup_title'] = strip_tags(func_get_langvar_by_name('lbl_paylike_popup_title_text', NULL, FALSE, TRUE, TRUE));
            #$paylike_payment_cc_data['popup_description'] = $paylike_module_params['param07'];
            $paylike_payment_cc_data['popup_description'] = strip_tags(func_get_langvar_by_name('lbl_paylike_popup_description_text', NULL, FALSE, TRUE, TRUE));

            $CurrencyCode_Exponent = explode('_', $paylike_module_params['param08']);
            $paylike_payment_cc_data['CurrencyCode'] = $CurrencyCode_Exponent[0];
            $paylike_payment_cc_data['Exponent'] = $CurrencyCode_Exponent[1];

            if (isset($paylike_payment_cc_data['Exponent']) && $paylike_payment_cc_data['Exponent'] > 0){
                $paylike_payment_cc_data['Multiplier'] = pow( 10, $paylike_payment_cc_data['Exponent'] );
            }
            else {
                $paylike_payment_cc_data['Multiplier'] = pow( 10, 2 );
            }

            $smarty->assign('paylike_payment_cc_data', $paylike_payment_cc_data);
	        $smarty->assign('paylike_CLIENT_IP', $CLIENT_IP);

            if (!empty($cart['products']) && is_array($cart['products'])){
                $paylike_products_array = array();
                $p = 0;
                foreach ( $cart['products'] as $key => $product ) {
                    $paylike_products_array[ $p ] = array(
                        'ID'       => $product['productid'],
                        'name'     => $product['product'],
                        'quantity' => $product['amount']
                    );
                    $p ++;
                }
                $paylike_products_json = json_encode( $paylike_products_array );
                $smarty->assign('paylike_products_json', $paylike_products_json);
            }
        }
    }
}
?>
