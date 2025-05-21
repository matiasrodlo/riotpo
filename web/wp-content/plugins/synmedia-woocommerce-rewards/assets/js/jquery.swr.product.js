jQuery(document).ready(function($){
	var swrTime = null;
	$(".qty").bind("change", swr_qty_options_changed);
	$('.single_variation_wrap').bind('show_variation', swr_variations_options_changed);
	$(".variations_button").append($(".buywithpoints"));
	$("#buywithpoints_container").remove();
	
	function swr_qty_options_changed(){
		clearTimeout(swrTime);
		swrTime = setTimeout(swr_options_changed, 400);
	}
	
	function swr_variations_options_changed(){
		if($('form input[name=variation_id]').val() != ''){
			swr_options_changed();
		}
	}
	
	function swr_options_changed(){
		var product_id = 0;
		var cartaction = '';
		var qtys = '';
		if($('form input[name=variation_id]').length > 0){
			product_id = $('form input[name=variation_id]').val();
		}else if($("form.cart .qty").length > 1){
			qtys = $("form.cart .qty").serialize();
			qtys = qtys.replace(/\&/g, '|');
			qtys = qtys.replace(/\=/g, ':');
		}else{
			cartaction = $("form.cart").attr("action");
		}
		$.ajax({
			type: "POST",
			url: "/wp-admin/admin-ajax.php",
			data: "action=swr_update_product_qty"+(product_id>0?"&qty="+$(".qty").val()+"&product_id="+product_id:'')+(cartaction==''?'':"&qty="+$(".qty").val()+"&cartaction="+cartaction)+(qtys==''?'':"&qtys="+qtys),
			dataType: "json",
			success: function(data){
				if(data != undefined){
					if(data.swr_new_reward != undefined && data.swr_old_reward != undefined && data.swr_old_reward != data.swr_new_reward && $(".swr_old_reward").length <= 0){
						$(".swr_new_reward").replaceWith('<del class="swr_old_reward">'+data.swr_old_reward+'</del> <ins class="swr_new_reward">'+data.swr_new_reward+'</ins>');
					}else if(data.swr_new_reward != undefined && data.swr_old_reward != undefined && data.swr_old_reward == data.swr_new_reward && $(".swr_old_reward").length > 0){
						$(".swr_old_reward").remove();
						$(".swr_new_reward").replaceWith('<span class="swr_new_reward">'+data.swr_new_reward+'</span>');
					}else{
						if($(".swr_new_reward").length > 0 && data.swr_new_reward != undefined)
							$(".swr_new_reward").html(data.swr_new_reward);
						if($(".swr_old_reward").length > 0 && data.swr_old_reward != undefined)
							$(".swr_old_reward").html(data.swr_old_reward);
					}
				}
			}
		});
	}
	
});