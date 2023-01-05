<?php

namespace Uitoux\EYatra;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviationApprover extends Model {
	use SoftDeletes;
	protected $table = 'deviation_approvers';
	protected $fillable = [
		// 'id',
		'deviation_employee_id',
		'created_by',
	];
}
