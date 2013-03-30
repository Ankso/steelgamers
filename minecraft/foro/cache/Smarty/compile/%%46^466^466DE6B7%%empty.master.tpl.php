<?php /* Smarty version 2.6.25, created on 2013-03-29 21:43:56
         compiled from F:%5CGamersNet%5Csteelgamers%5Ccommunity%5Cforo/applications/dashboard/views/empty.master.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'asset', 'F:\\GamersNet\\steelgamers\\community\\foro/applications/dashboard/views/empty.master.tpl', 7, false),array('function', 'event', 'F:\\GamersNet\\steelgamers\\community\\foro/applications/dashboard/views/empty.master.tpl', 12, false),)), $this); ?>
<?php echo '<?xml'; ?>
 version="1.0" encoding="utf-8"<?php echo '?>'; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-ca">
<head>
	<?php echo smarty_function_asset(array('name' => 'Head'), $this);?>

</head>
<body id="<?php echo $this->_tpl_vars['BodyID']; ?>
" class="<?php echo $this->_tpl_vars['BodyClass']; ?>
">
<div id="Content"><?php echo smarty_function_asset(array('name' => 'Content'), $this);?>
</div>
<?php echo smarty_function_asset(array('name' => 'Foot'), $this);?>

<?php echo smarty_function_event(array('name' => 'AfterBody'), $this);?>

</body>
</html>