<?php

namespace App\Modules;

use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;

class Public_audioController extends Module{

    public function upload_box($params){
        $tpl = Registry::get('tpl');
        $config = include __DIR__.'/../data/config.php';
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];

            $pid = intval($_GET['pid']);
            echo <<<HTML
                    <div class="audio_upload_cont">
                    <div class="upload_limits_title" dir="auto">Ограничения</div>
                    <ul class="upload_limits_list" dir="auto">
                    <li><span>Аудиофайл не должен превышать 200 Мб и должен быть в формате MP3.</span></li>
                    <li><span>Аудиофайл не должен нарушать авторские права.</span></li>
                    </ul>
                    <div class="audio_upload_but_wrap">
                    <div id="audio_choose_wrap">
                    <center>
                    <div class="button_div fl_l">
                    <button onClick="this.nextSibling.click();">Выбрать файл</button><input type="file" accept="audio/mp3" multiple="true" onChange="audio.onFile(this,'{$pid}');" style="display:none;" id="audio_upload_inp">
                    </div>
                    </center>
                    </div>
                    <div class="audio_upload_progress no_display">
                    <div class="audio_progress_text">
                    <div class="str" id="progress_str">0%</div>
                    </div>
                    <div class="audio_upload_pr_line">
                    <div class="audio_progress_text">
                    <div class="str">0%</div>
                    </div>
                    </div>
                    </div>
                    </div>
                    <div class="audio_upload_drop">
                    <div class="audio_upload_drop_wrap">
                    <div class="audio_upload_drop_text bsbb">Отпустите файлы для начала загрузки</div>
                    </div>
                    <div class="audio_drop_wrap"></div>
                    </div>
                    </div>
                    HTML;
            die();
        }
    }

    public function upload($params){
        $tpl = Registry::get('tpl');
        $config = include __DIR__.'/../data/config.php';
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];

            $pid = intval($_GET['pid']);
            $info = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$pid}'");


            if(stripos($info['admin'], "u{$user_info['user_id']}|") !== false){

                include __DIR__.'/../../vendor/james-heinrich/getid3/getid3.php';
                $getID3 = new \getID3;


                $file_tmp = $_FILES['file']['tmp_name'];
                $file_name = Gramatic::totranslit($_FILES['file']['name']);
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $file_rename = substr(md5($server_time+rand(1,100000)), 0, 15);
                $file_size = $_FILES['file']['size'];
                $tmp = explode('.', $file_name);
                $file_extension = end($tmp);
                $type = strtolower($file_extension);
                if($type == 'mp3' AND $config['audio_mod_add'] == 'yes' AND $file_size < 10000000){
                    $res_type = '.'.$type;
                    if(move_uploaded_file($file_tmp, ROOT_DIR.'/uploads/audio_tmp/'.$file_rename.'.mp3')){


                        $res = $getID3->analyze(ROOT_DIR.'/uploads/audio_tmp/'.$file_rename.'.mp3');

                        if(!$res['error'] && $res['playtime_seconds']){

                            if($res['tags']['id3v2']){
                                $artist = Validation::textFilter($res['tags']['id3v2']['artist'][0]);
                                $name = Validation::textFilter($res['tags']['id3v2']['title'][0]);
                            } else if($res['tags']['id3v1']){
                                $artist = Validation::textFilter($res['tags']['id3v1']['artist'][0]);
                                $name = Validation::textFilter($res['tags']['id3v1']['title'][0]);
                            }

                            $time_sec = round(str_replace(',','.',$res['playtime_seconds']));


                            $lnk = '/uploads/audio_tmp/'.$file_rename.'.mp3';
                            $db->query("INSERT INTO `audio` SET public = '1', duration = '{$time_sec}', filename = '{$file_rename}{$res_type}', oid = '{$pid}', url = '{$lnk}', artist = '{$artist}', title = '{$name}',  date = '{$server_time}'");
                            $db->query("UPDATE `communities` SET audio_num = audio_num + 1 WHERE id = '{$pid}'");

                        }


                        //@unlink(ROOT_DIR.'/uploads/audio_tmp/'.$file_rename.'.mp3');
                        echo json_encode(array('status' => 1));

                    } else json_encode(array('status' => 0));
                } else json_encode(array('status' => 0));

            } else json_encode(array('status' => 0));

            die();
        }
    }

    public function add($params){
        $tpl = Registry::get('tpl');
        $config = include __DIR__.'/../data/config.php';
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;
            $act = $_REQUEST['act'];

            if(!$logged) die();
            $id = intval($_POST['id']);
            $pid = intval($_POST['pid']);
            $info = $db->super_query("SELECT admin FROM `communities` WHERE id = '{$pid}'");
            $check = $db->super_query("SELECT url, artist, title, duration FROM `audio` WHERE id = '{$id}'");
            if(stripos($info['admin'], "u{$user_info['user_id']}|") !== false && $check){
                $server_time = intval($_SERVER['REQUEST_TIME']);
                $db->query("INSERT INTO `audio` SET original = '{$id}', duration = '{$check['duration']}',oid = '{$pid}', public = '1', url = '{$db->safesql($check['url'])}', artist = '{$db->safesql($check['artist'])}', title = '{$db->safesql($check['title'])}', date = '{$server_time}'");
                $db->query("UPDATE `communities` SET audio_num = audio_num + 1 WHERE id = '{$pid}'");
                $db->query("UPDATE `audio` SET add_count = add_count + 1 WHERE id = '{$id}'");
            }
            die();
        }
    }

    public function index($params){
        $tpl = Registry::get('tpl');

        $config = include __DIR__.'/../data/config.php';

        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        if($logged){
            $count = 40;
            $page = intval($_REQUEST['page']);
            $offset = $count * $page;

            $act = $_REQUEST['act'];

            $audios = array();
            $pid = intval($_GET['pid']);
            $metatags['title'] = 'Аудиозаписи сообщества';

            $plname = 'publicaudios'.$pid;

            $info = $db->super_query("SELECT admin, title, audio_num,ulist,ctype FROM `communities` WHERE id = '{$pid}'");


            if($info['ctype'] == 2 && stripos($info['ulist'], "|{$user_info['user_id']}|") === false) msgbox('', '<br /><br />Ошибка доступа.<br /><br /><br />', 'info_2');
            else {


                $sql_ = $db->super_query("SELECT id, oid, url, artist, title, duration, text FROM `audio` WHERE oid = '{$pid}' and public = '1' ORDER by `id` DESC LIMIT {$offset}, {$count}", 1);

                foreach($sql_ as $row){
                    $stime = gmdate("i:s", $row['duration']);
                    if(!$row['artist']) $row['artist'] = 'Неизвестный исполнитель';
                    if(!$row['title']) $row['title'] = 'Без названия';


                    if($row['text']) $is_text = 'text_avilable';
                    else $is_text = '';

                    $audios['a_'.$row['id']] = array($row['oid'], $row['id'], $row['url'], $row['artist'], $row['title'], $row['duration'], $stime, $plname, 'user_audios', ($row['text']) ? 1 : 0);



                    $res = <<<HTML
                    <div class="audio" id="audio_{$row['id']}_{$row['oid']}_{$plname}" onclick="playNewAudio('{$row['id']}_{$row['oid']}_{$plname}', event);">
                    <div class="audio_cont">
                    <div class="play_btn icon-play-4"></div>
                    <div class="name"><span id="artist" onClick="Page.Go('/?go=search&query=&type=5&q={$row['artist']}')">{$row['artist']}</span> – <span id="name" class="{$is_text}" onClick="audio_player.get_text('{$row['id']}_{$row['oid']}_{$plname}', this);">{$row['title']}</span></div>
                    <div class="fl_r">
                    <div class="time" id="audio_time_{$row['id']}_{$row['oid']}_{$plname}">{$stime}</div>
                    <div class="tools">
                    [tools]<li class="icon-pencil-7" onclick="audio.edit_box('{$row['id']}_{$row['oid']}_{$plname}')" id="edit_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Редактировать аудиозапись', shift:[0,7,0]});"></li>
                    <li class="icon-cancel-3" onclick="audio.delete_box('{$row['id']}_{$row['oid']}_{$plname}', {$row['oid']})" id="del_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Удалить аудиозапись', shift:[0,5,0]});"></li>[/tools]
                    [add]<li class="icon-plus-6" onclick="audio.add('{$row['id']}_{$row['oid']}_{$plname}')" id="add_tt_{$row['id']}_{$row['oid']}_{$plname}" onmouseover="showTooltip(this, {text: 'Добавить аудиозапись', shift:[0,7,0]});"></li>[/add]
                    <div class="clear"></div>
                    </div>
                    </div>
                    <input type="hidden" value="{$row['url']},{$row['duration']},user_audios" id="audio_url_{$row['id']}_{$row['oid']}_{$plname}"/>
                    <div class="clear"></div>
                    </div>
                    <div id="audio_text_res"></div>
                    </div>
                    HTML;
                    if(stripos($info['admin'], "u{$user_info['user_id']}|") !== false){
                        $res = str_replace(array('[tools]','[/tools]'), '', $res);
                        $res = preg_replace("'\\[add\\](.*?)\\[/add\\]'si", "", $res);
                    } else {
                        $res = str_replace(array('[add]','[/add]'), '', $res);
                        $res = preg_replace("'\\[tools\\](.*?)\\[/tools\\]'si", "", $res);
                    }
                    $audios_res .= $res;

                }

                $pname = 'Сейчас играют аудиозаписи '.$info['title'].' | '.$info['audio_num'].' '.declOfNum($info['audio_num'], array('аудиозапись','аудиозаписи','аудиозаписей'));



                $audio_json = array('id' => 'user_audios', 'uname' => $info['title'], 'usex' => $user['user_sex'], 'pname' => $pname, 'playList' => $audios);

                $title = '<div class="audio_page_title">В сообществе '.$info['audio_num'].' '.declOfNum($info['audio_num'], array('аудиозапись','аудиозаписи','аудиозаписей')).'</div>';


                if($_POST['doload']){

                    echo json_encode(array('result' => $audios_res, 'playList' => $audios, 'pname' => $pname,'title' => $title,'plname' => $plname, 'but' => ($info['audio_num'] > $count+$offset) ? '<div class="audioLoadBut" style="margin-top:10px" onClick="audio.loadMore()" id="audio_more_but">Показать больше</div>' : ''));
                    die();
                }



                $tpl->load_template('audio/main.html');

                $tpl->set('{my_music-active}', 'no_display');
                $tpl->set('{feed-active}', 'no_display');
                $tpl->set('{recommendations-active}', 'no_display');
                $tpl->set('{popular-active}', 'no_display');

                $tpl->set('{public_audios}', '');



                $tpl->set('[public]', '');
                $tpl->set('[/public]', '');




                if(stripos($info['admin'], "u{$user_info['user_id']}|") !== false){
                    $tpl->set('[owner]', '');
                    $tpl->set('[/owner]', '');
                    $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");
                } else {
                    $tpl->set('[not-owner]', '');
                    $tpl->set('[/not-owner]', '');
                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");
                }
                $tpl->set_block("'\\[is_user\\](.*?)\\[/is_user\\]'si","");
                $tpl->set_block("'\\[friends_block\\](.*?)\\[/friends_block\\]'si","");

                $tpl->set('{public_audios-active}', 'active');

                $tpl->set('{plname}', $plname);

                $tpl->set('{uid}', $pid);
                $tpl->set('{title}', $title);
                $tpl->set('{audios_res}', $audios_res);

                $tpl->set('{user_name}', $info['title']);


                $tpl->set('{init}', json_encode($audio_json));
                $tpl->compile('content');

            }

            $tpl->clear();
            $db->free();

        } else {
            include __DIR__.'/../lang/'.$checkLang.'/site.lng';
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}