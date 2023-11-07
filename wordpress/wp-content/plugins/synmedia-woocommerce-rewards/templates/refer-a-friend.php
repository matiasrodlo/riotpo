<?php
/**
 * Refer a friend
 *
 * Shows the page to refer a friend
 */
 
global $woocommerce, $swr_settings, $refer_message, $refer_option, $post;

$googer = new GoogleURLAPI();

$c_lang = str_replace('-','_',get_bloginfo('language'));
$langs = explode('_', $c_lang);
$s_lang = $langs[0];

$c_link = get_bloginfo("url").'?ref='.get_current_user_id();
$short_link = $googer->shorten($c_link);

?>

<?php if($refer_option==1): ?>
<form action="<?php echo esc_url( get_permalink($post->ID) ); ?>" method="post" class="refer_a_friend">
<?php $woocommerce->nonce_field('refer_a_friend') ?>
<p class="form-row"><label for="swr_refer_emails"><?php _e('Email addresses', 'rewards') ?></label> <textarea class="input-text" type="text" name="swr_refer_emails" id="swr_refer_emails" placeholder="<?php _e("Enter your friend's email addresses, separated by commas", 'rewards') ?>"></textarea></p>

<p class="form-row"><label for="swr_refer_message"><?php _e('Your message (optional)', 'rewards') ?></label> <textarea class="input-text" type="text" name="swr_refer_message" id="swr_refer_message" placeholder="<?php _e("Enter the message you want to send to your friends", 'rewards') ?>"><?php echo($refer_message); ?></textarea></p>

<p class="form-row"><input type="submit" class="button" name="send" value="<?php _e('Send invitations', 'rewards') ?>"></p>
<div class="clear"></div>
</form>
<?php elseif($refer_option==2): ?>

<p class="form-row">
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php echo($c_lang); ?>/all.js#xfbml=1";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<div class="fb-like" data-href="<?php echo($c_link); ?>1" data-send="true" data-width="450" data-show-faces="true" data-action="recommend"></div>
<div class="clear"></div>
<a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo($short_link); ?>" data-text="<?php _e("Place your first order and earn more rewards than ordinary.","rewards"); ?>" data-lang="<?php echo($s_lang); ?>" data-size="large" data-count="none">Tweeter</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
<div class="clear"></div>
<!-- Place this tag where you want the share button to render. -->
<div class="g-plus" data-action="share" data-annotation="none" data-height="24" data-href="<?php echo($c_link); ?>"></div>

<!-- Place this tag after the last share tag. -->
<script type="text/javascript">
  (function() {
    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
    po.src = 'https://apis.google.com/js/plusone.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
  })();
</script>
</p>
<div class="clear"></div>
<?php elseif($refer_option==3): ?>

<p class="form-row"><code class="swr_refer_link"><?php echo($c_link); ?></code> or <code class="swr_refer_link"><?php echo($short_link); ?></code></p>
<div class="clear"></div>

<?php elseif($refer_option==4): ?>
<p class="form-row">
	<img src="https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=<?php echo(urlencode($short_link)); ?>&choe=UTF-8" class="qrcode small_qr" />
	<img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl=<?php echo(urlencode($short_link)); ?>&choe=UTF-8" class="qrcode medium_qr" />
	<img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=<?php echo(urlencode($short_link)); ?>&choe=UTF-8" class="qrcode big_qr" />
</p>
<div class="clear"></div>
<?php endif; ?>