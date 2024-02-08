<?php
require_once "setup.php";
require_once "utils.php";
require_once "objects/DBConnection.php";

// Класс для работы с сообщениями (создать, изменить, удалить, просмотреть)
// работает с таблицей БД messages (поля id, user_id, date, title, brief, text)
class msg 
{
private $conn; // объект класса PDO
/*
Эти свойства не используются
private $id;
private $user_id;
private $date;
private $title;
private $brief;
private $text;
*/

public function __construct()
{
    // создание подключения
    $dbc = new DBConnection();
    $this->conn = $dbc->getConnection(); 
}
//------------------------------------------------------------------------------
// создание сообщения
public function create($user_id, $date, $title, $brief, $text)
{
if (!$this->conn)
    return false;

// очистка 
$msg_user_id=sanitizeString($user_id);  
$msg_date=sanitizeString($date);
$msg_title=sanitizeString($title);
$msg_brief=sanitizeString($brief);
$msg_text=sanitizeString($text);

// если заголовок, краткое содержание или полное содержание пустые
if (empty($msg_title) || empty($msg_brief) || empty($msg_text))
    return false;

// запрос для создания записи
// поле id будет заполнено автоматически 
$query="INSERT INTO messages SET user_id=:user_id, date=:date, title=:title,  
        brief=:brief, text=:text";  

// подготовка запроса
$stmt = $this->conn->prepare($query);

// привязка значений
$stmt->bindParam(":user_id", $msg_user_id);
$stmt->bindParam(":date", $msg_date);
$stmt->bindParam(":title", $msg_title);
$stmt->bindParam(":brief", $msg_brief);
$stmt->bindParam(":text", $msg_text);

// выполняем запрос
if ($stmt->execute()) 
{
    return $this->conn->lastInsertId();
}
return false;
}
//--------------------------------------------------------------------------
// список сообщений (из заданного диапазона)
public function readList($from_record_num, $records_per_page)
{
if (!$this->conn)
    return false;

// выборка
$query = "SELECT m.id, m.user_id, m.date, m.title, m.brief, m.text, users.login AS author " .
         "FROM messages AS m JOIN users ON m.user_id=users.id ORDER BY m.date DESC LIMIT ?, ?";

// подготовка запроса
$stmt = $this->conn->prepare( $query );

// свяжем значения переменных
$stmt->bindParam(1, $from_record_num, PDO::PARAM_INT);
$stmt->bindParam(2, $records_per_page, PDO::PARAM_INT);

// выполняем запрос
if (!$stmt->execute()) return false;

// массив сообщений
$messages=array();

$num = $stmt->rowCount();

if ($num>0) 
{
    // сохранить все записи запроса в массив
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
    {
        $messages[]=$row;
    }
}
// массив сообщений
return $messages;
}
//------------------------------------------------------------------------------
// прочитать одно сообщение
function readOne($id) 
{
    if (!$this->conn)
    return false;

    // запрос для чтения одного сообщения
    $query = "SELECT m.id, m.user_id, m.date, m.title, m.brief, m.text, users.login AS author " .
             "FROM messages AS m JOIN users ON m.user_id=users.id " .
             "WHERE m.id = ? LIMIT 0, 1";

    // подготовка запроса
    $stmt = $this->conn->prepare($query);

    // очистка
    $msg_id=sanitizeString($id);
    
    // привязываем id сообщения
    $stmt->bindParam(1, $msg_id);

    // выполняем запрос
    if ($stmt->execute()) 
    {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);   
        return $row;
    }
    else return false;
}
//--------------------------------------------------------------------------
// изменение сообщения
function update($id, $date, $title, $brief, $text)
{
    if (!$this->conn)
    return false;

    // запрос для обновления записи
    $query = "UPDATE messages SET date=:date," .                
             "title=:title, brief=:brief, text=:text " .
             "WHERE id=:id";

    // подготовка запроса
    $stmt = $this->conn->prepare($query);

    // очистка
    $msg_id=sanitizeString($id);
    $msg_date=sanitizeString($date);
    $msg_title=sanitizeString($title);
    $msg_brief=sanitizeString($brief);
    $msg_text=sanitizeString($text);
    
    // привязываем значения
    $stmt->bindParam(":id", $msg_id);
    $stmt->bindParam(":date", $msg_date);
    $stmt->bindParam(":title", $msg_title);    
    $stmt->bindParam(":brief", $msg_brief);
    $stmt->bindParam(":text", $msg_text);
    
    // выполняем запрос
    if ($stmt->execute()) {
        return true;
    }

    return false;
}
//--------------------------------------------------------------------------
// получить количество сообщений
public function count() 
{
    if (!$this->conn)
    return 0;

    $query = "SELECT COUNT(*) as num FROM messages";

    $stmt = $this->conn->prepare( $query );
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row["num"];
}    
//------------------------------------------------------------------------------
}