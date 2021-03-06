<?php

namespace App\Modules;

use Sura\Libs\Langs;
use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;

class FaveController extends Module{

    /**
     * Добвление юзера в закладки
     */
    public function add($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 70;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['fave'];

            Tools::NoAjaxQuery();
            $fave_id = intval($_POST['fave_id']);
            //Проверяем на факт существования юзера которого добавляем в закладки
            $row = $db->super_query("SELECT `user_id` FROM `users` WHERE user_id = '{$fave_id}'");
            if($row AND $user_id != $fave_id){

                //Проверям на факт существование этого юзера в закладках, если нету то пропускаем
                $db->query("SELECT `user_id` FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
                if(!$db->num_rows()){
                    $db->query("INSERT INTO `fave` SET user_id = '{$user_id}', fave_id = '{$fave_id}', date = NOW()");
                    $db->query("UPDATE `users` SET user_fave_num = user_fave_num+1 WHERE user_id = '{$user_id}'");
                } else
                    echo 'yes_user';
            } else
                echo 'no_user';

            die();
        }
    }

    /**
     * Удаление юзера из закладок
     */
    public function delet($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
        $db = $this->db();
        $logged = Registry::get('logged');
        $user_info = Registry::get('user_info');
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 70;
            $limit_page = ($page-1)*$gcount;
            $metatags['title'] = $lang['fave'];

            Tools::NoAjaxQuery();
            $fave_id = intval($_POST['fave_id']);

            //Проверям на факт существование этого юзера в закладках, если есть то пропускаем
            $row = $db->super_query("SELECT `user_id` FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
            if($row){
                $db->query("DELETE FROM `fave` WHERE user_id = '{$user_id}' AND fave_id = '{$fave_id}'");
                $db->query("UPDATE `users` SET user_fave_num = user_fave_num-1 WHERE user_id = '{$user_id}'");
            } else
                echo 'yes_user';

            die();
        }
    }

    /**
     * Вывод людей которые есть в закладках
     */
    public function index($params){
        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();

        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $act = $_GET['act'];
            $user_id = $user_info['user_id'];

            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 70;
            $limit_page = ($page-1)*$gcount;

            $metatags['title'] = $lang['fave'];

            $mobile_speedbar = 'Закладки';

            //Выводим кол-во людей в закладках
            $user = $db->super_query("SELECT user_fave_num FROM `users` WHERE user_id = '{$user_id}'");

            //Если кто-то есть в заклаках то выводим
            if($user['user_fave_num']){
                $titles = array('человек', 'человека', 'человек');//fave
                $user_speedbar = '<span id="fave_num">'.$user['user_fave_num'].'</span> '.Gramatic::declOfNum($user['user_fave_num'], $titles);

                //Загружаем поиск на странице
                $tpl->load_template('fave_search.tpl');
                $tpl->compile('content');

                //Выводи из базы
                $sql_ = $db->super_query("SELECT tb1.fave_id, tb2.user_search_pref, user_photo, user_last_visit, user_logged_mobile FROM `fave` tb1, `users` tb2 WHERE tb1.user_id = '{$user_id}' AND tb1.fave_id = tb2.user_id ORDER by `date` ASC LIMIT {$limit_page}, {$gcount}", 1);
                $tpl->load_template('fave.tpl');
                $tpl->result['content'] .= '<table class="food_planner" id="fave_users">';

                $config = include __DIR__.'/../data/config.php';

                foreach($sql_ as $row){
                    if($row['user_photo'])
                        $tpl->set('{ava}', $config['home_url'].'uploads/users/'.$row['fave_id'].'/100_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/100_no_ava.png');

                    $tpl->set('{name}', $row['user_search_pref']);
                    $tpl->set('{user-id}', $row['fave_id']);

                    $online = Online($row['user_last_visit'], $row['user_logged_mobile']);
                    $tpl->set('{online}', $online);

                    $tpl->compile('content');
                }
                $tpl->result['content'] .= '</table>';
                navigation($gcount, $user['user_fave_num'], $config['home_url'].'fave/page/');
            } else {
                $user_speedbar = $lang['no_infooo'];
                msgbox('', $lang['no_fave'], 'info_2');
            }
            $tpl->clear();
            $db->free();
        } else {
            $user_speedbar = $lang['no_infooo'];
            msgbox('', $lang['not_logged'], 'info');
        }

        $params['tpl'] = $tpl;
        Page::generate($params);
        return true;
    }
}