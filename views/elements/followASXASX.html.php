<?php $following = $following->isFollowing($followed) ? true : false ?>
<a class="<?= $class; ?> follow <?= $following ? 'btn-success' : ''; ?>" rel="tooltip" 
   title="<?= $following ? $unfollow_text : $follow_text; ?>" 
   data-follow_text="<?= $follow_text ?>" 
   data-unfollow_text="<?= $unfollow_text ?>" href="#" 
   data-followed="<?= $followed->modelName() ?>" 
   data-followed_id="<?= $followed->_id ?>" >
<i class="icon-tags <?= $following ? 'icon-white' : ''; ?>"></i> <?= $button_text; ?>
</a>
