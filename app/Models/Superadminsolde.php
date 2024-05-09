<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Superadminsolde
 * 
 * @property int $idsuperAdmin
 * @property int|null $solde
 * @property int|null $soldeGyms
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Superadminsolde extends Model
{
	protected $table = 'superadminsolde';
	protected $primaryKey = 'idsuperAdmin';

	protected $casts = [
		'solde' => 'int',
		'soldeGyms' => 'int'
	];

	protected $fillable = [
		'solde',
		'soldeGyms'
	];
}
