<?php
require_once "setup.php";
require_once "utils.php";
require_once "objects/DBConnection.php";

// класс для работы с комментариями к сообщению (создание, просмотр списка)
class comment 
{
private $conn; // объект класса PDO
/*
Эти свойства не используются
private $id;
private $msg_id;
private $user_id;
private $date;
private $text;
*/

public function __construct()
{
    // создание подключения
    $dbc = new DBConnection();
    $this->conn = $dbc->getConnection();         
}
//------------------------------------------------------------------------------
// создание комментария
public function create($msg_id, $user_id, $date, $text)
{
if (!$this->conn)
    return false;

// очистка 
$com_msg_id=sanitizeString($msg_id);  
$com_user_id=sanitizeString($user_id);  
$com_date=sanitizeString($date);
$com_text=sanitizeString($text);

// если комментарий пустой
if (empty($com_text))
    return false;

// запрос для создания записи
// поле id будет заполнено автоматически 
$query="INSERT INTO comments SET msg_id=:msg_id, user_id=:user_id, date=:date,  
        text=:text";  

// подготовка запроса
$stmt = $this->conn->prepare($query);

// привязка значений
$stmt->bindParam(":msg_id", $com_msg_id);
$stmt->bindParam(":user_id", $com_user_id);
$stmt->bindParam(":date", $com_date);
$stmt->bindParam(":text", $com_text);

// выполняем запрос
if ($stmt->execute()) 
{
    return $this->conn->lastInsertId();
}

return false;
}
//--------------------------------------------------------------------------
// список всех комментариев для заданного сообщения
public function readList($id)
{
if (!$this->conn)
    return false;

// выбираем все записи
$query = "SELECT c.id, c.msg_id, c.user_id, c.date, c.text, users.login AS author " .
         "FROM comments AS c " .
         "JOIN users ON c.user_id=users.id WHERE c.msg_id=? ORDER BY c.date DESC";

// подготовка запроса
$stmt = $this->conn->prepare($query);

// очистка
$msg_id=sanitizeString($id);
    
// привязываем id сообщения
$stmt->bindParam(1, $msg_id);

// выполняем запрос
if (!$stmt->execute()) return false;

// массив комментариев
$comments=array();

$num = $stmt->rowCount();

if ($num>0) 
{
    // сохранить все записи запроса в массив
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
        $comments[]=$row;
    }
}
// массив комментариев
return $comments;
}
//--------------------------------------------------------------------------    
}