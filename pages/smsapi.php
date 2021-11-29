<?php
layout_page_header( plugin_lang_get( 'title' ) );
layout_page_begin();
$t_plugin_path = config_get_global( 'plugin_path' );
require_once( $t_plugin_path . '/SmsApi/core/TemplateAPI.class.php' );

$templateApi = new \SmsApi\TemplateApi();

# The current project
$g_project_id = helper_get_current_project();
if( $g_project_id == ALL_PROJECTS ) {
    plugin_error( 'ERROR_ALL_PROJECT', ERROR );
}

$message = $templateApi->getMessageByProject($g_project_id);

if ($_POST) {
    if (empty($message)) {
        $templateApi->insertMessage($_POST['project_id'], $_POST['message']);
    } else {
        $templateApi->updateMessage($_POST['project_id'], $_POST['message']);
    }
    $t_redirect_to = config_get_global( 'default_home_page' );
    $message = $_POST['message'];
    html_operation_successful( $t_redirect_to );
}
$t_project_title = sprintf( lang_get( 'config_project' ), string_display_line( project_get_name( $g_project_id ) ) );
print_r($t_project_title);

$t_form_encoding = '';

?>

    <div class="col-md-12 col-xs-12">
        <form id="sms_api_form"
              method="post" <?php echo $t_form_encoding; ?>
              action="plugin.php?page=SmsApi/smsapi">
            <input type="hidden" name="project_id" value="<?php echo $g_project_id ?>" />
            <div class="widget-box widget-color-blue2">
                <div class="widget-header widget-header-small">
                    <h4 class="widget-title lighter">
                        <i class="ace-icon fa fa-edit"></i>
                        <?php echo plugin_lang_get( 'configuration_title' ) ?>
                    </h4>
                </div>
                <div class="widget-body dz-clickable">
                    <div class="widget-main no-padding">
                        <div class="table-responsive">
                            <table class="table table-bordered table-condensed">
                                    <tr>
                                        <th class="category">
                                            <label for="message"><?php echo plugin_lang_get('message') ?></label>
                                        </th>
                                        <td>
                                            <textarea class="form-control" <?php echo helper_get_tab_index() ?> id="message" name="message" cols="80" rows="10"><?php echo string_textarea( $message ) ?></textarea>
                                        </td>
                                    </tr>
                            </table>
                        </div>
                    </div>
                    <div class="widget-toolbox padding-8 clearfix">
                        <input <?php echo helper_get_tab_index() ?> type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'submit_report_button' ) ?>" />
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php
layout_page_end();
