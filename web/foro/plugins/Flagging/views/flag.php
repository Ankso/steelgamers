<?php if (!defined('APPLICATION')) exit(); ?>
<?php
   $UcContext = ucfirst($this->Data['Plugin.Flagging.Data']['Context']);
   $ElementID = $this->Data['Plugin.Flagging.Data']['ElementID'];
   $URL = $this->Data['Plugin.Flagging.Data']['URL'];
   $Title = sprintf("Reportar esta %s",ucfirst($this->Data['Plugin.Flagging.Data']['Context']));
?>
<h2><?php echo T($Title); ?></h2>
<?php
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<ul>
   <li>
      <div class="Warning">
         <?php echo T('FlagForReview', "Est&aacute;s a punto de reportar este contenido como inapropiado. Si est&aacute;s seguro de que quieres hacerlo, introduce una breve raz&oacute;n a continuaci&oacute;n y haz clik en '&iexcl;Reportar esto!'."); ?>
      </div>
      <?php echo T('FlagLinkContent', 'Enlace al contenido:') .' '. Anchor("{$UcContext} #{$ElementID}", $URL); ?> &ndash; 
         <?php echo $this->Data['Plugin.Flagging.Data']['ElementAuthor']; ?>
   </li>
   <li>
      <?php
         echo $this->Form->Label('Raz&oacute;n', 'Plugin.Flagging.Reason');
         echo $this->Form->TextBox('Plugin.Flagging.Reason', array('MultiLine' => TRUE));
      ?>
   </li>
   <?php
      $this->FireEvent('FlagContentAfter');
   ?>
</ul>
<?php echo $this->Form->Close('&iexcl;Reportar esto!');