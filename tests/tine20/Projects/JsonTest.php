<?php
/**
 * Tine 2.0 - http://www.tine20.org
 * 
 * @package     Projects
 * @license     http://www.gnu.org/licenses/agpl.html
 * @copyright   Copyright (c) 2011 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Philipp Schüle <p.schuele@metaways.de>
 * 
 * @todo        add attendee tests
 */

/**
 * Test helper
 */
require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'TestHelper.php';

/**
 * Test class for Tinebase_Group
 */
class Projects_JsonTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Projects_Frontend_Json
     */
    protected $_json = array();
    
    /**
     * projects to delete in tearDown
     * 
     * @var array
     */
    protected $_projectsToDelete = array(); 
    
    /**
     * test department
     * 
     * @var Tinebase_Model_Department
     */
    protected $_department = NULL;
    
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
    public static function main()
    {
		$suite  = new PHPUnit_Framework_TestSuite('Tine 2.0 Projects Json Tests');
        PHPUnit_TextUI_TestRunner::run($suite);
	}

    /**
     * Sets up the fixture.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->_json = new Projects_Frontend_Json();
    }

    /**
     * Tears down the fixture
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
        if (! empty($this->_projectsToDelete)) {
            $this->_json->deleteProjects($this->_projectsToDelete);
        }
    }
    
    /**
     * try to add a Project
     *
     */
    public function testAddProject()
    {
        $project = $this->_getProjectData();
        $projectData = $this->_json->saveProject($project);
        
        // checks
        $this->assertEquals($project['description'], $projectData['description']);
        $this->assertEquals(Tinebase_Core::getUser()->getId(), $projectData['created_by'], 'Created by has not been set.');
        
        // cleanup
        $this->_json->deleteProjects($projectData['id']);

        // check if it got deleted
        $this->setExpectedException('Tinebase_Exception_NotFound');
        Projects_Controller_Project::getInstance()->get($projectData['id']);
    }
    
    /**
     * try to get a Project
     *
     */
    public function testGetProject()
    {
        $project = $this->_getProjectData();
        $projectData = $this->_json->saveProject($project);
        $projectData = $this->_json->getProject($projectData['id']);
        $this->_projectsToDelete[] = $projectData['id'];
        
        // checks
        $this->assertEquals($project['description'], $projectData['description']);
        $this->assertEquals(Tinebase_Core::getUser()->getId(), $projectData['created_by']);
    }

    /**
     * try to update a Project
     *
     */
    public function testUpdateProject()
    {
        $project = $this->_getProjectData();
        $projectData = $this->_json->saveProject($project);
        $this->_projectsToDelete[] = $projectData['id'];
        
        // update Project
        $projectData['description'] = "blubbblubb";
        $projectUpdated = $this->_json->saveProject($projectData);
        
        // check
        $this->assertEquals($projectData['id'], $projectUpdated['id']);
        $this->assertEquals($projectData['description'], $projectUpdated['description']);
        $this->assertEquals(Tinebase_Core::getUser()->getId(), $projectUpdated['last_modified_by']);
    }
    
    /**
     * try to get a Project
     *
     */
    public function testSearchProjects()
    {
        // create
        $project = $this->_getProjectData();
        $projectData = $this->_json->saveProject($project);
        $this->_projectsToDelete[] = $projectData['id'];
        
        // search & check
        $search = $this->_json->searchProjects($this->_getProjectFilter($projectData['title']), $this->_getPaging());
        $this->assertEquals($project['description'], $search['results'][0]['description']);
        $this->assertEquals(1, $search['totalcount']);
    }

    /************ protected helper funcs *************/
    
    /**
     * get Project
     *
     * @return array
     */
    protected function _getProjectData()
    {
        return array(
            'title'         => Tinebase_Record_Abstract::generateUID(),
            'description'   => 'blabla',
            'status'        => 'IN-PROCESS',
        );
    }
        
    /**
     * get paging
     *
     * @return array
     */
    protected function _getPaging()
    {
        return array(
            'start' => 0,
            'limit' => 50,
            'sort' => 'creation_time',
            'dir' => 'ASC',
        );
    }

    /**
     * get Project filter
     *
     * @return array
     */
    protected function _getProjectFilter($_projectName)
    {
        return array(
            array(
                'field' => 'title', 
                'operator' => 'contains', 
                'value' => $_projectName
            ),     
        );
    }
}
