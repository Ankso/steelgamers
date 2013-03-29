<?php if (!defined('APPLICATION')) exit();
/**
* # Category Accordion #
* 
* ### About ###
* Makes a nice Sliding Accordion out of root categories in the Categories Module (sidebar), using jquery.ui.accordion.
* 
* ### Sponsor ###
* Special thanks to ponpon (http://www.jrc.or.jp/eq-japan2011/index.html) for making this happen.
*/
$PluginInfo['CategoryAccordion'] = array(
   'Name' => 'Category Accordion',
   'Description' => 'Makes a nice Sliding Accordion out of root categories in the Categories Module (sidebar)',
   'Version' => '0.1.4b',
   'Author' => 'Paul Thomas',
   'AuthorEmail' => 'dt01pqt_pt@yahoo.com ',
   'AuthorUrl' => 'http://www.vanillaforums.org/profile/x00'
);

/*
 * change log:
 * 0.1.4b:Tue Aug  7 08:11:49 BST 2012
 * - ExpandActiveOnLoad config option
 * 
 */ 

class CategoryAccordionPlugin extends Gdn_Plugin {
	public function Base_Render_Before($Sender, $Args) {
		if(GetValue('Panel',$Sender->Assets) && GetValue('CategoriesModule',$Sender->Assets['Panel'])){
			$Sender->AddDefinition('ExpandActiveOnLoad',C('Plugins.CategoryAccordion.ExpandActiveOnLoad'));
			$Sender->AddCSSFile('accordion.css', 'plugins/CategoryAccordion');
			$Sender->AddJSFile('accordion.js', 'plugins/CategoryAccordion');
		}
	}
}
