<ul class="list-group"><li class="panel-widget ads-side">
<div class="head">

<?php if($preview == 0){ include './themes/' . $wo['config']['theme'] . '/layout/plugins/ads_random_btn.phtml'; } ?>

<span><img class="fa fa-fw mr5" src="<?php echo $wo['config']['theme_url'];?>/img/promote.png" alt="ads" title="ads"><?php echo $wo['lang']['plugin_ads_sponsored']; ?></span></div>
    <div class="ad-image">
        <a class="ads-click-link" data-id="<?php echo $ad['id']; ?>" onclick="return window.open('<?php echo $ad['link']; ?>')" href="javascript:void(0)" data-ajaxify="true"><img src="<?php echo $ad['images']; ?>"></a>
    </div>
<h5 class="ad-title"><a class="ads-click-link" data-id="<?php echo $ad['id']; ?>" onclick="return window.open('<?php echo $ad['link']; ?>')" href="javascript:void(0)"><?php echo $ad['title']; ?></a> </h5>
<a class="ads-click-link" data-id="<?php echo $ad['id']; ?>" onclick="return window.open('<?php echo $ad['link']; ?>')" href="javascript:void(0)"><?php echo $ad['display_link']; ?></a>
<p class="ad-description"><?php echo $ad['description']; ?></p>
</li> 
</ul>