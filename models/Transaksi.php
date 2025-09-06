<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaksi".
 *
 * @property int $id
 * @property int $pasien_id
 * @property int $total_harga
 * @property string $tanggal_transaksi
 *
 * @property Pasien $pasien
 * @property TransaksiObat[] $transaksiObats
 * @property TransaksiTindakan[] $transaksiTindakans
 */
class Transaksi extends \yii\db\ActiveRecord
{
    public $tindakanIds = [];
    public $obat1_id;
    public $obat2_id;
    public $obat3_id;
    public $jumlah_obat1;
    public $jumlah_obat2;
    public $jumlah_obat3;
    public $tindakanPrices = [];
    public $obatPrices;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->obatPrices = \yii\helpers\ArrayHelper::map(\app\models\Obat::find()->all(), 'id', 'harga');
        $this->tindakanPrices = \yii\helpers\ArrayHelper::map(\app\models\Tindakan::find()->all(), 'id', 'harga');
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            // simpan tindakan
            foreach ($this->tindakanIds as $tindakanId) {
                $transaksiTindakan = new TransaksiTindakan();
                $transaksiTindakan->transaksi_id = $this->id;
                $transaksiTindakan->tindakan_id = $tindakanId;
                $transaksiTindakan->save();
            }

            // simpan obat 1
            if ($this->obat1_id) {
                $transaksiObat = new TransaksiObat();
                $transaksiObat->transaksi_id = $this->id;
                $transaksiObat->obat_id = $this->obat1_id;
                $transaksiObat->jumlah = $this->jumlah_obat1;
                $transaksiObat->save();
            }

            // simpan obat 2
            if ($this->obat2_id) {
                $transaksiObat = new TransaksiObat();
                $transaksiObat->transaksi_id = $this->id;
                $transaksiObat->obat_id = $this->obat2_id;
                $transaksiObat->jumlah = $this->jumlah_obat2;
                $transaksiObat->save();
            }

            // simpan obat 3
            if ($this->obat3_id) {
                $transaksiObat = new TransaksiObat();
                $transaksiObat->transaksi_id = $this->id;
                $transaksiObat->obat_id = $this->obat3_id;
                $transaksiObat->jumlah = $this->jumlah_obat3;
                $transaksiObat->save();
            }
        }
    }

    public static function tableName()
    {
        return 'transaksi';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pasien_id'], 'required'],
            [['pasien_id', 'total_harga'], 'integer'],
            [['tanggal_transaksi'], 'default', 'value' => date('Y-m-d')],
            [['obat1_id', 'obat2_id', 'obat3_id', 'jumlah_obat1', 'jumlah_obat2', 'jumlah_obat3'], 'integer'],
            [['tindakanIds'], 'required', 'message' => 'Pilih minimal satu tindakan.'],
            [['pasien_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pasien::class, 'targetAttribute' => ['pasien_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pasien_id' => 'Pasien ID',
            'total_harga' => 'Total Harga',
            'tanggal_transaksi' => 'Tanggal Transaksi',
        ];
    }

    /**
     * Gets query for [[Pasien]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPasien()
    {
        return $this->hasOne(Pasien::class, ['id' => 'pasien_id']);
    }

    public function getObat1()
    {
        return $this->hasOne(Obat::className(), ['id' => 'obat1_id']);
    }

    public function getObat2()
    {
        return $this->hasOne(Obat::className(), ['id' => 'obat2_id']);
    }

    public function getObat3()
    {
        return $this->hasOne(Obat::className(), ['id' => 'obat3_id']);
    }

    // method untuk menghitung total harga tindakan
    public function hitungTindakanTotal()
    {
        $total = 0;
        foreach ($this->tindakanIds as $tindakanId) {
            $total += $this->tindakanPrices[$tindakanId];
        }
        return $total;
    }

    // method untuk menghitung total harga obat
    public function hitungObatTotal()
    {
        $total = 0;
        if ($this->obat1_id) {
            $total += $this->jumlah_obat1 * $this->obat1->harga;
        }
        if ($this->obat2_id) {
            $total += $this->jumlah_obat2 * $this->obat2->harga;
        }
        if ($this->obat3_id) {
            $total += $this->jumlah_obat3 * $this->obat3->harga;
        }
        return $total;
    }

    // method untuk menghitung total harga
    public function getTotalHarga()
    {
        $total = 0;
        foreach ($this->transaksiTindakans as $transaksiTindakan) {
            $total += $transaksiTindakan->tindakan->harga;
        }
        foreach ($this->transaksiObats as $transaksiObat) {
            $total += $transaksiObat->obat->harga * $transaksiObat->jumlah;
        }
        return $total;
    }

    public function getTransaksiTindakans()
    {
        return $this->hasMany(TransaksiTindakan::class, ['transaksi_id' => 'id']);
    }

    public function getTransaksiObats()
    {
        return $this->hasMany(TransaksiObat::class, ['transaksi_id' => 'id']);
    }

    public function getObat()
    {
        return $this->hasOne(Obat::class, ['id' => 'obat_id']);
    }

    public function getTindakans()
    {
        return $this->hasMany(Tindakan::class, ['id' => 'tindakan_id'])
            ->viaTable('transaksi_tindakan', ['transaksi_id' => 'id']);
    }
}
