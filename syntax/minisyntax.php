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
    function handle($match, $state, $pos, Doku_Handler $handler)
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


    function render($mode, Doku_Renderer $renderer, $data)
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
                    if ($parameters['includedirectory']){
                        $includeDirectory = $parameters['includedirectory'];
                    } else {
                        $includeDirectory = false;
                    }
                    $pagesOfNamespace = $this->getNamespaceChildren($nameSpacePath, $sort='natural', $listdirs = $includeDirectory);

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
                    //$pagesCount = count($pagesOfNamespace); // number of pages in the namespace
                    foreach ($pagesOfNamespace as $page) {

                        // The title of the page
                        $title = '';

                        // If it's a directory
                        if ($page['type']=="d") {

                            $pageId = $this->getNamespaceStartId($page['id']);

                        } else {

                            $pageNum++;
                            $pageId = $page['id'];

                        }

                        // The title of the page
                        if (useHeading('navigation')) {
                            // $title = $page['title'] can not be used to retrieve the title
                            // because it didn't encode the HTML tag
                            // for instance if <math></math> is used, the output must have &lgt ...
                            // otherwise browser may add quote and the math plugin will not work
                            // May be a solution was just to encode the output
                            $title = tpl_pagetitle($pageId, true);
                        }

                        // Name if the variable that it's shown. A part of it can be suppressed
                        // Title will stay full in the link
                        $name = noNSorNS($pageId);
                        if ($title) {
                            $name = $title;
                        } else {
                            $title = $name;
                        }

                        // If debug mode
                        if ($parameters['debug']) {
                            $title .= ' (' . $pageId . ')';
                        }

                        // Add the page nummer in the URL title
                        $title .= ' (' . $pageNum . ')';

                        // Suppress the parts in the name with the regexp defines in the 'suppress' params
                        if ($parameters['suppress']) {
                            $substrPattern = '/' . $parameters['suppress'] . '/i';
                            $replacement = '';
                            $name = preg_replace($substrPattern, $replacement, $name);
                        }

                        // See in which page we are
                        // The style will then change
                        $active = '';
                        if ($callingId == $pageId) {
                            $active = 'active';
                        }

                        // Not all page are printed
                        // sidebar are not for instance

                        // Are we in the root ?
                        if ($page['ns']) {
                            $nameSpacePathPrefix = $page['ns'] . ':';
                        } else {
                            $nameSpacePathPrefix = '';
                        }
                        $print = true;
                        if ($page['id'] == $nameSpacePathPrefix . $currentNameSpace) {
                            // If the start page exists, the page with the same name
                            // than the namespace must be shown
                            if (page_exists($nameSpacePathPrefix . $startConf) ) {
                                $print = true;
                            } else {
                                $print = false;
                            }
                            $homePageFound = true;
                        } else if ($page['id'] == $nameSpacePathPrefix . $startConf) {
                            $print = false;
                            $startPageFound = true;
                        } else if ($page['id'] == $nameSpacePathPrefix . $conf['sidebar']) {
                            $pageNum -= 1;
                            $print = false;
                        };


                        // If the page must be printed, build the link
                        if ($print) {

                            // Open the item tag
                            $miniMapList .= "<li class=\"list-group-item " . $active . "\">";

                            // Add a glyphicon if it's a directory
                            if ($page['type']=="d"){
                                $miniMapList .= "<span class=\"glyphicon glyphicon-folder-open\" aria-hidden=\"true\"></span>&nbsp;&nbsp;";
                            }

                            // Add the link
                            $miniMapList .= tpl_link(
                                wl($pageId),
                                ucfirst($name), // First letter upper case
                                'title="' . $title . '"',
                                $return = true
                            );

                            // Close the item
                            $miniMapList .= "</li>";

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
                        $panelHeaderContent = tpl_link(
                            wl($startId),
                            tpl_pagetitle($startId, true),
                            'title="' . $startId . '"',
                            $return = true);
                    }

                    // We are not counting the header page
                    $pageNum--;

                    $miniMapPanel .= '<div class="panel-heading">' . $panelHeaderContent . '  <span class="label label-primary">' . $pageNum . ' pages</span></div>';
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
     * Return all pages and/of sub-namespaces (subdirectory) of a namespace (ie directory)
     * Adapted from feed.php
     *
     * @param $namespace The container of the pages
     * @param string $sort 'natural' to use natural order sorting (default); 'date' to sort by filemtime
     * @param $listdirs - Add the directory to the list of files
     * @return array An array of the pages for the namespace
     */
    function getNamespaceChildren($namespace, $sort = 'natural', $listdirs = false)
    {
        require_once(DOKU_INC . 'inc/search.php');
        global $conf;

        $ns = ':' . cleanID($namespace);
        // ns as a path
        $ns = utf8_encodeFN(str_replace(':', '/', $ns));

        $data = array();

        // Options of the callback function search_universal
        // in the search.php file
        $search_opts = array(
            'depth' => 1,
            'pagesonly' => true,
            'listfiles' => true,
            'listdirs' => $listdirs,
            'firsthead' => true
        );
        // search_universal is a function in inc/search.php that accepts the $search_opts parameters
        search($data, $conf['datadir'], 'search_universal', $search_opts, $ns, $lvl = 1, $sort);

        return $data;
    }

    /**
     * Return the id of the start page of a namespace
     *
     * @param $id an id of a namespace (directory)
     * @return string the id of the home page
     */
    function getNamespaceStartId($id){

        global $conf;

        $id = $id.":";

        if(page_exists($id.$conf['start'])){
            // start page inside namespace
            $homePageId = $id.$conf['start'];
        }elseif(page_exists($id.noNS(cleanID($id)))){
            // page named like the NS inside the NS
            $homePageId = $id.noNS(cleanID($id));
        }elseif(page_exists($id)){
            // page like namespace exists
            $homePageId = substr($id,0,-1);
        } else {
            // fall back to default
            $homePageId = $id.$conf['start'];
        }
        return $homePageId;
    }


}
