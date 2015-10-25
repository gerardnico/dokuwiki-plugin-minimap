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
        return array($state,$pages,$parameters);
    }


    function render($mode, &$renderer, $data) {

        // The $data variable comes from the handle() function
        //
        // $mode = 'xhtml' means that we output html
        // There is other mode such as metadata where you can output data for the headers (Not 100% sure)
        if ($mode == 'xhtml') {

            list($state, $pages, $parameters) = $data;

            switch ($state) {

                case DOKU_LEXER_SPECIAL :
                    $renderer->doc .= '<P>Number of pages: '.count($pages).'</P>';
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
