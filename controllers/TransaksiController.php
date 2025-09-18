<?php

namespace app\controllers;

use Yii;
use app\models\Transaksi;
use app\models\TransaksiTindakan;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TransaksiController implements the CRUD actions for Transaksi model.
 */
class TransaksiController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Transaksi models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Transaksi::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Transaksi model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Transaksi model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
{
    $model = new Transaksi();

    if ($model->load(Yii::$app->request->post())) {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $total = 0;

            // ambil tindakan
            $tindakanIds = Yii::$app->request->post('Transaksi')['tindakanIds'] ?? [];
            if (!empty($tindakanIds)) {
                $tindakans = \app\models\Tindakan::find()->where(['id' => $tindakanIds])->all();
                foreach ($tindakans as $t) {
                    $total += $t->harga;
                }
            }

            // ambil obat
            for ($i = 1; $i <= 3; $i++) {
                $obatId = Yii::$app->request->post('Transaksi')["obat{$i}_id"] ?? null;
                $jumlah = Yii::$app->request->post('Transaksi')["jumlah_obat{$i}"] ?? 1;

                if (!empty($obatId)) {
                    $obat = \app\models\Obat::findOne($obatId);
                    if ($obat) {
                        $total += $obat->harga_obat * $jumlah;
                    }
                }
            }

            // set total harga
            $model->total_harga = $total;

            if ($model->save(false)) {
                // simpan ke transaksi_tindakan
                foreach ($tindakanIds as $tid) {
                    $tt = new \app\models\TransaksiTindakan();
                    $tt->transaksi_id = $model->id;
                    $tt->tindakan_id = $tid;
                    $tt->save(false);
                }

                // simpan ke transaksi_obat
                for ($i = 1; $i <= 3; $i++) {
                    $obatId = Yii::$app->request->post('Transaksi')["obat{$i}_id"] ?? null;
                    $jumlah = Yii::$app->request->post('Transaksi')["jumlah_obat{$i}"] ?? 1;

                    if (!empty($obatId)) {
                        $to = new \app\models\TransaksiObat();
                        $to->transaksi_id = $model->id;
                        $to->obat_id = $obatId;
                        $to->jumlah = $jumlah;
                        $to->save(false);
                    }
                }

                $transaction->commit();
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}




    /**
     * Updates an existing Transaksi model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Transaksi model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Transaksi model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Transaksi the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Transaksi::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
