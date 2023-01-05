<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Uitoux\EYatra\DeviationApprover;
use Validator;
use Yajra\Datatables\Datatables;

class DeviationController extends Controller {
	public function list(Request $r) {
		$list = DeviationApprover::withTrashed()->select(
                'deviation_approvers.id',
                'deviation_approvers.deviation_employee_id',
                'deviation_approvers.deleted_at',
                DB::raw('GROUP_CONCAT(sbus.name) as sbu'),
                'employees.code as emp_code',
                'users.name as user_name'
            )->leftjoin('deviation_approver_sbus', 'deviation_approver_sbus.deviation_approver_id', 'deviation_approvers.id')
			->leftjoin('sbus', 'sbus.id', 'deviation_approver_sbus.sbu_id')
			->leftjoin('employees', 'employees.id', 'deviation_approvers.deviation_employee_id')
			->leftjoin('users', function($j) {
                $j->on('users.entity_id', 'employees.id')
                ->where('users.user_type_id', 3121);    // 3121 -> Employee
            })
			->orderby('deviation_approvers.id', 'DESC')
			->groupBy('deviation_approvers.id');
		// dd($list);
		return Datatables::of($list)
			->addColumn('status', function ($list) {
				if ($list->deleted_at) {
					return '<span style="color:#ea4335">In Active</span>';
				} else {
					return '<span style="color:#63ce63">Active</span>';
				}
			})
			->make(true);
	}
}
