<?php

/** 
  *Автор: Валерий Павлунин
  *
  *Дата реализации: 05.08.2022 22:00
  * 
  *Утилита для работы с базой данных людей
  */

/**
  *Класс для обработки базы данных людей
  *Класс имеет поля: id, имя, фамилия, дата рождения,
  * пол(0,1), город рождения.
  *Класс имеет методы:
  *1. Сохранение полей экземпляра класса в БД (setDatabaseValues);
  *2. Удаление человека из БД в соответствии с id объекта (deleteRecord);
  *3. static преобразование даты рождения в возраст (getAge);
  *4. static преобразование пола из двоичной системы в текстовую
  *(getGender);
  *5. Конструктор класса либо создает человека в БД с заданной
  *  информацией, либо берет информацию из БД по id (в зависимости от количества аргументов);
  *6. Форматирование человека с преобразованием возраста и (или) пола
  * в зависимости от параметров (возвращает новый
  * экземпляр StdClass со всеми полями изначального класса) (formattingPerson).
  */

class PeopleDataBase
{
    
    protected $id;
    protected $surname;
    protected $name;
    protected $date_of_birth;
    protected $gender;
    protected $city;

    protected static function connectToDataBase()
    {
        $connect = mysqli_connect('localhost', 'root', '', 'slmax');
        if (!$connect) {
            die('Ошибка при подключении к базе данных');
        }
        return $connect;
    }


    private static function getAge($date_of_birth)
    {
        $difference = date('Ymd') - date('Ymd', strtotime($date_of_birth));
        $age = intval(substr($difference, 0, -4));
        return intval($age);
    }
    private static function getGender($gender)
    {
        return $gender == 0 ? 'жен' : 'муж';
    }
    public function setDatabaseValues()
    {
        $connect = PeopleDataBase::connectToDataBase();
        mysqli_query($connect,"INSERT INTO `person` ( `id`, `name`, `surname`,
             `birth`, `gender`, `city`) VALUES ('$this->id','$this->name',
              '$this->surname', '$this->date_of_birth', '$this->gender', '$this->city')"
        );
    }

    protected function deleteRecord($id)
    {
        $connect = PeopleDataBase::connectToDataBase();
        mysqli_query(
            $connect,
            "DELETE FROM `person` WHERE `id`='$id'"
        );
    }

    private static function validation($id, $name, $surname, $date_of_birth,
         $gender, $city) {
        if ($id < 1) {
            die('Некорректный идентификатор');
        }
        if (!ctype_alnum($name) || !ctype_alnum($surname)) {
            die('Имя и фамилия не может содержать символы!');
        }
        if ($name === '' || $surname === '' || $date_of_birth === '' 
        || $gender === '' || $city === ''
        ) {
            die('Не все данные введены');
        }
        $test_data_ar = explode('-', $date_of_birth);
        if (checkdate($test_data_ar[1], $test_data_ar[2], $test_data_ar[0])) {
            /* -------- */
        } else {
            die('Дата введена не корректно!');
        }
        if ($gender < 0 || $gender > 1) {
            die('Пол введен не корректно!');
        }
    }

/**
  *Конструктор класса либо создает человека в БД с заданной
  *информацией, либо берет информацию из БД по id (в зависимости от количества аргументов).
  *В качестве аргументов принимает: идентификатор, имя, фамилия, дата рождения, пол, город.
  *Конструктор не возвращает значений.
  */

    public function __construct($id = null, $name = null, $surname = null,
         $date_of_birth = null, $gender = null, $city = null
         ) {
        $number_of_arguments = func_num_args();
        if ($number_of_arguments == 6) {
            PeopleDataBase::validation($id, $name, $surname, $date_of_birth,
                 $gender, $city
            );
            $this->id = $id;
            $this->name = $name;
            $this->surname = $surname;
            $this->date_of_birth = $date_of_birth;
            $this->gender = $gender;
            $this->city = $city;
        } elseif ($number_of_arguments == 1) {
            $connect = PeopleDataBase::connectToDataBase();
            $info = mysqli_query($connect, "SELECT* FROM `person` WHERE `person`.`id`=$id");
            
            $info = mysqli_fetch_all($info);
           
            $info =$info[0];
            $this->id = $info[0];
            $this->name = $info[1];
            $this->surname = $info[2];
            $this->date_of_birth = $info[3];
            $this->gender = $info[4];
            $this->city = $info[5];
            
            
        } elseif ($number_of_arguments == 0) {

        } else {
            die('Неправильное число аргументов передано конструктору');
        }
    }

    public function formattingPerson($format_metod)
    {
        $StdClass = new stdClass;
        $StdClass->id = $this->id;
        $StdClass->name = $this->name;
        $StdClass->surname = $this->surname;
        $StdClass->date_of_birth = $this->date_of_birth;
        $StdClass->gender = $this->gender;
        $StdClass->city = $this->city;
        if ($format_metod === 0) {
            $StdClass->gender = PeopleDataBase::getGender($this->gender);
            $StdClass->age = PeopleDataBase::getAge($this->date_of_birth);
        } elseif ($format_metod === -1) {
            $StdClass->gender = PeopleDataBase::getGender($this->gender);
        } elseif ($format_metod === 1) {
            $StdClass->age = PeopleDataBase::getAge($this->date_of_birth);
        }
        return $StdClass;
    }
}
