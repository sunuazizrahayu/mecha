<?php echo $messages; ?>
<h3><?php echo $file->title; ?></h3>
<p><?php echo $file->content; ?></p>
<form class="form-kill form-plugin" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/plugin'); ?>
<?php echo Form::hidden('token', $token); ?>
</form>