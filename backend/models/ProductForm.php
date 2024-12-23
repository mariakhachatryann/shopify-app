<?php

namespace backend\models;

use Yii;
use yii\base\Model;

class ProductForm extends Model
{
    public $title;
    public $description;
    public $price;
    public $vendor;
    public $category;
    public $images = []; // To handle multiple images

    public function rules()
    {
        return [
            [['title', 'description', 'price', 'vendor'], 'required'],
            [['price'], 'number'],
            [['title', 'description', 'vendor'], 'string', 'max' => 255],
            [['images'], 'file', 'maxFiles' => 5],
        ];
    }
}
