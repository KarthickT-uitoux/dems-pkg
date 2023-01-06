<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Entrust;
use Illuminate\Http\Request;
use Uitoux\EYatra\NCity;
use Uitoux\EYatra\NCountry;
use Uitoux\EYatra\NState;
use Validator;
use Yajra\Datatables\Datatables;

class CityController extends Controller {

	public function listEYatraCity(Request $r) {
		if (!empty($r->country)) {
			$country = $r->country;
		} else {
			$country = null;
		}

		if (!empty($r->state_id)) {
			$state_id = $r->state_id;
		} else {
			$state_id = null;
		}
		if (!empty($r->status)) {
			$status = $r->status;
		} else {
			$status = null;
		}
		$cities = NCity::withTrashed()->join('nstates', 'nstates.id', 'ncities.state_id')
			->leftjoin('countries', 'countries.id', 'nstates.country_id')
			->leftjoin('entities', 'entities.id', 'ncities.category_id')
			->select(
				'ncities.id',
				'ncities.name as city_name',
				'nstates.name as state_name',
				'countries.name as country_name',
				'entities.name',
				DB::raw('IF(ncities.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where('countries.company_id', Auth::user()->company_id)
			->where('ncities.company_id', Auth::user()->company_id)
			->orderBy('ncities.id', 'asc')
			->where(function ($query) use ($r, $country) {
				if (!empty($country)) {
					$query->where('nstates.country_id', $country);
				}

			})
			->where(function ($query) use ($r, $state_id) {
				if (!empty($state_id)) {
					$query->where('nstates.id', $state_id);
				}

			})
			->where(function ($query) use ($r, $status) {
				if ($status == '2') {
					$query->whereNull('ncities.deleted_at');
				} elseif ($status == '1') {
					$query->whereNotNull('ncities.deleted_at');
				}
			})
		;
		// dd($cities);
		return Datatables::of($cities)
			->addColumn('action', function ($city) {

				$img1 = asset('public/img/content/yatra/table/edit.svg');
				$img1_active = asset('public/img/content/yatra/table/edit-active.svg');
				$img2 = asset('public/img/content/yatra/table/view.svg');
				$img2_active = asset('public/img/content/yatra/table/view-active.svg');
				$img3 = asset('public/img/content/yatra/table/delete.svg');
				$img3_active = asset('public/img/content/yatra/table/delete-active.svg');

				$action = '';
				$edit_class = "visibility:hidden";
				if (Entrust::can('eyatra-city-edit')) {
					$edit_class = "";
				}

				$delete_class = "visibility:hidden";
				if (Entrust::can('eyatra-city-delete')) {
					$delete_class = "";
				}

				$action = '';

				$action .= '<a style="' . $edit_class . '" href="#!/city/edit/' . $city->id . '">
					<img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '">
				</a> ';

				$action .= '<a href="#!/city/view/' . $city->id . '">
					<img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '" >
				</a> ';

				$action .= '<a style="' . $delete_class . '" href="javascript:;" data-toggle="modal" data-target="#delete_city"
				onclick="angular.element(this).scope().deleteCityConfirm(' . $city->id . ')" dusk = "delete-btn" title="Delete">
                <img src="' . $img3 . '" alt="delete" class="img-responsive" onmouseover=this.src="' . $img3_active . '" onmouseout=this.src="' . $img3 . '" >
                </a> ';

				return $action;

			})
			->addColumn('status', function ($city) {
				if ($city->status == 'Inactive') {
					return '<span style="color:red">In Active</span>';
				} else {
					return '<span style="color:green">Active</span>';
				}

			})

			->make(true);
	}

	public function searchCity(Request $request) {

		$key = $request->key;

		$list = NCity::from('ncities')
			->join('nstates as s', 's.id', 'ncities.state_id')
			->select(
				'ncities.id',
				DB::raw('CONCAT(ncities.name," - ",s.name) as name'),
				's.name as state_name'
			)
			->where('company_id', Auth::user()->company_id)
			->where(function ($q) use ($key) {
				$q->where('ncities.name', 'like', '%' . $key . '%')
				;
			})
			->get();
		return response()->json($list);
	}

	public function getCityList(Request $request) {
		return NCity::getList($request->state_id);
	}

	public function eyatraCityFormData($city_id = NULL) {
		if (!$city_id) {
			$this->data['action'] = 'Add';
			$city = new NCity;
			$this->data['status'] = 'Active';
			$this->data['guest_house_status'] = 'Inactive';

			$this->data['success'] = true;
		} else {
			$this->data['action'] = 'Edit';
			$city = NCity::withTrashed()->find($city_id);

			if (!$city) {
				$this->data['success'] = false;
				$this->data['message'] = 'City not found';
			}

			if ($city->deleted_at == NULL) {
				$this->data['status'] = 'Active';
			} else {
				$this->data['status'] = 'Inactive';
			}
			$this->data['guest_house_status'] = $city->guest_house_status == 1 ? 'Active' : 'Inactive';
		}
		// $option = new NState;
		// $option->name = 'Select State';
		// $option->id = null;
		$this->data['category_list'] = $category_list = collect(Entity::cityCategoryList())->prepend(['id' => '', 'name' => 'Select Category']);

		$this->data['extras'] = [
			'country_list' => NCountry::getList(),
			'category_list' => $category_list,
			'state_list' => $this->data['action'] == 'Add' ? [] : NState::getList($city->state->country_id),
			// 'city_list' => NCity::getList(),
		];
		// dd($city->state->country_id);
		// dd($this->data['extras']);
		$this->data['city'] = $city;
		$this->data['success'] = true;

		return response()->json($this->data);
	}

	public function eyatraCityFilterData() {

		$option = new NCountry;
		$option->name = 'Select Country';
		$option->id = null;
		$this->data['country_list'] = $country_list = NCountry::select('name', 'id')->where('company_id', Auth::user()->company_id)->get()->prepend($option);
		$this->data['state_list'] = NState::getList();
		$this->data['status_list'] = array(
			array('name' => "Select Status", 'id' => null),
			array('name' => "All", 'id' => "-1"),
			array('name' => "Active", 'id' => "2"),
			array('name' => "Inactive", 'id' => "1"),
		);
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function saveEYatraCity(Request $request) {
		//validation
		// dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'City Name is required',
				'name.unique' => ' City Name has already been taken',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required',
					'unique:ncities,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',state_id,' . $request->state_id,
					'max:191',
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}
			DB::beginTransaction();

			$city = NCity::firstOrNew([
				// 'company_id' => Auth::user()->company_id,
				'id' => $request->id,
				// 'name' => $request->name,
				// 'state_id' => $request->state_id,
			]);

			if (!$request->id) {

				$city->created_by = Auth::user()->id;
				$city->created_at = Carbon::now();
				$city->updated_at = NULL;
			} else {
				$city->updated_by = Auth::user()->id;
				$city->updated_at = Carbon::now();
				// $city->travelModes()->sync([]);
			}
			$city->company_id = Auth::user()->company_id;
			$city->fill($request->all());
			if ($request->status == 'Active') {
				$city->deleted_at = NULL;
				$city->deleted_by = NULL;
			} else {
				$city->deleted_at = date('Y-m-d H:i:s');
				$city->deleted_by = Auth::user()->id;

			}
			$city->guest_house_status = (isset($request->guest_house_status) && $request->guest_house_status == 'Active') ? 1 : 0;
			$city->category_id = $request->category_id;

			$city->save();
			// $city->company_id = Auth::user()->company_id;
			$activity['entity_id'] = $city->id;
			$activity['entity_type'] = "City";
			$activity['details'] = empty($request->id) ? "City is added" : "City is updated";
			$activity['activity'] = empty($request->id) ? "add" : "edit";
			$activity_log = ActivityLog::saveLog($activity);
			//SAVING state_agent_travel_mode
			// if (count($request->travel_modes) > 0) {
			// 	foreach ($request->travel_modes as $travel_mode => $pivot_data) {
			// 		if (!isset($pivot_data['agent_id'])) {
			// 			continue;
			// 		}
			// 		if (!isset($pivot_data['service_charge'])) {
			// 			continue;
			// 		}
			// 		$state->travelModes()->attach($travel_mode, $pivot_data);
			// 	}
			// }

			DB::commit();
			$request->session()->flash('success', 'State saved successfully!');
			if (empty($request->id)) {
				return response()->json(['success' => true, 'message' => 'City Added successfully']);
			} else {
				return response()->json(['success' => true, 'message' => 'City Updated Successfully']);
			}
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function viewEYatraCity($city_id) {

		$city = NCity::withTrashed()->join('nstates', 'nstates.id', 'ncities.state_id')
			->leftjoin('entities', 'entities.id', 'ncities.category_id')
			->leftjoin('countries', 'countries.id', 'nstates.country_id')
			->select(
				'ncities.id',
				'ncities.name as city_name',
				'nstates.name as state_name',
				'entities.name as category_name',
				'countries.name as country_name',
				DB::raw('IF(ncities.deleted_at IS NULL,"Active","Inactive") as status')
			)
			->where('ncities.id', $city_id)->first();
		$this->data['city'] = $city;
		$this->data['action'] = 'View';
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function deleteEYatraCity($city_id) {
		$city = NCity::withTrashed()->where('id', $city_id)->first();
		$activity['entity_id'] = $city->id;
		$activity['entity_type'] = "City";
		$activity['details'] = "City is deleted";
		$activity['activity'] = "delete";
		$activity_log = ActivityLog::saveLog($activity);
		$city->forceDelete();
		if (!$city) {
			return response()->json(['success' => false, 'errors' => ['City not found']]);
		}
		return response()->json(['success' => true]);
	}
	public function getStateList(Request $request) {
		return NState::getList($request->country_id);
	}

}
