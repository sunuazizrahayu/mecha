<?php Weapon::fire('unit_composer_content_before', array($FT)); ?>
<textarea name="content" class="textarea-block code" placeholder="<?php echo $speak->manager->placeholder_content; ?>" data-mte-languages='<?php echo Text::parse($speak->MTE)->to_encoded_json; ?>'><?php echo Text::parse($wb['content'])->to_encoded_html; ?></textarea>
<?php Weapon::fire('unit_composer_content_after', array($FT)); ?>