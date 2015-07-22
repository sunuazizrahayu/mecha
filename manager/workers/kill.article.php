<?php echo $messages; ?>
<h3><?php echo $article->title; ?></h3>
<p><?php echo $article->description; ?></p>
<p><strong><?php echo $article->total_comments_text; ?></strong></p>
<?php if( ! empty($article->css)): ?>
<pre><code><?php echo substr(Text::parse($article->css, '->encoded_html'), 0, $config->excerpt_length); ?><?php if(strlen($article->css) > $config->excerpt_length) echo ' &hellip;'; ?></code></pre>
<?php endif; ?>
<?php if( ! empty($article->js)): ?>
<pre><code><?php echo substr(Text::parse($article->js, '->encoded_html'), 0, $config->excerpt_length); ?><?php if(strlen($article->js) > $config->excerpt_length) echo ' &hellip;'; ?></code></pre>
<?php endif; ?>
<form class="form-kill form-article" id="form-kill" action="<?php echo $config->url_current . $config->url_query; ?>" method="post">
<?php echo Jot::button('action', $speak->yes); ?> <?php echo Jot::btn('reject', $speak->no, $config->manager->slug . '/article/repair/id:' . $article->id); ?>
<?php echo Form::hidden('token', $token); ?>
</form>