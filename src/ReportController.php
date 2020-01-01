<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use DB;
use Excel;
use Illuminate\Http\Request;
use Uitoux\EYatra\LocalTrip;
use Yajra\Datatables\Datatables;

class ReportController extends Controller {

	public function eyatraOutstationFilterData() {
		$data['employee_list'] = collect(Employee::select(DB::raw('CONCAT(users.name, " / ", employees.code) as name'), 'employees.id')
				->leftJoin('users', 'users.entity_id', 'employees.id')
				->where('users.user_type_id', 3121)
				->where('employees.company_id', Auth::user()->company_id)
				->get())->prepend(['id' => '-1', 'name' => 'Select Employee Code/Name']);
		$data['purpose_list'] = collect(Entity::select('name', 'id')->where('entity_type_id', 501)->where('company_id', Auth::user()->company_id)->get())->prepend(['id' => '-1', 'name' => 'Select Purpose']);
		$data['outlet_list'] = collect(Outlet::select('name', 'id')->get())->prepend(['id' => '-1', 'name' => 'Select Outlet']);

		$data['success'] = true;
		return response()->json($data);
	}

	public function eyatraLocalFilterData() {
		$data['employee_list'] = collect(Employee::select(DB::raw('CONCAT(users.name, " / ", employees.code) as name'), 'employees.id')
				->leftJoin('users', 'users.entity_id', 'employees.id')
				->where('users.user_type_id', 3121)
				->where('employees.company_id', Auth::user()->company_id)
				->get())->prepend(['id' => '-1', 'name' => 'Select Employee Code/Name']);
		$data['purpose_list'] = collect(Entity::select('name', 'id')->where('entity_type_id', 501)->where('company_id', Auth::user()->company_id)->get())->prepend(['id' => '-1', 'name' => 'Select Purpose']);
		$data['outlet_list'] = collect(Outlet::select('name', 'id')->get())->prepend(['id' => '-1', 'name' => 'Select Outlet']);

		$data['success'] = true;
		return response()->json($data);
	}

	public function listOutstationTripReport(Request $r) {

		session(['employee_id' => $r->get('employee_id')]);
		session(['purpose_id' => $r->get('purpose_id')]);

		$trips = EmployeeClaim::join('trips', 'trips.id', 'ey_employee_claims.trip_id')
			->join('visits as v', 'v.trip_id', 'trips.id')
			->join('ncities as c', 'c.id', 'v.from_city_id')
			->join('employees as e', 'e.id', 'trips.employee_id')
		// ->join('outlets', 'outlets.id', 'e.outlet_id')
			->join('entities as purpose', 'purpose.id', 'trips.purpose_id')
			->join('configs as status', 'status.id', 'trips.status_id')
			->leftJoin('users', 'users.entity_id', 'trips.employee_id')
			->where('users.user_type_id', 3121)
			->select(
				'trips.id',
				'trips.number',
				DB::raw('DATE_FORMAT(trips.created_at,"%d/%m/%Y %h:%i %p") as created_date'),
				'e.code as ecode',
				'users.name as ename',
				DB::raw('CONCAT(DATE_FORMAT(trips.start_date,"%d-%m-%Y"), " to ", DATE_FORMAT(trips.end_date,"%d-%m-%Y")) as travel_period'),
				'purpose.name as purpose',
				'ey_employee_claims.total_amount',
				DB::raw('DATE_FORMAT(ey_employee_claims.claim_approval_datetime,"%d/%m/%Y %h:%i %p") as claim_approval_datetime')
			)
			->where('e.company_id', Auth::user()->company_id)
			->where(function ($query) use ($r) {
				if ($r->get('employee_id')) {
					$query->where("e.id", $r->get('employee_id'))->orWhere(DB::raw("-1"), $r->get('employee_id'));
				}
			})
			->where(function ($query) use ($r) {
				if ($r->get('purpose_id')) {
					$query->where("purpose.id", $r->get('purpose_id'))->orWhere(DB::raw("-1"), $r->get('purpose_id'));
				}
			})
			->where(function ($query) use ($r) {
				if (!empty($r->from_date)) {
					$query->where('trips.start_date', date('Y-m-d', strtotime($r->from_date)));
				}
			})
			->where(function ($query) use ($r) {
				if (!empty($r->to_date)) {
					$query->where('trips.end_date', date('Y-m-d', strtotime($r->to_date)));
				}
			})
			->where('ey_employee_claims.status_id', 3026)
			->groupBy('trips.id')
			->orderBy('trips.created_at', 'desc');

		return Datatables::of($trips)
			->addColumn('action', function ($trip) {

				$img2 = asset('public/img/content/yatra/table/view.svg');
				$img2_active = asset('public/img/content/yatra/table/view-active.svg');

				return '
				<a href="#!/trip/claim/view/' . $trip->id . '">
					<img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '" >
				</a>';

			})
			->make(true);
	}

	public function outstationTripExport() {

		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 0);

		$employee_id = session('employee_id');
		$purpose_id = session('purpose_id');
		// dd($employee_id, $purpose_id);

		$trips = EmployeeClaim::join('trips', 'trips.id', 'ey_employee_claims.trip_id')
			->join('visits as v', 'v.trip_id', 'trips.id')
			->join('ncities as c', 'c.id', 'v.from_city_id')
			->join('employees as e', 'e.id', 'trips.employee_id')
		// ->join('outlets', 'outlets.id', 'e.outlet_id')
			->join('entities as purpose', 'purpose.id', 'trips.purpose_id')
			->join('configs as status', 'status.id', 'trips.status_id')
			->leftJoin('users', 'users.entity_id', 'trips.employee_id')
			->where('users.user_type_id', 3121)
			->select(
				'trips.id',
				'trips.number',
				DB::raw('DATE_FORMAT(trips.created_at,"%d/%m/%Y %h:%i %p") as created_date'),
				'e.code as ecode',
				'users.name as ename',
				DB::raw('CONCAT(DATE_FORMAT(trips.start_date,"%d-%m-%Y"), " to ", DATE_FORMAT(trips.end_date,"%d-%m-%Y")) as travel_period'),
				'purpose.name as purpose',
				'ey_employee_claims.total_amount',
				DB::raw('DATE_FORMAT(ey_employee_claims.claim_approval_datetime,"%d/%m/%Y %h:%i %p") as claim_approval_datetime')
			)
			->where('e.company_id', Auth::user()->company_id)
			->where('ey_employee_claims.status_id', 3026)
			->groupBy('trips.id');

		if ($employee_id != '-1') {
			$trips = $trips->where("e.id", $employee_id);
		}
		if ($purpose_id != '-1') {
			$trips = $trips->where("purpose.id", $purpose_id);
		}
		$trips = $trips->get();

		$trips_header = ['Trip', 'Employee Code', 'Employee Name', 'Travel Period', 'Purpose', 'Total Amount', 'Claim Approved Date'];
		$trips_details = array();
		if ($trips) {
			foreach ($trips as $key => $trip) {
				$trips_detail = array();
				$trips_detail['trip_id'] = $trip->number;
				$trips_detail['emp_code'] = $trip->ecode;
				$trips_detail['emp_name'] = $trip->ename;
				$trips_detail['travel_period'] = $trip->travel_period;
				$trips_detail['purpose'] = $trip->purpose;
				$trips_detail['total_amount'] = $trip->total_amount;
				$trips_detail['calim_approve_date'] = $trip->claim_approval_datetime;

				$trips_details[] = $trips_detail;
			}
		}

		// dd($trips_header, $trips_details);
		Excel::create('Outstation Trip Report', function ($excel) use ($trips_header, $trips_details) {
			$excel->sheet('Outstation Trip Report', function ($sheet) use ($trips_header, $trips_details) {
				$sheet->fromArray($trips_details, NULL, 'A2');
				$sheet->row(1, $trips_header);
				$sheet->row(1, function ($row) {
					$row->setBackground('#07c63a');
				});
			});
		})->export('xls');

	}

	public function listLocalTripReport(Request $r) {

		session(['local_employee_id' => $r->get('employee_id')]);
		session(['local_purpose_id' => $r->get('purpose_id')]);

		$trips = LocalTrip::from('local_trips')
			->join('employees as e', 'e.id', 'local_trips.employee_id')
			->join('entities as purpose', 'purpose.id', 'local_trips.purpose_id')
			->join('configs as status', 'status.id', 'local_trips.status_id')
			->leftJoin('users', 'users.entity_id', 'local_trips.employee_id')
			->where('users.user_type_id', 3121)
			->select(
				'local_trips.id',
				'local_trips.number',
				DB::raw('DATE_FORMAT(local_trips.created_at,"%d/%m/%Y %h:%i %p") as created_date'),
				'e.code as ecode',
				'users.name as ename',
				DB::raw('CONCAT(DATE_FORMAT(local_trips.start_date,"%d-%m-%Y"), " to ", DATE_FORMAT(local_trips.end_date,"%d-%m-%Y")) as travel_period'),
				'purpose.name as purpose',
				'local_trips.claim_amount as total_amount',
				DB::raw('DATE_FORMAT(local_trips.claim_approval_datetime,"%d/%m/%Y %h:%i %p") as claim_approval_datetime')

			)
			->where('e.company_id', Auth::user()->company_id)
			->where(function ($query) use ($r) {
				if ($r->get('employee_id')) {
					$query->where("e.id", $r->get('employee_id'))->orWhere(DB::raw("-1"), $r->get('employee_id'));
				}
			})
			->where(function ($query) use ($r) {
				if ($r->get('purpose_id')) {
					$query->where("purpose.id", $r->get('purpose_id'))->orWhere(DB::raw("-1"), $r->get('purpose_id'));
				}
			})
			->where(function ($query) use ($r) {
				if (!empty($r->from_date)) {
					$query->where('local_trips.start_date', date('Y-m-d', strtotime($r->from_date)));
				}
			})
			->where(function ($query) use ($r) {
				if (!empty($r->to_date)) {
					$query->where('local_trips.end_date', date('Y-m-d', strtotime($r->to_date)));
				}
			})
			->where('local_trips.status_id', 3026)
			->groupBy('local_trips.id')
			->orderBy('local_trips.created_at', 'desc');

		return Datatables::of($trips)
			->addColumn('action', function ($trip) {

				$img2 = asset('public/img/content/yatra/table/view.svg');
				$img2_active = asset('public/img/content/yatra/table/view-active.svg');
				return '
				<a href="#!/local-trip/detail-view/' . $trip->id . '">
					<img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '" >
				</a>';

			})
			->make(true);
	}

	public function localTripExport() {

		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', 0);

		$employee_id = session('local_employee_id');
		$purpose_id = session('local_purpose_id');
		// dd($employee_id, $purpose_id);

		$trips = LocalTrip::from('local_trips')
			->join('employees as e', 'e.id', 'local_trips.employee_id')
			->join('entities as purpose', 'purpose.id', 'local_trips.purpose_id')
			->join('configs as status', 'status.id', 'local_trips.status_id')
			->leftJoin('users', 'users.entity_id', 'local_trips.employee_id')
			->where('users.user_type_id', 3121)
			->select(
				'local_trips.id',
				'local_trips.number',
				DB::raw('DATE_FORMAT(local_trips.created_at,"%d/%m/%Y %h:%i %p") as created_date'),
				'e.code as ecode',
				'users.name as ename',
				DB::raw('CONCAT(DATE_FORMAT(local_trips.start_date,"%d-%m-%Y"), " to ", DATE_FORMAT(local_trips.end_date,"%d-%m-%Y")) as travel_period'),
				'purpose.name as purpose',
				'local_trips.claim_amount as total_amount',
				DB::raw('DATE_FORMAT(local_trips.claim_approval_datetime,"%d/%m/%Y %h:%i %p") as claim_approval_datetime')

			)
			->where('e.company_id', Auth::user()->company_id)
			->where('local_trips.status_id', 3026)
			->groupBy('local_trips.id')
			->orderBy('local_trips.created_at', 'desc');

		if ($employee_id != '-1') {
			$trips = $trips->where("e.id", $employee_id);
		}
		if ($purpose_id != '-1') {
			$trips = $trips->where("purpose.id", $purpose_id);
		}
		$trips = $trips->get();

		$trips_header = ['Trip', 'Employee Code', 'Employee Name', 'Travel Period', 'Purpose', 'Total Amount', 'Claim Approved Date'];
		$trips_details = array();
		if ($trips) {
			foreach ($trips as $key => $trip) {
				$trips_detail = array();
				$trips_detail['trip_id'] = $trip->number;
				$trips_detail['emp_code'] = $trip->ecode;
				$trips_detail['emp_name'] = $trip->ename;
				$trips_detail['travel_period'] = $trip->travel_period;
				$trips_detail['purpose'] = $trip->purpose;
				$trips_detail['total_amount'] = $trip->total_amount;
				$trips_detail['calim_approve_date'] = $trip->claim_approval_datetime;

				$trips_details[] = $trips_detail;
			}
		}

		// dd($trips_header, $trips_details);
		Excel::create('Local Trip Report', function ($excel) use ($trips_header, $trips_details) {
			$excel->sheet('Local Trip Report', function ($sheet) use ($trips_header, $trips_details) {
				$sheet->fromArray($trips_details, NULL, 'A2');
				$sheet->row(1, $trips_header);
				$sheet->row(1, function ($row) {
					$row->setBackground('#07c63a');
				});
			});
		})->export('xls');

	}
}
