<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use app\models\SetParentForm;
use app\models\User;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\widgets\Pjax;

$isChild = false;
$hasChild = false;

//проверим, не является ли наш профиль семейным, то есть привязанным к чему либо
$dbuser = User::find()
    ->where(['id' => Yii::$app->user->id])
    ->one();

if(!is_null($dbuser->parent_id))
{
    $isChild = true;
}

if(User::find()
    ->where(['parent_id' => Yii::$app->user->id])
    ->count() > 0
)
{
    $hasChild = true;
}

?>

<h4>Семейные параметры</h4>

<?php Pjax::begin(['id' => 'family_prefs']) ?>

    <?php if(!empty($answer)): ?>
        <?php echo 'Пользователь не найден' ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-12">
            <?php
            if(!$isChild && !$hasChild)
            {
                echo $this->render('set_parent', ['model' => new SetParentForm()]);
            }
            else if($isChild)
            {
                echo $this->render('remove_parent', ['model' => new SetParentForm()]);
            }
            ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php
                if(!$isChild && $hasChild)
                {
                    echo $this->render('child_list');
                }
            ?>
        </div>
    </div>
<?php Pjax::end() ?>

