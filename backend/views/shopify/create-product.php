<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = 'Create Product for Shopify';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="shopify-create-product">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'price')->textInput(['type' => 'number', 'step' => '0.01']) ?>
    <?= $form->field($model, 'vendor')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'images[]')->fileInput(['multiple' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Create Product', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
