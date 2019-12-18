<script>
//<![CDATA[

        function pay(elm){

	        var paylike = Paylike('{$paylike_payment_cc_data.Public_Key}');

        	var cart_total_cost = '{$cart.total_cost}';
	        var paylike_multiplier = '{$paylike_payment_cc_data.Multiplier|default:100}';
        	var paylike_cart_total_cost;

                paylike_cart_total_cost = cart_total_cost*paylike_multiplier;

                paylike.popup({
                        title: '{$paylike_payment_cc_data.popup_title|replace:"'":"\'"}',
                        description: '{$paylike_payment_cc_data.popup_description|replace:"'":"\'"}',
                        currency: '{$paylike_payment_cc_data.CurrencyCode}',
                        amount: paylike_cart_total_cost,
			locale: '{$shop_language}',
                        custom: {
                                products: {$paylike_products_json},
                                customer: {
                                    name: '{$userinfo.address.B.firstname|replace:"'":"\'"} {$userinfo.address.B.lastname|replace:"'":"\'"}',
                                    email: '{$userinfo.email}',
                                    phoneNo: '{$userinfo.address.B.phone|replace:"'":"\'"}',
                                    address: '{$userinfo.address.B.address|replace:"'":"\'"}{if $userinfo.address.B.address_2 ne ""} {$userinfo.address.B.address_2|replace:"'":"\'"}{/if}, {$userinfo.address.B.city|replace:"'":"\'"}, {$userinfo.address.B.statename|replace:"'":"\'"}, {$userinfo.address.B.countryname|replace:"'":"\'"}, {$userinfo.address.B.zipcode|replace:"'":"\'"}',
                                    IP: '{$paylike_CLIENT_IP}'
                                },
                                platform: {
                                    name: 'X-cart',
                                    version: '{$config.version}'
                                }
                        }
                },
                function (err, r) {
                    if (typeof r !== 'undefined') {
                        if (err) {
                            return console.warn(err);
                        }

                        $(document.createElement('input'))
                              .attr('type','hidden')
                              .attr('name','paylike_trans_ref')
                              .val(r.transaction.id)
                              .appendTo(elm);

			{if $active_modules.One_Page_Checkout}
                        	showXCblockUI(msg_being_placed);
			{else}
				showXCblockUI('{$lng.msg_order_is_being_placed}');

				if (document.getElementById('msg'))
					document.getElementById('msg').style.display = '';

				if (document.getElementById('btn_box'))
					document.getElementById('btn_box').style.display = 'none';
			{/if}

                        elm.submit();
                    }
                }
        	);
        }
//]]>
</script>
