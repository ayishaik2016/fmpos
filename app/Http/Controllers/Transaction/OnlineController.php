<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use App\Models\PaymentTransaction;
use App\Services\PaymentTypeService;
use App\Enums\PaymentTypesUniqueCode;
use App\Services\PaymentTransactionService;
use App\Traits\FormatNumber; 
use App\Traits\FormatsDateInputs;

class OnlineController extends Controller
{   
    use FormatNumber;
    use FormatsDateInputs;

    private $paymentTypeService;

    private $paymentTransactionService;

    public function __construct(PaymentTypeService $paymentTypeService, PaymentTransactionService $paymentTransactionService)
    {
        $this->paymentTypeService = $paymentTypeService;
        $this->paymentTransactionService = $paymentTransactionService;
    }


    /**
     * List the cash transactions
     *
     * @return \Illuminate\View\View
     */
    public function list() : View {
        return view('transaction.online-list');
    }

    /**
     * Bank Transaction list
     * */
    public function datatableList(Request $request){
        // Ensure morph map keys are defined
        $this->paymentTransactionService->usedTransactionTypeValue();

        $dangerTypes = ['Expense', 'Purchase', 'Purchase Return', 'Purchase Order'];

        $cashAdjustmentKey = 'Bank Adjustment';

        $onlineId = $this->paymentTypeService->returnPaymentTypeId(PaymentTypesUniqueCode::ONLINE->value);

        $data = PaymentTransaction::with('user', 'paymentType')
                                    ->where(function ($query) use ($onlineId) {
                                        $query->where('payment_type_id', $onlineId)
                                              ->orWhere('transfer_to_payment_type_id', $onlineId);
                                    })
                                    ->when($request->from_date, function ($query) use ($request) {
                                        return $query->where('transaction_date', '>=', $this->toSystemDateFormat($request->from_date));
                                    })
                                    ->when($request->to_date, function ($query) use ($request) {
                                        return $query->where('transaction_date', '<=', $this->toSystemDateFormat($request->to_date));
                                    });

        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    ->addColumn('username', function ($row) {
                        return $row->user->username??'';
                    })
                    ->addColumn('amount', function ($row) {
                        return $this->formatWithPrecision($row->amount);
                    })
                    ->addColumn('color_class', function ($row) use ($dangerTypes) {
                        return in_array($row->transaction_type, $dangerTypes) ? "danger" : "success";
                    })
                    ->addColumn('transaction_type', function ($row) use ($cashAdjustmentKey) {
                        if($row->transaction_type == $cashAdjustmentKey){
                            return $row->transaction->adjustment_type;
                        }else{
                            return $row->transaction_type;
                        }
                         
                    })
                    ->addColumn('party_name', function ($row) {
                        return $row->transaction->party? $row->transaction->party->getFullName() : $row->transaction->category->name;
                    })
                    ->addColumn('action', function($row) use ($cashAdjustmentKey){
                            $id = $row->id;

                            $actionBtn = 'NA';

                            if($row->transaction_type == $cashAdjustmentKey){
                            $actionBtn = '<div class="dropdown ms-auto">
                                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                                            </a>
                                            <ul class="dropdown-menu">';
                                                
                                                    $actionBtn .= '<li>
                                                        <a class="dropdown-item edit-cash-adjustment" data-cash-adjustment-id="' . $row->transaction->id . '" role="button"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
                                                    </li>';

                                                    $actionBtn .= '<li>
                                                    <button type="button" class="dropdown-item text-danger deleteRequest " data-delete-id='.$row->transaction->id.'><i class="bx bx-trash"></i> '.__('app.delete').'</button>
                                                </li>';
                                                
                                            $actionBtn .= '</ul>
                                        </div>';
                          }
                        return $actionBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }
}
