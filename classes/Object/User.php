<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This class defines the object for PDO to use when retrives data about a talk.
 * 
 * @category Object_User
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Object_User extends Abstract_GenericObject
{
    protected $arrDBItems = array(
        'strName'     => array('type' => 'varchar', 'length' => 255, 'required' => 'worker'),
        'jsonLinks'   => array('type' => 'text'),
        'isWorker'    => array('type' => 'tinyint', 'length' => 1, 'optional' => 'admin'),
        'isAdmin'     => array('type' => 'tinyint', 'length' => 1, 'optional' => 'admin'),
        'hasAttended' => array('type' => 'tinyint', 'length' => 1, 'optional' => 'worker'),
        'isHere'      => array('type' => 'tinyint', 'length' => 1, 'optional' => 'worker'),
        'lastChange'  => array('type' => 'datetime')
    );
    protected $strDBTable      = "user";
    protected $strDBKeyCol     = "intUserID";
    protected $reqCreatorToMod = true;
    // Local Object Requirements
    protected $intUserID       = null;
    protected $strName         = null;
    protected $jsonLinks       = null;
    protected $isWorker        = false;
    protected $isAdmin         = false;
    protected $hasAttended     = false;
    protected $isHere          = false;
    protected $lastChange      = false;
    // Temporary storage values
    public $objUserAuthTemp    = null;

    /**
     * This function should only be used by system activites - such as room sorting
     *
     * @param boolean $isSystem Optionally set whether this is a system call or not
     * 
     * @return boolean Whether this is acting as a system request or not.
     */
    public static function isSystem($isSystem = null)
    {
        $objCache = Base_Cache::getHandler();
        if ($isSystem === false && isset($objCache->arrCache['Object_User']['isSystem'])) {
            unset($objCache->arrCache['Object_User']['isSystem']);
        } elseif ($isSystem != null && $isSystem != false) {
            $objCache->arrCache['Object_User']['isSystem'] = (boolean) $isSystem;
        }
        if (isset($objCache->arrCache['Object_User']['isSystem'])) {
            return $objCache->arrCache['Object_User']['isSystem'];
        } else {
            return false;
        }
    }
    
    /**
     * Calculate whether the user is an admin
     * 
     * @param integer $intUserID The UserID to check whether they're an admin.
     *
     * @return boolean 
     */
    public static function isAdmin($intUserID = null)
    {
        if (self::isSystem()) {
            return true;
        } elseif ($intUserID == null) {
            $self = self::brokerCurrent();
        } else {
            $self = self::brokerByID($intUserID);
        }
        if ($self != false && $self->getKey('isAdmin') == 1) {
            return true;
        }
        return false;
    }
    
    /**
     * Calculate whether the user is a worker
     * 
     * @param integer $intUserID The UserID to check whether they're a worker.
     *
     * @return boolean 
     */
    public static function isWorker($intUserID = null)
    {
        if (self::isSystem()) {
            return true;
        } elseif ($intUserID == null) {
            $self = self::brokerCurrent();
        } else {
            $self = self::brokerByID($intUserID);
        }
        if ($self != false && $self->getKey('isWorker') == 1) {
            return true;
        }
        return false;
    }

    /**
     * Calculate whether the user is the creator (or admin, or system)
     *
     * @param integer $intUserID  The user who created the object
     * @param integer $thisUserID The user we are checking against in the function
     * 
     * @return boolean 
     */
    public static function isCreator($intUserID = null, $thisUserID = null)
    {
        if ($thisUserID == null) {
            $self = self::brokerCurrent();
        } else {
            $self = self::brokerByID($thisUserID);
        }
        if ($self != false && $self->getKey('intUserID') == $intUserID) {
            return true;
        }
        if ($self != false && $self->getKey('isAdmin') == 1) {
            return true;
        }
        if ($self != false && $self->getKey('isWorker') == 1) {
            return true;
        }
        return self::isSystem();
    }
    
    /**
     * Get the object for the current user.
     * 
     * @return object UserObject for intUserID
     */
    public static function brokerCurrent()
    {
        $objCache = Base_Cache::getHandler();
        $thisClassName = get_called_class();
        $thisClass = new $thisClassName(false);
        if (true === isset($objCache->arrCache[$thisClassName]['current'])
            && $objCache->arrCache[$thisClassName]['current'] != null
            && $objCache->arrCache[$thisClassName]['current'] != false
        ) {
            return $objCache->arrCache[$thisClassName]['current'];
        }
        $objUserAuth = Object_Userauth::brokerCurrent();
        if (!is_object($objUserAuth)) {
            return false;
        }
        try {
            $objDatabase = Container_Database::getConnection();
            $sql = "SELECT * FROM {$thisClass->strDBTable} WHERE {$thisClass->strDBKeyCol} = ? LIMIT 1";
            $query = $objDatabase->prepare($sql);
            $values = array($objUserAuth->getKey('intUserID'));
            $query->execute($values);
            $result = $query->fetchObject($thisClassName);
            if ($result != false) {
                $objCache->arrCache[$thisClassName]['id'][$objUserAuth->getKey('intUserID')] = $result;
                $objCache->arrCache[$thisClassName]['current'] = $result;
            }
            return $result;
        } catch(PDOException $e) {
            error_log("SQL Error: " . $e->getMessage() . " in SQL: $sql with values " . print_r($values, true));
            return false;
        }
    }
    
    /**
     * Get the intUserID for the input received.
     *
     * @param object $objInput The Object_Input object to process
     * 
     * @return object 
     */
    public static function brokerByCodeOnly($objInput = null)
    {
        if (is_object($objInput) && get_class($objInput) == 'Object_Input') {
            $strCodeOnly = $objInput->getKey('strInterface') . '++' . $objInput->getKey('strSender');
            $arrUserAuth = Object_Userauth::brokerByColumnSearch('strAuthValue', $strCodeOnly . ':%');
            $intUserID = null;
            foreach ($arrUserAuth as $objUserAuth) {
                if ($intUserID == null 
                    && $objUserAuth->getKey('enumAuthType') == 'codeonly'
                ) {
                    $intUserID = $objUserAuth->getKey('intUserID');
                }
            }
            if ($intUserID == null) {
                $objUser = new Object_User(true, $strCodeOnly);
            } else {
                $objUser = Object_User::brokerByID($intUserID);
            }
            $objCache = Base_Cache::getHandler();
            $objCache->arrCache['Object_User']['current'] = $objUser;
            return $objUser;
        } else {
            return false;
        }
    }


    /**
     * Create a new User object, or post-process the PDO data
     *
     * @param boolean        $isCreationAction Perform Creation Actions (default
     * false)
     * @param string|boolean $strCodeOnly      CodeOnly string to pass to the 
     * UserAuth object
     *
     * @return object This object
     */
    function __construct($isCreationAction = false, $strCodeOnly = false)
    {
        parent::__construct();
        if (! $isCreationAction) {
            return $this;
        }
        try {
            $objUserAuth = new Object_Userauth(true, $strCodeOnly);
            if (is_object($objUserAuth)) {
                $objRequest = Container_Request::getRequest();
                $this->setKey('strUserName', $objRequest->get_strUsername());
                $this->create();
                Object_User::isSystem(true);
                $objUserAuth->setKey('intUserID', $this->getKey('intUserID'));
                $objUserAuth->write();
                Object_User::isSystem(false);
                $this->objUserAuthTemp = $objUserAuth;
                if ($objUserAuth->getKey('enumAuthType') == 'openid'
                    || $objUserAuth->getKey('enumAuthType') == 'basicauth'
                ) {
                    $this->objUserAuthTemp = new Object_Userauth(false);
                    $this->objUserAuthTemp->setKey('enumAuthType', 'codeonly');
                    $authString = '';
                    while ($authString == '') {
                        $authString = Base_GeneralFunctions::genRandStr(5, 9);
                        if (count(Object_Userauth::brokerByColumnSearch('strAuthValue', '%:' . sha1(Container_Config::getSecureByID('salt', 'Not Yet Set!!!')->getKey('value') . $authString))) > 0) {
                            $authString == '';
                        }
                    }
                    $this->objUserAuthTemp->setKey('strAuthValue', array('password' => $authString, 'username' => 'codeonly_' . $objUserAuth->getKey('enumAuthType') . '_' . $objUserAuth->getKey('intUserAuthID')));
                }
            } else {
                $this->errorMessageReturn = $objUserAuth;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * This function clears values related to a logged in user.
     * 
     * @return void
     */
    static function logout()
    {
        Base_GeneralFunctions::startSession();
        $objRequest = Container_Request::getRequest();
        if (isset($_SESSION['intUserAuthID']) && $_SESSION['intUserAuthID'] != '') {
            unset($_SESSION['intUserAuthID']);
        }
        if (isset($_SESSION['OPENID_AUTH']) && $_SESSION['OPENID_AUTH'] != '') {
            unset($_SESSION['OPENID_AUTH']);
        }
        if ($objRequest->get_strUsername() != '') {
            Base_Response::sendHttpResponse(401);
        }
    }
    
    /**
     * This overloaded function returns the data from the PDO object and adds
     * supplimental data based on linked tables
     * 
     * @return array
     */
    function getSelf()
    {
        $self = parent::getSelf();
        if ($this->isFull() == true) {
            $arrUserAuth = Object_Userauth::brokerByColumnSearch('intUserID', $this->intUserID);
            foreach ($arrUserAuth as $key => $value) {
                $self['arrUserAuth'][$key] = $value->getSelf();
                if ($self['arrUserAuth'][$key]['epochLastChange'] > $self['epochLastChange']) {
                    $self['epochLastChange'] = $self['arrUserAuth'][$key]['epochLastChange'];
                }
            }
        }
        Base_Response::setLastModifiedTime($self['epochLastChange']);
        $self['lastChange'] = date('Y-m-d H:i:s', $self['epochLastChange']);
        return $self;
    }

    /**
     * This function will merge the content of two user accounts, as well as 
     *
     * @param object $objUser The user object to merge into this user account
     * 
     * @return type 
     */
    public function merge($objUser)
    {
        if (!is_object($objUser)
            || get_class($objUser) != 'Object_User'
            || $objUser->getKey('intUserID') == $this->getKey('intUserID')
        ) {
            return false;
        }
        if ($objUser->getKey('intUserID') > $this->getKey('intUserID')) {
            $objToUser = $this;
            $objFromUser = $objUser;
        } else {
            $objToUser = $objUser;
            $objFromUser = $this;
        }
        if ($objToUser->getKey('strName') == '' && $objFromUser->getKey('strName') != '') {
            $objToUser->setKey('strName', $objFromUser->getKey('strName'));
            $objToUser->write();
        }
        if ($objFromUser->getKey('isWorker') == 1 && $objToUser->getKey('isWorker') != 1) {
            $objToUser->setKey('isWorker', 1);
        }
        if ($objFromUser->getKey('isAdmin') == 1 && $objToUser->getKey('isAdmin') != 1) {
            $objToUser->setKey('isAdmin', 1);
        }
        if ($objFromUser->getKey('hasAttended') == 1 && $objToUser->getKey('hasAttended') != 1) {
            $objToUser->setKey('hasAttended', 1);
        }
        if ($objFromUser->getKey('isHere') == 1 && $objToUser->getKey('isHere') != 1) {
            $objToUser->setKey('isHere', 1);
        }
        $arrAttendee = Object_Attendee::brokerByColumnSearch('intUserID', $objFromUser->getKey('intUserID'));
        $arrUserAuth = Object_Userauth::brokerByColumnSearch('intUserID', $objFromUser->getKey('intUserID'));
        $arrTalk = Object_Talk::brokerByColumnSearch('intUserID', $objFromUser->getKey('intUserID'));
        $arrTalkPresenters = Object_Talk::brokerByColumnSearch('jsonOtherPresenters', $objFromUser->getKey('intUserID'), false, true);
        foreach ($arrAttendee as $objAttendee) {
            $objAttendee->setKey('intUserID', $objToUser->getKey('intUserID'));
            $objAttendee->write();
        }
        foreach ($arrUserAuth as $objUserAuth) {
            $objUserAuth->setKey('intUserID', $objToUser->getKey('intUserID'));
            $objUserAuth->write();
        }
        foreach ($arrTalk as $objTalk) {
            $objTalk->setKey('intUserID', $objToUser->getKey('intUserID'));
            $objTalk->write();
        }
        foreach ($arrTalkPresenters as $objTalk) {
            $arrPresenters = json_decode($objTalk->getKey('jsonOtherPresenters'), true);
            foreach ($arrPresenters as $intPresenterID) {
                if ($intPresenterID != $objFromUser->getKey('intUserID')) {
                    $arrNewPresenters[] = $intPresenterID;
                }
            }
            $arrNewPresenters[] = $objToUser->getKey('intUserID');
            $objTalk->setKey('jsonOtherPresenters', json_encode($arrNewPresenters));
            $objTalk->write();
        }
        $objFromUser->delete();
    }
}

/**
 * This class defines some default and demo data for the use in demos.
 * 
 * @category Object_User
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
class Object_User_Demo extends Object_User
{
    protected $arrDemoData = array(
        array('intUserID' => 1, 'strName' => 'Mr Keynote', 'isWorker' => 0, 'isAdmin' => 0, 'hasAttended' => 1, 'isHere' => 1),
        array('intUserID' => 2, 'strName' => 'Mr CFM Admin', 'isWorker' => 1, 'isAdmin' => 1, 'hasAttended' => 1, 'isHere' => 1),
        array('intUserID' => 3, 'strName' => 'Ms SoftSkills', 'isWorker' => 1, 'isAdmin' => 0, 'hasAttended' => 1, 'isHere' => 1),
        array('intUserID' => 4, 'strName' => 'Ms Attendee', 'isWorker' => 0, 'isAdmin' => 0, 'hasAttended' => 0, 'isHere' => 0)
    );
}