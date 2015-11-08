<div class="tab-area">
  <a class="tab active" href="#tab-content-1"><?php echo Jot::icon('plug', 'fw') . ' ' . $speak->plugin; ?></a>
  <a class="tab" href="#tab-content-2"><?php echo Jot::icon('file-archive-o', 'fw') . ' ' . $speak->upload; ?></a>
</div>
<div class="tab-content-area">
  <?php echo $messages; ?>
  <div class="tab-content" id="tab-content-1">
    <h3><?php echo Config::speak('manager.title_your_', $speak->plugins); ?></h3>
    <?php if($folders): ?>
    <?php foreach($folders as $folder): $folder = File::B($folder); ?>
    <?php $r = PLUGIN . DS . $folder . DS; $c = File::exist($r . 'capture.png'); ?>
    <?php $page = Plugin::info($folder); ?>
    <div class="media<?php if( ! $c): ?> no-capture<?php endif; ?>" id="plugin:<?php echo $folder; ?>">
      <?php if($c): ?>
      <div class="media-capture" style="background-image:url('<?php echo File::url($c); ?>?v=<?php echo filemtime($c); ?>');" role="image"></div>
      <?php endif; ?>
      <h4 class="media-title"><?php echo Jot::icon(File::exist($r . 'pending.php') ? 'unlock-alt' : 'lock') . ' ' . $page->title; ?></h4>
      <div class="media-content">
        <?php

        if(preg_match('#<blockquote(>| .*?>)\s*([\s\S]*?)\s*<\/blockquote>#', $page->content, $matches)) {
            $curt = Text::parse($matches[2], '->text', '<abbr><sub><sup>'); // get first blockquote content as description
        } else {
            $curt = Converter::curt($page->content);
        }

        ?>
        <p><?php echo $curt; ?></p>
        <p>
          <?php if(File::exist($r . 'launch.php')): ?>
          <?php echo Jot::btn('begin.small:cog', $speak->manage, $config->manager->slug . '/plugin/' . $folder); ?> <?php echo Jot::btn('action.small:cog', $speak->uninstall, $config->manager->slug . '/plugin/freeze/id:' . $folder . '?o=' . $config->offset); ?>
          <?php else: ?>
          <?php if(File::exist($r . 'pending.php')): ?>
          <?php echo Jot::btn('action.small:plus-circle', $speak->install, $config->manager->slug . '/plugin/fire/id:' . $folder . '?o=' . $config->offset); ?>
          <?php endif; ?>
          <?php endif; ?>
          <?php if( ! File::exist($r . 'configurator.php') && ! File::exist($r . 'launch.php') && ! File::exist($r . 'pending.php')): ?>
          <?php echo Jot::btn('destruct.small.disabled:times-circle', $speak->remove, null); ?>
          <?php else: ?>
          <?php echo Jot::btn('destruct.small:times-circle', $speak->remove, $config->manager->slug . '/plugin/kill/id:' . $folder); ?>
          <?php endif; ?>
        </p>
      </div>
    </div>
    <?php endforeach; ?>
    <?php include DECK . DS . 'workers' . DS . 'unit.pager.1.php'; ?>
    <?php else: ?>
    <p><?php echo Config::speak('notify_' . (Request::get('id') || $config->offset === 1 ? 'empty' : 'error_not_found'), strtolower($speak->plugins)); ?></p>
    <?php endif; ?>
  </div>
  <div class="tab-content hidden" id="tab-content-2">
    <h3><?php echo Config::speak('manager.title__upload_package', $speak->plugin); ?></h3>
    <?php echo Jot::uploader($config->manager->slug . '/plugin', 'zip'); ?>
    <hr>
    <?php echo Config::speak('file:' . $segment); ?>
  </div>
</div>