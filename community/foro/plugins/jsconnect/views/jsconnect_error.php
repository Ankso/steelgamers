<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo $this->Data('Title'); ?></h1>
<?php
echo $this->Form->Open(), $this->Form->Errors();

//echo '<div><div class="Info">',
//   T('Verificando tus credenciales...'),
//   '<div class="Progress"></div>',
//   '</div></div>';

echo $this->Form->Close();


?>