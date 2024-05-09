<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PayoutUser
 * 
 * @property int $idpayout_user
 * @property string|null $nom
 * @property string|null $prenom
 * @property string|null $tel
 * @property string|null $phone_money
 * @property string|null $montant
 * @property string|null $datepay
 * @property string|null $heure
 * @property string|null $trans_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class PayoutUser extends Model
{
	protected $table = 'payout_user';
	protected $primaryKey = 'idpayout_user';

	protected $fillable = [
		'nom',
		'prenom',
		'tel',
		'phone_money',
		'montant',
		'datepay',
		'heure',
		'trans_id'
	];
}
