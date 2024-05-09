<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Paiement
 * 
 * @property int $id_paiement
 * @property string|null $transaction_id
 * @property string|null $type_paiement
 * @property string|null $montantpay
 * @property string|null $description
 * @property string|null $montant
 * @property int $fee_apps
 * @property int $fee_cinetpay
 * @property string|null $client_name
 * @property string|null $client_surname
 * @property string|null $client_phone
 * @property string|null $client_email
 * @property string|null $state
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Paiement extends Model
{
	protected $table = 'paiement';
	protected $primaryKey = 'id_paiement';

	protected $casts = [
		'fee_apps' => 'int',
		'fee_cinetpay' => 'int'
	];

	protected $fillable = [
		'transaction_id',
		'type_paiement',
		'montantpay',
		'description',
		'montant',
		'fee_apps',
		'fee_cinetpay',
		'client_name',
		'client_surname',
		'client_phone',
		'client_email',
		'state'
	];
}
