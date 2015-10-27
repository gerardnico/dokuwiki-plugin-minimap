<?php
/**
 * Plugin minimap : Displays mini-map for namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Nicolas GERARD
 */
if(!defined('DOKU_INC')) die();


class syntax_plugin_minimap_minisyntax extends DokuWiki_Syntax_Plugin {

    function connectTo($aMode) {
        $this->Lexer->addSpecialPattern('<minimap[^>]*>', $aMode, 'plugin_minimap_'.$this->getPluginComponent());
    }

    function getSort() {
        return 150;
    }

    function getType() {
        return 'substition';
    }

    function handle($match, $state, $pos, &$handler) {

        switch ($state) {

            // As there is only one call to connect to in order to a add a pattern,
            // there is only one state entering the function
            // but I leave it for better understanding of the process flow
            case DOKU_LEXER_ENTER :

                // Parse the parameters
                $match = utf8_substr($match, 8, -1); //9 = strlen("<minimap")
                $parameters['substr'] = 1;

                // /i not case sensitive
                $attributePattern = "\\s*(\w+)\\s*=\\s*\"?(\\d+)\"?\\s*";
                $result = preg_match_all('/' . $attributePattern . '/i', $match, $matches);
                if ($result != 0) {
                    foreach ($matches[1] as $key => $parameterKey) {
                        $parameters[strtolower($parameterKey)] = $matches[2][$key];
                    }
                }

                break;

        }


        global $ID;
        $currentNameSpace = getNS($ID);
        $pages = $this->getPagesOfNamespace($currentNameSpace);

        // Cache the values
        return array($state,$pages,$parameters,$currentNameSpace);
    }


    function render($mode, &$renderer, $data) {

        // The $data variable comes from the handle() function
        //
        // $mode = 'xhtml' means that we output html
        // There is other mode such as metadata where you can output data for the headers (Not 100% sure)
        if ($mode == 'xhtml') {

            list($state, $pages, $parameters, $currentNameSpace) = $data;

            switch ($state) {

                case DOKU_LEXER_SPECIAL :

                    // Set the two possible home page for the namespace
                    // with the start conf of the name of the last map
                    global $conf;
                    $parts = explode(':', $currentNameSpace);
                    $lastContainingNameSpace = $parts[count($parts)-1];
                    $homePageNamespace=$currentNameSpace.':'.$lastContainingNameSpace;
                    $startConf = $conf['start'];
                    $startPageNamespace=$currentNameSpace.':'.$startConf;

                    // Build the list of page
                    $miniMapList = '<div class="list-group">';
                    foreach ($pages as $page) {
                        // page names
                        $name = noNSorNS($page['id']);
                        if(useHeading('navigation')) {
                            // get page title
                            $title = p_get_first_heading($page['id'], METADATA_RENDER_USING_SIMPLE_CACHE);
                            if($title) {
                                $name = $title;
                            }
                            global $ID;
                            $active='';
                            if ($ID==$page['id']) {
                                $active='active';
                            }

                            $print = true;
                            if ($page[id] == $page['ns'].':'.$page['ns']) {
                                $print=false;
                                $homePageFound=true;
                            } else if ($page[id] == $page['ns'].':'.$startConf) {
                                $print=false;
                                $startPageFound=true;
                            } else if ($page[id] == $page['ns'].':'.$conf['sidebar']){
                                $print=false;
                            };

                            if ($print) {
                                $miniMapList .= tpl_link(
                                    wl($page['id']),
                                    $name,
                                    'class="list-group-item ' . $active . '" title="' . $name . '"',
                                    $return = true
                                );
                            }


                        }

                    }
                    $miniMapList .= '</div>'; // End list-group

                    // Build the panel header
                    $miniMapPanel = '<div class="panel panel-default">';
                    if ($homePageFound) {
                        $startId = $homePageNamespace;
                    } else {
                        if ($startPageFound) {
                            $startId = $startPageNamespace;
                        } else {
                            $panelHeaderContent = 'No Home Page found';
                        }
                    }
                    if (!$panelHeaderContent) {
                        $panelHeaderContent = tpl_link(wl($startId), tpl_pagetitle($startId,true), 'title="' . $startId . '"',$return=true);
                    }
                    $miniMapPanel .= '<div class="panel-heading">'.$panelHeaderContent.'  <span class="label label-primary">'.count($pages).' pages</span></div>';
                    //$miniMapPanel .= '<div class="panel-body">An optional text</div>';
                    $renderer->doc .= $miniMapPanel.$miniMapList.'</div>';
                    break;
            }

            return true;
        }
        return false;

    }

    /**
     * Return all pages of a namespace
     * Adapted from feed.php
     *
     * @param $namespace The container of the pages
     * @param string $sort 'natural' to use natural order sorting (default); 'date' to sort by filemtime
     * @return array An array of the pages for the namespace
     */
    function getPagesOfNamespace($namespace, $sort='natural') {
        require_once(DOKU_INC.'inc/search.php');
        global $conf;

        $ns = ':'.cleanID($namespace);
        // ns as a path
        $ns = utf8_encodeFN(str_replace(':', '/', $ns));

        $data = array();
        $search_opts = array(
            'depth' => 1,
            'pagesonly' => true,
            'listfiles' => true
        );
        // search_universal is a function in inc/search.php that accetps the $search_opts parameters
        search($data, $conf['datadir'], 'search_universal', $search_opts, $ns, $lvl = 1, $sort);

        return $data;
    }


}
