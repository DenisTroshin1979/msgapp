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

$id=0;
$title="";
$brief="";
$text="";

$info=""; // вывод сообщений об ошибке, предупреждений и т.д.

if ($_SERVER['REQUEST_METHOD']=='GET')
{
    if (filter_has_var(INPUT_GET, 'id'))
    {
        $id=filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        if ($id!==false)        
        {
            // прочитать сообщение по заданному id
            $msg=new msg;
            $message=$msg->readOne($id);
            if (is_array($message))    
            {
                $title=$message['title'];
                $brief=$message['brief'];
                $text=$message['text'];
            }
        }
    }
}
else if ($_SERVER['REQUEST_METHOD']=='POST')
{    
    // валидация формы Редактирование сообщения
    if (filter_has_var(INPUT_POST, 'btnModifyMessage'))
    {
        if (filter_has_var(INPUT_POST, 'id'))
           $id=filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        if (filter_has_var(INPUT_POST, 'editTitle'))
        {
           $title=filter_input(INPUT_POST, 'editTitle');
           if ( (mb_strlen($title)<1) || ( mb_strlen($title) > 100) )
                { $title=""; $info .= "Поле Заголовок должно быть от 1 до 100 символов.<br>";}
        }    

        if (filter_has_var(INPUT_POST, 'editBrief'))
        {
           $brief=filter_input(INPUT_POST, 'editBrief');
           if ( (mb_strlen($brief)<1) || ( mb_strlen($brief) > 255) )
                { $brief=""; $info .= "Поле Краткое содержание должно быть от 1 до 255 символов.<br>";}
        }    

        if (filter_has_var(INPUT_POST, 'editText'))
        {
           $text=filter_input(INPUT_POST, 'editText');
           if ( (mb_strlen($text)<1) || ( mb_strlen($text) > 4000) )
                { $text=""; $info .= "Поле Полное содержание должно быть от 1 до 4000 символов. ";}
        }    

        $date=date("Y-m-d H:i:s");

        if (!empty($title) && !empty($brief) && !empty($text))
        {
            $message=new msg;
            if ($message->update($id, $date, $title, $brief, $text))
            {
                header("Location: showmsg.php?id={$id}");
                die();
            }
            else 
            {
                $info="Изменения не были сохранены!";
            }
        }
        else 
        {
            $info="Заполните все поля сообщения!";
        }
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
            <p><a href="messages.php?page=1">Сообщения</a>
            <h1 align="left">Редактирование сообщения</h1><br>     
            <p>
            
_START;                
            // если есть сообщения об ошибках - вывести 
            if (!empty($info)) {echo $info; echo "<p>";}
            // вывод формы редактирования сообщения
            echo <<< _FORM
                <form action="editmsg.php" method="post" enctype="application/x-www-form-urlencoded" accept-charset="utf-8">
                <label for="editTitle">Заголовок</label><br>              
                <input name="editTitle" type="text" value='{$title}' maxlength="100" size="100" tabindex="1" required><br> 
                <p>
                <label for="editBrief">Краткое содержание</label><br>              
                <textarea name="editBrief" maxlength="255" cols="100" rows="10" tabindex="2" required>{$brief}</textarea><br>
                <p>
                <label for="editText">Полное содержание</label><br>              
                <textarea name="editText" maxlength="4000" cols="100" rows="25" tabindex="3" required>{$text}</textarea><br>
                <input type="hidden" name="id" value="$id">
                <p>
                <input type="submit" name="btnModifyMessage" value="Сохранить изменения" tabindex="4">&nbsp;
                </form>
            _FORM;
echo <<< _END
        </div><!--Конец container-->
    </body>
</html>
_END;
?>