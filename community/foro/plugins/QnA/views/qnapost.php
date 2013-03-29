<?php if (!defined('APPLICATION')) exit(); ?>
<div class="P">
   <?php echo T('Puedes realizar una pregunta o empezar una discusi&oacute;n.', 'Puedes realizar una pregunta o empezar una discusi&oacute;n. Escoge en los botones de abajo lo que quieres hacer.'); ?>
</div>
<style>.NoScript { display: none; }</style>
<noscript>
   <style>.NoScript { display: block; } .YesScript { display: none; }</style>
</noscript>
<div class="P NoScript">
   <?php echo $Form->RadioList('Type', array('Question' => 'Ask a Question', 'Discussion' => 'Empezar una nueva Discusi&oacute;n')); ?>
</div>
<div class="YesScript">
   <div class="Tabs">
      <ul>
         <li style="padding-left: 20px;" class="<?php echo $Form->GetValue('Type') == 'Question' ? 'Active' : '' ?>"><a id="QnA_Question" class="QnAButton TabLink" rel="Question" href="#"><?php echo T('Hacer una Pregunta'); ?></a></li>
         <li class="<?php echo $Form->GetValue('Type') == 'Discussion' ? 'Active' : '' ?>"><a id="QnA_Discussion" class="QnAButton TabLink" rel="Discussion" href="#"><?php echo T('Empezar una nueva Discusi&oacute;n'); ?></a></li>
      </ul>
   </div>
</div>