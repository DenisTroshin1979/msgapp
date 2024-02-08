<?php

// обезвреживание строки
function sanitizeString($var)
{
    //$var = stripslashes($var);
    //$var = strip_tags($var);
    $var = htmlentities($var); 
    return $var;
}
//------------------------------------------------------------------------------
// уничтожение сессии
function destroySession()
{
    session_start();
    $_SESSION=array();
    if (session_id() != "" || isset($_COOKIE[session_name()]))
    setcookie(session_name(), '', time()-2592000, '/');
    session_destroy();
}
//------------------------------------------------------------------------------
function validate_login($login) 
{
    if ($login == "") 
        return false;
    else if ( (mb_strlen($login) < 3) || (mb_strlen($login) > 20) )
        return false;
    else if (preg_match("/[^a-zA-Z0-9_-]/", $login))
        return false;
    return true;
}
//------------------------------------------------------------------------------
function validate_password($pass) 
{
    if ($pass == "")  // 
        return true;  // для тестирования разрешить пустой пароль
    if (mb_strlen($pass) > 20)
        return false;
    else if (preg_match("/[^a-zA-Z0-9_-]/", $pass))
        return false;
    return "";
}
//------------------------------------------------------------------------------
// подготавливает список ссылок на страницы c сообщениями
function getPageList($page, $total_rows, $records_per_page, $page_url) 
{
    // массив пагинации
    $paging_arr = array();

    // вычислить количество страниц 
    $num = ceil($total_rows / $records_per_page);

    $paging_arr["pages"] = array();

    for($i=0; $i<$num; $i++)
    {
        $p=$i+1;
        $paging_arr["pages"][$i]["page"] = $p;
        $paging_arr["pages"][$i]["url"] = "{$page_url}page={$p}";
        $paging_arr["pages"][$i]["current"] = $p==$page ? "1" : "0";

    }
    return $paging_arr;
}
//------------------------------------------------------------------------------
?>