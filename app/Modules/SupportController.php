<?php

namespace App\Modules;

use Sura\Classes\Db;
use Sura\Libs\Langs;
use Sura\Libs\Page;
use Sura\Libs\Registry;
use Sura\Libs\Tools;
use Sura\Libs\Gramatic;
use Sura\Libs\Validation;

use Sura\Modules\Module;

class SupportController extends Module{

    function __construct() {
//        parent::__construct();
    }

    public function new($params){
        $tpl = Registry::get('tpl');
        $lang = langs::get_langs();
//        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
//            $act = $_GET['act'];
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['support_title'];
//            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
//            $gcount = 20;
//            $limit_page = ($page-1)*$gcount;
//
//            $mobile_speedbar = 'Новый вопрос';

            $tpl->load_template('support/new.tpl');
            $tpl->set('{uid}', $user_id);
            $tpl->compile('content');

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    public function send($params){
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
            $metatags['title'] = $lang['support_title'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();
            $title = Validation::ajax_utf8(Validation::textFilter($_POST['title']), false, true);
            $question = Validation::ajax_utf8(Validation::textFilter($_POST['question']));
            $server_time = intval($_SERVER['REQUEST_TIME']);
            $limitTime = $server_time-3600;
            $rowLast = $db->super_query("SELECT COUNT(*) AS cnt FROM `support` WHERE сdate > '{$limitTime}'");
            if(!$rowLast['cnt'] AND isset($title) AND !empty($title) AND isset($question) AND !empty($question) AND $user_info['user_group'] != 4){
                $question = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<!--link:$1--><a href="$1" target="_blank">$1</a><!--/link-->', $question);
                $db->query("INSERT INTO `support` SET title = '{$title}', question = '{$question}', suser_id = '{$user_id}', sfor_user_id = '{$user_id}', sdate = '{$server_time}', сdate = '{$server_time}'");
                $dbid = $db->insert_id();
                $row = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");
                $tpl->load_template('support/show.tpl');
                $tpl->set('{title}', stripslashes($title));
                $tpl->set('{question}', stripslashes($question));
                $tpl->set('{qid}', $dbid);

                $date = megaDate($server_time);
                $tpl->set('{date}', $date);
                $tpl->set('{status}', 'Вопрос ожидает обработки.');
                $tpl->set('{name}', $row['user_search_pref']);
                $tpl->set('{uid}', $user_id);
                if($row['user_photo'])
                    $tpl->set('{ava}', '/uploads/users/'.$user_id.'/50_'.$row['user_photo']);
                else
                    $tpl->set('{ava}', '/images/no_ava_50.png');
                $tpl->set('{answers}', '');
                $tpl->compile('content');
                Tools::AjaxTpl($tpl);

                echo 'r|x'.$dbid;
            } else
                echo 'limit';

            die();
        }
    }

    public function delet($params){
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
            $metatags['title'] = $lang['support_title'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();
            $qid = intval($_POST['qid']);
            $row = $db->super_query("SELECT suser_id FROM `support` WHERE id = '{$qid}'");
            if($row['suser_id'] == $user_id OR $user_info['user_group'] == 4){
                $db->query("DELETE FROM `support` WHERE id = '{$qid}'");
                $db->query("DELETE FROM `support_answers` WHERE qid = '{$qid}'");
            }
            die();
        }
    }

    public function delet_answer($params){
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
            $metatags['title'] = $lang['support_title'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();
            $id = intval($_POST['id']);
            $row = $db->super_query("SELECT auser_id FROM `support_answers` WHERE id = '{$id}'");
            if($row['auser_id'] == $user_id OR $user_info['user_group'] == 4)
                $db->query("DELETE FROM `support_answers` WHERE id = '{$id}'");

            die();
        }
    }

    public function close($params){
        $lang = langs::get_langs();
        $db = $this->db();
        $user_info = $this->user_info();
        $logged = $this->logged();
        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();
        if($logged){
            $act = $_GET['act'];
//            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['support_title'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();
            $qid = intval($_POST['qid']);
            if($user_info['user_group'] == 4){
                $row = $db->super_query("SELECT COUNT(*) AS cnt FROM `support` WHERE id = '{$qid}'");
                if($row['cnt'])
                    $db->query("UPDATE `support` SET sfor_user_id = 0 WHERE id = '{$qid}'");
            }
            die();
        }
    }

    public function answer($params){
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
            $metatags['title'] = $lang['support_title'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            Tools::NoAjaxQuery();
            $qid = intval($_POST['qid']);
            $answer = Validation::ajax_utf8(Validation::textFilter($_POST['answer']));
            $check = $db->super_query("SELECT suser_id FROM `support` WHERE id = '{$qid}'");
            if($check['suser_id'] == $user_id OR $user_info['user_group'] == 4 AND isset($answer) AND !empty($answer)){
                if($user_info['user_group'] == 4){
                    $auser_id = 0;
                    $db->query("UPDATE `users` SET user_support = user_support+1 WHERE user_id = '{$check['suser_id']}'");
                } else
                    $auser_id = $user_id;

                $answer = preg_replace('`(http(?:s)?://\w+[^\s\[\]\<]+)`i', '<!--link:$1--><a href="$1" target="_blank">$1</a><!--/link-->', $answer);

                $server_time = intval($_SERVER['REQUEST_TIME']);

                $db->query("INSERT INTO `support_answers` SET qid = '{$qid}', auser_id = '{$auser_id}', adate = '{$server_time}', answer = '{$answer}'");
                $db->query("UPDATE `support` SET sfor_user_id = '{$auser_id}', sdate = '{$server_time}' WHERE id = '{$qid}'");

                $row = $db->super_query("SELECT user_search_pref, user_photo FROM `users` WHERE user_id = '{$user_id}'");

                $tpl->load_template('support/answer.tpl');
                if(!$auser_id){
                    $tpl->set('{name}', 'Агент поддержки');
                    $tpl->set('{ava}', '/images/support.png');
                    $tpl->set_block("'\\[no-agent\\](.*?)\\[/no-agent\\]'si","");
                } else {
                    $tpl->set('{name}', $row['user_search_pref']);
                    if($row['user_photo'])
                        $tpl->set('{ava}', '/uploads/users/'.$user_id.'/50_'.$row['user_photo']);
                    else
                        $tpl->set('{ava}', '/images/no_ava_50.png');

                    $tpl->set('[no-agent]', '');
                    $tpl->set('[/no-agent]', '');
                }

                if($auser_id == $user_id OR $user_info['user_group'] == 4){
                    $tpl->set('[owner]', '');
                    $tpl->set('[/owner]', '');
                } else
                    $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                $tpl->set('{uid}', $user_id);
                $tpl->set('{answer}', stripslashes($answer));

                $date = megaDate($server_time);
                $tpl->set('{date}', $date);
                $tpl->compile('content');
                Tools::AjaxTpl($tpl);

                $params['tpl'] = $tpl;
                Page::generate($params);
                return true;
            }
            die();
        }
    }

    public function show($params){
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
            $metatags['title'] = $lang['support_title'];
            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $qid = intval($_GET['qid']);

            $mobile_speedbar = 'Просмотр вопроса';

            if($user_info['user_group'] == 4)
                $sql_where = "";
            else
                $sql_where = "AND tb1.suser_id = '{$user_id}'";

            $row = $db->super_query("SELECT tb1.id, title, question, sdate, sfor_user_id, suser_id, tb2.user_search_pref, user_photo FROM `support` tb1, `users` tb2 WHERE tb1.id = '{$qid}' AND tb1.suser_id = tb2.user_id {$sql_where}");
            if($row){
                //Выводим ответы
                $sql_answer = $db->super_query("SELECT id, adate, answer, auser_id FROM `support_answers` WHERE qid = '{$qid}' ORDER by `adate` ASC LIMIT 0, 100", 1);

                $tpl->load_template('support/answer.tpl');
                foreach($sql_answer as $row_answer){
                    if(!$row_answer['auser_id']){
                        $tpl->set('{name}', 'Агент поддержки');
                        $tpl->set('{ava}', '/images/support.png');
                        $tpl->set_block("'\\[no-agent\\](.*?)\\[/no-agent\\]'si","");
                    } else {
                        $tpl->set('{name}', $row['user_search_pref']);
                        if($row['user_photo'])
                            $tpl->set('{ava}', '/uploads/users/'.$row['suser_id'].'/50_'.$row['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');

                        $tpl->set('[no-agent]', '');
                        $tpl->set('[/no-agent]', '');
                    }

                    if($row_answer['auser_id'] == $user_id OR $user_info['user_group'] == 4){
                        $tpl->set('[owner]', '');
                        $tpl->set('[/owner]', '');
                    } else
                        $tpl->set_block("'\\[owner\\](.*?)\\[/owner\\]'si","");

                    $tpl->set('{id}', $row_answer['id']);
                    $tpl->set('{uid}', $user_id);
                    $tpl->set('{answer}', stripslashes($row_answer['answer']));
                    $date = megaDate(strtotime($row_answer['adate']));
                    $tpl->set('{date}', $date);
                    $tpl->compile('answers');
                }

                $tpl->load_template('support/show.tpl');
                $tpl->set('{title}', stripslashes($row['title']));
                $tpl->set('{question}', stripslashes($row['question']));
                $tpl->set('{qid}', $qid);

                $date = megaDate(strtotime($row['sdate']));
                $tpl->set('{date}', $date);

                if($row['sfor_user_id'] == $row['suser_id'])
                    $tpl->set('{status}', 'Вопрос ожидает обработки.');
                else
                    $tpl->set('{status}', 'Есть ответ.');

                $tpl->set('{name}', $row['user_search_pref']);

                if($user_info['user_group'] == 4)
                    $tpl->set('{uid}', $row['suser_id']);
                else
                    $tpl->set('{uid}', $user_id);

                if($row['user_photo'])
                    $tpl->set('{ava}', '/uploads/users/'.$row['suser_id'].'/50_'.$row['user_photo']);
                else
                    $tpl->set('{ava}', '/images/no_ava_50.png');

                $tpl->set('{answers}', $tpl->result['answers']);
                $tpl->compile('content');
            } else {
                $speedbar = $lang['error'];
                msgbox('', $lang['support_no_quest'], 'info');
            }

            $params['tpl'] = $tpl;
            Page::generate($params);
            return true;
        }
    }

    public function index($params){
//        $bar = new SupportController();
//        $tr = (new Module)->user_info();
//        $tr = (new SupportController())->user_info();
//        $tr = Module::user_info2();
        $user_info = $this->user_info();
        $logged = $this->logged();

//        var_dump($tr);
//        unlink($tr);

        $tpl = Registry::get('tpl');

        $lang = langs::get_langs();
//        $db = $this->db();
        $db = Db::getDB();
//        $logged = Registry::get('logged');
//        $user_info = Registry::get('user_info');

        $ajax = $_POST['ajax'];
        if($ajax == 'yes')
            Tools::NoAjaxQuery();

        if($logged){
            $user_id = $user_info['user_id'];
            $metatags['title'] = $lang['support_title'];

            if($_GET['page'] > 0) $page = intval($_GET['page']); else $page = 1;
            $gcount = 20;
            $limit_page = ($page-1)*$gcount;

            $mobile_speedbar = 'Помощь';

            if($user_info['user_support'] AND $user_info['user_group'] != 4)
                $db->query("UPDATE `users` SET user_support = 0 WHERE user_id = '{$user_id}'");

            if($user_info['user_group'] == 4){
                $sql_where = "ORDER by `sdate` DESC";
                $sql_where_cnt = "";
            } else {
                $sql_where = "AND tb1.suser_id = '{$user_id}' ORDER by `sdate` DESC";
                $sql_where_cnt = "WHERE suser_id = '{$user_id}'";
            }

            $sql_ = $db->super_query("SELECT tb1.id, title, suser_id, sfor_user_id, sdate, tb2.user_photo, user_search_pref FROM `support` tb1, `users` tb2 WHERE tb1.suser_id = tb2.user_id {$sql_where} LIMIT {$limit_page}, {$gcount}", 1);

            if($sql_)
                $count = $db->super_query("SELECT COUNT(*) AS cnt FROM `support` {$sql_where_cnt}");

            $tpl->load_template('support/head.tpl');
            if($sql_){
                $titles = array('вопрос', 'вопроса', 'вопросов');//questions
                if($user_info['user_group'] == 4)
                    $tpl->set('{cnt}', $count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles));
                else
                    $tpl->set('{cnt}', 'Вы задали '.$count['cnt'].' '.Gramatic::declOfNum($count['cnt'], $titles));
            }else
                $tpl->set('{cnt}', '');

            $tpl->compile('info');

            if($sql_){
                $tpl->load_template('support/question.tpl');
                foreach($sql_ as $row){
                    $tpl->set('{title}', stripslashes($row['title']));
                    $date = megaDate(strtotime($row['sdate']));
                    $tpl->set('{date}', $date);
                    if($row['sfor_user_id'] == $row['suser_id'] OR $user_info['user_group'] == 4){
                        if($row['sfor_user_id'] == $row['suser_id'])
                            $tpl->set('{status}', 'Вопрос ожидает обработки.');
                        else
                            $tpl->set('{status}', 'Есть ответ.');
                        $tpl->set('{name}', $row['user_search_pref']);
                        $tpl->set('{answer}', '');
                        if($row['user_photo'])
                            $tpl->set('{ava}', '/uploads/users/'.$row['suser_id'].'/50_'.$row['user_photo']);
                        else
                            $tpl->set('{ava}', '/images/no_ava_50.png');
                    } else {
                        $tpl->set('{name}', 'Агент поддержки');
                        $tpl->set('{status}', 'Есть ответ.');
                        $tpl->set('{ava}', '/images/support.png');
                        $tpl->set('{answer}', 'ответил');
                    }
                    $tpl->set('{qid}', $row['id']);
                    $tpl->compile('content');
                }
                Registry::set('tpl', $tpl);
                $tpl = Tools::navigation($gcount, $count['cnt'], '/support?page=', $tpl);
//                $tpl = Registry::get('tpl');
            } else
                if($user_info['user_group'] == 4)
                    msgbox('', $lang['support_no_quest3'], 'info_2');
                else
                    msgbox('', $lang['support_no_quest2'], 'info_2');
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