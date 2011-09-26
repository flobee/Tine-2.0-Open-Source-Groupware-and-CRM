<?php
/**
 * class to handle a single vcard
 *
 * @package     Addressbook
 * @subpackage  Frontend
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Lars Kneschke <l.kneschke@metaways.de>
 * @copyright   Copyright (c) 2011-2011 Metaways Infosystems GmbH (http://www.metaways.de)
 *
 */

/**
 * class to handle a single vcard
 *
 * This class handles the creation, update and deletion of vcards
 *
 * @package     Addressbook
 * @subpackage  Frontend
 */
class Addressbook_Frontend_WebDAV_Contact extends Sabre_DAV_File implements Sabre_CardDAV_ICard, Sabre_DAVACL_IACL
{
    /**
     * @var Addressbook_Model_Contact
     */
    protected $_contact;
    
    /**
     * holds the vcard returned to the client
     * 
     * @var string
     */
    protected $_vcard;
    
    /**
     * @var Addressbook_Convert_Contact_VCard
     */
    protected $_converter;
    
    /**
     * Constructor 
     * 
     * @param  string|Addressbook_Model_Contact  $_contact  the id of a contact or the contact itself 
     */
    public function __construct($_contact = null) 
    {
        $this->_contact = $_contact;
        $this->_converter = new Addressbook_Convert_Contact_VCard();
    }
    
    /**
     * this function creates a Addressbook_Model_Contact and stores it in the database
     * 
     * @todo the header handling does not belong here. It should be moved to the DAV_Server class when supported
     * 
     * @param  Tinebase_Model_Container  $container
     * @param  stream|string           $vcardData
     */
    public static function create(Tinebase_Model_Container $container, $vcardData)
    {
        $contact = $this->_converter->toTine20Model($vcardData);
        $contact->container_id = $container->getId();
        
        $contact = Addressbook_Controller_Contact::getInstance()->create($contact);
        
        $card = new self($contact);
        
        // this belongs to DAV_Server, but is currently not supported
        header('ETag: ' . $card->getETag());
        header('Location: /' . $card->getName());
        
        return $card;
    }
    
    /**
     * Deletes the card
     *
     * @return void
     */
    public function delete() 
    {
        Addressbook_Controller_Contact::getInstance()->delete($this->_contact);
    }
    
    /**
     * Returns the VCard-formatted object 
     * 
     * @return stream
     */
    public function get() 
    {
        $s = fopen('php://temp','r+');
        fwrite($s, $this->_getVCard());
        rewind($s);
        
        return $s;
    }
    
    /**
     * Returns the uri for this object 
     * 
     * @return string 
     */
    public function getName() 
    {
        return $this->_getContact()->getId() . '.vcf';
    }
    
    /**
     * Returns the owner principal
     *
     * This must be a url to a principal, or null if there's no owner 
     * 
     * @todo add real owner
     * @return string|null
     */
    public function getOwner() 
    {
        return null;
        return $this->addressBookInfo['principaluri'];
    }

    /**
     * Returns a group principal
     *
     * This must be a url to a principal, or null if there's no owner
     * 
     * @todo add real group
     * @return string|null 
     */
    public function getGroup() 
    {
        return null;
    }
    
    /**
     * Returns a list of ACE's for this node.
     *
     * Each ACE has the following properties:
     *   * 'privilege', a string such as {DAV:}read or {DAV:}write. These are 
     *     currently the only supported privileges
     *   * 'principal', a url to the principal who owns the node
     *   * 'protected' (optional), indicating that this ACE is not allowed to 
     *      be updated. 
     * 
     * @todo add the real logic
     * @return array 
     */
    public function getACL() 
    {
        return null;
        
        return array(
            array(
                'privilege' => '{DAV:}read',
                'principal' => $this->addressBookInfo['principaluri'],
                'protected' => true,
            ),
            array(
                'privilege' => '{DAV:}write',
                'principal' => $this->addressBookInfo['principaluri'],
                'protected' => true,
            ),
        );

    }
    
    /**
     * Returns the mime content-type
     *
     * @return string
     */
    public function getContentType() {
    
        return 'text/x-vcard';
    
    }
    
    /**
     * Returns an ETag for this object
     *
     * @return string
     */
    public function getETag() 
    {
        return '"' . md5($this->_getContact()->getId() . $this->getLastModified()) . '"';
    }
    
    /**
     * Returns the last modification date as a unix timestamp
     *
     * @return time
     */
    public function getLastModified() 
    {
        return ($this->_getContact()->last_modified_time instanceof Tinebase_DateTime) ? $this->_getContact()->last_modified_time->toString() : $this->_getContact()->creation_time->toString();
    }
    
    /**
     * Returns the size of the vcard in bytes
     *
     * @return int
     */
    public function getSize() 
    {
        return strlen($this->_getVCard());
    }
    
    /**
     * Updates the VCard-formatted object
     *
     * @param string $cardData
     * @return void
     */
    public function put($cardData) 
    {
        $contact = self::convertToAddressbookModelContact($cardData, $this->_getContact());
        $contact = $this->_converter->toTine20Model($cardData, $this->_getContact());
        
        $this->_contact = Addressbook_Controller_Contact::getInstance()->update($contact);
        
        // @todo this belong to DAV_Server, but it currently not supported
        header('ETag: ' . $this->getETag());
    }
    
    /**
     * Updates the ACL
     *
     * This method will receive a list of new ACE's. 
     * 
     * @param array $acl 
     * @return void
     */
    public function setACL(array $acl) 
    {
        throw new Sabre_DAV_Exception_MethodNotAllowed('Changing ACL is not yet supported');
    }
    
    /**
     * return Addressbook_Model_Contact and convert contact id to model if needed
     * 
     * @return Addressbook_Model_Contact
     */
    protected function _getContact()
    {
        if (! $this->_contact instanceof Addressbook_Model_Contact) {
            $this->_contact = str_replace('.vcf', '', $this->_contact);
            $this->_contact = Addressbook_Controller_Contact::getInstance()->get($this->_contact);
        }
        
        return $this->_contact;
    }
    
    /**
     * return vcard and convert Addressbook_Model_Contact to vcard if needed
     * 
     * @return string
     */
    protected function _getVCard()
    {
        if ($this->_vcard == null) {
            $this->_vcard = $this->_converter->fromTine20Model($this->_getContact());
        }
        
        return $this->_vcard;
    }
}
