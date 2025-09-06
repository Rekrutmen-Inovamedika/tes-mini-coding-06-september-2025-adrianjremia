<?php


namespace app\models;



use Yii;
use app\models\Pasien;

/**
 * This is the model class for table "transaksi".
 *
 * @property int $id
 * @property int $pasien_id
 * @property int $total_harga
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Pasien $pasien
 * @property TransaksiObat[] $transaksiObats
 * @property TransaksiTindakan[] $transaksiTindakans
 */
class Transaksi extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'transaksi';
    }
    public $tindakan = [];
    public $obat = [];
    public $jumlah_obat = [];
    public $obat_1;
    public $obat_2;
    public $obat_3;
    public $jumlah_obat_1;
    public $jumlah_obat_2;
    public $jumlah_obat_3;
    public $harga;







    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pasien_id'], 'required'],
            [['pasien_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['jumlah_obat'], 'safe'],
            [['pasien_id'], 'exist', 'skipOnError' => true, 'targetClass' => Pasien::class, 'targetAttribute' => ['pasien_id' => 'id']],
        ];
    }

public function beforeSave($insert)
{
    if (parent::beforeSave($insert)) {
        $totalTindakan = 0;
        if (!empty($this->tindakanIds)) {
            foreach ($this->tindakanIds as $tindakanId) {
                $tindakan = Tindakan::findOne($tindakanId);
                if ($tindakan) {
                    $totalTindakan += $tindakan->harga;
                }
            }
        }

        $totalObat = 0;
        if ($this->obat1_id) {
            $obat = Obat::findOne($this->obat1_id);
            if ($obat) {
                $totalObat += $obat->harga * (int)$this->jumlah_obat1;
            }
        }
        if ($this->obat2_id) {
            $obat = Obat::findOne($this->obat2_id);
            if ($obat) {
                $totalObat += $obat->harga * (int)$this->jumlah_obat2;
            }
        }
        if ($this->obat3_id) {
            $obat = Obat::findOne($this->obat3_id);
            if ($obat) {
                $totalObat += $obat->harga * (int)$this->jumlah_obat3;
            }
        }

        $this->total_harga = $totalTindakan + $totalObat;

        return true;
    }
    return false;
}






    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pasien_id' => 'Nama Pasien',
            'total_harga' => 'Total Harga',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Pasien]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPasien()
    {
        return $this->hasOne(\app\models\Pasien::class, ['id' => 'pasien_id']);
    }

    /**
     * Gets query for [[TransaksiObats]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransaksiObats()
    {
        return $this->hasMany(TransaksiObat::class, ['transaksi_id' => 'id']);
    }
    public function getDetailTransaksiObat()
    {
        return $this->hasMany(TransaksiObat::class, ['transaksi_id' => 'id'])->with('obat');
    }
    // pada model Transaksi



    /**
     * Gets query for [[TransaksiTindakans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTransaksiTindakans()
    {
        return $this->hasMany(TransaksiTindakan::class, ['transaksi_id' => 'id']);
    }

    public function getTanggalLahir()
    {
        return $this->pasien->tanggal_lahir;
    }
    public function getNama()
    {
        return $this->pasien->nama;
    }

    public function getJenisKelamin()
    {
        return $this->pasien->jenis_kelamin;
    }

    public function getNik()
    {
        return $this->pasien->nik;
    }
    public function getNamaWilayah()
    {
        return $this->wilayah->nama;
    }



    public function getTindakans()
    {
        return $this->hasMany(Tindakan::className(), ['id' => 'tindakan_id'])
            ->viaTable('transaksi_tindakan', ['transaksi_id' => 'id']);
    }
}
