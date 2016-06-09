<?php
/**
 * Created by PhpStorm.
 * User: Ramin
 * Date: 5/12/2015
 * Time: 8:58 PM
 */
class cmController extends Controller{

    /*
     * Check user identity
     */
    public function userIdentity(){

        #This function not need to check identity
        $notNeed = array(
            'index',
            'groupcrm',
            'pointitem',
            'commentscrm'
        );

        #Check user login
        if( (!isset($_SESSION['account']) || !isset($_SESSION['account']['username'])) && !in_array(App::$actionId, $notNeed) ){
            $this->redirectToNewController('profile/user', 'login');

        }
    }//End userIdentity function

    public function __construct(){

        //Check user package
        if( isset($_SESSION['account']) ){
            $pkForm = new packageForm();
            $pk = $pkForm->getPackage($_SESSION['account']['package']);

            $access = $pk['access'];
            $access = explode(',', $access);

            if( !in_array('product', $access) ){
                App::$actionId = 'accessDenied';
                $_REQUEST['op'] = 'package';
                parent::__construct();
                $this->renderPartialWithAction('package', 'accessDenied');
            }
        }//end if
        parent::__construct();

        if( isset($_SESSION['account']) )
            $this->default_parameters['account'] = $_SESSION['account'];
        $this->default_parameters['whichUser'] = 'user';

    }

    /*
     * Index action
     */
    public function actionIndex(){
        echo '<strong>Everything is OK!</strong>';
    }

    /*
     * new comment group action
     */
    public function actionGroupCRM()
    {
        $cgForm = new cmGroupForm();

        if( isset($_REQUEST['op']) ){
            switch($_REQUEST['op']){
                /*
                 * Get all groups
                 */
                case 'gall':
                    $gs = $cgForm->getGroups();
                    $this->renderPartial('_groups', array('gs'=>$gs));
                    break;
                /*
                 * new group form
                 */
                case 'cr-g-f':
                    $this->renderPartial('_new-group-form');
                    break;
                /*
                 * Get group-store or product-group list
                 */
                case 'g-list':
                    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : 100;

                    if( $type == cmGroupForm::$CM_TYPE_GROUP_PRODUCT ){
                        $pgForm = new productGroupForm();
                        $list = $pgForm->getPGs();

                        die( json_encode($list) );
                    }else if( $type == cmGroupForm::$CM_TYPE_GROUP_STORE ){
                        $sgForm = new groupStoreForm();

                        $list = $sgForm->getGroups();

                        die( json_encode($list) );
                    }
                    break;
                /*
                 * new group
                 */
                case 'n-g':
                    parse_str($_POST['el'], $els);
                    $cgForm->setAttributes($els);
                    if( $els['type'] == cmGroupForm::$CM_TYPE_GROUP_STORE )
                        $r = $cgForm->crGroupStore();
                    else
                        $r = $cgForm->crGroupProduct();

                    if( $r === false ) die( $this->getErrorLabel(ER_UNKNOWN) ); else
                        if( $r !== true ) die( $this->getErrorLabel($r) ); else die('');
                    break;
                /*
                 * remove an group by name
                 */
                case 'rm-g':
                    $name = isset($_POST['gName']) ? $_POST['gName'] : null;
                    $cgForm->rmGroup($name);
                    break;
            }
        }else{
            $this->renderPartial('groups-full-width');
        }
    }//end actionGroup function

    /*
     * point item action
     */
    public function actionPointItem()
    {
        $cgForm = new cmGroupForm();

        if( isset($_REQUEST['op']) ){
            switch($_REQUEST['op']){
                /*
                 * Get point item by gName, ENCODING-JSON
                 */
                case 'g':
                    $gName = isset($_POST['gName']) ? $_POST['gName'] : null;
                    $r = $cgForm->getPointItems($gName, $gTitle, $gType);

                    $this->renderPartial('_items', array('items'=>$r, 'gTitle'=>$gTitle, 'gType'=>$gType, 'gName'=>$gName));
                    break;
                /*
                 * Refresh g op
                 */
                case 'ref-g':
                    $gName = isset($_POST['gName']) ? $_POST['gName'] : null;
                    $r = $cgForm->getPointItems($gName, $gTitle, $gType);

                    $this->renderPartial('_ref-items', array('items'=>$r, 'gTitle'=>$gTitle, 'gType'=>$gType, 'gName'=>$gName));
                    break;
                /*
                 * create new point item form
                 */
                case 'cr-f':
                    $this->renderPartial('_new-item');
                    break;
                /*
                 * create new point
                 */
                case 'n':
                    parse_str($_POST['el'], $el);
                    $cgForm->setAttributes($el);
                    $r = $cgForm->crPointItem($_POST['gName']);

                    if( $r === false ) die( $this->getErrorLabel(ER_UNKNOWN) ); else
                        if( $r !== true ) die( $this->getErrorLabel($r) ); else die('');
                    break;
                /*
                 * remove an point item
                 */
                case 'rm':
                    $gName = isset($_POST['gName']) ? $_POST['gName'] : null;
                    $pName = isset($_POST['pName']) ? $_POST['pName'] : null;

                    $r = $cgForm->rmPointItem($gName, $pName);
                    if( $r === false ) die( $this->getErrorLabel(ER_UNKNOWN) ); else
                        if( $r !== true ) die( $this->getErrorLabel($r) ); else die('');
                    break;
            }
        }//end if
    }//end actionPointItem function


    /*
     * get comment action
     */
    public function actionComments()
    {
        //Get necessary params
        $storeID = isset($_POST['storeID']) ? $_POST['storeID'] : null;
        $pCode = isset($_POST['pCode']) ? $_POST['pCode'] : null;
        $pId = isset($_POST['pId']) ? $_POST['pId'] : null;

        $cForm = new commentForm();
        $path = $cForm->crPath($storeID, $pCode, $pId);

        $comments = $cForm->getCommentsByPath($path);

        if( isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'json' ) die( json_encode($comments) );else
            $this->renderPartial('_comments', array('comments'=>$comments));
    }//end actionComments function

    /*
     * Get all comments action in crm
     */
    public function actionCommentsCRM()
    {
        $cmForm = new commentForm();

        if( isset($_POST['op']) ){
            switch($_POST['op']){
                /*
                 * remove an comments
                 * @param id=>comment id
                 * @param server=>comment server
                 */
                case 'rm':
                    $id = isset($_POST['id']) ? $_POST['id'] : null;
                    $server = isset($_POST['server']) ? json_decode($_POST['server'], true) : null;

                    $r = $cmForm->rmCommentWithID($id, $server);
                    if( $r === false ) die( $this->getErrorLabel(ER_UNKNOWN) ); else
                        if( $r === true ) die(''); else die($this->getErrorLabel($r));
                    break;
                /*
                 * refresh comments
                 */
                case 'ref-cms':
                    $comments = $cmForm->allComments();

                    $start = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
                    $per = isset($_REQUEST['per']) ? $_REQUEST['per'] : 30;

                    $comments = $cmForm->createPagination($comments, $per, $start);

                    $this->renderPartial('_ref-cms', array('cms'=>$comments));
                    break;
                /*
                 * change state form
                 */
                case 'ch-state':
                    $id = isset($_POST['id']) ? $_POST['id'] : null;
                    $server = isset($_POST['server']) ? json_decode($_POST['server'], true) : null;

                    if( $server === null ) die('');

                    $cm = $cmForm->getCommentByID($id, $server);
                    $this->renderPartial('_ch-state', array('publish'=>$cm['publish'], 'id'=>$id, 'server'=>$_POST['server']));
                    break;
                case 'up-state':
                    $id = isset($_POST['id']) ? $_POST['id'] : null;
                    $server = isset($_POST['server']) ? json_decode($_POST['server'], true) : null;

                    $r = $cmForm->changePublish($id, $server, $_POST['publish']);

                    if( $r === false ) die( $this->getErrorLabel(ER_UNKNOWN) ); else
                        if( $r === true ) die(''); else die($this->getErrorLabel($r));
                    break;
                /*
                 * Just show on comment
                 */
                case 'show':
                    $id = isset($_POST['id']) ? $_POST['id'] : null;
                    $server = isset($_POST['server']) ? json_decode($_POST['server'], true) : null;

                    $cm = $cmForm->getCommentByID($id, $server);
                    $group = $cmForm->getCommentGroupByPath($cm['path']);

                    $this->renderPartial('_sh', array('comment'=>$cm, 'group'=>$group));
                    break;
            }
        }else{
            $comments = $cmForm->allComments();

            $start = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
            $per = isset($_REQUEST['per']) ? $_REQUEST['per'] : 30;

            $comments = $cmForm->createPagination($comments, $per, $start);

            $this->renderPartial('_cms', array('cms'=>$comments));
        }
    }//end actionCommentsCRM function

    /*
        Call this function before any function
    */
    public function beforeAction(){

        App::$config->pageLayouts = 'cmlayouts/column1';

    }//end function

}