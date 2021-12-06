<?php
class SmsApiPlugin extends MantisPlugin
{
    var $whoami;
    var $inputs;
    var $inputPrefix;
    var $smsApi;
    var $userAPI;

    const STATUS_PRZYPISANY = 50;
    const STATUS_NOWY = 10;

    function register() {
        $this->name = 'SmsApi';
        $this->description = 'Plugin to send notifications via SMS api';
        $this->page = 'config_page';
        $this->version = '1.0.0';
        $this->requires = array( 'MantisCore' => '2.0.0' );
        $this->author = 'michal@go2ecommerce.pl';
        $this->contact = '';
        $this->url = 'https://agencja-ecommerce.pl';
    }

    function hooks() {
        return array(
            'EVENT_MENU_MAIN' => 'menu',

            'EVENT_MANAGE_USER_CREATE_FORM' => 'manageUserCreateForm',
            'EVENT_MANAGE_USER_CREATE' => 'saveUser',
            'EVENT_MANAGE_USER_UPDATE_FORM' => 'manageUserUpdateForm',
            'EVENT_MANAGE_USER_UPDATE' => 'saveUser',
            'EVENT_MANAGE_USER_DELETE' => 'deleteUser',
            'EVENT_MANAGE_PROJECT_DELETE' => 'deleteProject',

            'EVENT_REPORT_BUG' => 'reportBug',
            'EVENT_UPDATE_BUG' => 'updateBug'
        );
    }

    function config()
    {
        $cfg = array(
            'smsApiKey'     => ''
        );

        $this->whoami = str_replace('Plugin', '', __CLASS__);
        $this->inputPrefix = $this->whoami . '_';

        $this->fieldsKeysFor = array('user' => array());
        $prefix = array('user' => 'phone_number');

        foreach ($prefix as $artifact => $pfx) {
            $this->fieldsKeysFor[$artifact][$pfx] = $pfx;
        }

        $this->inputsFor = $this->fieldsKeysFor;
        foreach ($this->inputsFor as $artifact => $in) {
            foreach ($in as $t_ty => $t_tv) {
                $this->inputsFor[$artifact][$t_ty] =
                    $this->inputPrefix . $t_ty;
            }
        }

        return $cfg;
    }

    function init() {
        plugin_require_api('core/UserAPI.class.php');
        plugin_require_api('core/SMSApi.class.php');

        $obj = new stdClass();
        $obj->fieldsKeys = $this->fieldsKeysFor['user'];
        $obj->inputs = $this->inputsFor['user'];
        $obj->inputPrefix = $this->inputPrefix;
        $this->userAPI = new SmsApi\UserAPI( $obj );
        $this->smsApi = new SmsApi\SMSApi();
    }

    function manageUserCreateForm( $p_event, $p_user_id = null ) {
        $this->userAPI->smsApiInputs();
    }

    function manageUserUpdateForm( $p_event, $p_user_id = null ) {
        $this->userAPI->smsApiInputs($p_user_id,'edit');
    }

    function saveUser( $p_event, $p_user_id ) {
        $table = plugin_table( 'users' );
        db_param_push();
        $t_query = " SELECT user_id 
                     FROM {$table} WHERE user_id=" . db_param();
        db_query( $t_query, array( $p_user_id ) );

        if( db_affected_rows() == 0 ) {
            $this->userAPI->insertSmsApiUser( $p_user_id );
        } else {
            $this->userAPI->updateSmsApiUser( $p_user_id );
        }

    }

    function deleteUser($p_event, $p_user_id) {
        $t_debug = '/* ' . __METHOD__ . ' */ ';

        $table = plugin_table( 'users' );
        $t_query = " $t_debug DELETE FROM {$table} 
                     WHERE user_id=" . db_param();

        $t_sql_param = array($p_user_id);
        db_query($t_query,$t_sql_param);
    }

    function deleteProject($p_event, $p_project_id) {
        $t_debug = '/* ' . __METHOD__ . ' */ ';

        $table = plugin_table( 'templates' );
        $t_query = " $t_debug DELETE FROM {$table} 
                     WHERE project_id=" . db_param();

        $t_sql_param = array($p_project_id);
        db_query($t_query,$t_sql_param);
    }

    function reportBug($p_event, $p_created_bug) {
        if ($p_created_bug->handler_id !== 0) {
            $this->smsApi->sendSMS($p_created_bug);
        }

    }
    
    function updateBug($p_event, $p_original_bug, $p_updated_bug) {
        if (in_array($p_original_bug->status, array(self::STATUS_NOWY,self::STATUS_PRZYPISANY)) && $p_updated_bug->status === self::STATUS_PRZYPISANY) {
            $this->smsApi->sendSMS($p_updated_bug);
        }
    }

    function schema() {
        $t_schema = array();

        //add users table
        $t_table = plugin_table( 'users' );
        $t_ddl = " id  I   NOTNULL UNSIGNED PRIMARY AUTOINCREMENT,
                 user_id I   UNSIGNED NOTNULL DEFAULT '0',
                 phone_number C(64) NOTNULL DEFAULT ''";

        $t_schema[] = array( 'CreateTableSQL',
            array($t_table , $t_ddl) );

        $t_schema[] = array( 'CreateIndexSQL',
            array( 'idx_user_sms_api',
                $t_table,
                'user_id', array( 'UNIQUE' ) ) );

        //add templates table
        $t_table = plugin_table( 'templates' );
        $t_ddl = " id  I   NOTNULL UNSIGNED PRIMARY AUTOINCREMENT,
                 project_id I UNSIGNED NOTNULL DEFAULT '0',
                 message C(160) NOTNULL DEFAULT ''";

        $t_schema[] = array( 'CreateTableSQL',
            array($t_table , $t_ddl) );

        $t_schema[] = array( 'CreateIndexSQL',
            array( 'idx_project_sms_api',
                $t_table,
                'project_id', array( 'UNIQUE' ) ) );

        return $t_schema;
    }

    function menu() {
        $t_menu[] = array(
            'title' => $this->name,
            'url' => plugin_page( 'smsapi' ),
            'access_level' => ADMINISTRATOR,
            'icon' => 'fa-phone'
        );
        return $t_menu;
    }
}
