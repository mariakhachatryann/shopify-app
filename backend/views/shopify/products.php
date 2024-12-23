<?php

/* @var $this yii\web\View */
/* @var $products array */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Shopify Products';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="shopify-products">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="product-list">
        <?php foreach ($products as $product): ?>
        <div class="product-item">
            <h2><?= Html::encode($product['title']) ?></h2>
            <p><strong>Vendor:</strong> <?= Html::encode($product['vendor']) ?></p>
            <p><strong>Created At:</strong> <?= Html::encode($product['createdAt']) ?></p>
            <p><strong>Description:</strong> <?= Html::encode($product['descriptionHtml']) ?></p>

                <?php if (!empty($product['category'])): ?>
            <p><strong>Category:</strong> <?= Html::encode($product['category']['name']) ?></p>
            <?php endif; ?>

            <div class="media">
                <h3>Images:</h3>
                <?php foreach ($product['media']['edges'] as $media): ?>
                    <img src="<?= Html::encode($media['node']['preview']['image']['src']) ?>"
                         alt="<?= Html::encode($media['node']['preview']['image']['altText']) ?>"
                         class="product-image" />
                <?php endforeach; ?>
            </div>

            <div class="variants">
                <h3>Variants:</h3>
                    <?php foreach ($product['variants']['edges'] as $variant): ?>
                <p><strong>Variant Title:</strong> <?= Html::encode($variant['node']['title']) ?></p>
                <p><strong>Price:</strong> $<?= Html::encode($variant['node']['price']) ?></p>
                <p><strong>Available for Sale:</strong> <?= $variant['node']['availableForSale'] ? 'Yes' : 'No' ?></p>
                <p><strong>Options:</strong>
                        <?php foreach ($variant['node']['selectedOptions'] as $option): ?>
                    <br><?= Html::encode($option['name']) ?>: <?= Html::encode($option['value']) ?>
                                                              <?php endforeach; ?>
                </p>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
