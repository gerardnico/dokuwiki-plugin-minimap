<?php
require_once(DOKU_PLUGIN.'action.php');

class action_plugin_minimap extends Dokuwiki_Action_Plugin {

    /**
     * Register its handlers with the dokuwiki's event controller
     */
    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER',  $this, 'handle_toolbar', array());
    }

    function handle_toolbar(& $event, $param) {

        $minimapShortcutKey = $this->getConf('WebCodeShortCutKey');

        $event->data[] = array(
            'type'   => 'insert',
            'title'  => $this->getLang('MiniMapButtonTitle').' ('.$this->getLang('AccessKey').': '.$minimapShortcutKey.')',
            'icon'   => '../../plugins/minimap/images/minimap.png',
            'insert' => '<minimap suppress="">\n',
            'key'    => $minimapShortcutKey,
            'block' => true
        );
    }
}
