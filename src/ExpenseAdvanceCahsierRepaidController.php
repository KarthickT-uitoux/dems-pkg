<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Storage;
use Carbon\Carbon;
use Uitoux\EYatra\Employee;
use Uitoux\EYatra\ExpenseVoucherAdvanceRequest;
use Validator;
use Yajra\Datatables\Datatables;

class ExpenseAdvanceCahsierRepaidController extends Controller {
public function listExpenseVoucherCashierRepaidList(Request $r) {
	//dd(Auth::user()->entity->outlet->id);
		$expense_voucher_requests = ExpenseVoucherAdvanceRequest::select(
			'expense_voucher_advance_requests.id',
			'expense_voucher_advance_requests.employee_id',
			'users.name as ename',
			'employees.code as ecode',
			'outlets.code as ocode',
			'outlets.name as oname',
			DB::raw('DATE_FORMAT(expense_voucher_advance_requests.date,"%d-%m-%Y") as date'),
			'expense_voucher_advance_requests.advance_amount as advance_amount',
			DB::raw('IF(expense_voucher_advance_requests.balance_amount IS NULL,"--",expense_voucher_advance_requests.balance_amount) as balance_amount'),
			'expense_voucher_advance_requests.status_id as status_id',
			'configs.name as status'
		)
			->join('configs', 'configs.id', 'expense_voucher_advance_requests.status_id')
			->join('employees', 'employees.id', 'expense_voucher_advance_requests.employee_id')
			->join('users', 'users.entity_id', 'employees.id')
			->join('outlets', 'outlets.id', 'employees.outlet_id')
			->join('employees as cashier', 'cashier.id', 'outlets.cashier_id')
			->whereIn('expense_voucher_advance_requests.status_id', [3470])
			->where('users.user_type_id', 3121)
			->where('employees.outlet_id', Auth::user()->entity->outlet->id)
			->orderBy('expense_voucher_advance_requests.id', 'desc')
			->where(function ($query) use ($r) {
				if (!empty($r->employee_id)) {
					$query->where('employees.id', $r->employee_id);
				}
			})
			->where(function ($query) use ($r) {
				if (!empty($r->outlet)) {
					$query->where('outlets.id', $r->outlet);
				}
			})
		;
		// dd($expense_voucher_requests->get());
		return Datatables::of($expense_voucher_requests)
		->addColumn('checkbox', function ($expense_voucher_requests) {
				return '<input id="employee_claim_' . $expense_voucher_requests->id . '" type="checkbox" class="check-bottom-layer employee_claim_list " name="employee_claim_list"  value="' . $expense_voucher_requests->id . '" data-trip_id="' . $expense_voucher_requests->trip_id . '" >
				<label for="employee_claim_' . $expense_voucher_requests->id . '"></label>';
			})
			->addColumn('action', function ($expense_voucher_requests) {
				$img1 = asset('public/img/content/yatra/table/edit.svg');
				$img2 = asset('public/img/content/yatra/table/view.svg');
				$img2_active = asset('public/img/content/yatra/table/view-active.svg');
				$img1_active = asset('public/img/content/yatra/table/edit-active.svg');
				$img3 = asset('public/img/content/yatra/table/delete.svg');
				$img3_active = asset('public/img/content/yatra/table/delete-active.svg');

				return '
				<a href="#!/expense/voucher-advance/cashier/repaid/view/' . $expense_voucher_requests->id . '">
					<img src="' . $img2 . '" alt="View" class="img-responsive" onmouseover=this.src="' . $img2_active . '" onmouseout=this.src="' . $img2 . '" >
				</a>
				<button title="paid"  data-toggle="modal"  data-target="#cashier_repaid_confirm"  	onclick="angular.element(this).scope().paidExpenseVoucherAdvance(' . $expense_voucher_requests->id . ')" data-expense_id="' . $expense_voucher_requests->id . '" class="btn btn-sm"><i class="fa fa-thumbs-up"></i></button>';
			})
			->make(true);
	}
	

	public function ExpenseVoucherAdvanceCashierRepaidFilterData() {
		//$list_of_status = array_merge(Config::ExpenseVoucherAdvanceStatus(), Config::ExpenseVoucherAdvanceStatusList());
		$this->data['status_list'] = $status_list = collect(Config::ExpenseVoucherAdvanceStatus())->prepend(['id' => '', 'name' => 'Select Status']);
		$this->data['employee_list'] = collect(Employee::getEmployeeListBasedCompany())->prepend(['id' => '', 'name' => 'Select Employee']);
		$this->data['outlet_list'] = collect(Outlet::getOutletList())->prepend(['id' => '', 'name' => 'Select Outlet']);

		return response()->json($this->data);
	}
	
	public function ExpenseVoucherAdvanceCashierRepaidView($id) {
		$this->data['expense_voucher_view'] = $expense_voucher_view = ExpenseVoucherAdvanceRequest::select(
			'employees.code',
			'users.name',
			'expense_voucher_advance_requests.id',
			'expense_voucher_advance_requests.date',
			'expense_voucher_advance_requests.advance_amount',
			'expense_voucher_advance_requests.expense_amount',
			'expense_voucher_advance_requests.balance_amount',
			'expense_voucher_advance_requests.description',
			'expense_voucher_advance_requests.expense_description',
			'configs.name as status'
		)
			->leftJoin('employees', 'employees.id', 'expense_voucher_advance_requests.employee_id')
			->leftJoin('users', 'users.entity_id', 'employees.id')
			->leftJoin('configs', 'configs.id', 'expense_voucher_advance_requests.status_id')
			->where('users.user_type_id', 3121)
			->where('expense_voucher_advance_requests.id', $id)
			->first();

		$expense_voucher_advance_attachment = Attachment::where('attachment_of_id', 3442)->where('entity_id', $expense_voucher_view->id)->select('name', 'id')->get();
		$expense_voucher_view->attachments = $expense_voucher_advance_attachment;
		// dd($expense_voucher_view);
		return response()->json($this->data);

	}
	public function expenseVoucherCashierSingleRepaidApprove(Request $request){
		//dd($request->all());
		DB::beginTransaction();
		try {
			if ($request->id) {
				$employee_expense_voucher_id = $request->id;
			} else {
				return back()->with('error', 'Expense Voucher Advance not found');
			}
			$expense_voucher_advance = ExpenseVoucherAdvanceRequest::where('id', $employee_expense_voucher_id)->update(['status_id' => 3275]);
			if($expense_voucher_advance){
				//Approval Log Save
				ApprovalLog::saveApprovalLog(3585, $employee_expense_voucher_id, 3258, Auth::user()->entity_id, Carbon::now());
			}
			DB::commit();
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Error_Message' => $e->getMessage()]]);
		}

	}
	public function expenseVoucherCashierMultipleRepaidApprove(Request $request) {
		DB::beginTransaction();
		try {
			if ($request->approve_ids) {
				$employee_expense_voucher_ids = explode(',', $request->approve_ids);
			} else {
				return back()->with('error', 'Expense Voucher Advance not found');
			}
			$expense_voucher_advance = ExpenseVoucherAdvanceRequest::whereIn('id', $employee_expense_voucher_ids)->update(['status_id' => 3275]);
			//Approval Log Save For Multiple Trips
			if (count($employee_expense_voucher_ids) > 0) {
				foreach ($employee_expense_voucher_ids as $key => $employee_expense_voucher_id) {
					ApprovalLog::saveApprovalLog(3585, $employee_expense_voucher_id, 3258, Auth::user()->entity_id, Carbon::now());
				}
			}
			DB::commit();
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Error_Message' => $e->getMessage()]]);
		}
	}
	
}
