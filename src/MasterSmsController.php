<?php

namespace Uitoux\EYatra;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class MasterSmsController extends Controller {
	public function list(Request $r) {
        $smsTemplates = config('custom.SMS_TEMPLATES');
        $list = [];
        foreach ($smsTemplates as $key => $smsTemplate) {
            $list[count($list)] = [
                'type' => $key,
                'content' => $smsTemplate,
            ];
        }
		// dd($list);
		return Datatables::of($list)
			->make(true);
	}
}
