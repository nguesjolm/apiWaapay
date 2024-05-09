<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Setting
 * 
 * @property int $idsettings
 * @property string|null $payday
 * @property string|null $monthpay
 * @property string|null $inscriptionpay
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Setting extends Model
{
	protected $table = 'settings';
	protected $primaryKey = 'idsettings';

	protected $fillable = [
		'payday',
		'monthpay',
		'inscriptionpay'
	];
}
