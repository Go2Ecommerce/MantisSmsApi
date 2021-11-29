<?php
namespace SmsApi;

class SMSApi {

    function sendSMS($updatedBug) {
        $table = plugin_table('users');
        db_param_push();
        $t_query = " SELECT phone_number
                     FROM {$table} WHERE user_id=" . db_param();
        $t_result_user = db_query( $t_query, array($updatedBug->handler_id) );
        $t_row_user = db_fetch_array( $t_result_user );

        if (db_affected_rows() > 0  && $t_row_user) {
            $table = plugin_table('templates');
            db_param_push();
            $t_query = " SELECT message
                     FROM {$table} WHERE project_id=" . db_param();
            $t_result = db_query( $t_query, array( $updatedBug->project_id ) );
            $t_row = db_fetch_array( $t_result );
            $message = str_replace(array('{bug_id}', '{summary}'), array($updatedBug->id, $updatedBug->summary), $t_row['message']);

            $params = array(
                'to' => $t_row_user['phone_number'],
                'from' => 'Test',
                'message' => $message,
                'format' => 'json',
                'encoding' => 'utf-8'
            );

            $this->sms_send($params, plugin_config_get( 'smsApiKey' ));
        }

    }

    function sms_send($params, $token, $backup = false) {
        static $content;

        if ($backup == true) {
            $url = 'https://api2.smsapi.pl/sms.do';
        } else {
            $url = 'https://api.smsapi.pl/sms.do';
        }

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $params);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $token"
        ));

        $content = curl_exec($c);
        $http_status = curl_getinfo($c, CURLINFO_HTTP_CODE);

        if ($http_status != 200 && $backup == false) {
            $backup = true;
            $this->sms_send($params, $token, $backup);
        }

        curl_close($c);
        return $content;
    }
}
