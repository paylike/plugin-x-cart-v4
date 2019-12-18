{if $usertype eq 'A'}
<h1>{$module_data.module_name}</h1>

{$lng.txt_cc_configure_top_text}
<br />
{* <br /><input type="button" name="paylike_signup" value="{$lng.lbl_register}" onclick="javascript: window.open('https://paylike.io/sign-up');" /><br /> *}
<br />
{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

<table cellspacing="10">

<tr>
<td>{$lng.lbl_paylike_primary_currency}:</td>
<td>
<select name="param08">
{foreach from=$paylike_currencies item=c key=code}
<option value="{$code}_{$c.exponent}"{if $module_data.param08 eq "`$code`_`$c.exponent`"} selected="selected"{/if}>{$code} ({$c.currency})</option>
{/foreach}
</select>
</td>
</tr>

<tr>
<td>{$lng.lbl_paylike_live_app_key}:</td>
<td><input type="text" name="param01" size="36" value="{$module_data.param01|escape}" /></td>
</tr>

<tr>
<td>{$lng.lbl_paylike_live_public_key}:</td>
<td><input type="text" name="param02" size="36" value="{$module_data.param02|escape}" /></td>
</tr>

<tr>
<td>{$lng.lbl_paylike_test_app_key}:</td>
<td><input type="text" name="param03" size="36" value="{$module_data.param03|escape}" /></td>
</tr>

<tr>
<td>{$lng.lbl_paylike_test_public_key}:</td>
<td><input type="text" name="param04" size="36" value="{$module_data.param04|escape}" /></td>
</tr>

<tr>
<td>{$lng.lbl_cc_testlive_mode}:</td>
<td>
<select name="testmode">
<option value=Y{if $module_data.testmode eq "Y"} selected="selected"{/if}>{$lng.lbl_cc_testlive_test}</option>
<option value=N{if $module_data.testmode eq "N"} selected="selected"{/if}>{$lng.lbl_cc_testlive_live}</option>
</select>
</td>
</tr>

<tr>
<td class="setting-name">{$lng.lbl_use_preauth_method}:</td>
<td>
<select name="use_preauth">
<option value="">{$lng.lbl_auth_and_capture_method}</option>
<option value="Y"{if $module_data.use_preauth eq 'Y'} selected="selected"{/if}>{$lng.lbl_auth_method}</option>
</select>
</td>
</tr>

<tr>
<td>{$lng.lbl_paylike_popup_title}:</td>
<td>
{$lng.lbl_paylike_PopupInfoTitle|substitute:"shop_language":$shop_language}
{* <input type="text" name="param06" size="36" value="{$module_data.param06|escape|replace:"\\":""}" /> *}
</td>
</tr>

<tr>
<td>{$lng.lbl_paylike_popup_description}:</td>
<td>
{$lng.lbl_paylike_PopupInfoDescription|substitute:"shop_language":$shop_language}
{* <input type="text" name="param07" size="72" value="{$module_data.param07|escape|replace:"\\":""}" /> *}
</td>
</tr>

</table>
<br /><br />
<input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
</form>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_ch_settings content=$smarty.capture.dialog extra='width="100%"'}
{else}

  {$lng.lbl_paylike_cc_info}

  <input type="hidden" name="cc_processor_{$payment_cc_data.paymentid}" id="cc_processor_{$payment_cc_data.paymentid}" value="paylike" />

  <script src="https://sdk.paylike.io/3.js"></script>

  {if $active_modules.Fast_Lane_Checkout}
    {include file="payments/cc_paylike_form.tpl"}
  {/if} 

{/if}
