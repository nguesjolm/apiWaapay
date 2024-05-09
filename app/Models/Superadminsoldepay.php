<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Superadminsoldepay
 * 
 * @property int $idsuperAdminsoldePay
 * @property string|null $typepay
 * @property string|null $montant
 * @property string|null $frais_admin
 * @property string|null $frais_cinetpay
 * @property string|null $nom_client
 * @property string|null $tel_client
 * @property string|null $transaction_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Superadminsoldepay extends Model
{
	protected $table = 'superadminsoldepay';
	protected $primaryKey = 'idsuperAdminsoldePay';

	protected $fillable = [
		'typepay',
		'montant',
		'frais_admin',
		'frais_cinetpay',
		'nom_client',
		'tel_client',
		'transaction_id'
	];
}
