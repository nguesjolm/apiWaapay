<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class About
 * 
 * @property int $id
 * @property string|null $nom
 * @property string|null $logo
 * @property string|null $tel
 * @property string|null $email
 * @property string|null $adresse
 * @property string|null $details
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class About extends Model
{
	protected $table = 'about';

	protected $fillable = [
		'nom',
		'logo',
		'tel',
		'email',
		'adresse',
		'details'
	];
}
