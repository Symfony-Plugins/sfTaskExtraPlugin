<?php

include dirname(__FILE__).'/../../bootstrap/unit.php';

function task_extra_cleanup()
{
  sfToolkit::clearDirectory(dirname(__FILE__).'/../../fixtures/project/cache');
  sfToolkit::clearDirectory(dirname(__FILE__).'/../../fixtures/project/log');
  sfToolkit::clearDirectory(dirname(__FILE__).'/../../fixtures/project/plugins');
}
task_extra_cleanup();
register_shutdown_function('task_extra_cleanup');

$t = new task_extra_lime_test(20, new lime_output_color());

$t->diag('sfGeneratePluginTask');
$t->task_ok('sfGeneratePluginTask', array('sfTest*Plugin'), array(), false, '"sfGeneratePluginTask" fails when plugin name includes bad characters');
$t->task_ok('sfGeneratePluginTask', array('sfTest'), array(), false, '"sfGeneratePluginTask" fails when plugin name ends other than "Plugin"');
$t->task_ok('sfGeneratePluginTask', array('sfTestPlugin'));

$plugin_dir  = sfConfig::get('sf_plugins_dir').'/sfTestPlugin';
$config_file = $plugin_dir.'/config/sfTestPluginConfiguration.class.php';

$t->ok(is_dir($plugin_dir), '"sfGeneratePluginTask" creates the plugin directory');
$t->ok(file_exists($config_file), '"sfGeneratePluginTask" creates a plugin configuration file');
$t->like(@file_get_contents($config_file), '/class sfTestPluginConfiguration extends sfPluginConfiguration/', '"sfGeneratePluginTask" creates the plugin configuration class');
$t->like(@file_get_contents($plugin_dir.'/test/bootstrap/unit.php'), '/new sfTestPluginConfiguration/', '"sfGeneratePluginTask" includes the plugin config in the unit test bootstrapper');

$t->task_ok('sfGeneratePluginTask', array('sfTestPlugin'), array(), false, '"sfGeneratePluginTask" fails if plugin already exists');

$t->diag('sfGeneratePluginModuleTask');
$t->task_ok('sfGeneratePluginModuleTask', array('nonexistantPlugin', 'example'), array(), false, '"sfGeneratePluginModuleTask" fails when plugin does not exist');
$t->task_ok('sfGeneratePluginModuleTask', array('sfTestPlugin', 'example*'), array(), false, '"sfGeneratePluginModuleTask" fails when module name includes bad characters');
$t->task_ok('sfGeneratePluginModuleTask', array('sfTestPlugin', 'example'));

$module_dir        = $plugin_dir.'/modules/example';
$actions_file      = $module_dir.'/actions/actions.class.php';
$base_actions_file = $module_dir.'/lib/BaseexampleActions.class.php';

$t->ok(is_dir($module_dir), '"sfGeneratePluginModuleTask" creates a module directory');
$t->ok(file_exists($actions_file), '"sfGeneratePluginModuleTask" creates an actions file');
$t->like(@file_get_contents($actions_file), '/class exampleActions extends BaseexampleActions/', '"sfGeneratePluginModuleTask" creates an actions class');
$t->ok(file_exists($base_actions_file), '"sfGeneratePluginModuleTask" creates a base actions file');
$t->like(@file_get_contents($base_actions_file), '/class BaseexampleActions extends sfActions/', '"sfGeneratePluginModuleTask" creates a base actions class');
$t->ok(file_exists($plugin_dir.'/test/functional/exampleActionsTest.php'), '"sfGeneratePluginModuleTask" creates a functional test file');

$t->diag('sfGeneratePluginTask --module option');
$t->task_ok('sfGeneratePluginTask', array('sfTestAgainPlugin'), array('--module=one', '--module=two'));
$t->ok(is_dir(sfConfig::get('sf_plugins_dir').'/sfTestAgainPlugin/modules/one'), '"sfGeneratePluginTask" creates modules when "--module" is used');
$t->ok(is_dir(sfConfig::get('sf_plugins_dir').'/sfTestAgainPlugin/modules/two'), '"sfGeneratePluginTask" creates modules when "--module" is used');
