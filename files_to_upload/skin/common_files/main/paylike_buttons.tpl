{if $show_paylike_buttons}
<br /> 
<B>{$lng.lbl_paylike_transaction}:</B> {$order.extra.paylike_txnid}
<br />
{$lng.txt_paylike_button_info}
<br />
<br />
{/if}


{if $show_paylike_buttons.capture eq "Y"}
<input type="button" value="{$lng.lbl_capture}" onclick="javascript: if (confirm('{$lng.txt_are_you_sure|wm_remove|escape:javascript}')) self.location = 'order.php?orderid={$order.orderid}&amp;mode=paylike_capture';" />
{/if}

{if $show_paylike_buttons.void eq "Y"}
<input type="button" value="{$lng.lbl_decline}" onclick="javascript: if (confirm('{$lng.txt_are_you_sure|wm_remove|escape:javascript}')) self.location = 'order.php?orderid={$order.orderid}&amp;mode=paylike_void';"/>
{/if}

{if $show_paylike_buttons.refund eq "Y"}
<input type="button" value="{$lng.lbl_refund}" onclick="javascript: if (confirm('{$lng.txt_are_you_sure|wm_remove|escape:javascript}')) self.location = 'order.php?orderid={$order.orderid}&amp;mode=paylike_refund';"/>
{/if}

{if $show_paylike_buttons}
<br />
<br />
<br />
{$lng.txt_paylike_status_below}
<br />
<br />
{/if}
