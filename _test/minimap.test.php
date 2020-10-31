<?php


/**
 * Test the component plugin
 *
 * @group plugin_minimap
 * @group plugins
 */
class plugin_minimap_test extends DokuWikiTest
{

    protected $pluginsEnabled = [syntax_plugin_minimap::PLUGIN_NAME];

    public function setUp()
    {
        parent::setUp();
        global $conf;
        $conf['template']='dokuwiki';
    }


    public function test_component_name()
    {

        $componentName = syntax_plugin_minimap::getTag();

        $this->assertEquals('minimap', $componentName);

    }

    public function test_minimap_basic()
    {

        $namespace = 'minimap_basic';
        $page1 = $namespace.':'.'page1';
        saveWikiText($page1, 'First page', 'First page');
        idx_addPage($page1);

        $page2 = $namespace.':'.'page2';
        saveWikiText($page2, 'First page', 'First page');
        idx_addPage($page2);

        $minimapPage = $namespace.':'.'sidebar';
        saveWikiText($minimapPage, '<' . syntax_plugin_minimap::PLUGIN_NAME . ' />', 'Page with minimap');
        idx_addPage($minimapPage);

        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $page1));

        // One minimap
        $phpQueryObject = $testResponse->queryHTML('#minimap__plugin');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see the id");

        // Panel present
        $phpQueryObject = $testResponse->queryHTML('.panel');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count,"We should see container for the whole map");

        // Panel header
        $phpQueryObject = $testResponse->queryHTML('.panel-heading');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see one container for the header");

        // List group (container page)
        $phpQueryObject = $testResponse->queryHTML('.list-group');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count,"We should see one container for the pages");

        // List  (page)
        $phpQueryObject = $testResponse->queryHTML('.list-group-item');
        $count = $phpQueryObject->count();
        $this->assertEquals(2, $count, "We should see two pages");

        // List  (one item active)
        $phpQueryObject = $testResponse->queryHTML('.list-group-item.active');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count,"One page should be active");

    }


    public function test_minimap_conf_subdirectory()
    {

        // Changing it to true
        global $conf;
        $conf['plugin'][syntax_plugin_minimap::PLUGIN_NAME][syntax_plugin_minimap::INCLUDE_DIRECTORY_PARAMETERS]=true;

        // Test
        $namespace = 'minimap_conf_sub_directory';
        $page1 = $namespace.':'.'page1';
        saveWikiText($page1, 'First page', 'First page');
        idx_addPage($page1);

        $subpage = $namespace.':sub:sub';
        saveWikiText($subpage, 'Sub page', 'Sub page');
        idx_addPage($subpage);

        $minimapPage = $namespace.':'.'sidebar';
        saveWikiText($minimapPage, '<' . syntax_plugin_minimap::PLUGIN_NAME . ' />', 'Page with minimap');
        idx_addPage($minimapPage);

        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $page1));

        // One minimap
        $phpQueryObject = $testResponse->queryHTML('#minimap__plugin');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see the id");

        // List  (page)
        $phpQueryObject = $testResponse->queryHTML('.list-group-item');
        $count = $phpQueryObject->count();
        $this->assertEquals(2, $count, "We should see two pages ");

        // List  (page)
        $phpQueryObject = $testResponse->queryHTML('.nicon_folder_open');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see the folder");


    }

    /**
     * Test the inline parameter includeDirectory
     */
    public function test_minimap_param_subdirectory()
    {


        // Test
        $namespace = 'minimap_param_subdir';
        $page1 = $namespace.':'.'page1';
        saveWikiText($page1, 'First page', 'First page');
        idx_addPage($page1);

        $subpage = $namespace.':sub:sub';
        saveWikiText($subpage, 'Sub page', 'Sub page');
        idx_addPage($subpage);

        $minimapPage = $namespace.':'.'sidebar';
        saveWikiText($minimapPage, '<' . syntax_plugin_minimap::PLUGIN_NAME . ' '.syntax_plugin_minimap::INCLUDE_DIRECTORY_PARAMETERS.'="true" />', 'Page with minimap');
        idx_addPage($minimapPage);

        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $page1));

        // One minimap
        $phpQueryObject = $testResponse->queryHTML('#minimap__plugin');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see the id");

        // List  (page)
        $phpQueryObject = $testResponse->queryHTML('.nicon_folder_open');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see the folder");


        // List  (page)
        $phpQueryObject = $testResponse->queryHTML('.list-group-item');
        $count = $phpQueryObject->count();
        $this->assertEquals(2, $count, "We should see two pages ");





    }


    /**
     * The header should not show up if there is no header
     */
    public function test_minimap_conf_showheader()
    {

        // Changing it to true
        global $conf;
        $conf['plugin'][syntax_plugin_minimap::PLUGIN_NAME][syntax_plugin_minimap::SHOW_HEADER]=false;

        // Test
        $namespace = 'minimap_conf_show_header';
        $page1 = $namespace.':'.'page1';
        saveWikiText($page1, 'First page', 'First page');
        idx_addPage($page1);

        $minimapPage = $namespace.':'.'sidebar';
        saveWikiText($minimapPage, '<' . syntax_plugin_minimap::PLUGIN_NAME . ' />', 'Page with minimap');
        idx_addPage($minimapPage);

        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $page1));

        // One minimap
        $phpQueryObject = $testResponse->queryHTML('#minimap__plugin');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see the id");


        // List  (page)
        $phpQueryObject = $testResponse->queryHTML('.panel-heading');
        $count = $phpQueryObject->count();
        $this->assertEquals(0, $count, "Because there is no home page, we should not see the header");



    }

    /**
     * The header should not show up if there is a inline showHeader param
     */
    public function test_minimap_param_showheader()
    {


        // Test
        $namespace = 'minimap_param_show_header';
        $page1 = $namespace.':'.'page1';
        saveWikiText($page1, 'First page', 'First page');
        idx_addPage($page1);

        $minimapPage = $namespace.':'.'sidebar';
        saveWikiText($minimapPage, '<' . syntax_plugin_minimap::PLUGIN_NAME . ' '.syntax_plugin_minimap::SHOW_HEADER.'="false" />', 'Page with minimap');
        idx_addPage($minimapPage);

        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $page1));

        // One minimap
        $phpQueryObject = $testResponse->queryHTML('#minimap__plugin');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see the id");


        // List  (page)
        $phpQueryObject = $testResponse->queryHTML('.panel-heading');
        $count = $phpQueryObject->count();
        $this->assertEquals(0, $count, "Because there is no home page, we should not see the header");



    }



    /**
     * Test the inline namespace parameter
     */
    public function test_minimap_param_namespace()
    {


        // Test
        $namespace = 'minimap_param_namespace';
        $page1 = $namespace.':'.'page1';
        saveWikiText($page1, 'First page', 'First page');
        idx_addPage($page1);

        $page2 = $namespace.':'.'page2';
        saveWikiText($page2, 'Second page', 'Second page');
        idx_addPage($page2);

        $subNamespace=$namespace.':sub';
        $subpage = $subNamespace.':sub';
        saveWikiText($subpage, 'Sub page', 'Sub page');
        idx_addPage($subpage);

        $minimapPage = $namespace.':'.'sidebar';
        saveWikiText($minimapPage, '<' . syntax_plugin_minimap::PLUGIN_NAME . ' '.syntax_plugin_minimap::NAMESPACE_KEY_ATT.'="'.$subNamespace.'" />', 'Page with minimap');
        idx_addPage($minimapPage);

        $testRequest = new TestRequest();
        $testResponse = $testRequest->get(array('id' => $page1));

        // One minimap
        $phpQueryObject = $testResponse->queryHTML('#minimap__plugin');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should see the id");


        // List  (page)
        $phpQueryObject = $testResponse->queryHTML('.list-group-item');
        $count = $phpQueryObject->count();
        $this->assertEquals(1, $count, "We should only one page");



    }



}

