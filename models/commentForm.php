<?php
/**
 * Created by PhpStorm.
 * User: Ramin
 * Date: 12/19/2015
 * Time: 4:07 PM
 */
class commentForm extends remoteServer{

    public $lastComment = null;

    public function init()
    {
        //Connect to active db server
        $sForm = new serverForm();
        $server = $sForm->getActiveServer();

        if( !is_array($server) || !is_array($server['db']) ) return false;

        $this->newConnectArray($server['db']);

        //Create table if not exits
        /*
         * @param id is integer and auto increment
         * @param path is comment path like this, storeID=12131; or storeID=12312;pCode=13121; or pId=1213;
         * @param title is subject of your comment
         * @param shortcoming is 255 character and show in red color
         * @param strengths is 255 character and show in green color
         * @param msg is text and is body comment
         * @param pointItems is JSON param of point-item. like point_item_name=1;
         * @param other is other param if you need to use
         * @param images is image's list like this, 23123.jpg. All image saved in user profile folder
         * @param publish is tinyint, it can be 0 or 1 or 2. 0=it can not publish, 1=can publish 2=it pending 
         */
        $sql = "CREATE TABLE IF NOT EXISTS comment
                (
                  id INT PRIMARY KEY AUTO_INCREMENT,
                  username VARCHAR (255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
                  path text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                  title VARCHAR (255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
                  shortcoming VARCHAR (255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
                  strengths VARCHAR (255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
                  msg text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
                  pointItems text,
                  other text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                  images text,
                  publish TINYINT,
                  ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  server VARCHAR (255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
                  bonus INT,
                  stuffPoint INT
                )ENGINE MyISAM COLLATE utf8_general_ci
               ";
        $this->dbQuery($sql, false);
    }//end init function

    /*
     * table name
     */
    public function tableName()
    {
        return 'comment';
    }

    /*
     * rules
     */
    public function rules()
    {
        return array(
            'username'=>array('allowEmpty'=>false, 'username'=>true),
            'path'=>array('allowEmpty'=>false),
            'title'=>array('allowEmpty'=>false, 'maxLength'=>255),
            'shortcoming'=>array('allowEmpty'=>true, 'maxLength'=>255),
            'strengths'=>array('allowEmpty'=>true, 'maxLength'=>255),
            'msg'=>array('allowEmpty'=>false, 'trimTag'=>true),
            'pointItems'=>array('allowEmpty'=>true),
            'other'=>array('allowEmpty'=>true),
            'images'=>array('allowEmpty'=>true),
            'publish'=>array('allowEmpty'=>false, 'max'=>2, 'min'=>0),
            'server'=>array('allowEmpty'=>false),
            'bonus'=>array('allowEmpty'=>false),
            'stuffPoint'=>array('allowEmpty'=>false, 'max'=>5, 'min'=>1),
            'ts'=>array('allowEmpty'=>true)
        );
    }//end rules function

    /*
     * new record
     */
    public function newRecord($username = null, $path = null, $title = null, $shortcoming = null, $strengths = null, $msg = null, $pointItems = null, $other = null, $images = null, $publish = 2, $stuffPoint = 1)
    {
        if( $username === null )
            if( isset($this->username) ) $username = $this->username;
        if( empty($username) ){
            if( isset($_SESSION['account']) ) $username = $_SESSION['account']['username'];
        }

        if( $path === null )
            if( isset($this->path) ) $path = $this->path;
        if( empty($path) ) return ER_COMMENTS_PATH_INVALID;

        if( $title === null )
            if( isset($this->title) ) $title = $this->title;
        if( empty($title) ) return ER_COMMENTS_TITLE_EMPTY;

        if( $shortcoming === null )
            if( isset($this->shortcoming) ) $shortcoming = $this->shortcoming;

        if( $strengths === null )
            if( isset($this->strengths) ) $strengths = $this->strengths;

        if( $msg === null )
            if( isset($this->msg) ) $msg = $this->msg;

        if( empty($msg) ) return ER_COMMENTS_MSG_EMPTY;

        if( $pointItems === null )
            if( isset($this->pointItems) ) $pointItems = $this->pointItems;

        if( $other === null )
            if( isset($this->other) ) $other = $this->other;

        if( $images === null )
            if( isset($this->images) ) $images = $this->images;

        if( $publish === 2 )
            if( isset($this->publish) ) $publish = $this->publish;

        if( $stuffPoint === 1 )
            if( isset($this->stuffPoint) ) $stuffPoint = $this->stuffPoint;

        //Connect to active db server
        $sForm = new serverForm();
        $server = $sForm->getActiveServer();

        if( !is_array($server) || !is_array($server['db']) ) return ER_SERVER_NOT_FOUND;

        $this->newConnectArray($server['db']);

        //create param
        $p = array(
            'username'=>$username,
            'path'=>$path,
            'title'=>$title,
            'shortcoming'=>$shortcoming,
            'strengths'=>$strengths,
            'msg'=>htmlentities($msg, null, "UTF-8"),
            'pointItems'=>$pointItems,
            'other'=>$other,
            'images'=>$images,
            'publish'=>$publish,
            'server'=>json_encode(array('db'=>$server['db']['s_title'], 'dp'=>$server['dp']['s_title'])),
            'bonus'=>0,
            'stuffPoint'=>$stuffPoint
        );

        $r = $this->save(null, $p);

        if( $r !== true ) return $r;

        $id = $this->getLastIdFrom();

        $p['id'] = $id;

        $this->lastComment = $p;

        return true;
    }

    /*
     * Normalize result search in comments
     */
    private function normalizeComments($cs)
    {
        if( empty($cs) ) return null;

        $comments = null;
        foreach($cs as $c){
            $r['username'] = $c['username'];
            $r['id'] = $c['id'];

            //normal path
            $path = explode(';', $c['path']);
            $nList = null;
            for($i=0; $i<=count($path)-1; $i++){
                if( empty($path[$i]) ) continue;

                $p = explode('=', $path[$i]);
                if( empty($p) ) continue;

                $nList[$p[0]] = $p[1];
            }
            if( $nList === null ) $r['path'] = ''; else $r['path'] = $nList;

            $r['title'] = $c['title'];
            $r['shortcoming'] = $c['shortcoming'];
            $r['strengths'] = $c['strengths'];
            $r['msg'] = $c['msg'];

            //normal pointItems
            $pointItems = explode(';', $c['pointItems']);
            $nList = null;
            for($i=0; $i<=count($pointItems)-1; $i++){
                if( empty($pointItems[$i]) ) continue;

                $p = explode('=', $pointItems[$i]);
                if( empty($p) ) continue;

                $nList[$p[0]] = $p[1];
            }
            if( $nList === null ) $r['pointItems'] = ''; else $r['pointItems'] = $nList;

            $r['other'] = $c['other'];

            $r['images'] = explode(',', $c['images']);
            $r['publish'] = $c['publish'];
            $r['server'] = json_decode($c['server'], true);
            $r['ts'] = $c['ts'];
            $r['bonus'] = $c['bonus'];
            $r['stuffPoint'] = $c['stuffPoint'];

            $comments[] = $r;
        }

        return $comments;
    }//end normalizeComments function

    /*
     * Get comments by path
     * @param path must be string
     */
    public function getCommentsByPath($path)
    {
        $sForm = new serverForm();
        $dbs = $sForm->getServersByType('db');

        if( empty($dbs) ) return ER_SERVER_NOT_FOUND;

        $criteria = array(
            'condition'=>'path=:p',
            'params'=>array(':p'=>array($path, PDO::PARAM_STR))
        );

        $sort = array(
            'order'=>'ts',
            'sort'=>'DESC'
        );

        $list = null;
        foreach($dbs as $db){
            $this->newConnectArray($db);

            $r = $this->findAll($criteria, $sort);
            if( $list === null ) $list = $r; else $list = array_merge_recursive($list, $r);
        }

        return $this->normalizeComments($list);
    }//end getCommentsByPath

    /*
     * Get comments by username
     */
    public function getCommentsByUsername($username)
    {
        $sForm = new serverForm();
        $dbs = $sForm->getServersByType('db');

        if( empty($dbs) ) return ER_SERVER_NOT_FOUND;

        $criteria = array(
            'condition'=>'username=:p',
            'params'=>array(':p'=>array($username, PDO::PARAM_STR))
        );

        $sort = array(
            'order'=>'ts',
            'sort'=>'DESC'
        );

        $list = null;
        foreach($dbs as $db){
            $this->newConnectArray($db);

            $r = $this->findAll($criteria, $sort);
            if( $list === null ) $list = $r; else $list = array_merge_recursive($list, $r);
        }

        return $this->normalizeComments($list);

    }//end getCommentsByUsername function

    /*
     * Get comment by id and server path
     */
    public function getCommentByID($id, array $server)
    {
        if( !is_array($server) || !isset($server['db']) ) return ER_SERVER_NOT_FOUND;

        $sForm = new serverForm();
        $db = $sForm->getServer($server['db']);

        if( !is_array($db) ) return ER_SERVER_NOT_FOUND;

        $this->newConnectArray($db);

        $criteria = array(
            'condition'=>'id=:i',
            'params'=>array(':i'=>array($id, PDO::PARAM_INT))
        );

        $r =  $this->find($criteria);

        $r =  $this->normalizeComments(array($r));

        return isset($r[0]) ? $r[0] : $r;
    }//end getCommentByID function

    /*
     * Create path
     */
    public function crPath($storeID = null, $pCode = null, $pId = null)
    {
        if( $storeID === null )
            if( isset($this->storeID) ) $storeID = $this->storeID;

        if( $pCode === null )
            if( isset($this->pCode) ) $pCode = $this->pCode;

        if( $pId === null)
            if( isset($this->pId) ) $pId = $this->pId;

        $s = '';
        if( !empty($storeID) ) $s .= 'storeID=' . $storeID . ';';
        if( !empty($pCode) ){
            $s .= 'pCode=' . $pCode . ';';
            return $s;
        }
        if( !empty($pId) ) return 'pId=' . $pId;

        return $s;
    }//end crPath function

    /*
     * Normalize Path
     */
    private function normalizePath($path)
    {
        if( is_array($path) ) return $path;

        $ps = explode(';', $path);

        if( empty($ps) ) return $path;

        $l = null;
        foreach($ps as $p){
            if( empty($p) ) continue;

            $p = explode('=', $p);

            if( !isset($p[0]) || !isset($p[1]) ) continue;

            $l[$p[0]] = $p[1];
        }

        return $l;
    }//end normalizePath function

    /*
     * Get comment group by path
     */
    public function getCommentGroupByPath($path)
    {
        $cgForm = new cmGroupForm();

        #normalize path
        $path = $this->normalizePath($path);

        if( isset($path['storeID']) && (!isset($path['pCode']) && !isset($path['pId'])) ){
            //Get store group
            $sForm = new storeForm();
            $s = $sForm->getStore($path['storeID'], true);
            $storeGroup = $s['storeGroup'];

            return $cgForm->getGroupsByPGName($storeGroup);
        }

        if( isset($path['storeID']) && isset($path['pCode']) && !isset($path['pId']) ){
            $ppForm = new profileProductForm();
            $mp = $ppForm->getMyProductByStoreIDAndPCode($path['storeID'], $path['pCode']);

            return $cgForm->getGroupsByPGName($mp['pgName']);
        }

        if( !isset($path['storeID']) && !isset($path['pCode']) && isset($path['pId']) ){
            $pForm = new productForm();
            $p = $pForm->getProduct($path['pId']);

            return $cgForm->getGroupsByPGName($p['_pgName']);
        }

        return null;
    }//end getCommentGroupByPath function

    /*
     * Check user commented on this path ?
     */
    public function checkUserCommented($username, $path)
    {
        $criteria = array(
            'condition'=>'username=:u,path=:p',
            'params'=>array(
                ':u'=>array($username, PDO::PARAM_STR),
                ':p'=>array($path, PDO::PARAM_STR)
            )
        );

        $sForm = new serverForm();
        $dbs = $sForm->getServersByType('db');

        if( empty($dbs) ) return ER_SERVER_NOT_FOUND;

        foreach($dbs as $db){
            $this->newConnectArray($db);

            $r = $this->find($criteria, null, 'AND');

            if( !empty($r) && is_array($r) ) return true;
        }

        return false;
    }//end checkUserCommented function

    /*
     * Upload comment image by username and path
     */
    public function uploadImageToCommentFolder($username, $fileToMode, &$out =null, &$crLinkOut = null)
    {
        //Get user server
        $accForm = new accountForm();
        $user =$accForm->getAccountInformation($username);

        if( !is_array($user) ) return $user;

        $sForm = new serverForm();
        $dp = $sForm->getServer($user['server']['dp']);

        if( !is_array($dp) ) return ER_SERVER_NOT_FOUND;

        $path = namespaceForm::getNameSpacePath_S(namespaceForm::NS_PROFILE_REMOTE);
        if( $this->file_exists_r($dp['s_url'], $path, '') !== true )
            $this->crDir_r($dp['s_url'], '', $path, 0777);

        $path .= $username;
        if( $this->file_exists_r($dp['s_url'], $path, '') !== true )
            $this->crDir_r($dp['s_url'], namespaceForm::getNameSpacePath_S(namespaceForm::NS_PROFILE_REMOTE, false), $username, 0777);

        $path .= namespaceForm::getNameSpacePath_S(namespaceForm::NS_COMMENT_IMAGE_REMOTE);

        if( $this->file_exists_r($dp['s_url'], $path, '') !== true )
            $this->crDir_r($dp['s_url'], namespaceForm::getNameSpacePath_S(namespaceForm::NS_PROFILE_REMOTE) . $username, namespaceForm::getNameSpacePath_S(namespaceForm::NS_COMMENT_IMAGE_REMOTE), 0777);

        $randName = $this->randomString(10) . '.jpg';

        $gdForm = new gdForm();
        $gdForm->appendWaterMark(namespaceForm::getRootPath_S(true).$fileToMode);

        $r = $this->upFile_r($dp['s_url'], App::$config->baseUrl . $fileToMode, $path, array('desFileName'=>$randName), $crLinkOut);

        if( $r === true ){
            $out= $path . $randName;
            $crLinkOut = $this->crLink_r($dp['s_url'], $path.$randName);

            unlink( namespaceForm::getRootPath_S(true)  . $fileToMode);
        }

        return $r;
    }//end uploadImageToCommentFolder function

    /*
     * Send to operator notification to uphold comment
     */
    public function sendNotificationToCRMToUphold($id, $server, $username)
    {
        #Write this to confirm table
        $confirmForm = new confirmForm();
        $confirmForm->confrim_name          = $confirmForm::$COMMENT_CONFIRM;
        $confirmForm->confirm_params        = json_encode( array('username'=>$username, 'id'=>$id, 'server'=>$server) );
        $confirmForm->other_params          = '';
        $confirmForm->confirm_state         = 0;
        $confirmForm->confirm_answerable    = '';
        $confirmForm->confirm_time          = '';
        $r = $confirmForm->newConfirm($_id);

        //Write in notification table and send information from websocket to crm admins
        if( $r === true ) {
            $notiForm = new notificationForm();
            $r = $notiForm->newNoti_Custom(notificationForm::$CASE_MODE_CONFIRM_COMMENT, 0, null, $_id);
        }

        return $r;
    }//end sendNotificationToUphold

    /*
     * Change publish value
     */
    public function changePublish($id, $server, $publish = 0)
    {
        if( !is_array($server) || !isset($server['db']) ) return ER_SERVER_NOT_FOUND;

        $sForm = new serverForm();
        $db = $sForm->getServer($server['db']);

        if( !is_array($db) ) return ER_SERVER_NOT_FOUND;

        $this->newConnectArray($db);

        //Get comment
        $cm = $this->getCommentByID($id, $server);
        $ts = $cm['ts'];

        return $this->save(array('id'=>$id), array('publish'=>$publish, 'ts'=>$ts));
    }//end changePublish function

    /*
     * Get comments count
     */
    public function commentsCount()
    {
        $sForm = new serverForm();
        $dbs = $sForm->getServersByType('db');

        if( empty($dbs) ) return ER_SERVER_NOT_FOUND;

        $list = array();
        foreach($dbs as $db){
            $this->newConnectArray($db);

            $r = $this->findAll();
            if( is_array($r) ) $list = array_merge_recursive($list, $r);
        }

        return count($list);
    }//end commentsCount function

    /*
     * Get all comments in all server
     */
    public function allComments()
    {
        $sForm = new serverForm();
        $dbs = $sForm->getServersByType('db');

        if( empty($dbs) ) return ER_SERVER_NOT_FOUND;

        $list = array();
        foreach($dbs as $db){
            $this->newConnectArray($db);

            $r = $this->findAll();

            $list = array_merge_recursive($list, $this->normalizeComments($r));
        }

        return $list;
    }//end allComments function

    /*
     * remove an comment by id and server path
     */
    public function rmCommentWithID($id, array $server)
    {
        if( $id === null ) return ER_COMMENTS_ID_INVALID;

        if( empty($server) ) return ER_SERVER_NOT_FOUND;

        #1. get comment first by id
        $cm = $this->getCommentByID($id, $server);

        if( !is_array($cm) ) return ER_UNKNOWN;

        $sForm = new serverForm();
        $dp = $sForm->getServer($server['dp']);
        $db = $sForm->getServer($server['db']);

        if( !is_array($dp) || !is_array($db) ) return ER_SERVER_NOT_FOUND;

        #Now delete image from folder
        for($i=0; $i<=count($cm['images'])-1; $i++){
            $this->rmFile_r($dp['s_url'], $cm['images'][$i]);
        }

        #Now delete record from db
        $this->newConnectArray($db);
        return $this->delete(array('id'=>$id));
    }

    /*
     * Remove comment by username
     */
    public function rmCommentsWithUsername($username)
    {
        if( empty($username) ) return ERROR_USER_NOT_EXISTS;

        $sForm = new serverForm();
        $dbs = $sForm->getServersByType('db');

        if( empty($dbs) || !is_array($dbs) ) return ER_SERVER_NOT_FOUND;

        foreach ($dbs as $db) {
            $this->newConnectArray($db);

            $r = $this->delete(array('username'=>$username), 'AND');

            if( $r === false ) return false;
        }

        return true;
    }//end rmCommentsWithUsername function

    /*
     * Calculate rate of comments
     *
     * return is percent rate of 5 number
     */
    public function calculateRate($comments, $minimumVote = 1, $precision = 2)
    {
        if( !is_array($comments) ) return 0;
        if( count($comments) < $minimumVote ) return 0;

        $count = 0;
        $stuff = 0;
        foreach($comments as $cm){
            if( !isset($cm['stuffPoint']) ) continue;

            $stuff += $cm['stuffPoint'];
            $count++;
        }

        if( $count <= 0 || $stuff <= 0 ) return 0;

        return round( (($stuff / ($count*5)))*100, $precision);
    }//end calculateRate function
}