<?php

namespace Uitoux\EYatra;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NState extends Model {
	use SoftDeletes;
	protected $table = 'nstates';
	public function country() {
		return $this->belongsTo('App\Country');
	}

	public static function getList($country_id = NULL) {
		$data = [];
		$option = new NState;
		$option->name = 'Select State';
		$option->id = null;
		if (!$country_id) {
			$state_list = NState::select('id', 'name')->get();
			$data = $state_list->prepend($option);
			return $data;
			// return NState::select('id', 'name')->get();
		} else {
			$state_list = NState::select('id', 'name')->where('country_id', $country_id)->get();
			$data = $state_list->prepend($option);
			return $data;
			// return NState::select('id', 'name')->where('country_id', $country_id)->get();
		}
	}

	public function cities() {
		return $this->hasMany('Uitoux\EYatra\NCity', 'state_id');
	}

	public function travelModes() {
		return $this->belongsToMany('Uitoux\EYatra\Entity', 'state_agent_travel_mode', 'state_id', 'travel_mode_id');
	}

	public function agents() {
		return $this->belongsToMany('Uitoux\EYatra\Agent', 'state_agent_travel_mode', 'state_id', 'agent_id');
	}

}
