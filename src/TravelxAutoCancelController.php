<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use App\TravelxAutoCancel;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class TravelxAutoCancelController extends Controller {
	public function list(Request $r) {
		$list = TravelxAutoCancel::withTrashed()->select(
                'name',
				'normal_days',
				'warning_days',
				'approve_cancel_days',
                'deleted_at'
            )->orderby('id', 'DESC')
			->groupBy('id');
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
