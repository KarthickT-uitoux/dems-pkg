<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use App\Role;
use App\User;
use Auth;
use Carbon\Carbon;
use DateTime;
use DB;
use Entrust;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Uitoux\EYatra\BankDetail;
use Uitoux\EYatra\Business;
use Uitoux\EYatra\ChequeDetail;
use Uitoux\EYatra\Config;
use Uitoux\EYatra\Department;
use Uitoux\EYatra\Designation;
use Uitoux\EYatra\Employee;
use Uitoux\EYatra\Entity;
use Uitoux\EYatra\ImportJob;
use Uitoux\EYatra\Lob;
use Uitoux\EYatra\Sbu;
use Uitoux\EYatra\SoapController;
use Uitoux\EYatra\WalletDetail;
use Validator;
use Yajra\Datatables\Datatables;

class EmployeeController extends Controller {

	public function __construct(SoapController $getSoap) {
		$this->getSoap = $getSoap;
	}

	public function filterEYatraEmployee() {
		$this->data['outlet_list'] = $outlet_list = collect(Outlet::getList())->prepend(['id' => '', 'name' => 'Select Outlet']);
		$this->data['grade_list'] = $grade_list = collect(Entity::getGradeList())->prepend(['id' => '', 'name' => 'Select Grade']);
		$this->data['role_list'] = $role_list = collect(Role::getList())->prepend(['id' => '', 'name' => 'Select Role']);

		$employees = Employee::select('reporting_to_id')
			->distinct()
			->whereNotNull('reporting_to_id')
			->where('employees.company_id', Auth::user()->company_id)
			->get()->toArray();
		$employee_ids = array_column($employees, 'reporting_to_id');

		$this->data['manager_list'] = collect(Employee::join('users', 'users.entity_id', 'employees.id')
				->where('users.user_type_id', 3121)
				->where('users.company_id', Auth::user()->company_id)
				->whereIn('employees.id', $employee_ids)
				->select('employees.id', 'users.name')
				->get())->prepend(['id' => '', 'name' => 'Select Manager']);
		$this->data['filter_manager_id'] = '';
		$this->data['filter_outlet_id'] = '-1';
		return response()->json($this->data);
	}

	public function listEYatraEmployee(Request $r) {
		// dd($r->all());
		if (!empty($r->outlet)) {
			$outlet = $r->outlet;
		} else {
			$outlet = null;
		}
		if (!empty($r->role)) {
			$role = $r->role;
		} else {
			$role = null;
		}
		if (!empty($r->manager)) {
			$manager = $r->manager;
		} else {
			$manager = null;
		}

		if (!empty($r->grade)) {
			$grade = $r->grade;
		} else {
			$grade = null;
		}
		$employees = Employee::withTrashed()->from('employees as e')
			->join('entities as grd', 'grd.id', 'e.grade_id')
			->leftJoin('employees as m', 'e.reporting_to_id', 'm.id')
			->join('outlets as o', 'o.id', 'e.outlet_id')
		// ->join('users as u', 'u.entity_id', 'e.id')

			->join('users as u', function ($join) {
				$join->on('u.entity_id', '=', 'e.id')
					->where('u.user_type_id', 3121);
			})
			->leftJoin('users as mngr', function ($join) {
				$join->on('mngr.entity_id', 'm.id')
					->where('mngr.user_type_id', 3121);
			})
		// ->leftJoin('users as mngr', 'mngr.entity_id', 'm.id')
			->leftJoin('role_user', 'role_user.user_id', 'u.id')
		// ->where('users.user_type_id', 3121)
			->withTrashed()
			->select(
				'e.id',
				'e.code',
				'u.name',
				'o.code as outlet_code',
				DB::raw('CONCAT(m.code," / ",mngr.name) as manager_code'),
				// DB::raw('IF(m.code IS NULL,"--",m.code) as manager_code'),
				// DB::raw('IF(mngr.name IS NULL,"--",mngr.name) as manager_name'),
				'grd.name as grade',
				DB::raw('IF(e.deleted_at IS NULL, "Active","Inactive") as status')
			)
			->where(function ($query) use ($r, $outlet) {
				if (!empty($outlet)) {
					$query->where('o.id', $outlet);
				}
			})
			->where(function ($query) use ($r, $manager) {
				if (!empty($manager)) {
					$query->where('e.reporting_to_id', $manager);
				}
			})
			->where(function ($query) use ($r, $role) {
				if (!empty($role)) {
					$query->where('role_user.role_id', $role);
				}
			})
			->where(function ($query) use ($r, $grade) {
				if (!empty($grade)) {
					$query->where('grd.id', $grade);
				}
			})
		// ->where(function ($query) use ($r, $grade) {

		// 	$query->where('u.user_type_id', 3121)->orWhere('mngr.user_type_id', 3121);

		// })
		// ->
		// ->orWhere('mngr.user_type_id', 3121)
			->where('e.company_id', Auth::user()->company_id)
			->orderBy('e.code', 'asc')
			->groupBy('e.id')
		;
		// dd($employees);
		return Datatables::of($employees)
			->addColumn('action', function ($employee) {

				$img1 = asset('public/img/content/yatra/table/edit.svg');
				$img2 = asset('public/img/content/yatra/table/view.svg');
				$img1_active = asset('public/img/content/yatra/table/edit-active.svg');
				$img2_active = asset('public/img/content/yatra/table/view-active.svg');
				$img3 = asset('public/img/content/yatra/table/delete.svg');
				$img3_active = asset('public/img/content/yatra/table/delete-active.svg');

				$action = '';
				$edit_class = "visibility:hidden";
				if (Entrust::can('eyatra-employee-edit')) {
					$edit_class = "";
				}
				$delete_class = "visibility:hidden";
				if (Entrust::can('eyatra-employee-delete')) {
					$delete_class = "";
				}

				$action .= '<a style="' . $edit_class . '" href="#!/employee/edit/' . $employee->id . '">
					<img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '">
				</a> ';
				$action .= '<a href="#!/employee/view/' . $employee->id . '">
					<img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '" >
				</a> ';
				$action .= '<a style="' . $delete_class . '"href="javascript:;" data-toggle="modal" data-target="#delete_emp"
				onclick="angular.element(this).scope().deleteEmployee(' . $employee->id . ')" dusk = "delete-btn" title="Delete">
                <img src="' . $img3 . '" alt="delete" class="img-responsive" onmouseover=this.src="' . $img3_active . '" onmouseout=this.src="' . $img3 . '" >
                </a>';

				return $action;
			})
			->addColumn('status', function ($agent) {
				if ($agent->status == 'Inactive') {
					return '<span style="color:#ea4335;">Inactive</span>';
				} else {
					return '<span style="color:#63ce63;">Active</span>';
				}
			})
			->make(true);
	}

	public function eyatraEmployeeFormData($employee_id = NULL) {

		if (!$employee_id) {
			$this->data['action'] = 'Add';
			$employee = new Employee;
			$employee['date_of_birth'] = date('Y-m-d');
			$employee['date_of_joining'] = date('Y-m-d');
			//dd($employee);
			$this->data['success'] = true;
		} else {
			$this->data['action'] = 'Edit';
			$employee = Employee::withTrashed()->with('sbu', 'department', 'bankDetail', 'reportingTo', 'walletDetail', 'user', 'chequeDetail')->find($employee_id);
			$employee->reporting_to_name = $employee->reportingTo ? $employee->reportingTo->user : '';
			//dd($employee);
			if (!$employee) {
				$this->data['success'] = false;
				$this->data['message'] = 'Employee not found';
			}
			$employee->roles = $employee->user->roles()->pluck('role_id')->toArray();
			$this->data['success'] = true;
		}
		$outlet_list = collect(Outlet::getList())->prepend(['id' => '', 'name' => 'Select Outlet']);
		$payment_mode_list = collect(Config::paymentModeList())->prepend(['id' => '', 'name' => 'Select Payment Mode']);
		$wallet_mode_list = collect(Entity::walletModeList())->prepend(['id' => '', 'name' => 'Select Wallet Type']);
		$role_list = collect(Role::getList());
		$grade_list = collect(Entity::getGradeList())->prepend(['id' => '', 'name' => 'Select Grade']);
		$designation_list = [];
		// dd($designation_list);
		$business_list = collect(Business::select('name', 'id')->where('company_id', Auth::user()->company_id)->get())->prepend(['id' => '', 'name' => 'Select Business']);
		$department_list = [];
		$lob_list = collect(Lob::select('name', 'id')->where('company_id', Auth::user()->company_id)->get())->prepend(['id' => '', 'name' => 'Select LOB']);
		$sbu_list = [];
		$this->data['extras'] = [
			'manager_list' => Employee::getList(),
			'outlet_list' => $outlet_list,
			'payment_mode_list' => $payment_mode_list,
			'wallet_mode_list' => $wallet_mode_list,
			'role_list' => $role_list,
			'business_list' => $business_list,
			'department_list' => $department_list,
			'lob_list' => $lob_list,
			'sbu_list' => $sbu_list,
			'grade_list' => $grade_list,
			'designation_list' => $designation_list,
		];

		$is_grade_active = Entity::where('entity_type_id', 500)->where('id', $employee->grade_id)->first();
		$is_grade_active = $is_grade_active ? '1' : '0';
		$this->data['is_grade_active'] = $is_grade_active;
		$this->data['employee'] = $employee;

		return response()->json($this->data);
	}

	public function getManagerByOutlet(Request $r) {
		$employees = Employee::select('reporting_to_id')
			->distinct()
			->whereNotNull('reporting_to_id')
			->where('employees.company_id', Auth::user()->company_id)
			->get()->toArray();
		$employee_ids = array_column($employees, 'reporting_to_id');
		$manager_list = collect(Employee::select('employees.id', 'users.name')
				->join('users', 'users.entity_id', 'employees.id')
				->where('users.user_type_id', 3121)
				->where('users.company_id', Auth::user()->company_id)
				->whereIn('employees.id', $employee_ids)
				->where(function ($query) use ($r) {
					if ($r->outlet_id != '-1') {
						$query->where('employees.outlet_id', $r->outlet_id);

					}
				})
				->get())->prepend(['id' => '', 'name' => 'Select Manager']);

		$data['manager_list'] = $manager_list;
		$data['success'] = true;
		return response()->json($data);
	}

	public function saveEYatraEmployee(Request $request) {
		//dd($request->all());
		//validation
		try {
			$error_messages = [
				'code.required' => "Employee Code is Required",
				'code.unique' => "Employee Code is already taken",
				'mobile_number.required' => "Mobile Number is Required",
				'username.required' => "Username is Required",
				'mobile_number.unique' => "Mobile Number is already taken",
				'username.unique' => "Username is already taken",
				'aadhar_no.unique' => "Aadhar Number is already taken",
				'pan_no.unique' => "PAN Number is already taken",
				// 'email.unique' => "Email is already taken",
			];

			$validator = Validator::make($request->all(), [
				'code' => [
					'unique:employees,code,' . $request->id . ',id',
					'required:true',
				],
				'mobile_number' => [
					'required:true',
					'unique:users,mobile_number,' . $request->user_id . ',id',
				],
				'name' => [
					'required:true',
				],
				'username' => [
					'required:true',
					'unique:users,username,' . $request->user_id . ',id',

				],
				// 'email' => [
				// 	'required:true',
				// 	'unique:users,email,' . $request->user_id . ',id',

				// ],
				'aadhar_no' => [
					'required:true',
					'unique:employees,aadhar_no,' . $request->id . ',id',

				],
				'pan_no' => [
					'required:true',
					'unique:employees,pan_no,' . $request->id . ',id',

				],

			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}
		
			if(!is_numeric($request->business_id)){
              return response()->json(['success' => false, 'errors' => ['Business Not Exist']]);
			}
			if(!is_numeric($request->lob_id)){
              return response()->json(['success' => false, 'errors' => ['Lob Not Exist']]);
			}
			if(!is_numeric($request->outlet_id)){
              return response()->json(['success' => false, 'errors' => ['Outlet Not Exist']]);
			}
			if(!is_numeric($request->grade_id)){
              return response()->json(['success' => false, 'errors' => ['Grade Not Exist']]);
			}
			if(!is_numeric($request->designation_id)){
              return response()->json(['success' => false, 'errors' => ['Designation Not Exist']]);
			}
			if(!is_numeric($request->reporting_to_id)){
              return response()->json(['success' => false, 'errors' => ['Reporting Manager Not Exist']]);
			}
			//ROLE VALIDATION
			$roles_valid = json_decode($request->roles);
			if (empty($roles_valid)) {
				return response()->json(['success' => false, 'errors' => ['Role is required']]);
			}

			if (strtotime($request->date_of_joining) <= strtotime($request->date_of_birth)) {
				return response()->json(['success' => false, 'errors' => ['Date of joining should be greater than date of birth']]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$employee = new Employee;
				$employee->created_by = Auth::user()->id;
				$employee->created_at = Carbon::now();
				$employee->updated_at = NULL;
			} else {
				$employee = Employee::withTrashed()->find($request->id);
				$employee->updated_by = Auth::user()->id;
				$employee->updated_at = Carbon::now();
			}
			$employee->fill($request->all());
			//dd($employee->fill($request->all()));
			$employee->company_id = Auth::user()->company_id;
			if ($request->status == 0) {
				$employee->deleted_at = date('Y-m-d H:i:s');
				$employee->deleted_by = Auth::user()->id;
			} else {
				$employee->deleted_by = NULL;
				$employee->deleted_at = NULL;
			}
			$employee->save();
			$activity['entity_id'] = $employee->id;
			$activity['entity_type'] = "Employee";
			$activity['details'] = empty($request->id) ? "Employee is  Added" : "Employee is  updated";
			$activity['activity'] = empty($request->id) ? "Add" : "Edit";
			$activity_log = ActivityLog::saveLog($activity);
			//USER ACCOUNT
			$user = User::withTrashed()->firstOrNew([
				'id' => $request->user_id,
			]);
			$user->mobile_number = $request->mobile_number;
			$user->entity_type = 0;
			$user->user_type_id = 3121;
			$user->company_id = Auth::user()->company_id;
			$user->entity_id = $employee->id;
			$user->fill($request->all());
			$user->email = $request->email;
			//dd($request->password_change);
			if ($request->password_change == 'Yes') {
				// if (!empty($request->user['password'])) {
				// 	$user->password = $request->user['password'];
				// }
				if (!empty($request->password)) {
					$user->password = $request->password;
				}
				$user->force_password_change = 1;
			} else {
				$user->force_password_change = 0;
			}
			if ($request->status == 0) {
				$user->deleted_at = date('Y-m-d H:i:s');
				$user->deleted_by = Auth::user()->id;
			} else {
				$user->deleted_by = NULL;
				$user->deleted_at = NULL;
			}
			$user->save();
//dd($user);
			//USER ROLE SYNC
			$user->roles()->sync(json_decode($request->roles));

			//BANK DETAIL SAVE
			if ($request->bank_name) {
				$bank_detail = BankDetail::firstOrNew(['entity_id' => $employee->id]);
				$bank_detail->fill($request->all());
				$bank_detail->detail_of_id = 3121;
				$bank_detail->entity_id = $employee->id;
				$bank_detail->account_type_id = 3243;
				$bank_detail->save();
			}
			//CHEQUE DETAIL SAVE
			if ($request->cheque_favour) {
				$cheque_detail = ChequeDetail::firstOrNew(['entity_id' => $employee->id]);
				$cheque_detail->fill($request->all());
				$cheque_detail->detail_of_id = 3121;
				$cheque_detail->entity_id = $employee->id;
				$cheque_detail->account_type_id = 3243;
				$cheque_detail->save();
			}

			//WALLET SAVE
			if ($request->type_id) {
				$wallet_detail = WalletDetail::firstOrNew(['entity_id' => $employee->id]);
				$wallet_detail->fill($request->all());
				$wallet_detail->wallet_of_id = 3121;
				$wallet_detail->entity_id = $employee->id;
				$wallet_detail->save();
			}
			DB::commit();
			// return response()->json(['success' => true]);
			if (empty($request->id)) {

				return response()->json(['success' => true, 'message' => ['Employee Added Successfully']]);
			} else {

				return response()->json(['success' => true, 'message' => ['Employee Updated Successfully']]);
			}
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function viewEYatraEmployee($employee_id) {

		$employee = Employee::withTrashed()->with([
			'Sbu',
			'Sbu.lob',
			'reportingTo',
			'outlet',
			'grade',
			'bankDetail',
			'chequeDetail',
			'walletDetail',
			'walletDetail.type',
			'user',
			'reportingTo.user',
			'paymentMode',
			'designation',
		])
			->find($employee_id);

		$dob = new DateTime($employee['date_of_birth']);
		$today_date = new DateTime('today');
		$employee['age'] = $dob->diff($today_date)->y;

		if (!$employee) {
			$this->data['success'] = false;
			$this->data['errors'] = ['Employee not found'];
			return response()->json($this->data);
		}
		$employee->roles = $employee->user->roles()->pluck('name')->toArray();
		$this->data['employee'] = $employee;

		$is_grade_active = Entity::where('entity_type_id', 500)->where('id', $employee->grade_id)->first();
		$is_grade_active = $is_grade_active ? '1' : '0';
		$this->data['is_grade_active'] = $is_grade_active;

		$this->data['success'] = true;
		return response()->json($this->data);
	}

	public function deleteEYatraEmployee($employee_id) {
		$user = User::withTrashed()->where('entity_id', $employee_id)->first();

		$e_name = DB::table('entity_types')->where('id', $user->entity_type_id)->first();
		$activity['entity_id'] = $user->id;
		$activity['entity_type'] = "Employee";
		$activity['details'] = "Employee is deleted";
		$activity['activity'] = "Delete";
		$activity_log = ActivityLog::saveLog($activity);
		$user->forceDelete();
		if (!$user) {
			return response()->json(['success' => false, 'errors' => ['User not found']]);
		}
		$employee = Employee::withTrashed()->where('id', $employee_id)->forcedelete();
		if (!$employee) {
			return response()->json(['success' => false, 'errors' => ['Employee not found']]);
		}
		return response()->json(['success' => true]);
	}

	public function searchManager(Request $r) {
		/*$key = $r->key;
			$manager_list = Employee::select(
				'name',
				'code',
				'employees.id'
			)
				->join('users as u', 'u.entity_id', 'employees.id')
				->where('u.user_type_id', 3121)
				->where(function ($q) use ($key) {
					$q->where('code', 'like', '%' . $key . '%')
					// ->where('name', 'like', '%' . $key . '%')
					;
				})
		*/
		$key = $r->key;
		$manager_list = Employee::leftJoin('users as emp_user', 'emp_user.entity_id', 'employees.id')->select(
			'emp_user.name',
			'employees.code',
			'employees.id'
		)
			->where(function ($q) use ($key) {
				$q->where('employees.code', 'like', '%' . $key . '%')
					->orWhere('emp_user.name', 'like', '%' . $key . '%')
				;
			})->where('emp_user.user_type_id', 3121)
			->where('employees.company_id', Auth::user()->company_id)
			->get();
		return response()->json($manager_list);
	}
	public function getDesignationByGrade(Request $request) {
		if (!empty($request->grade_id)) {

			$designation_list = collect(Designation::where('grade_id', $request->grade_id)->select('name', 'id')->get())->prepend(['id' => '', 'name' => 'Select Designation']);
		} else {
			$designation_list = [];
		}
		return response()->json(['designation_list' => $designation_list]);
	}
	public function getSbuByLob(Request $request) {
		//dd($request);
		if (!empty($request->lob_id)) {
			$sbu_list = collect(Sbu::where('lob_id', $request->lob_id)->select('name', 'id')->get())->prepend(['id' => '', 'name' => 'Select Sub Business']);
		} else {
			$sbu_list = [];
		}
		return response()->json(['sbu_list' => $sbu_list]);
	}
	public function getDepartmentByBusiness(Request $request) {
		//dd($request);
		if (!empty($request->business_id)) {
			$department_list = collect(Department::where('business_id', $request->business_id)->select('name', 'id')->get())->prepend(['id' => '', 'name' => 'Select Sub Business/Dept']);
		} else {
			$department_list = [];
		}
		//dd($department_list);
		return response()->json(['department_list' => $department_list]);
	}

	public function getImportJobsList(Request $request) {
		// dd($request->all());
		if (!empty($request->from_date)) {
			$start_date = str_replace('/', '-', $request->from_date);
			$start_date = date('Y-m-d', strtotime($start_date));
		} else {
			$start_date = null;
		}
		if (!empty($request->to_date)) {
			$end_date = str_replace('/', '-', $request->to_date);
			$end_date = date('Y-m-d', strtotime($end_date));
		} else {
			$end_date = null;
		}
		if (!empty($request->type)) {
			$type = $request->type;
		} else {
			$type = null;
		}
		$import_data = ImportJob::select(
			'import_jobs.*',
			DB::raw('DATE_FORMAT(import_jobs.created_at,"%d-%m-%Y %h:%i %p") as import_date'),
			'users.name as user_name',
			'status.name as job_status',
			'type.name as type'
		)
			->join('users', 'users.id', 'import_jobs.created_by')
			->join('configs as type', 'type.id', 'import_jobs.type_id')
			->join('configs as status', 'status.id', 'import_jobs.status_id')
			->where(function ($query) use ($request, $start_date, $end_date) {
				if (!empty($start_date) && !empty($end_date)) {
					$query->whereDate('import_jobs.created_at', '>=', $start_date)
						->whereDate('import_jobs.created_at', '<=', $end_date);
				}
			})
			->where(function ($query) use ($request, $type) {
				if (!empty($type)) {
					$query->where('import_jobs.type_id', '=', $type);
				}
			})
			->where('import_jobs.created_by', '=', Auth::user()->id)
			->orderBy('import_jobs.created_at', 'desc')
			->get();

		return Datatables::of($import_data)
			->addColumn('job_status', function ($import_data) {
				return $import_data->job_status;
			})
			->addColumn('import_status', function ($import_data) {
				return 'Processed : ' . $import_data->processed . ', Remaining : ' . $import_data->remaining;
			})
			->addColumn('processed_status', function ($import_data) {
				return 'Imported : ' . $import_data->new . ', Error : ' . $import_data->error;
			})
			->addColumn('server_status', function ($import_data) {
				if ($import_data->server_status == '') {
					return '-';
				} else {
					return $import_data->server_status;
				}
			})
			->addColumn('action', function ($import_data) {
				return '
				<a class="btn btn-sm btn-red" href="' . URL::to($import_data->export_file) . '">
					Download Error Reports
				</a>
				<br><br>
                <a class="btn btn-sm btn-blue" href="' . URL::to($import_data->src_file) . '">
                	Download Source File
                </a>
				<br><br>
                <a class="btn btn-sm btn-green" id="update_job_import_status" class="restart-import-job update_job_import_status" data-id="' . $import_data->id . '">
                	Restart
                </a>
                ';

			})
			->make(true);

	}
	public function getImportFormData() {

		$this->data['import_type_list'] = collect(Config::select(
			'id',
			'name'
		)
				->where('config_type_id', 523)
				->get())->prepend(['id' => '', 'name' => 'Select Import Type']);

		return response()->json($this->data);
	}
	public function saveImportJobs(Request $request) {
		// dd($request->all());
		DB::beginTransaction();
		try {
			$validator = Validator::make($request->all(), [
				'file' => 'required',
			]);

			$attachment = 'file';
			if (empty($request->file)) {
				return response()->json(['success' => false, 'errors' => ['Please Upload FIle']]);
			}

			$extension = $request->file($attachment)->getClientOriginalExtension();
			if ($extension != "xlsx" && $extension != "xls") {
				return response()->json(['success' => false, 'errors' => ['Cannot Read File, Please Import Excel Format File']]);
			}

			$destination = config('custom.jobs_import_path');
			$store_path = storage_path('app/public/job/import');

			$time_stamp = date('Y_m_d_h_i_s');
			$file_name = $time_stamp . '_import' . '.' . $extension;
			Storage::makeDirectory($destination, 0777);
			$request->file($attachment)->storeAs($destination, $file_name);
			$file_path = $store_path . '/' . $file_name;

			$src_file = 'storage/app/public/job/import/' . $file_name;

			$import = new ImportJob;
			$import->type_id = $request->import_type_id;
			$import->company_id = Auth::user()->company_id;
			$import->status_id = 3361;
			$import->src_file = $src_file;
			$import->created_by = Auth::id();
			$import->save();

			DB::commit();
			return response()->json(['success' => true, 'message' => 'Uploading in Progress']);

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function update_import_jobs_status(Request $request) {
		$id = $request->id;
		$update_status = ImportJob::where('id', $id)->update(['total_records' => 0, 'processed' => 0, 'remaining' => 0, 'new' => 0, 'updated' => 0, 'error' => 0, 'status_id' => 3361, 'export_file' => null, 'server_status' => null]);

		return response()->json(['success' => true]);
	}

	public function getEmployeeFromApi(Request $r) {
		//dd($r->all());
      if (!empty($r->code)) {
		$employee = $this->getSoap->GetCMSEmployeeDetails($r->code);
		//dd($employee);
		if (empty($employee)) {
			return response()->json(['success' => false, 'errors' => ['Employee Details not found in AX']]);
		}
		$business = Business::where('name',$employee['business_id'])->pluck('id')->first();
		$employee['business_id']=$business;
		// dd($employee);
		$department = Department::where('name',$employee['department_id'])->where('id',$business)->pluck('id')->first();
		 $employee['department_id']=$department;
		 $lob = Lob::where('name',$employee['lob_id'])->pluck('id')->first();
		 $employee['lob_id']=$lob;
		 $sbu = Sbu::where('name',$employee['business_id'])->where('id',$lob)->pluck('id')->first();
		 $employee['business_id']=$sbu;
         $outlet = Outlet::where('name',$employee['outlet_id'])->pluck('id')->first();
         $employee['outlet_id']=$outlet;
		 $grade = Entity::where('name',$employee['grade_id'])->pluck('id')->first();
		 $employee['grade_id']=$grade;
		 //dd($grade);
		 $designation = Designation::where('name',$employee['designation_id'])->where('id',$grade)->pluck('id')->first();
		 $employee['grade_id']=$designation;
		 $reporting = User::where('username',$employee['reporting_to'])->pluck('id')->first();
		 $employee['grade_id']=$reporting;
         } else {
			return response()->json(['success' => false, 'errors' => ['Employee code is empty']]);
		}
		return response()->json(['success' => true, 'employee' => $employee]);
	}

	public function getSendSms(Request $r){
        if(!empty($r->sms_mobile_number)){
        	$message = str_replace('XXXXXX', 2, config('custom.SMS_TEMPLATES.SMS_SENT'));
        	$mobile_number=$r->sms_mobile_number;
        	$sms=sendNotificationTxtMsg(2,$message,$mobile_number);
        }else {
			return response()->json(['success' => false, 'errors' => ['Mobile Number is Empty']]);
		}
		return response()->json(['success' => true, 'message' => $sms]);
	}

}
