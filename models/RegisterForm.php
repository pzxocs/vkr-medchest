<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**

 *
 * @property User|null $user This property is read-only.
 *
 */
class RegisterForm extends Model
{
    public $username;
    public $name;
    public $password;
    public $confirmpassword;

    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username'], 'required', 'message' => 'Логин не может быть пустым'],
            [['name'], 'required', 'message' => 'Имя пользователя не может быть пустым'],
            [['password'], 'required', 'message' => 'Пароль не может быть пустым'],
            [['confirmpassword'], 'required', 'message' => 'Подтверждение пароля не может быть пустым'],
            [
                'confirmpassword', 'compare', 'compareAttribute' => 'password',
                'message' => "Введенные пароли не совпадают",
            ],
            // password is validated by validatePassword()
            //['password', 'validatePassword'],
        ];
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function register()
    {
        //создаем нового пользователя
        if ($this->validate()) {
            //проверяем, нет ли у нас уже такого пользователя
            $dbuser = User::find()
                ->where(['username' => $this->username])
                ->one();
            if($dbuser != null)
            {
                return false;
            }
            $dbuser = new User();
            $dbuser->username = $this->username;
            $dbuser->name = $this->name;
            //пароль сохраняем в хэшированном виде
            $dbuser->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
            $dbuser->save();
            //создадим запись с натройками
            $dbuserprefs = new UserPreferences();
            $dbuserprefs->user_id = $this->getUser()->getId();
            $dbuserprefs->email_notify = 1;
            $dbuserprefs->phone_notify = 1;
            $dbuserprefs->alert_days_to_expired = 15;
            $dbuserprefs->alert_critical_pcs_left = 30;
            $dbuserprefs->save();
            //добавили, теперь сразу логинимся
            return Yii::$app->user->login($this->getUser(), 3600*24*30);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
