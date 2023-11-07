jQuery(document).ready(function($){
	$(".my_account_orders thead tr th.order-total").after('<th class="order-total"><span class="nobr">'+rewards_title+'</span></th>');
	$(".my_account_orders tbody tr td.order-total").after('<td class="order-total" width="1%"><span class="amount">18,38$</span></td>');
});