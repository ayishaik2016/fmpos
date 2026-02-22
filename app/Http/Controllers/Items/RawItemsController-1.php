<?php

namespace App\Http\Controllers\Items;

use App\Traits\FormatNumber;
use App\Traits\FormatsDateInputs;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Requests\RawItemRequest;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Items\RawItems;
use App\Models\Items\RawItemTransaction;
use App\Models\Tax;
use App\Models\Unit;
use Carbon\Carbon;
use App\Services\CacheService;

use App\Enums\ItemTransactionUniqueCode;

use Spatie\Image\Image;

class RawItemsController extends Controller
{
    use FormatsDateInputs;

    use FormatNumber;

    public function __construct()
    {
        
    }

    /**
     * Create a new item.
     *
     * @return \Illuminate\View\View
     */
    public function create()  {
        //return $this->test();
        $data = [
            'count_id' => str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT),
        ];
        return view('items.raw-item.create', compact('data'));
    }
    /**
     * Get last count ID
     * */
    public function getLastCountId(){
        return RawItems::select('count_id')->orderBy('id', 'desc')->first()?->count_id ?? 0;
    }

    /**
     * Edit a item.
     *
     * @param int $id The ID of the item to edit.
     * @return \Illuminate\View\View
     */
    public function edit($id) : View {

        $item = RawItems::find($id);
        $transaction = $item->itemTransaction()->get()->first();//Used Morph
        $transactionId = ($transaction) ? $transaction->id : null;

        /**
         * Todays Date
         * */
        $todaysDate = $this->toUserDateFormat(now());

        return view('items.raw-item.edit', compact('item', 'transaction', 'todaysDate'));
    }

    /**
     * Return JsonResponse
     * */
    public function store(RawItemRequest $request)  {
        try {
            DB::beginTransaction();

            $filename = null;

            /**
             * Get the validated data from the ItemRequest
             * */
            $validatedData = $request->validated();

            /**
             * Know which operation want
             * `save` or `update`
             * */
            $operation = $request->operation;

            /**
             * Image Upload
             * */
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $filename = $this->uploadImage($request->file('image'));
            }

            /**
             * Save or Update the Items Model
             * */
            $recordsToSave = [
                'item_code'                 =>  $request->item_code,
                'name'                      =>  $request->name,
                'description'               =>  $request->description,
                'item_category_id'          =>  $request->item_category_id,
                'base_unit_id'              =>  $request->base_unit_id,
                'current_stock'             =>  $request->opening_quantity,
                'price'                     =>  $request->price,
                'is_sale_price_with_tax'    =>  0,
                'tax_id'                    =>  $request->tax_id,
                'status'                    =>  $request->status,
            ];

            if($request->operation == 'save'){
                // Create a new expense record using Eloquent and save it
                $recordsToSave['count_id']      = $this->getLastCountId()+1;
                $recordsToSave['image_path']    = $filename;

                $itemModel = RawItems::create($recordsToSave);

            } else {
                $itemModel = RawItems::find($request->item_id);
                if(!empty($filename)){
                    $recordsToSave['image_path']    = $filename;
                }

                //Load Item Transactions like a opening stock
                $itemTransactions = $itemModel->itemTransaction;
                foreach ($itemTransactions as $itemTransaction) {

                    $itemTransaction->delete();
                }

                //Update the records
                $itemModel->update($recordsToSave);
            }

            $request->request->add(['itemModel' => $itemModel]);

            if(!$transaction = $this->recordInItemTransactionEntry($request)){
                throw new \Exception(__('item.failed_to_record_item_transactions'));
            }
            DB::commit();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_saved_successfully'),
                'id' => $request->itemModel->id,
                'name' => $request->itemModel->name,

            ]);
        } catch (\Exception $e) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => $e->getMessage(),
                ], 409);

        }
    }

    public function recordInItemTransactionEntry($request)
    {
        /**
         * Item Model has method transaction method
         * */
        $itemModel = $request->itemModel;

        $transaction = RawItemTransaction::create([
            'transaction_type' => 'Raw Item Opening',
            'transaction_id' => $itemModel->id,
            'unique_code' => ItemTransactionUniqueCode::ITEM_OPENING->value,
            'transaction_date' => date('Y-m-d H:i:s'),
            'store_id' => config('constants.default_store'),
            'raw_item_id' => $itemModel->id,
            'description' => '',
            'unit_id' => $request->base_unit_id,
            'quantity' => $request->opening_quantity,
            'unit_price' => $request->price,
            'tax_id' => $request->tax_id,
            'tax_type' => 'inclusive',
            'total' => $request->opening_quantity * $request->price,

        ]);

        return $transaction;
    }

    private function uploadImage($image): string
    {
        // Generate a unique filename for the image
        $random = uniqid();
        $filename = $random . '.' . $image->getClientOriginalExtension();
        $directory = 'images/raw_items';

        // Create the directory if it doesn't exist
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        // Store the file in the 'items' directory with the specified filename
        Storage::disk('public')->putFileAs($directory, $image, $filename);

        // Create Thumbnail
        // Generate temporary file path for thumbnail
        $thumbnailDirectory = $directory . '/thumbnail';
        if (!Storage::disk('public')->exists($thumbnailDirectory)) {
            Storage::disk('public')->makeDirectory($thumbnailDirectory);
        }


        // Load the image
        $imagePath = Storage::disk('public')->path($directory . '/' . $filename);

        //Thumbnai Path
        $thumbnailPath = Storage::disk('public')->path($thumbnailDirectory . '/' . $filename );

        //Load Actual Image
        $thumbImage = Image::load(pathToImage: $imagePath)
                            ->width(width: 200)
                            ->height(200)
                            ->save($thumbnailPath);

        // Return both the original filename and the thumbnail data URI
        return $filename;
    }
    public function list() : View {
        return view('items.raw-item.list');
    }

    public function datatableList(Request $request){
        $warehouseId = request('warehouse_id');
        $data = RawItems::with([ 'user', 'tax', 'category'])
                        ->when($request->item_category_id, function ($query) use ($request) {
                            return $query->where('item_category_id', $request->item_category_id);
                        })
                        ->when($request->created_by, function ($query) use ($request) {
                            return $query->where('created_by', $request->created_by);
                        });

        return DataTables::of($data)
                    ->filter(function ($query) use ($request) {
                        if ($request->has('search')) {
                            $searchTerm = $request->search['value'];
                            $query->where(function ($q) use ($searchTerm) {
                                $q->where('name', 'like', "%{$searchTerm}%")
                                  ->orWhere('description', 'like', "%{$searchTerm}%")
                                  ->orWhere('price', 'like', "%{$searchTerm}%")
                                  ->orWhere('item_code', 'like', "%{$searchTerm}%")
                                  // Add more columns as needed

                                  ->orWhereHas('tax', function ($taxQuery) use ($searchTerm) {
                                      $taxQuery->where('name', 'like', "%{$searchTerm}%");
                                  })
                                  ->orWhereHas('category', function ($categoryQuery) use ($searchTerm) {
                                      $categoryQuery->where('name', 'like', "%{$searchTerm}%");
                                  });
                            });
                        }
                    })
                    ->addIndexColumn()
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at->format(app(\App\Models\Company::class)['date_format']);
                    })
                    ->addColumn('username', function ($row) {
                        return $row->user->username??'';
                    })
                    ->editColumn('price', function ($row) {
                        return $this->formatWithPrecision($row->price);
                    })
                    ->addColumn('category_name', function ($row) {
                        return $row->category->name;
                    })
                    ->editColumn('current_stock', function ($row){
                        return $this->formatQuantity($row->current_stock);

                    })
                    ->addColumn('action', function($row){
                            $id = $row->id;

                            $editUrl = route('raw_items.edit', ['id' => $id]);
                            // $deleteUrl = route('raw_items.delete', ['id' => $id]);
                            // $transactionUrl = route('item.transaction.list', ['id' => $id]);
                            
                            // <li>
                            //     <a class="dropdown-item" href="' . $transactionUrl . '"><i class="bi bi-trash"></i><i class="bx bx-transfer-alt"></i> '.__('app.transactions').'</a>
                            // </li>

                            $actionBtn = '<div class="dropdown ms-auto">
                            <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="' . $editUrl . '"><i class="bi bi-trash"></i><i class="bx bx-edit"></i> '.__('app.edit').'</a>
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

    public function delete(Request $request) : JsonResponse{

        $selectedRecordIds = $request->input('record_ids');

        // Perform validation for each selected record ID
        foreach ($selectedRecordIds as $recordId) {
            $record = RawItems::find($recordId);
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
            RawItems::whereIn('id', $selectedRecordIds)->chunk(100, function ($items) {
                foreach ($items as $item) {
                    //Load Item Transactions like Opening Balance
                    $itemTransactions = $item->itemTransaction;

                    //Delete only if Opening Stock transaction exist, else don't allow to delete
                    $filter = ItemTransaction::where('item_id', $item->id)
                       ->whereNotIn('unique_code', [ItemTransactionUniqueCode::ITEM_OPENING->value])
                       ->get();
                    if($filter->count() == 0){
                        foreach ($itemTransactions as $itemTransaction) {
                            //Delete Item Account Transactions
                            $itemTransaction->accountTransaction()->delete();

                            //Delete Item Transaction
                            $itemTransaction->delete();
                        }
                    }else{
                        throw new \Exception(__('app.cannot_delete_records')."<br>Item Name: ".$item->name);
                    }
                }
            });

            // Delete Complete Item
            $itemModel = RawItems::whereIn('id', $selectedRecordIds)->delete();

            return response()->json([
                'status'    => true,
                'message' => __('app.record_deleted_successfully'),
            ]);
        } catch (\Illuminate\Database\QueryException $e) {

                return response()->json([
                    'status'    => false,
                    'message' => __('app.cannot_delete_records'),
                ],409);

        }
    }

}
