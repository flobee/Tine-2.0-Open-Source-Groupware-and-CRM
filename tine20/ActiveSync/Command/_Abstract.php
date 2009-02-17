<?php
/**
 * Tine 2.0
 *
 * @package     ActiveSync
 * @subpackage  ActiveSync
 * @license     http://www.gnu.org/licenses/agpl.html AGPL3
 * @copyright   Copyright (c) 2008-2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @version     $Id$
 */

/**
 * class documentation
 *
 * @package     ActiveSync
 * @subpackage  ActiveSync
 */
 
abstract class ActiveSync_Command_Abstract
{
    protected $_inputStream;
    
    #protected $_backend;
    
    /**
     * Enter description here...
     *
     * @var DOMDocument
     */
    protected $_outputDom;

    /**
     * Enter description here...
     *
     * @var DOMDocument
     */
    protected $_inputDom;
        
    #protected $_user;
    
    #protected $_deviceId;
    
    #protected $_deviceType;   

    /**
     * Enter description here...
     *
     * @var ActiveSync_Model_Device
     */
    protected $_device;
    
    /**
     * timestamp to use for all sync requests
     *
     * @var Zend_Date
     */
    protected $_syncTimeStamp;
    
    const FILTERTYPE_ALL            = 0;
    const FILTERTYPE_1DAY           = 1;
    const FILTERTYPE_3DAYS          = 2;
    const FILTERTYPE_1WEEK          = 3;
    const FILTERTYPE_2WEEKS         = 4;
    const FILTERTYPE_1MONTH         = 5;
    const FILTERTYPE_3MONTHS        = 6;
    const FILTERTYPE_6MONTHS        = 7;

    const TRUNCATION_HEADERS        = 0;
    const TRUNCATION_512B           = 1;
    const TRUNCATION_1K             = 2;
    const TRUNCATION_5K             = 4;
    const TRUNCATION_ALL            = 9;
    
    
    public function __construct(ActiveSync_Model_Device $_device, $_inputStream)
    {
        if(!is_resource($_inputStream)) {
            throw new Exception('$_inputStrem must be resource and stream.');
        }
        
        #$this->_user = $_user;
        #$this->_deviceId = $_deviceId;
        #$this->_deviceType = $_deviceType;
        $this->_device = $_device;
        
        $this->_inputStream = $_inputStream;
        #$this->_backend = $_backend;
        
        try {
            $decoder = new Wbxml_Decoder($_inputStream);
            $this->_inputDom = $decoder->decode();
            Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " " . $this->_inputDom->saveXML());
        } catch(Wbxml_Exception_UnexpectedEndOfFile $e) {
            $this->_inputDom = NULL;
        }
        
        $this->_syncTimeStamp = Zend_Date::now();
        Tinebase_Core::getLogger()->debug(__METHOD__ . '::' . __LINE__ . " sync timestamp: " . $this->_syncTimeStamp->get(Tinebase_Record_Abstract::ISO8601LONG));
        
        $imp = new DOMImplementation;
        $dtd = $imp->createDocumentType('AirSync', '-//AIRSYNC//DTD AirSync//EN', 'http://www.microsoft.com/');
        $this->_outputDom = $imp->createDocument('', '', $dtd);
        $this->_outputDom->formatOutput = false;
    }
    
    abstract public function getResponse();    
}