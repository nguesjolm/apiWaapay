<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserBack
 * 
 * @property int $id
 * @property string|null $payment
 * @property string|null $parametres
 * @property string|null $solde
 * @property string|null $passwordfirst
 * @property int|null $user_id
 * @property string|null $locked
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class UserBack extends Model
{
	protected $table = 'user_backs';

	protected $casts = [
		'user_id' => 'int'
	];

	protected $fillable = [
		'payment',
		'parametres',
		'solde',
		'passwordfirst',
		'user_id',
		'locked'
	];
}
