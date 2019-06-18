<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Аптеки рядом - MedChest';
?>

<?php
    if($model->address != null)
    {
        echo '<input id="address_input" type="hidden" value="'.$model->address.'">';
    }
    else
    {
        echo '<input id="address_input" type="hidden" value="">';
    }

?>


<h4>Ближайшие аптеки на карте</h4>
<div id="map" style="width: 500px;height: 400px;"></div>