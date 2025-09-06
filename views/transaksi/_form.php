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
    <?= $form->field($model, 'obat1_id')->dropDownList(\app\models\Obat::getList(), ['prompt' => 'Pilih Obat 1']) ?>
    <?= $form->field($model, 'jumlah_obat1')->textInput(['type' => 'number', 'min' => 1, 'value' => 1]) ?>

    <?= $form->field($model, 'obat2_id')->dropDownList(\app\models\Obat::getList(), ['prompt' => 'Pilih Obat 2']) ?>
    <?= $form->field($model, 'jumlah_obat2')->textInput(['type' => 'number', 'min' => 1, 'value' => 1]) ?>

    <?= $form->field($model, 'obat3_id')->dropDownList(\app\models\Obat::getList(), ['prompt' => 'Pilih Obat 3']) ?>
    <?= $form->field($model, 'jumlah_obat3')->textInput(['type' => 'number', 'min' => 1, 'value' => 1]) ?>

    <?= $form->field($model, 'total_harga')->textInput(['readonly' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
<?php

$this->registerJs("
    var tindakanPrice = " . json_encode($model->tindakanPrices) . ";
    var obatPrice = " . json_encode($model->obatPrices) . ";
    var totalPriceField = $('#" . Html::getInputId($model, 'total_harga') . "');

    function updateTotalPrice() {
        var tindakanTotal = 0;
        $('input[name=\"Transaksi[tindakanIds][]\"]:checked').each(function() {
            var tindakanId = parseInt($(this).val());
            if (tindakanPrice.hasOwnProperty(tindakanId)) {
                tindakanTotal += Number(tindakanPrice[tindakanId]);
            }
        });
        
        var obatTotal = 0;

        var obat1Price = Number(obatPrice[$('#transaksi-obat1_id').val()] || 0);
        var jumlah1 = parseInt($('#transaksi-jumlah_obat1').val()) || 0;
        obatTotal += obat1Price * jumlah1;

        var obat2Price = Number(obatPrice[$('#transaksi-obat2_id').val()] || 0);
        var jumlah2 = parseInt($('#transaksi-jumlah_obat2').val()) || 0;
        obatTotal += obat2Price * jumlah2;

        var obat3Price = Number(obatPrice[$('#transaksi-obat3_id').val()] || 0);
        var jumlah3 = parseInt($('#transaksi-jumlah_obat3').val()) || 0;
        obatTotal += obat3Price * jumlah3;

        var totalPrice = tindakanTotal + obatTotal;
        totalPriceField.val(totalPrice);
    }

    $('input[name=\"Transaksi[tindakanIds][]\"]').change(updateTotalPrice);
    $('#transaksi-obat1_id, #transaksi-jumlah_obat1').change(updateTotalPrice);
    $('#transaksi-obat2_id, #transaksi-jumlah_obat2').change(updateTotalPrice);
    $('#transaksi-obat3_id, #transaksi-jumlah_obat3').change(updateTotalPrice);
    updateTotalPrice();
");

?>