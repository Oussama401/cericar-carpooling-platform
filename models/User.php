<?php

namespace app\models;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;

    private static $users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'password' => 'admin',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id' => '101',
            'username' => 'demo',
            'password' => 'demo',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ],
    ];


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
    $m = \app\models\MyUsers::findOne($id);
    if ($m !== null) {
        return new static([
            'id' => $m->id,
            'username' => $m->identifiant, // nom de colonne dans ta BDD
            'password' => $m->motpasse,    // colonne contenant le SHA1
            'authKey' => null,
            'accessToken' => null,
        ]);
    }
    return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }


    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
    $m = \app\models\MyUsers::findOne(['identifiant' => $username]);
    if ($m !== null) {
        return new static([
            'id' => $m->id,
            'username' => $m->identifiant,
            'password' => $m->motpasse,
            'authKey' => null,
            'accessToken' => null,
        ]);
    }

    foreach (self::$users as $user) {
        if (strcasecmp($user['username'], $username) === 0) {
            return new static($user);
        }
    }

    return null;
    }


    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
    return $this->password !== null && sha1($password) === $this->password;
    }
  
}
