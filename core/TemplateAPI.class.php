<?php
namespace SmsApi;

class TemplateApi {

    function getMessageByProject($g_project_id = null) {
        $table = plugin_table( 'templates' );

        $t_query = " SELECT * FROM {$table} 
                             WHERE project_id=" . db_param();

        $t_sql_param = array( $g_project_id );
        $t_result = db_query( $t_query, $t_sql_param);

        if( db_affected_rows() == 0 ) {
            return '';
        } else {
            $t_row = db_fetch_array( $t_result );
            return $t_row['message'];
        }
    }

    function insertMessage($g_project_id, $message) {
        $table = plugin_table( 'templates' );

        $fields = implode(',',array('message'));

        $t_db_param = array(db_param());
        $t_sql_param = array($g_project_id);

        $t_db_param[] = db_param();
        $t_sql_param[] = $message;
        $t_db_param = implode(',',$t_db_param);

        $t_query = " INSERT INTO {$table}
                     (project_id,{$fields})
                     VALUES( {$t_db_param} )";
        db_query($t_query, $t_sql_param);
    }

    function updateMessage($g_project_id, $message) {
        $table = plugin_table( 'templates' );

        $t_db_param[] = 'message' . '=' . db_param();
        $t_sql_param[] = $message;
        $t_sql_param[] = $g_project_id;

        $t_db_param = implode(',',$t_db_param);

        $t_query = " UPDATE {$table}
                     SET {$t_db_param} 
                     WHERE project_id = " . db_param();

        db_query($t_query, $t_sql_param);
    }

}