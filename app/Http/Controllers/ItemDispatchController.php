<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Prefix;
use Illuminate\Support\Facades\Auth;

use App\Models\Items\Item;
use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Enums\App;

use App\Services\PaymentTypeService;
use App\Services\GeneralDataService;
use App\Services\PaymentTransactionService;
use App\Http\Requests\ItemDispatchRequest;
use App\Services\AccountTransactionService;
use App\Services\ItemTransactionService;

use App\Services\CacheService;
use App\Services\ItemService;
use App\Enums\ItemTransactionUniqueCode;
use App\Models\StockAdjustment;
use App\Models\ItemDispatch;
use App\Models\ItemDispatchTransaction;
use App\Services\Communication\Email\PurchaseBillEmailNotificationService;
use App\Services\Communication\Sms\PurchaseBillSmsNotificationService;

use Mpdf\Mpdf;

class ItemDispatchController extends Controller
{
    use FormatNumber;

    use FormatsDateInputs;

    protected $companyId;

    private $paymentTypeService;

    private $paymentTransactionService;

    private $accountTransactionService;

    private $itemTransactionService;

    private $itemService;

    public $previousHistoryOfItems;

    public $adjustmentBillEmailNotificationService;

    public $adjustmentBillSmsNotificationService;

    public function __construct(
                                ItemTransactionService $itemTransactionService,
                                ItemService $itemService
                            )
    {
        $this->companyId = App::APP_SETTINGS_RECORD_ID->value;
        $this->itemTransactionService = $itemTransactionService;
        $this->itemService = $itemService;
        $this->previousHistoryOfItems = [];

    }

    /**
     * Create a new order.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View  {
        $prefix = Prefix::findOrNew($this->companyId);
        $lastCountId = $this->getLastCountId();
        $data = [
            'prefix_code' => $prefix->item_dispatch,
            'count_id' => ($lastCountId+1),
        ];
        return view('item-dispatch.create',compact('data'));
    }

    /**
     * Get last count ID
     * */
    public function getLastCountId(){
        return ItemDispatch::select('count_id')->orderBy('id', 'desc')->first()?->count_id ?? 0;
    }

    /**
     * List the orders
     *
     * @return \Illuminate\View\View
     */
    public function list() : View {
        return view('item-dispatch.list');
    }

     /**
     * Edit a Purchase Order.
     *
     * @param int $id The ID of the expense to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id) {
        $itemDispatch = ItemDispatch::with([
            'ItemDispatchTransaction' => [
                'item.brand',
                'warehouse',
                'tax'
            ]])->findOrFail($id);

        $lastestItemDispatch = ItemDispatch::where('vehicle_id', $itemDispatch->vehicle_id)->orderBy('id', 'desc')->first();  
        if($lastestItemDispatch->id != $itemDispatch->id) {
            // throw new \Exception('You are allow to edit the previous item dispatch');
            return redirect()
            ->route('item_dispatch.list')
            ->with('error', __('You are not allowed to create entries for a previous date.'));

        }

        $allUnits = CacheService::get('unit');

        $itemTransactions = $itemDispatch->ItemDispatchTransaction->map(function ($transaction) use ($allUnits ) {
            $itemData = $transaction->toArray();

            // Use the getOnlySelectedUnits helper function
            $selectedUnits = getOnlySelectedUnits(
                $allUnits,
                $transaction->item->base_unit_id,
                $transaction->item->secondary_unit_id
            );
            
            // Add unitList to the item data
            $itemData['unitList'] = $selectedUnits->toArray();
            return $itemData;
        })->toArray();

        // krsort($itemTransactions);
        $itemTransactionsJson = json_encode($itemTransactions);

        return view('item-dispatch.edit', compact('itemDispatch', 'itemTransactionsJson'));
    }

    public function transaction($id) {
        $itemDispatch = ItemDispatch::where('vehicle_id', $id)->orderBy('id', 'desc')->first();  

        $allUnits = CacheService::get('unit');
        $itemTransactions = $itemDispatch->ItemDispatchTransaction->map(function ($transaction) use ($allUnits ) {
            $itemData = $transaction->toArray();

            // Use the getOnlySelectedUnits helper function
            $selectedUnits = getOnlySelectedUnits(
                $allUnits,
                $transaction->item->base_unit_id,
                $transaction->item->secondary_unit_id
            );

            $itemData['item'] = collect($transaction->item);
            // Add unitList to the item data
            $itemData['unitList'] = $selectedUnits->toArray();
            return $itemData;
        })->toArray();

        echo json_encode($itemTransactions);
    }

    /**
     * View Purchase Order details
     *
     * @param int $id, the ID of the order
     * @return \Illuminate\View\View
     */
    public function details($id) : View {
        $itemDispatch = ItemDispatch::with(['user', 'vehicle', 'salesman', 'driver',
            'ItemDispatchTransaction' => [
                'item',
                'tax'
            ]])->findOrFail($id);


        //Batch Tracking Row count for invoice columns setting
        $batchTrackingRowCount = (new GeneralDataService())->getBatchTranckingRowCount();

        return view('item-dispatch.details', compact('itemDispatch', 'batchTrackingRowCount'));
    }

    /**
     * Print Purchase
     *
     * @param int $id, the ID of the adjustment
     * @return \Illuminate\View\View
     */
    public function print($id, $isPdf = false, $thermalPrint = false) : View {

        $itemDispatch = ItemDispatch::with(['user', 'vehicle', 'salesman', 'driver',
            'ItemDispatchTransaction' => [
                'item',
                'tax'
            ]])->findOrFail($id);

        //Batch Tracking Row count for invoice columns setting
        $batchTrackingRowCount = (new GeneralDataService())->getBatchTranckingRowCount();

        $invoiceData = [
            'name' => __('warehouse.item_dispatch'),
        ];


        return view('print.item-dispatch.print', compact('isPdf', 'invoiceData', 'itemDispatch','batchTrackingRowCount'));

    }

    /**
     * Thermal Print Purchase
     *
     * @param int $id, the ID of the adjustment
     * @return \Illuminate\View\View
     */
    public function thermalPrint($id) : View {
        return $this->print($id, isPdf:false, thermalPrint:true);
    }

    /**
     * Generate PDF using View: print() method
     * */
    public function generatePdf($id){
        $html = $this->print($id, isPdf:true);

        $mpdf = new Mpdf([
                'tempDir' => base_path('storage/mpdf_temp'),
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
        $mpdf->Output('Purchase-Bill-'.$id.'.pdf', 'D');
    }

    /**
     * Store Records
     * */
    public function store(ItemDispatchRequest $request) : JsonResponse  {
        try {
            DB::beginTransaction();
            // Get the validated data from the expenseRequest
            $validatedData = $request->validated();

            if($request->operation == 'save') {
                 $lastestItemDispatch = ItemDispatch::where('vehicle_id', $validatedData['vehicle_id'])->orderBy('id', 'desc')->first();  
                if(!empty($lastestItemDispatch) && ($lastestItemDispatch->transaction_date >  $validatedData['transaction_date'])) {
                    throw new \Exception('You are allow to create for previous date');
                }
                
                $validatedData['transaction_id'] = $validatedData['prefix_code'] . $validatedData['count_id'];

                $newItemDispatch = ItemDispatch::create($validatedData);
            } else {
                $fillableColumns = [
                    'transaction_date' => $validatedData['transaction_date'],
                    'prefix_code' => $validatedData['prefix_code'],
                    'count_id' => $validatedData['count_id'],
                    'vehicle_id' => $validatedData['vehicle_id'],
                    'driver_id' => $validatedData['driver_id'],
                    'salesman_id' => $validatedData['salesman_id'],
                    'transaction_code' => $validatedData['transaction_code'],
                    'reference_no' => $validatedData['reference_no'],
                    'note' => $validatedData['note'],
                    'row_count' => $validatedData['row_count'],
                    'total_quantity' => $validatedData['total_quantity'],
                    'total_remaining_quantity' => $validatedData['total_remaining_quantity'],
                ];

                $newItemDispatch = ItemDispatch::findOrFail($validatedData['item_dispatch_id']);
                $newItemDispatch->update($fillableColumns);

                $newItemDispatch->ItemDispatchTransaction()->delete();
            }

            $adjustedItemsArray = $this->saveDispatchItems($request->input(), $newItemDispatch, $request->operation);
            if(!$adjustedItemsArray['status']){
                DB::rollback();

                throw new \Exception($adjustedItemsArray['message']);
            }

            DB::commit();

            // Regenerate the CSRF token
            //Session::regenerateToken();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_saved_successfully'),
                'id' => $newItemDispatch->id,

            ]);

        } catch (\Exception $e) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 409);

        }

    }

    public function saveDispatchItems($request, $newItemDispatch, $operation = 'save')
    {
        $itemId = array_values($request['item_id']);
        $quantity = array_values($request['quantity']);
        $warehouseId = array_values($request['warehouse_id']);
        $description = array_values($request['description']);
        $unitId = array_values($request['unit_id']);
        $totalPurchasePrice = 0;
        $totalSalePrice = 0;
        $totalQuantity = 0;
        
        $itemsCount = count($itemId);
        if($itemsCount > 0) {
            for ($i=0; $i < $itemsCount; $i++) {
                $itemDetails = Item::find($itemId[$i]);
                $itemName = $itemDetails->name;
                $itemQuantity = $quantity[$i];
                $totalPurchasePrice += $itemDetails->purchase_price * $itemQuantity;
                $totalSalePrice += $itemDetails->sale_price * $itemQuantity;
                $totalQuantity += $itemQuantity;
                // if(empty($itemQuantity) || $itemQuantity === 0 || $itemQuantity < 0){
                //     return [
                //         'status' => false,
                //         'message' => ($itemQuantity<0) ? __('item.item_qty_negative', ['item_name' => $itemName]) : __('item.please_enter_item_quantity', ['item_name' => $itemName]),
                //     ];
                // }

                $itemDispatchTransactionArr[$itemDetails->item_code] = array(
                    'item_dispatch_id' => $newItemDispatch->id,
                    'item_name' => $itemName,
                    'transaction_id' => $newItemDispatch->transaction_id,
                    'transaction_date' => $request['transaction_date'],
                    'warehouse_id' => $warehouseId[$i],
                    'item_id' => $itemId[$i],
                    'description' => $description[$i],
                    'tracking_type' => $itemDetails->tracking_type,
                    'quantity' => $itemQuantity,
                    'unit_id' => $unitId[$i],
                    'sale_price' => $itemDetails->sale_price,
                    'purchase_price' => $itemDetails->purchase_price,
                    'tax_id' => $itemDetails->tax_id,
                    'sold_quantity' => 0,
                    'remaining_quantity' => $itemQuantity,
                    'created_at' => date('Y-m-d H:i:s', strtotime('now')),
                    'created_by' => auth()->id()
                );
            }

            
            arsort($itemDispatchTransactionArr);
            // if($operation == 'save') {
                // rsort($itemDispatchTransactionArr);
            // }
            ItemDispatchTransaction::insert($itemDispatchTransactionArr);

            ItemDispatch::where('id',  $newItemDispatch->id)->update([
                'total_quantity' => $totalQuantity,
                'total_purchase_price' => $totalPurchasePrice,
                'total_actual_sale_price' => $totalSalePrice,
                'total_remaining_quantity' => DB::raw('total_quantity - total_sold_quantity'),
            ]);
        }
        
        return ['status' => true];
    }


    /**
     * Datatabale
     * */
    public function datatableList(Request $request){

        $data = ItemDispatch::with(['user', 'vehicle', 'salesman', 'driver'])
            ->when($request->from_date, function ($query) use ($request) {
                return $query->where('transaction_date', '>=', $this->toSystemDateFormat($request->from_date));
            })
            ->when($request->to_date, function ($query) use ($request) {
                return $query->where('transaction_date', '<=', $this->toSystemDateFormat($request->to_date));
            });

        return DataTables::of($data)
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search') && $request->search['value']) {
                            $searchTerm = $request->search['value'];
                            $query->where(function ($q) use ($searchTerm) {
                                $q->where('transaction_id', 'like', "%{$searchTerm}%")
                                  ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                                      $userQuery->where('username', 'like', "%{$searchTerm}%");
                                  });
                            });
                        }
                    })
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app('company')['date_format']);
                    })
                    ->addColumn('username', function ($row) {
                        return $row->user->username??'';
                    })
                    ->addColumn('vehicle', function ($row) {
                        $vehicleName = $row->vehicle->name ?? '';
                        $vehicleNumber = $row->vehicle->vehicle_number ?? '';
                        return $vehicleName . ' (' . $vehicleNumber . ')';
                    })
                    ->addColumn('transaction_date', function ($row) {
                        return $row->formatted_transaction_date;
                    })
                    ->addColumn('transaction_code', function ($row) {
                        return $row->transaction_code;
                    })
                    ->addColumn('total_purchase_price', function ($row) {
                        $purchasePrice = 0;
                        if(in_array(auth()->user()->role_id, config('constants.purchase_price_access'))) {
                            $purchasePrice = $row->formatted_total_purchase_price;
                        }
                        return $purchasePrice;
                    })
                    ->addColumn('total_actual_sale_price', function ($row) {
                        return $row->formatted_total_actual_sale_price;
                    })
                    ->addColumn('total_sale_price', function ($row) {
                        return $row->formatted_total_sale_price;
                    })
                    ->addColumn('total_quantity', function ($row) {
                        return $row->formatted_total_quantity;
                    })
                    ->addColumn('total_sold_quantity', function ($row) {
                        return $row->formatted_total_sold_quantity;
                    })
                    ->addColumn('total_remaining_quantity', function ($row) {
                        return $row->formatted_total_remaining_quantity;
                    })
                    ->addColumn('action', function($row){
                            $id = $row->id;

                            $editUrl = route('item_dispatch.edit', ['id' => $id]);
                            //$deleteUrl = route('item_dispatch.delete', ['id' => $id]);
                            $detailsUrl = route('item_dispatch.details', ['id' => $id]);
                            $printUrl = route('item_dispatch.print', ['id' => $id]);
                            $pdfUrl = route('item_dispatch.pdf', ['id' => $id]);

                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-edit"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="' . $detailsUrl . '"></i><i class="bx bx-show-alt"></i> '.__('app.details').'</a>
                                </li>
                                <li>
                                    <a target="_blank" class="dropdown-item" href="' . $printUrl . '"></i><i class="bx bx-printer "></i> '.__('app.print').'</a>
                                </li>
                                <li>
                                    <a target="_blank" class="dropdown-item" href="' . $pdfUrl . '"></i><i class="bx bxs-file-pdf"></i> '.__('app.pdf').'</a>
                                </li>
                            </ul>
                        </div>';
                            return $actionBtn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
    }

    /**
     * Delete Purchase Records
     * @return JsonResponse
     * */
    public function delete(Request $request) : JsonResponse{

        DB::beginTransaction();

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = StockAdjustment::find($recordId);
            if (!$record) {
                // Invalid record ID, handle the error (e.g., show a message, log, etc.)
                return response()->json([
                    'status'    => false,
                    'message' => __('app.invalid_record_id',['record_id' => $recordId]),
                ]);

            }
            // You can perform additional validation checks here if needed before deletion
        }

        /**
         * All selected record IDs are valid, proceed with the deletion
         * Delete all records with the selected IDs in one query
         * */


        try {
            // Attempt deletion (as in previous responses)
            StockAdjustment::whereIn('id', $selectedRecordIds)->chunk(100, function ($adjustments) {
                foreach ($adjustments as $adjustment) {
                    /**
                    * Before deleting ItemTransaction data take the
                    * old data of the item_serial_master_id
                    * to update the item_serial_quantity
                    * */
                   $this->previousHistoryOfItems = $this->itemTransactionService->getHistoryOfItems($adjustment);

                    $itemIdArray = [];
                    //Purchasr Item delete and update the stock
                    foreach($adjustment->itemTransaction as $itemTransaction){
                        //get item id
                        $itemId = $itemTransaction->item_id;

                        //delete item Transactions
                        $itemTransaction->delete();

                        $itemIdArray[] = $itemId;
                    }//adjustment account


                    //Delete Purchase
                    $adjustment->delete();


                    /**
                     * UPDATE HISTORY DATA
                     * LIKE: ITEM SERIAL NUMBER QUNATITY, BATCH NUMBER QUANTITY, GENERAL DATA QUANTITY
                     * */

                     $this->itemTransactionService->updatePreviousHistoryOfItems($adjustment, $this->previousHistoryOfItems);


                    //Update stock update in master
                    if(count($itemIdArray) > 0){
                        foreach($itemIdArray as $itemId){
                            $this->itemService->updateItemStock($itemId);
                        }
                    }

                }//adjustments

            });//chunk


            DB::commit();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_deleted_successfully'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            return response()->json([
                'status'    => false,
                'message' => __('app.cannot_delete_records'),
            ],409);
        }
    }

    /**
     * vehicle
     * */
    public function vehicle($id){
        $itemDispatchDetail = ItemDispatch::where('vehicle_id', $id)->orderBy('id', 'desc')->first();

        return response()->json($itemDispatchDetail);
    }

}
