<?php
class VbeeApiClass {
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    private $dataApi;

    /**
     * Start up
     */
    public function __construct(){
        $this->callback();
        $this->options = get_option('vbee-options');
        $voices = [];
        if (isset($this->options['id1'])) $voices[] = [
            "id" => $this->options['id1'] ? $this->options['id1'] : "hn_female_ngochuyen_full_48k-fhg",
            "rate" => $this->options['rate'] ? $this->options['rate'] : 1
        ];

        if (isset($this->options['id2'])) $voices[] = [
            "id" => $this->options['id2'] ? $this->options['id2'] : "hue_female_huonggiang_full_48k-fhg",
            "rate" => $this->options['rate'] ? $this->options['rate'] : 1
        ];

        if (isset($this->options['id3'])) $voices[] = [
            "id" => $this->options['id3'] ? $this->options['id3'] : "sg_male_minhhoang_full_48k-fhg",
            "rate" => $this->options['rate'] ? $this->options['rate'] : 1
        ];

        if (!isset($this->options['id1']) && !isset($this->options['id2']) && !isset($this->options['id3'])) {
            $voices[] = [
                "id" => "hn_female_ngochuyen_full_48k-fhg",
                "rate" => 1
            ];
        }

        // $this->dataApi = array(
        //     "content" => "",
        //     "appId" => $this->options['appid'] ? $this->options['appid'] : 'c1c4a3f82f182ba5099edb1c',
        //     "token" => $this->options['token'] ? $this->options['token'] : '',
        //     "httpCallback" => "",
        //     "sampleRate" => "48000",
        //     "bitRate" => $this->options['bitrate'] ? $this->options['bitrate'] : "128000",
        //     "audioType" => $this->options['audiotype'] ? $this->options['audiotype'] : "mp3",
        //     "timeBreakAfterTitle" => $this->options['timebreakaftertitle'] ? $this->options['timebreakaftertitle'] : "0.5",
        //     "timeBreakAfterSapo" => $this->options['timebreakaftersapo'] ? $this->options['timebreakaftersapo'] : "0.5",
        //     "timeBreakOfParagraph" => $this->options['timebreakofparagraph'] ? $this->options['timebreakofparagraph'] : "0.5",
        //     "voices" => $voices
        // );

         $this->dataApi = array(
            "content" => "",
            "app_id" => $this->options['appid'] ? $this->options['appid'] : 'c1c4a3f82f182ba5099edb1c',
            "token" => $this->options['token'] ? $this->options['token'] : '',
            "callback_url" => "",
            "bitrate" => $this->options['bitrate'] ? $this->options['bitrate'] : "128",
            "audio_type" => $this->options['audiotype'] ? $this->options['audiotype'] : "mp3",
            "voice_code" => $voices[0]["id"],
            "speed_rate" => $this->options['rate'] ? $this->options['rate'] : "1.0",
            "voices" => $voices
        );

        /*
         "app_id": "55e0053d-f86f-4c2b-b791-b1ba6d59a868",
    "callback_url": "https://mydomain/callback",
    "input_text": "Chào mừng đén với website của chúng tôi! Đây là trang web cung cấp một giải pháp văn bản thành giọng nói, trên cơ sở, nó hỗ trợ các doanh nghiệp xây dựng các hệ thống trung tâm cuộc gọi tự động, hệ thống thông báo công khai, trợ lý ảo, tin tức âm thanh, podcast, sách âm thanh và tường thuật phim.",
    "voice_code": "hn_female_ngochuyen_full_48k-fhg",
    "audio_type":"mp3",
    "bitrate": 128,
    "speed_rate": "1.0"*/

    }

    // call api
    public function call($id, $content){
        $this->dataApi['input_text'] = $content;
        $this->dataApi['callback_url'] = get_the_permalink($id);
        $urlApi = $this->options['address'];
        $this->dataApi["address"] = $urlApi;
        
        // $this->dataApi["jdataPost"] = $jdataPost;
        foreach ($this->dataApi["voices"] as $key => $voice){
            $jdataPost = array(
                "app_id" => $this->dataApi["app_id"],
                "input_text" => $content,
                "audio_type"=> "mp3",
                "speed_rate"=> $this->dataApi["speed_rate"],
                "callback_url" => $this->dataApi["callback_url"],
                "bitrate" =>  $this->dataApi["bitrate"],
                "audio_type" =>  $this->dataApi["audio_type"],
                "voice_code" =>  $voice["id"],
            );
            $args = array(
                'body'        => json_encode($jdataPost),
                'timeout'     => '0',
                'redirection' => '5',
                'httpversion' => '2',
                'blocking'    => true,
                'headers'     => array(
                    "Content-Type" => "application/json",
                    "Authorization"=>"Bearer ".$this->dataApi["token"]
                ),
                'cookies'     => array(),
            );

            $response = wp_remote_post("https://hub-bidv.vbeecore.com/api/v1/tts", $args);
        }

        
        // $args['response'] = $response;
        // $response = wp_remote_post("https://hub-bidv.vbeecore.com/api/tts/index", $args);
        return array(
            'res' => $response,
            'linkCallback' => $this->dataApi['callback_url'] 
        );
    }

    // callback and save database
    public function callback(){
        if(is_single()){
            $post_id = get_the_ID();
            $postdata = file_get_contents('php://input');
            $postdatajson = json_decode($postdata, true);
            if(isset($postdatajson['status'])){
                if($postdatajson['status'] == 'SUCCESS') {
                    if(isset($postdatajson['audio_link'])){
                        if ( ! function_exists( 'download_url' ) ) {
                            require_once ABSPATH . 'wp-admin/includes/file.php';
                        }
                        $link  = $postdatajson['audio_link'];
                        $voice  = $postdatajson['voice_code'];
                        $tmp_file = download_url( $link );
                        $upload_dir = wp_upload_dir();
                        $dir_path = $upload_dir['basedir'] . '/' . VBEE_FOLDER_AUDIO;
                        if (!file_exists($dir_path)) {
                            wp_mkdir_p($dir_path);
                        }
                        $filepath = $dir_path .'/' . $post_id . '--' . $voice . '.mp3';
                        copy( $tmp_file, $filepath );
                        @unlink( $tmp_file );

                        $audio = get_post_meta( $post_id, 'audio', false);
                        update_post_meta( $post_id, 'check_audio', 1, '');
                        if($audio != ''){
                            update_post_meta( $post_id, 'audio', $link, '');
                        } else {
                            add_post_meta( $post_id, 'audio', $link );
                        }
                    }
                }
            } else {
                return false;
                exit();
            }
        }
    }
}