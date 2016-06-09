<?php
/**
 * Created by PhpStorm.
 * User: Ramin
 * Date: 12/17/2015
 * Time: 4:34 PM
 */
class cmGroupForm extends xml{

    public static $CM_TYPE_GROUP_STORE   = 100;
    public static $CM_TYPE_GROUP_PRODUCT = 101;

    private $xmlName = 'comment';
    /*
     * Create globalParam.xml file
     */
    public function init()
    {
        //Check global folder
        if( !file_exists(namespaceForm::getRootPath_S(true) . namespaceForm::getNameSpacePath_S(namespaceForm::NS_XML, false) . '/comment') )
            mkdir(namespaceForm::getRootPath_S(true) . namespaceForm::getNameSpacePath_S(namespaceForm::NS_XML, false) . '/comment', 0777);

        $this->createXML('<comments></comments>', namespaceForm::getRootPath_S(true) . namespaceForm::getNameSpacePath_S(namespaceForm::NS_COMMENT, false), false, true);
    }//end init function

    /*
     * Set xml attributes rules
     */
    public function rules()
    {
        if( $this->xmlName === 'comment' ) {
            return array(
                'name' => array('allowEmpty' => false, 'maxLength' => 10),
                'des' => array('allowEmpty' => true, 'maxLength' => 255),
                'assigned_name' => array('allowEmpty' => false,),
                'assigned_title' => array('allowEmpty' => false),
                'type' => array('allowEmpty' => false,)
            );
        }else{
            return array(
                'name'=>array('allowEmpty'=>false, 'maxLength'=>10),
                'title'=>array('allowEmpty'=>true, 'maxLength'=>255),
                'default'=>array('allowEmpty'=>false, 'max'=>5, 'min'=>1)
            );
        }
    }//end rules function

    /*
     * Create product group
     */
    public function crGroupProduct($name = null, $des = null, $pgName = null)
    {
        $this->xmlName = 'comment';

        if( $name === null )
            if( isset($this->name) ) $name = $this->name;

        if( empty($name) ) $name = $this->randomString();

        if( $des === null )
            if( isset($this->des) ) $des = $this->des;

        if( $pgName === null )
            if( isset($this->assigned_name) ) $pgName = $this->assigned_name;

        if( empty($pgName) ) return ER_PGNAME_INVALID;

        $pgForm = new productGroupForm();

        $pg = $pgForm->getPG($pgName);

        if( !is_array($pg) ) return ER_PGNAME_INVALID;

        //check name
        $find = $this->getGroup($name);

        if( is_array($find) ) return ER_COMMENTS_NAME_EXISTS;

        return $this->addElement('comment', '', '', '',
            array('name'=>$name, 'des'=>$des, 'assigned_name'=>$pgName, 'assigned_title'=>$pg['@attributes']['description'], 'type'=>self::$CM_TYPE_GROUP_PRODUCT), 'name');
    }//end crGroup function

    /*
     * Create store group comment
     */
    public function crGroupStore($name = null, $des = null, $gName = null)
    {
        $this->xmlName = 'comment';

        if( $name === null )
            if( isset($this->name) ) $name = $this->name;

        if( empty($name) ) $name = $this->randomString();

        if( $des === null )
            if( isset($this->des) ) $des = $this->des;

        if( $gName === null )
            if( isset($this->assigned_name) ) $gName = $this->assigned_name;

        if( empty($gName) ) return ER_UNKNOWN;

        $sgForm = new groupStoreForm();

        $gs = $sgForm->getGroup($gName);

        if( !is_array($gs) ) return ER_UNKNOWN;

        //check name
        $find = $this->getGroup($name);

        if( is_array($find) ) return ER_COMMENTS_NAME_EXISTS;

        return $this->addElement('comment', '', '', '',
            array('name'=>$name, 'des'=>$des, 'assigned_name'=>$gName, 'assigned_title'=>$gs['@attributes']['title'], 'type'=>self::$CM_TYPE_GROUP_STORE), 'name');

    }//end crGroupStore function


    /*
     * Get group by name
     */
    public function getGroup($name)
    {
        $this->xmlName = 'comment';
        return $this->find('comment', '', array('name'=>$name), 'AND', false);
    }//end getGroup function

    /*
     * Get groups by assigned_name
     */
    public function getGroupsByPGName($assigned_name)
    {
        $this->xmlName = 'comment';
        return $this->findAll('comment', '', array('assigned_name'=>$assigned_name), 'AND', false);
    }//end getGroupsByPGName function

    /*
     * Get group by type
     */
    private function getGroupsByType($type)
    {
        $this->xmlName = 'comment';
        return $this->findAll('comment', '', array('type'=>$type), 'AND', false);
    }//end getGroupsByType function

    public function getGroupsProductType()
    {
        $this->xmlName = 'comment';
        return $this->getGroupsByType(self::$CM_TYPE_GROUP_PRODUCT);
    }

    public function getGroupsStoreType()
    {
        $this->xmlName = 'comment';
        return $this->getGroupsByType(self::$CM_TYPE_GROUP_STORE);
    }

    /*
     * Get all comment groups
     */
    public function getGroups()
    {
        $this->xmlName = 'comment';
        return $this->findAll('comment', '', null, 'AND', false);
    }//end getGroups function

    /*
     * Remove group by name
     */
    public function rmGroup($name)
    {
        $this->xmlName = 'comment';
        if( empty($name) ) return ER_COMMENTS_NAME_NOT_FOUND;

        return $this->deleteElement('comment', '', array('name'=>$name), 'AND', true, false);
    }//end rmGroup function

    /*
     * New point item
     */
    public function crPointItem($gName = null, $pName = null, $pTitle = null, $defaultPoint = 1)
    {
        $this->xmlName = 'item';

        if( $gName === null )
            if( isset($this->gName) ) $gName = $this->gName;

        if( empty($gName) ) return ER_COMMENTS_NAME_NOT_FOUND;

        if( $pName === null )
            if( isset($this->pName) ) $pName = $this->pName;

        if( empty($pName) ) $pName = $this->randomString();

        if( empty($pName) ) return ER_COMMENTS_POINT_ITEM_NAME_INVALID;

        if( $pTitle === null )
            if( isset($this->pTitle) ) $pTitle = $this->pTitle;

        if( $defaultPoint === 1 )
            if( isset($this->defaultPoint) ) $defaultPoint = $this->defaultPoint;

        return $this->addElement('comment', 'item', '', '//comment[@name="' . $gName . '"]',
            array(
                'name'=>$pName,
                'title'=>$pTitle,
                'default'=>$defaultPoint
            ), 'name', true, true);
    }//end function

    /*
     * Get all point items with gName
     */
    public function getPointItems($gName, &$gTitle = null, &$gType = null)
    {
        if( empty($gName) ) return ER_COMMENTS_NAME_NOT_FOUND;

        $r = $this->find('comment', '', array('name'=>$gName), 'AND', false);

        $gTitle = $r['@attributes']['des'];
        $gType = $r['@attributes']['type'];

        if( isset($r['item']) && !isset($r['item'][0]) ) $r['item'] = array($r['item']);

        if( is_array($r) ) return isset($r['item']) ? $r['item'] : null; else return $r;
    }//end getPointItems function

    /*
     * Remove point item,
     */
    public function rmPointItem($gName, $pName)
    {
        if( empty($gName) ) return ER_COMMENTS_NAME_NOT_FOUND;
        if( empty($pName) ) return ER_COMMENTS_POINT_ITEM_NAME_INVALID;

        return $this->deleteElement('item', '//comment[@name="' . $gName . '"]//item[@name="' . $pName . '"]', array('name'=>$pName), 'AND');
    }//end rmPointItem function
}