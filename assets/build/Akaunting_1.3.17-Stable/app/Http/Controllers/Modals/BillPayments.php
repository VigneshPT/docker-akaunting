<?php

namespace App\Http\Controllers\Modals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\BillPayment as Request;
use App\Models\Expense\Bill;
use App\Models\Banking\Account;
use App\Models\Expense\BillPayment;
use App\Models\Expense\BillHistory;
use App\Models\Setting\Currency;
use App\Utilities\Modules;
use App\Traits\Uploads;
use App\Jobs\Expense\CreateBillPayment;

class BillPayments extends Controller
{
    use Uploads;

    /**
     * Instantiate a new controller instance.
     */
    public function __construct()
    {
        // Add CRUD permission check
        $this->middleware('permission:create-expenses-bills')->only(['create', 'store', 'duplicate', 'import']);
        $this->middleware('permission:read-expenses-bills')->only(['index', 'show', 'edit', 'export']);
        $this->middleware('permission:update-expenses-bills')->only(['update', 'enable', 'disable']);
        $this->middleware('permission:delete-expenses-bills')->only('destroy');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Bill $bill)
    {
        $accounts = Account::enabled()->orderBy('name')->pluck('name', 'id');

        $currencies = Currency::enabled()->orderBy('name')->pluck('name', 'code')->toArray();

        $currency = Currency::where('code', $bill->currency_code)->first();

        $payment_methods = Modules::getPaymentMethods();

        $paid = $this->getPaid($bill);

        // Get Bill Totals
        foreach ($bill->totals as $bill_total) {
            $bill->{$bill_total->code} = $bill_total->amount;
        }

        $total = money($bill->total, $currency->code, true)->format();

        $bill->grand_total = money($total, $currency->code)->getAmount();

        if (!empty($paid)) {
            $bill->grand_total = round($bill->total - $paid, $currency->precision) ;
        }

        $rand = rand();

        $html = view('modals.bills.payment', compact('bill', 'accounts', 'currencies', 'currency', 'payment_methods', 'rand'))->render();

        return response()->json([
            'success' => true,
            'error' => false,
            'message' => 'null',
            'html' => $html,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Bill $bill, Request $request)
    {   
        $createBillFunc = new CreateBillPayment($request,$bill);
        return $createBillFunc->createExpBillPayment($bill,$request);
    }

    protected function getPaid($bill)
    {
        $paid = 0;

        // Get Bill Payments
        if ($bill->payments->count()) {
            $_currencies = Currency::enabled()->pluck('rate', 'code')->toArray();

            foreach ($bill->payments as $item) {
                $default_amount = (double) $item->amount;

                if ($bill->currency_code == $item->currency_code) {
                    $amount = $default_amount;
                } else {
                    $default_amount_model = new BillPayment();

                    $default_amount_model->default_currency_code = $bill->currency_code;
                    $default_amount_model->amount = $default_amount;
                    $default_amount_model->currency_code = $item->currency_code;
                    $default_amount_model->currency_rate = $_currencies[$item->currency_code];

                    $default_amount = (double) $default_amount_model->getDivideConvertedAmount();

                    $convert_amount = new BillPayment();

                    $convert_amount->default_currency_code = $item->currency_code;
                    $convert_amount->amount = $default_amount;
                    $convert_amount->currency_code = $bill->currency_code;
                    $convert_amount->currency_rate = $_currencies[$bill->currency_code];

                    $amount = (double) $convert_amount->getDynamicConvertedAmount();
                }

                $paid += $amount;
            }
        }

        return $paid;
    }
}
