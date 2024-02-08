<?php
require_once "setup.php";
require_once "utils.php";
require_once "objects/user.php";
require_once "objects/msg.php";
require_once "objects/comment.php";

if (!$loggedin)
{
    header("Location: index.php");
    die();
}    

$idCurrentUser=$_SESSION['idCurrentUser'];
$login=$_SESSION['loginCurrentUser'];
$id=0;

$info=""; // вывод сообщений об ошибке, предупреждений и т.д.

// чтение сообщения по id
if ($_SERVER['REQUEST_METHOD']=='GET')
{
    if (filter_has_var(INPUT_GET, 'id'))
    {
        $id=filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($id!==false)
        {    
            // прочитать заданное сообщение
            $msg=new msg;
            $message=$msg->readOne($id);
            if (is_array($message))    
            {
                // и комментарии к сообщению
                $comment=new comment;
                $comments=$comment->readList($id);
            }
            else 
                $info="Сообщение не найдено!";

        }
        else 
            $info="Недопустимый запрос!";
    }
}
// добавление комментария
else if ($_SERVER['REQUEST_METHOD']=='POST')
{
    if (filter_has_var(INPUT_POST, 'btnAddComment'))
    {
        if (filter_has_var(INPUT_POST, 'textarea1'))
        {
           $text=filter_input(INPUT_POST, 'textarea1');
           if ((mb_strlen($text)<1) || (mb_strlen($text)>1000))
           {
                $text="";
                $info .= "Длина комментария должна быть от 1 до 1000 символов. <br>";
           }
        }    
        
                
        if (filter_has_var(INPUT_POST,'id'))
           $id=filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if (filter_has_var(INPUT_POST, 'user_id'))
           $user_id=filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        if (!empty($id) && !empty($user_id) && !empty($text))
        {
            $date=date("Y-m-d H:i:s");
            $comment=new comment;
            if ($comment->create($id, $user_id, $date, $text))
            {
                header("Location: showmsg.php?id={$id}");
                die();
            }
            else 
            {
                $info="Комментарий не был добавлен!";
            }
        }
        else 
            $info="Не заполнена форма нового комментария!";
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
            <p>                
            <a href="messages.php?page=1">Сообщения</a><br>
            <h1 align="left">Просмотр сообщения</h1><br>     
            <p>
            
_START;                
            // если есть сообщения об ошибках - вывести
            if (!empty($info)) {echo $info; echo "<p>";}
            
            if (isset($message) && is_array($message))
            {
                // вывод сообщения
                echo "<b>Заголовок:</b><i>{$message['title']}</i><p>" .
                     "<i>Автор: {$message['author']}, &nbsp;время: {$message['date']}</i>&nbsp;";

                if ($message['user_id']==$idCurrentUser)     
                {echo "<a href=\"editmsg.php?id={$message['id']}\">Редактировать</a>";}

                echo "<p><b>Краткое содержание:</b><br><i>{$message['brief']}</i>" .
                     "<p><b>Полное содержание:</b><br><i>{$message['text']}</i>";
                
               echo "<p><h2>Комментарии</h2>";
               echo "<p>"; 
               if (is_array($comments))
               {    
                   // вывод комментариев к сообщению
                   foreach ($comments as $c)
                   {
                    echo "Автор: <b>{$c['author']}</b>, &nbsp; время: {$c['date']}&nbsp;";
                    echo "<br><i>{$c['text']}</i>";
                    echo "<p>";
                   }
               } 
            // форма добавления комментария
            echo <<< _FORM
                <form action="showmsg.php" method="post" enctype="application/x-www-form-urlencoded" accept-charset="utf-8">
                <textarea name="textarea1" cols="60" rows="20" tabindex="1" maxlength="1000" required></textarea><br>
                <input type="hidden" name="id" value="$id">             
                <input type="hidden" name="user_id" value="$idCurrentUser">
                <input type="submit" name="btnAddComment" value="Добавить комментарий" tabindex="2"><br>
                </form>
            _FORM;
               
            }
echo <<< _END
        </div><!--Конец container-->
    </body>
</html>
_END;
?>