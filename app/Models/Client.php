<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Client
 * 
 * @property int $id
 * @property string|null $nom
 * @property string|null $prenom
 * @property string|null $tel
 * @property string|null $domicile
 * @property string|null $region
 * @property string|null $datemembre
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class Client extends Model
{
	protected $table = 'clients';

	protected $fillable = [
		'nom',
		'prenom',
		'tel',
		'domicile',
		'region',
		'datemembre'
	];
}
