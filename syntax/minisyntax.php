<?php
/**
 * Plugin minimap : Displays mini-map for namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Nicolas GERARD
 */
if (!defined('DOKU_INC')) die();


class syntax_plugin_minimap_minisyntax extends DokuWiki_Syntax_Plugin
{

    function connectTo($aMode)
    {
        $this->Lexer->addSpecialPattern('<minimap[^>]*>', $aMode, 'plugin_minimap_' . $this->getPluginComponent());
    }

    function getSort()
    {
        return 150;
    }

    function getType()
    {
        return 'substition';
    }

    // The handle function goal is to parse the matched syntax through the pattern function
    // and to return the result for use in the renderer
    // This result is always cached until the page is modified.
    function handle($match, $state, $pos, &$handler)
    {

        switch ($state) {

            // As there is only one call to connect to in order to a add a pattern,
            // there is only one state entering the function
            // but I leave it for better understanding of the process flow
            case DOKU_LEXER_SPECIAL :

                // Parse the parameters
                $match = utf8_substr($match, 8, -1); //9 = strlen("<minimap")
                $parameters['substr'] = 1;

                // /i not case sensitive
                $attributePattern = "\\s*(\w+)\\s*=\\s*[\'\"]?([\w\d\s-_\|\*\.\(\)\?\/\\\\]+)[\'\"]?\\s*";
                $result = preg_match_all('/' . $attributePattern . '/i', $match, $matches);
                if ($result != 0) {
                    foreach ($matches[1] as $key => $parameterKey) {
                        $parameters[strtolower($parameterKey)] = $matches[2][$key];
                    }
                }

        }

        // Cache the values
        return array($state, $parameters);
    }


    function render($mode, &$renderer, $data)
    {

        // The $data variable comes from the handle() function
        //
        // $mode = 'xhtml' means that we output html
        // There is other mode such as metadata where you can output data for the headers (Not 100% sure)
        if ($mode == 'xhtml') {

            // Unfold the $data array in two separates variables
            list($state, $parameters) = $data;

            // As there is only one call to connect to in order to a add a pattern,
            // there is only one state entering the function
            // but I leave it for better understanding of the process flow
            switch ($state) {

                case DOKU_LEXER_SPECIAL :

                    global $ID;
                    global $INFO;
                    $callingId = $ID;
                    // If mini-map is in a sidebar, we don't want the ID of the sidebar
                    // but the ID of the page.
                    if ($INFO != null) {
                        $callingId = $INFO['id'];
                    }

                    $nameSpacePath = getNS($callingId); // The complete path to the directory
                    $currentNameSpace = curNS($callingId); // The name of the container directory
                    $pagesOfNamespace = $this->getPagesOfNamespace($nameSpacePath);

                    // Set the two possible home page for the namespace ie:
                    //   - the name of the containing map ($homePageWithContainingMapName)
                    //   - the start conf parameters ($homePageWithStartConf)
                    global $conf;
                    $parts = explode(':', $nameSpacePath);
                    $lastContainingNameSpace = $parts[count($parts) - 1];
                    $homePageWithContainingMapName = $nameSpacePath . ':' . $lastContainingNameSpace;
                    $startConf = $conf['start'];
                    $homePageWithStartConf = $nameSpacePath . ':' . $startConf;

                    // Build the list of page
                    $miniMapList = '<div class="list-group">';
                    $pageNum = 0;
                    foreach ($pagesOfNamespace as $page) {
                        $pageNum++;
                        // page names
                        $name = noNSorNS($page['id']);
                        $title = '';
                        if (useHeading('navigation')) {
                            // get page title
                            $title = p_get_first_heading($page['id'], METADATA_RENDER_USING_SIMPLE_CACHE);
                        }
                        if ($title) {
                            $name = $title;
                        } else {
                            $title = $name;
                        }
                        if ($parameters['debug']) {
                            $title .= ' (' . $page['id'] . ')';
                        }
                        $title .= ' (' . $pageNum . ')';

                        if ($parameters['suppress']) {
                            $substrPattern = '/' . $parameters['suppress'] . '/i';
                            $replacement = '';
                            $name = preg_replace($substrPattern, $replacement, $name);
                        }

                        $active = '';
                        if ($callingId == $page['id']) {
                            $active = 'active';
                        }

                        $print = true;
                        if ($page[id] == $page['ns'] . ':' . $currentNameSpace) {
                            // If the start page exists, the page with the same name
                            // than the namespace must be shown
                            if (page_exists($page['ns'] . ':' . $startConf) ) {
                                $print = true;
                            } else {
                                $print = false;
                            }
                            $homePageFound = true;
                        } else if ($page[id] == $page['ns'] . ':' . $startConf) {
                            $print = false;
                            $startPageFound = true;
                        } else if ($page[id] == $page['ns'] . ':' . $conf['sidebar']) {
                            $print = false;
                        };

                        if ($print) {
                            $miniMapList .= tpl_link(
                                wl($page['id']),
                                ucfirst($name), // First letter upper case
                                'class="list-group-item ' . $active . '" title="' . $title . '"',
                                $return = true
                            );
                        }

                    }
                    $miniMapList .= '</div>'; // End list-group

                    // Build the panel header
                    $miniMapPanel = '<div id="minimap__plugin"><div class="panel panel-default">';
                    if ($startPageFound) {
                        $startId = $homePageWithStartConf;
                    } else {
                        if ($homePageFound) {
                            $startId = $homePageWithContainingMapName;
                        } else {
                            $panelHeaderContent = 'No Home Page found';
                        }
                    }
                    if (!$panelHeaderContent) {
                        $panelHeaderContent = tpl_link(wl($startId), tpl_pagetitle($startId, true), 'title="' . $startId . '"', $return = true);
                    }
                    $miniMapPanel .= '<div class="panel-heading">' . $panelHeaderContent . '  <span class="label label-primary">' . count($pagesOfNamespace) . ' pages</span></div>';
                    if ($parameters['debug']) {
                        $miniMapPanel .= '<div class="panel-body">' .
                            '<B>Debug Information:</B><BR>' .
                            'CallingId: (' . $callingId . ')<BR>' .
                            'Suppress Option: (' . $parameters['suppress'] . ')<BR>' .
                            '</div>';
                    }
                    $renderer->doc .= $miniMapPanel . $miniMapList . '</div></div>';
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
    function getPagesOfNamespace($namespace, $sort = 'natural')
    {
        require_once(DOKU_INC . 'inc/search.php');
        global $conf;

        $ns = ':' . cleanID($namespace);
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
