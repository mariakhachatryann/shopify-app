<?php

namespace backend\controllers;

use backend\models\ProductForm;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;

class ShopifyController extends Controller
{
    public function actionProducts()
    {
        $products = [];
        $cursor = null;

            $query = <<<GRAPHQL
            query (\$cursor: String) {
              products(first: 1, after: \$cursor) {
                edges {
                  cursor
                  node {
                    id
                    title
                    descriptionHtml
                    vendor
                    createdAt
                    category { id name }
                    media(first: 10) {
                      edges {
                        node {
                          id
                          mediaContentType
                          preview { image { src altText } }
                        }
                      }
                    }
                    variants(first: 10) {
                      edges {
                        node {
                          id
                          title
                          price
                          availableForSale
                          selectedOptions { name value }
                        }
                      }
                    }
                  }
                }
                pageInfo { hasNextPage }
              }
            }
            GRAPHQL;

            try {
                $shopifyClient = Yii::$app->shopifyClient;
                $data = $shopifyClient->sendQuery($query, ['cursor' => $cursor]);

                foreach ($data['products']['edges'] as $edge) {
                    $products[] = $edge['node'];
                }
            } catch (\Exception $e) {
                return $this->asJson(['error' => $e->getMessage()]);
            }

        return $this->render('products', ['products' => $products]);
    }

    public function actionCreateProduct()
    {
        $model = new ProductForm();

        if ($model->load(Yii::$app->request->post())) {
            $imagePaths = [];
            $model->images = UploadedFile::getInstances($model, 'images');
            foreach ($model->images as $file) {
                if ($file instanceof \yii\web\UploadedFile) {
                    $imagePaths[] = $file->tempName;
                }
            }
            $mutation = $this->createProductMutation($model);

            try {
                $shopifyClient = Yii::$app->shopifyClient;
                $response = $shopifyClient->sendMutation($mutation);
                if (isset($response['productCreate']['product']['id'])) {
                    $productId = $response['productCreate']['product']['id'];
                    $this->createMedia($imagePaths, $productId);
                    return $this->redirect(['shopify/products']);
                }
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', 'Error: ' . $e->getMessage());
            }
        }

        return $this->render('create-product', ['model' => $model]);
    }

    private function createProductMutation($model)
        {
            return <<<GRAPHQL
                mutation {
                  productCreate(input: {
                    title: "{$model->title}",
                    descriptionHtml: "{$model->description}",
                    vendor: "{$model->vendor}",
                    tags: ["test"],
                  }) {
                    product {
                      id
                      title
                      descriptionHtml
                    }
                    userErrors {
                      field
                      message
                    }
                  }
                }
            GRAPHQL;
    }

    private function createMedia($imagePaths, $productId)
    {
        $mediaItems = [];
        foreach ($imagePaths as $path) {
                $escapedPath = addslashes($path);
                $mediaItems[] = <<<MEDIA_ITEM
            {
                alt: "Product Image",
                mediaContentType: IMAGE,
                originalSource: "$escapedPath"
            }
            MEDIA_ITEM;
        }

        if (empty($mediaItems)) {
            return ['status' => 'error', 'message' => 'No valid image URLs provided'];
        }

        $mediaArray = implode(", ", $mediaItems);

        $mutation = <<<GRAPHQL
            mutation {
              productCreateMedia(
                media: [$mediaArray],
                productId: "$productId"
              ) {
                media {
                  alt
                  mediaContentType
                  status
                  preview {
                    image {
                      src
                      altText
                    }
                  }
                }
                mediaUserErrors {
                  field
                  message
                }
                product {
                  id
                  title
                }
              }
            }
        GRAPHQL;

        try {
            $shopifyClient = Yii::$app->shopifyClient;
            $response = $shopifyClient->sendQuery($mutation);

            if (!empty($response['data']['productCreateMedia']['mediaUserErrors'])) {
                return [
                    'status' => 'error',
                    'errors' => $response['data']['productCreateMedia']['mediaUserErrors'],
                ];
            }

            return [
                'status' => 'success',
                'media' => $response['data']['productCreateMedia']['media'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error sending query to Shopify API: ' . $e->getMessage(),
            ];
        }
    }
}