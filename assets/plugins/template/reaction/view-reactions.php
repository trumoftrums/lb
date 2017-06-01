<div class="window-container"> <div class="window-background" onclick="SK_closeWindow();"></div>
<div class="reaction-wrapper">
<div class="reaction-header-wrapper">
<div class="reaction-header-tab selected-tab" title="All" onclick="viewReactions('all', {$post_id}, this);"><?php echo $wo['lang']['plugin_combo_all']; ?></div>
<abbr class="space1">&#183;</abbr><div class="reaction-header-tab blue-tab" title="Like" onclick="viewReactions('like', {$post_id}, this);"><img src="<?php echo $wo['config']['site_url']; ?>/assets/plugins/img/reaction_like.png" width="13px" style="display:inline-block;vertical-align:top;margin-top:2px;">{numReactions('likes')}</div>
<abbr class="space1">&#183;</abbr><div class="reaction-header-tab red-tab" title="Love" onclick="viewReactions('love', {$post_id}, this);"><img src="<?php echo $wo['config']['site_url']; ?>/assets/plugins/img/reaction_love.png" width="13px" style="display:inline-block;vertical-align:top;margin-top:2px;">{numReactions('love')}</div>
<abbr class="space1">&#183;</abbr><div class="reaction-header-tab yellow-tab" title="Haha" onclick="viewReactions('haha', {$post_id}, this);"><img src="<?php echo $wo['config']['site_url']; ?>/assets/plugins/img/reaction_haha.png" width="13px" style="display:inline-block;vertical-align:top;margin-top:2px;">{numReactions('haha')}</div>
<abbr class="space1">&#183;</abbr><div class="reaction-header-tab yellow-tab" title="Wow" onclick="viewReactions('wow', {$post_id}, this);"><img src="<?php echo $wo['config']['site_url']; ?>/assets/plugins/img/reaction_wow.png" width="13px" style="display:inline-block;vertical-align:top;margin-top:2px;">{numReactions('wow')}</div>
<abbr class="space1">&#183;</abbr><div class="reaction-header-tab yellow-tab" title="Sad" onclick="viewReactions('sad', {$post_id}, this);"><img src="<?php echo $wo['config']['site_url']; ?>/assets/plugins/img/reaction_sad.png" width="13px" style="display:inline-block;vertical-align:top;margin-top:2px;">{numReactions('sad')}</div>
<abbr class="space1">&#183;</abbr><div class="reaction-header-tab orange-tab" title="Angry" onclick="viewReactions('angry', {$post_id}, this);"><img src="<?php echo $wo['config']['site_url']; ?>/assets/plugins/img/reaction_angry.png" width="13px" style="display:inline-block;vertical-align:top;margin-top:2px;">{numReactions('angry')}</div>
<span class="reaction-close-btn" title="Close window" onclick="SK_closeWindow();"><i class="icon-remove"></i></span>
</div>
<div class="reaction-content-wrapper">{getReactionsListTemplate($r)}</div>
</div>
</div>