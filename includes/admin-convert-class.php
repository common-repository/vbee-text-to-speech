<?php
class VbeeAdminConvert {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $dataApi;

    /**
     * Start up
     */
    public function __construct(){
        add_filter('post_row_actions', array($this, 'add_action_post'), 10, 2);
        add_filter('bulk_actions-edit-post', array($this, 'add_bulk_action_post'), 10, 2);
        add_action('admin_footer', array($this, 'vbee_custom_content'));
        add_action('manage_posts_columns', array($this, 'vbee_custom_colums' ));
        add_action('manage_posts_custom_column', array($this, 'columns_vbee_data'), 10, 2);
    }


    // add bulk actions
    public function add_bulk_action_post($actions){
        $actions['convert'] = __("Tạo audio", "vbee");
        $actions['del_audio'] = __("Xóa audio", "vbee");
        return $actions;
    }

    // add row action
    public function add_action_post($actions, $post){
        $actions['become'] = '<a href="#" data-id="'.$post->ID.'" class="open-my-dialog bg-add">'.__("Tạo audio", "vbee").'</a>';
        return $actions;
    }

    // custom content
    public function vbee_custom_content(){
        echo '<div class="vbee-load"><img src="'.VBEE_PLUGIN_URL.'assets/images/vbee-load.gif"></div>';
        echo '<div id="my-dialog" style="width:320; display:none;">
                <input type="hidden" name="post_id" id="post_action" value="">
                <p>'.__("Bạn chắc chắn muốn xóa file audio của bài này?", "vbee").'</p>
                <div class="d-action">
                    <a class="action_canel">'.__("Hủy Bỏ", "vbee").'</a>
                    <a class="confirm_delete">'.__("Xóa audio", "vbee").'</a>
                </div>
             </div>';
    }

    // add colum admin wordpress
    public function vbee_custom_colums($columns){
        $columns['audio'] = 'Vbee Audio';
        return $columns;
    }

    // check isset audio
    public static function vbee_check_isset_audio($audio, $voice = ''){
        $upload_dir = wp_upload_dir();
        if (empty($voice)) {
            $option = get_option('vbee-options');
            $voice = '';
            if (isset($option['id1'])) {
                $voice = $option['id1'];
            } elseif (isset($option['id2'])) {
                $voice = $option['id2'];
            } elseif (isset($option['id3'])) {
                $voice = $option['id3'];
            }elseif (isset($option['id4'])) {
                $voice = $option['id4'];
            }elseif (isset($option['id5'])) {
                $voice = $option['id5'];
            }elseif (isset($option['id6'])) {
                $voice = $option['id6'];
            }elseif (isset($option['id7'])) {
                $voice = $option['id7'];
            }
        }
        $path = $upload_dir['basedir'] . '/' . VBEE_FOLDER_AUDIO . '/' . $audio . '--' . $voice . '.mp3';
        if(file_exists($path)){
            return $upload_dir['baseurl'] . '/' . VBEE_FOLDER_AUDIO . '/' . $audio . '--' . $voice . '.mp3';
        } else {
            return null;
        }
    }

    // show data colum
    public function columns_vbee_data($column, $post_id){
        if ($column === 'audio') {
            $check_isset = $this->vbee_check_isset_audio($post_id);
            $check_adio = get_post_meta( $post_id, 'check_audio', true);
            echo '<div class="action-vbee-audio" id="vbee-'.$post_id.'">';
                if($check_isset){
                    echo '<a href="' . esc_attr($check_isset) . '" tag class="test-audio" target="_blank">'.__("Nghe thử", "vbee").'</a>'
                            .'<a class="del-audio action_delete" data-id="' . esc_attr($post_id) . '">'.__("Xóa audio", "vbee").'</a>';
                } else {
                    if($check_adio == 2){
                        echo '<a class="inprocess-audio">'.__("Đang tạo audio", "vbee").'</a>';
                    } else if($check_adio == 3) {
                        echo '<a class="none-audio" style="background: #000;">'.__("Convert audio lỗi", "vbee").'</a>';
                    } else {
                        echo '<a class="none-audio">'.__("Chưa có audio", "vbee").'</a>';
                    }
                }
            echo '</div>';
        }
    }
}

if( is_admin() ) $vbee_admin_convert = new VbeeAdminConvert();