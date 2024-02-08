<?php
require_once "setup.php";
require_once "utils.php";
require_once "objects/msg.php";

if (!$loggedin)
{
    header("Location: index.php");
    die();
}    

$idCurrentUser=$_SESSION['idCurrentUser'];
$login=$_SESSION['loginCurrentUser'];
$info=""; // вывод сообщений об ошибке, предупреждений и т.д.

// количество сообщений на странице
$records_per_page = 5;

if ($_SERVER['REQUEST_METHOD']=='GET')
{
    if (filter_has_var(INPUT_GET, 'page'))
    {
        // номер страницы сообщений
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($page===false) 
            $page=1;
   
        // прочитать список сообщений (из заданного диапазона)
        $msg=new msg;
        $from_record_num = ($records_per_page * ($page-1));
        $messages=$msg->readList($from_record_num, $records_per_page);
        if (count($messages)!=0)
        {
            // подготовить список ссылок пагинации
            $total_rows=$msg->count();
            $page_url="http://" . $_SERVER['SERVER_NAME'] . "/" . "msgapp/messages.php?";
            $pageList=getPageList($page, $total_rows, $records_per_page, $page_url);
        }
        else $info= "Сообщений нет.";
    }

}
// вывод страницы
echo <<< _START
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="messages.css" rel="stylesheet">
        <title>Сообщения и комментарии</title>
    </head>
    <body>
        <div class="container">
            <p>Вы вошли как <b>{$login}</b>&nbsp;<a href="logout.php">Выйти</a>         
            <p><a href="newmsg.php">Новое сообщение</a>
            <h1 align="left">Сообщения</h1><br>     
            <p>    
_START;                
            // если есть сообщения об ошибках - вывести
            if (!empty($info)) {echo $info; echo "<p>";}         
            if (isset($messages) && is_array($messages))
            {
                echo "<table width='100%' border='0' cellpadding='10'>";
                // вывод списка сообщений
                foreach ($messages as $m)
                {
                    echo "<tr>";
                    echo "<td><a href=\"showmsg.php?id={$m['id']}\">{$m['title']}</a><br>
                            <i>{$m['brief']}</i><br>
                            Автор: {$m['author']}, &nbsp;время: {$m['date']}</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<p>";
                // вывод ссылок пагинации
                if ( isset($pageList) && (is_array($pageList)) )
                {    
                    foreach ($pageList['pages'] as $item)
                    {
                        if ($item['current']=='1') {echo "<b><a href=\"{$item['url']}\">{$item['page']}</a></b>";}
                            else {echo "<a href=\"{$item['url']}\">{$item['page']}</a>";}
                        echo "&nbsp;";
                    }
                }
            }
echo <<< _END
        </div><!--Конец container-->
    </body>
</html>
_END;
?>