<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload96927091bf7354178484f9702d580b05($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'boomerangdatasprocessor' => '/BoomerangDatasProcessor.Class.php',
            'boomerangplugin' => '/boomerangPlugin.class.php',
            'boomerangplugindescriptor' => '/BoomerangPluginDescriptor.class.php',
            'boomerangplugininfo' => '/BoomerangPluginInfo.class.php',
            'perfdatapresenter' => '/PerfDataPresenter.php',
            'statisticsmanager' => '/StatisticsManager.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload96927091bf7354178484f9702d580b05');
// @codeCoverageIgnoreEnd