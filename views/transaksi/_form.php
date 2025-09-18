<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\Transaksi $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="transaksi-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pasien_id')->dropDownList(
        ArrayHelper::map(\app\models\Pasien::find()->all(), 'id', 'nama'),
        ['prompt' => 'Pilih Pasien']
    ) ?>
    <?= $form->field($model, 'tindakanIds')->checkboxList(ArrayHelper::map(\app\models\Tindakan::find()->all(), 'id', 'nama_tindakan')) ?>
    <?= $form->field($model, 'obat1_id')->dropDownList(\app\models\Obat::getList(), ['prompt' => 'Pilih Obat 1', 'data-harga' => '']) ?>

    <?= $form->field($model, 'obat2_id')->dropDownList(\app\models\Obat::getList(), ['prompt' => 'Pilih Obat 2', 'data-harga' => '']) ?>

    <?= $form->field($model, 'obat3_id')->dropDownList(\app\models\Obat::getList(), ['prompt' => 'Pilih Obat 3', 'data-harga' => '']) ?>
    <?= $form->field($model, 'total_harga')->textInput(['readonly' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php

$this->registerJs('
    var tindakanPrice = ' . json_encode($model->tindakanPrices) . ';
    var obatPrice = ' . json_encode($model->obatPrices) . ';
    var totalPriceField = $("#' . Html::getInputId($model, 'total_harga') . '");

    function updateTotalPrice() {
        var tindakanTotal = 0;
        $("input[name=\'Transaksi[tindakanIds][]\']:checked").each(function() {
            var tindakanId = $(this).val();
            if (tindakanPrice.hasOwnProperty(tindakanId)) {
                tindakanTotal += tindakanPrice[tindakanId];
            }
        });
        
        var obatTotal = 0;
        var obat1Price = obatPrice[$("#transaksi-obat1_id").val()] || 0;
        obatTotal += obat1Price * $("#transaksi-jumlah_obat1").val();
        var obat2Price = obatPrice[$("#transaksi-obat2_id").val()] || 0;
        obatTotal += obat2Price * $("#transaksi-jumlah_obat2").val();
        var obat3Price = obatPrice[$("#transaksi-obat3_id").val()] || 0;
        obatTotal += obat3Price * $("#transaksi-jumlah_obat3").val();
        
        var totalPrice = tindakanTotal + obatTotal;
        totalPriceField.val(totalPrice);
    }

    $("input[name=\'Transaksi[tindakanIds][]\']").change(updateTotalPrice);
    $("#transaksi-obat1_id").change(updateTotalPrice);
    $("#transaksi-jumlah_obat1").change(updateTotalPrice);
    $("#transaksi-obat2_id").change(updateTotalPrice);
    $("#transaksi-jumlah_obat2").change(updateTotalPrice);
    $("#transaksi-obat3_id").change(updateTotalPrice);
    $("#transaksi-jumlah_obat3").change(updateTotalPrice);
    updateTotalPrice();');
?>