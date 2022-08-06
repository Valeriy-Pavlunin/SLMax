<?php
/** 
  *Автор: Валерий Павлунин
  *
  *Дата реализации: 05.08.2022 22:00
  *
  *Утилита для работы с со списком людей из базы данных
*/
require_once 'PeopleDataBase.php';

if (!class_exists('PeopleDataBase')) {
    die();
}

/**
 * Класс для обработки списка людей
 * Класс имеет поля: массив с id людей
 * Класс имеет методы:
 * 1. Конструктор ведет поиск id людей по всем полям БД;
 * 2. Получение массива экземпляров родительского класса из массива с id людей
 * полученного в конструкторе (getArrayOfParentInstances);
 * 3. Удаление людей из БД с помощью экземпляров родительского класса в
 * соответствии с массивом, полученным в конструкторе (deletePeople).
 */

class PeopleList extends PeopleDataBase
{
    private $id_list = [];
    
    /**
     * Конструктор класса, который инициализирует массив идентификаторов.
     * В качестве аргументов принемает переменную, относительно которой будут
     *  выбираться записи из бд ($oriented_value) и операцию, которой будут сравниваться
     * идентификаторы ($comparison_operation). 
     * Метод ничего не возвращает.
     */

    public function __construct($oriented_value, $comparison_operation = 1)
    {
        $connect = PeopleDataBase::connectToDataBase();
        switch ($comparison_operation) {
            case 1:
                $id_list = mysqli_query($connect, "SELECT `id` FROM `person` WHERE
                    `person`.`id` > '$oriented_value'"
                );
                break;
            case -1:
                $id_list = mysqli_query($connect, "SELECT `id` FROM `person` WHERE
                    `person`.`id` < '$oriented_value'"
                );
                break;
            case 0:
                $id_list = mysqli_query($connect, "SELECT `id` FROM `person` WHERE
                    `person`.`id` <> '$oriented_value'"
                );
                break;
            default:
                die('Второй аргумент некорректен (-1 - <, 0 - !=, 1 - >)');
        }
        $id_list = mysqli_fetch_all($id_list);
        $this->id_list = $id_list;
    }

    public function getArrayOfParentInstances()
    {
        $array_parent_instances = [];
        for ($i = 0; $i < count($this->id_list); $i++) {
            $parent_instance = new PeopleDataBase($this->id_list[$i][0]);
            $array_parent_instances[$i] =  $parent_instance;
        }
        print_r($array_parent_instances);
        return $array_parent_instances;
    }

    public function deletePeople()
    {
        $parent_instance = new PeopleDataBase(); 
        for ($i = 0; $i < count($this->id_list); $i++) {
            $parent_instance->deleteRecord($this->id_list[$i][0]);
        }
    }
}
