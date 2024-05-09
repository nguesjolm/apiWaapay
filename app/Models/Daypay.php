<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Daypay
 * 
 * @property int $iddaypay
 * @property string|null $nom
 * @property string|null $prenom
 * @property string|null $tel
 * @property string|null $datepay
 * @property string|null $montant
 * @property string|null $frais
 * @property string|null $debut
 * @property string|null $fin
 * @property string|null $transaction_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Daypay extends Model
{
	protected $table = 'daypay';
	protected $primaryKey = 'iddaypay';

	protected $fillable = [
		'nom',
		'prenom',
		'tel',
		'datepay',
		'montant',
		'frais',
		'debut',
		'fin',
		'transaction_id'
	];
}
