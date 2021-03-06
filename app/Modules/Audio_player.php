<?php

namespace App\Modules;

use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Tools;
use Sura\Libs\Validation;

class Audio_player extends Module{

    /**
     * Загрузка плей листа
     */
    public function index($params)
    {
        $tpl = Registry::get('tpl');

//        include __DIR__.'/../lang/'.$checkLang.'/site.lng';
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        Tools::NoAjaxQuery();

        if($logged){

            $user_id = $user_info['user_id'];

            //Если поиск
            $query = Validation::textFilter(Validation::ajax_utf8(Validation::strip_data(urldecode($_POST['query']))));
            $query = strtr($query, array(' ' => '%')); //Замеянем пробелы на проценты чтоб тоиск был точнее
            $doload = intval($_POST['doload']);

            $get_user_id = intval($_POST['get_user_id']);
            if($get_user_id == $user_id OR !$get_user_id) $get_user_id = $user_id;

            if(isset($query) AND !empty($query)){

                $sql_query = "WHERE MATCH (name, artist) AGAINST ('%{$query}%') OR artist LIKE '%{$query}%' OR name LIKE '%{$query}%'";
                $search = true;

            } else {

                $sql_query = "WHERE auser_id = '{$get_user_id}'";
                $search = false;

            }

            //Выводим из БД
            $limit_select = 20;
            if($_POST['page_cnt'] > 0) $page_cnt = intval($_POST['page_cnt']) * $limit_select;
            else $page_cnt = 0;

            $sql_ = $db->super_query("SELECT aid, url, artist, name FROM `audio` {$sql_query} ORDER by `adate` DESC LIMIT {$page_cnt}, {$limit_select}", 1);

            //Если есть отвеот из БД
            if($sql_){

                $jid = $page_cnt;

                $tpl->load_template('audio_player/track.tpl');
                foreach($sql_ as $row){

                    $jid++;
                    $tpl->set('{jid}', $jid);

                    $tpl->set('{aid}', $row['aid']);
                    $tpl->set('{url}', $row['url']);
                    $tpl->set('{artist}', stripslashes($row['artist']));
                    $tpl->set('{name}', stripslashes($row['name']));

                    if($get_user_id == $user_id AND !$search){

                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                        $tpl->set_block("'\\[not-owner\\](.*?)\\[/not-owner\\]'si","");

                    } else {

                        $tpl->set('[not-owner]', '');
                        $tpl->set('[/not-owner]', '');
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                    }

                    $tpl->compile('audios');

                }

                if(!$page_cnt AND !$doload){

                    $tpl->load_template('audio_player/player.tpl');

                    $tpl->set('{audios}', $tpl->result['audios']);
                    $tpl->set('{user-id}', $user_id);

                    if($jid == $limit_select) $tpl->set('{jQbut}', '');
                    else $tpl->set('{jQbut}', 'no_display');

                    $tpl->compile('content');

                } else
                    $tpl->result['content'] = $tpl->result['audios'];

            } else
                if($doload AND !$page_cnt){

                    $query = str_replace('%', ' ', $query);

                    $tpl->result['content'] = '<div class="info_center" style="padding-top:145px;padding-bottom:125px">По запросу <b>'.$query.'</b> не найдено ни одной аудиозаписи.</div>';

                } else{
                    $config = include __DIR__.'/../data/config.php';
                    if(!$page_cnt)
                        $tpl->result['content'] = '<div class="info_center" style="padding-top:145px;padding-bottom:125px"><center><img src="/templates/'.$config['temp'].'/images/snone.png" style="marign-bottom:60px;margin-top:-80px" /></center><div>Здесь Вы можете хранить Ваши аудиозаписи.<br />Для того, чтобы загрузить Вашу первую аудиозапись, <a href="/audio17" onClick="audio.addBox(1); return false;">нажмите здесь</a>.</div></div>';
                }


            Tools::AjaxTpl($tpl);


            $tpl->clear();
            $db->free();

        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;

    }
}
