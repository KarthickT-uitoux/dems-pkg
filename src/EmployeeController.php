<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Uitoux\EYatra\Employee;
use Uitoux\EYatra\Entity;
use Validator;
use Yajra\Datatables\Datatables;

class EmployeeController extends Controller {
	public function listEYatraEmployee(Request $r) {
		$employees = Employee::from('employees as e')
			->join('entities as grd', 'grd.id', 'e.grade_id')
			->leftJoin('employees as m', 'e.reporting_to_id', 'm.id')
			->join('outlets as o', 'o.id', 'e.outlet_id')
			->withTrashed()
			->select(
				'e.id',
				'e.code',
				'e.name',
				'o.code as outlet_code',
				'm.code as manager_code',
				'grd.name as grade',
				DB::raw('IF(e.deleted_at IS NULL, "Active","Inactive") as status')
			)
			->where('e.company_id', Auth::user()->company_id)
			->orderBy('e.code', 'asc');

		return Datatables::of($employees)
			->addColumn('action', function ($employee) {

				$img1 = asset('public/img/content/table/edit-yellow.svg');
				$img2 = asset('public/img/content/table/eye.svg');
				$img1_active = asset('public/img/content/table/edit-yellow-active.svg');
				$img2_active = asset('public/img/content/table/eye-active.svg');
				$img3 = asset('public/img/content/table/delete-default.svg');
				$img3_active = asset('public/img/content/table/delete-active.svg');
				return '
				<a href="#!/eyatra/employee/edit/' . $employee->id . '">
					<img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '">
				</a>
				<a href="#!/eyatra/employee/view/' . $employee->id . '">
					<img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '" >
				</a>
				<a href="javascript:;" data-toggle="modal" data-target="#delete_emp"
				onclick="angular.element(this).scope().deleteTrip(' . $employee->id . ')" dusk = "delete-btn" title="Delete">
                <img src="' . $img3 . '" alt="delete" class="img-responsive" onmouseover="this.src="' . $img3_active . '" onmouseout="this.src="' . $img3 . '" >
                </a>';

			})
			->make(true);
	}

	public function eyatraEmployeeFormData($employee_id = NULL) {

		if (!$employee_id) {
			$this->data['action'] = 'Add';
			$employee = new Employee;
			$this->data['success'] = true;
		} else {
			$this->data['action'] = 'Edit';
			$employee = Employee::find($employee_id);
			if (!$employee) {
				$this->data['success'] = false;
				$this->data['message'] = 'Employee not found';
			}
			$this->data['success'] = true;
		}
		$this->data['extras'] = [
			'manager_list' => Employee::getList(),
			'outlet_list' => Outlet::getList(),
			'grade_list' => Entity::getGradeList(),
		];
		$this->data['employee'] = $employee;

		return response()->json($this->data);
	}

	public function saveEYatraEmployee(Request $request) {
		//validation
		try {
			$validator = Validator::make($request->all(), [
				'code' => [
					'unique:employees,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
					'required:true',
				],
				'name' => [
					'required:true',
				],
			]);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$employee = new Employee;
				$employee->created_by = Auth::user()->id;
				$employee->created_at = Carbon::now();
				$employee->updated_at = NULL;
			} else {
				$employee = Employee::find($request->id);
				$employee->updated_by = Auth::user()->id;
				$employee->updated_at = Carbon::now();
			}
			$employee->fill($request->all());
			$employee->company_id = Auth::user()->company_id;
			if ($request->status == 0) {
				$employee->deleted_at = date('Y-m-d H:i:s');
				$employee->deleted_by = Auth::user()->id;
			} else {
				$employee->deleted_by = NULL;
				$employee->deleted_at = NULL;
			}
			$employee->save();
			DB::commit();
			$request->session()->flash('success', 'Employee saved successfully!');
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function viewEYatraEmployee($employee_id) {

		$trip = Trip::with([
			'visits',
			'visits.fromCity',
			'visits.toCity',
			'visits.travelMode',
			'visits.bookingMethod',
			'visits.bookingStatus',
			'visits.agent',
			'visits.status',
			'visits.managerVerificationStatus',
			'employee',
			'purpose',
			'status',
		])
			->find($trip_id);
		if (!$trip) {
			$this->data['success'] = false;
			$this->data['errors'] = ['Trip not found'];
			return response()->json($this->data);
		}
		$start_date = $trip->visits()->select(DB::raw('DATE_FORMAT(MIN(visits.date),"%d/%m/%Y") as start_date'))->first();
		$end_date = $trip->visits()->select(DB::raw('DATE_FORMAT(MIN(visits.date),"%d/%m/%Y") as start_date'))->first();
		$trip->start_date = $start_date->start_date;
		$trip->end_date = $start_date->end_date;
		$this->data['trip'] = $trip;
		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function deleteEYatraEmployee($employee_id) {
		$trip = Trip::where('id', $trip_id)->delete();
		if (!$trip) {
			return response()->json(['success' => false, 'errors' => ['Trip not found']]);
		}
		return response()->json(['success' => true]);
	}

}
