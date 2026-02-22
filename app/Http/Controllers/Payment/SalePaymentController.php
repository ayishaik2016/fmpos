<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\View\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Database\Eloquent\Builder;

use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Enums\General;

use App\Services\PaymentTransactionService;
use App\Services\AccountTransactionService;

use App\Http\Controllers\Sale\SaleController;
use App\Models\Sale\Sale;
use App\Models\PaymentTransaction;
use App\Services\PartyService;

use App\Models\Currency;

use App\Enums\PaymentTypesUniqueCode;

use Illuminate\Support\Facades\Storage;

use Mpdf\Mpdf;

class SalePaymentController extends Controller
{
    use FormatNumber;

    use FormatsDateInputs;

    private $paymentTransactionService;
    private $accountTransactionService;
    private $partyService;

    public function __construct(
                                PaymentTransactionService $paymentTransactionService,
                                AccountTransactionService $accountTransactionService,
                                PartyService $partyService
                            )
    {
        $this->paymentTransactionService = $paymentTransactionService;
        $this->accountTransactionService = $accountTransactionService;
        $this->partyService = $partyService;
    }

    /***
     * View Payment History
     *
     * */
    public function getSaleBillPaymentHistory($id) : JsonResponse{

        $data = $this->getSaleBillPaymentHistoryData($id);

        return response()->json([
            'status' => true,
            'message' => '',
            'data'  => $data,
        ]);

    }

    /**
     * Print Sale
     *
     * @param int $id, the ID of the sale
     * @return \Illuminate\View\View
     */
    public function printSaleBillPayment($id, $isPdf = false) : View {
        $payment = PaymentTransaction::with('paymentType')->find($id);

        $saleId = $payment->transaction_id;

        $sale = Sale::with('party')->find($saleId);

        $balanceData = $this->partyService->getPartyBalance([$sale->party->id]);

        return view('print.invoice-payment-receipt', compact('isPdf', 'sale', 'payment', 'balanceData'));
    }

    /**
     * Generate PDF using View: print() method
     * */
    public function pdfSaleBillPayment($id){
        $html = $this->printSaleBillPayment($id, isPdf:true);

        $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 2,
                'margin_right' => 2,
                'margin_top' => 2,
                'margin_bottom' => 2,
                'default_font' => 'dejavusans',
                //'direction' => 'rtl',
            ]);

        $mpdf->showImageErrors = true;
        $mpdf->WriteHTML($html);
        /**
         * Display in browser
         * 'I'
         * Downloadn PDF
         * 'D'
         * */
        $mpdf->Output('Sale-Bill-Payment-'.$id.'.pdf', 'D');
    }

    function getSaleBillPaymentHistoryData($id){
        $model = Sale::with('party','paymentTransaction.paymentType')->find($id);
        
        $data = [
            'party_id'  => $model->party->id,
            'party_name'  => $model->party->first_name.' '.$model->party->last_name,
            'balance'  => $this->formatWithPrecision($model->grand_total - $model->paid_amount),
            'invoice_id'  => $id,
            'invoice_code'  => $model->sale_code,
            'invoice_date'  => $this->toUserDateFormat($model->sale_date),
            'balance_amount'  => $this->formatWithPrecision($model->grand_total - $model->paid_amount),
            'paid_amount'  => $this->formatWithPrecision($model->paid_amount),
            'paid_amount_without_format'  => $model->paid_amount,
            'paymentTransactions' => $model->paymentTransaction->map(function ($transaction) { 
                                        return [
                                            'payment_id' => $transaction->id,
                                            'transaction_date' => $this->toUserDateFormat($transaction->transaction_date),
                                            'reference_no' => $transaction->reference_no??'',
                                            'payment_type' => $transaction->paymentType->name,
                                            'received_by' => $transaction->user->first_name.' '. $transaction->user->last_name,
                                            'amount' => $this->formatWithPrecision($transaction->amount),
                                            'attachment' => $transaction->payment_attachment
                                        ];
                                    })->toArray(),
        ];
        return $data;
    }
    public function getSaleBillPayment($id) : JsonResponse{
        $model = Sale::with('party')->find($id);

        $data = [
            'party_id'  => $model->party->id,
            'party_name'  => $model->party->first_name.' '.$model->party->last_name,
            'balance'  => $this->formatWithPrecision($model->grand_total - $model->paid_amount),
            'invoice_id'  => $id,
            'form_heading' => __('payment.receive_payment'),
        ];

        return response()->json([
            'status' => true,
            'message' => '',
            'data'  => $data,
        ]);

    }

    public function deleteSaleBillPayment($paymentId) : JsonResponse{
        try {
            DB::beginTransaction();
            $paymentTransaction = PaymentTransaction::find($paymentId);
            if(!$paymentTransaction){
                throw new \Exception(__('payment.failed_to_delete_payment_transactions'));
            }

            //Sale model id
            $saleId = $paymentTransaction->transaction_id;

            // Find the related account transaction
            $accountTransactions = $paymentTransaction->accountTransaction;
            if ($accountTransactions->isNotEmpty()) {
                foreach ($accountTransactions as $accountTransaction) {
                    $accountId = $accountTransaction->account_id;
                    // Do something with the individual accountTransaction
                    $accountTransaction->delete(); // Or any other operation
                    //Update  account
                    $this->accountTransactionService->calculateAccounts($accountId);
                }
            }

            $paymentTransaction->delete();

            /**
             * Update Sale Model
             * Total Paid Amunt
             * */
            $sale = Sale::find($saleId);
            if(!$this->paymentTransactionService->updateTotalPaidAmountInModel($sale)){
                throw new \Exception(__('payment.failed_to_update_paid_amount'));
            }

            DB::commit();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_deleted_successfully'),
                'data'  => $this->getSaleBillPaymentHistoryData($sale->id),
            ]);

        } catch (\Exception $e) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 409);

        }
    }

    public function invoiceSummary($invoiceId) : View {
        $sale = Sale::with(['party', 'user'])->find($invoiceId);

        $invoiceData = [
            'name' => __('sale.invoice'),
            'summary' => 0
        ];

        $currencyDetail = Currency::where('is_company_currency', 1)->first();

        return view('print.sale.summary.print', data: compact( 'invoiceData', 'sale', 'currencyDetail'));
    } 

    public function invoicePaymentDetail($invoiceId) {
        $sale = Sale::with(['party', 'user', 'paymentTransaction' => ['user']])->find($invoiceId);

        $invoiceData = [
            'name' => __('sale.invoice'),
            'summary' => 1
        ];

        $currencyDetail = Currency::where('is_company_currency', 1)->first();

        // echo "<pre>";print_r($sale->paymentTransaction);die;
        return view('print.sale.summary.print', data: compact( 'invoiceData', 'sale', 'currencyDetail'));
    }

    public function storeSaleBillPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $invoiceId          = $request->input('invoice_id');
            $transactionDate    = $request->input('transaction_date');
            $receiptNo          = $request->input('receipt_no');
            $paymentTypeId      = $request->input('payment_type_id');
            $payment            = $request->input('payment');
            $paymentNote        = $request->input('payment_note');

            $sale = Sale::find($invoiceId);

            if (!$sale) {
                throw new \Exception('Invoice not found');
            }

             // Validation rules
            $rules = [
                'transaction_date'  => 'required|date_format:'.implode(',', $this->getDateFormats()),
                'receipt_no'        => 'nullable|string|max:255',
                'payment_type_id'   => 'required|integer',
                'payment'           => 'required|numeric|gt:0',
            ];

            //validation message
            $messages = [
                'transaction_date.required' => 'Payment date is required.',
                'payment_type_id.required'  => 'Payment type is required.',
                'payment.required'          => 'Payment amount is required.',
                'payment.gt'                => 'Payment amount must be greater than zero.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            //Show validation message
            if ($validator->fails()) {
                throw new \Exception($validator->errors()->first());
            }

            $isAttachment = 0;
            if($paymentTypeId >= 2) {
                $isAttachment = 1;
                $attachmentfile = 'payment_attachment';
            }
    
            $paymentAttachment = '';
            if($isAttachment == 1) {
                if ($request->hasFile($attachmentfile) && $request->file($attachmentfile)->isValid()) {
                    $paymentAttachment = $this->uploadImage($request->file($attachmentfile));
                }
            }

            $paymentsArray = [
                'transaction_date'          => $transactionDate,
                'amount'                    => $payment,
                'payment_type_id'           => $paymentTypeId,
                'reference_no'              => $receiptNo,
                'note'                      => $paymentNote,
                'payment_from_unique_code'  => General::INVOICE_LIST->value,//Saving Sale-list page
                'payment_attachment'        => $paymentAttachment
            ];

            if(!$transaction = $this->paymentTransactionService->recordPayment($sale, $paymentsArray)){
                throw new \Exception(__('payment.failed_to_record_payment_transactions'));
            }

            /**
             * Update Sale Model
             * Total Paid Amunt
             * */
            if(!$this->paymentTransactionService->updateTotalPaidAmountInModel($sale)){
                throw new \Exception(__('payment.failed_to_update_paid_amount'));
            }

            /**
             * Update Account Transaction entry
             * Call Services
             * @return boolean
             * */
            // $accountTransactionStatus = $this->accountTransactionService->saleAccountTransaction($sale);
            // if(!$accountTransactionStatus){
            //     throw new \Exception(__('payment.failed_to_update_account'));
            // }

            DB::commit();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_saved_successfully'),
            ]);

        } catch (\Exception $e) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 409);

        }

    }

    private function uploadImage($image) : String{
        // Generate a unique filename for the image
        $filename = uniqid() . '.' . $image->getClientOriginalExtension();

        // Save the image to the storage disk
        Storage::putFileAs('public/sale/pos', $image, $filename);
        return $filename;
    }

    /**
     * Datatabale
     * */
    public function datatableSaleBillPayment(Request $request){
        $data = PaymentTransaction::query()
            ->when($request->user_id, function ($query) use ($request) {
                $query->where('created_by', $request->user_id);
            })
            ->when($request->payment_type_id, function ($query) use ($request) {
                $query->where('payment_type_id', $request->payment_type_id);
            })->whereHasMorph(
                'transaction',
                [Sale::class],
                function (Builder $query, string $type) use($request) {
                    //Class wise Apply filter
                    if($type === Sale::class){
                        $query->when($request->party_id, function ($query) use ($request) {
                            $query->where('party_id', $request->party_id);
                        })
                        ->when($request->invoice_number, function ($query) use ($request) {
                            return $query->where('sale_code', $request->invoice_number);
                        })
                        // ->when($request->user_id, function ($query) use ($request) {
                        //     return $query->where('created_by', $request->user_id);
                        // })
                        ->when($request->from_date, function ($query) use ($request) {
                            return $query->where('transaction_date', '>=', $this->toSystemDateFormat($request->from_date));
                        })
                        ->when($request->to_date, function ($query) use ($request) {
                            return $query->where('transaction_date', '<=', $this->toSystemDateFormat($request->to_date));
                        })
                        ->when(!auth()->user()->can('sale.invoice.can.view.other.users.sale.invoices'), function ($query) use ($request) {
                            return $query->where('created_by', auth()->user()->id);
                        });
                    }

                }
        )->with('transaction.party');

        return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    ->addColumn('username', function ($row) {
                        return $row->user->first_name . ' ' . $row->user->last_name;
                    })
                    ->addColumn('sale_code', function ($row) {
                        return $row->transaction->sale_code??'';
                    })
                    ->addColumn('invoice_date', function ($row) {
                        return $row->transaction->getFormattedSaleDateAttribute();
                    })
                    ->addColumn('party_name', function ($row) {
                        return $row->transaction->party->first_name." ".$row->transaction->party->last_name;
                    })
                    ->addColumn('payment_type', function ($row) {
                        return $row->paymentType->name;
                    })
                    ->addColumn('payment', function ($row) {
                        return $this->formatWithPrecision($row->amount);
                    })
                    ->addColumn('action', function($row){
                            $id = $row->id;
                            $deleteUrl = route('sale.invoice.delete', ['id' => $id]);
                            $printUrl = route('sale.invoice.payment.print', ['id' => $id]);
                            $pdfUrl = route('sale.invoice.payment.pdf', ['id' => $id]);
                            $detailUrl = route('sale.invoice.details', ['id' => $row->transaction->id]);
                            $paymentAttahment = '';
                            if($row->payment_type_id >= 2) {
                                if($row->payment_attachment != '') {
                                    $attachmentUrl = url("/payment/sale-invoice/getimage/" . $row->payment_attachment);
                                    $paymentAttahment = '<li>
                                        <a download target="_blank" class="dropdown-item" href="' . $attachmentUrl . '"></i><i class="bx bx-download"></i> '.__('app.payment_attachment').'</a>
                                    </li>';
                                }
                            }

                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                '.$paymentAttahment.'
                                <li>
                                    <a target="_blank" class="dropdown-item" href="' . $detailUrl . '"></i><i class="bx bx-show-alt"></i> '.__('app.details').'</a>
                                </li>
                                <li>
                                    <a target="_blank" class="dropdown-item" href="' . $printUrl . '"></i><i class="bx bx-printer "></i> '.__('app.print').'</a>
                                </li>
                                <li>
                                    <a target="_blank" class="dropdown-item" href="' . $pdfUrl . '"></i><i class="bx bxs-file-pdf"></i> '.__('app.pdf').'</a>
                                </li>
                                <li>
                                    <button type="button" class="dropdown-item text-danger deleteRequest" data-delete-id='.$id.'><i class="bx bx-trash"></i> '.__('app.delete').'</button>
                                </li>
                            </ul>
                        </div>';
                            return $actionBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }
}
