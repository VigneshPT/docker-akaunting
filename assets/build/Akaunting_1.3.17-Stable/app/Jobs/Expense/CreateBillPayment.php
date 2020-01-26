<?php

namespace App\Jobs\Expense;

use App\Models\Expense\BillHistory;
use App\Models\Expense\BillPayment;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Expense\Bill;
use App\Models\Setting\Currency;
use App\Models\Common\Media;
use App\Traits\Uploads;
use File;
class CreateBillPayment
{
    use Dispatchable,Uploads;

    protected $request;

    protected $bill;

    /**
     * Create a new job instance.
     *
     * @param  $request
     * @param  $bill
     */
    public function __construct($request, $bill)
    {
        $this->request = $request;
        $this->bill = $bill;
    }

    /**
     * Execute the job.
     *
     * @return BillPayment
     */
    public function handle()
    {

        $bill_payment = BillPayment::create($this->request->input());

        $desc_amount = money((float) $bill_payment->amount, (string) $bill_payment->currency_code, true)->format();

        $history_data = [
            'company_id' => $bill_payment->company_id,
            'bill_id' => $bill_payment->bill_id,
            'status_code' => $this->bill->bill_status_code,
            'notify' => '0',
            'description' => $desc_amount . ' ' . trans_choice('general.payments', 1),
        ];

        BillHistory::create($history_data);

        return $bill_payment;
    }
    public function createExpBillPayment(Bill $bill,$request){

        $currencies = Currency::enabled()->pluck('rate', 'code')->toArray();
        $currency = Currency::where('code', $request['currency_code'])->first();

        $request['currency_code'] = $currency->code;
        $request['currency_rate'] = $currency->rate;

        $total_amount = $bill->amount;

        $default_amount = (double) $request['amount'];

        if ($bill->currency_code == $request['currency_code']) {
            $amount = $default_amount;
        } else {
            $default_amount_model = new BillPayment();

            $default_amount_model->default_currency_code = $bill->currency_code;
            $default_amount_model->amount                = $default_amount;
            $default_amount_model->currency_code         = $request['currency_code'];
            $default_amount_model->currency_rate         = $currencies[$request['currency_code']];

            $default_amount = (double) $default_amount_model->getDivideConvertedAmount();

            $convert_amount = new BillPayment();

            $convert_amount->default_currency_code = $request['currency_code'];
            $convert_amount->amount = $default_amount;
            $convert_amount->currency_code = $bill->currency_code;
            $convert_amount->currency_rate = $currencies[$bill->currency_code];

            $amount = (double) $convert_amount->getDynamicConvertedAmount();
        }

        if ($bill->payments()->count()) {
            $total_amount -= $this->getPaid($bill);
        }

        // For amount cover integer
        $multiplier = 1;

        for ($i = 0; $i < $currency->precision; $i++) {
            $multiplier *= 10;
        }

        $amount_check = (int) ($amount * $multiplier);
        $total_amount_check = (int) (round($total_amount, $currency->precision) * $multiplier);

        if ($amount_check > $total_amount_check) {
            $error_amount = $total_amount;

            if ($bill->currency_code != $request['currency_code']) {
                $error_amount_model = new BillPayment();

                $error_amount_model->default_currency_code = $request['currency_code'];
                $error_amount_model->amount                = $error_amount;
                $error_amount_model->currency_code         = $bill->currency_code;
                $error_amount_model->currency_rate         = $currencies[$bill->currency_code];

                $error_amount = (double) $error_amount_model->getDivideConvertedAmount();

                $convert_amount = new BillPayment();

                $convert_amount->default_currency_code = $bill->currency_code;
                $convert_amount->amount = $error_amount;
                $convert_amount->currency_code = $request['currency_code'];
                $convert_amount->currency_rate = $currencies[$request['currency_code']];

                $error_amount = (double) $convert_amount->getDynamicConvertedAmount();
            }

            $message = trans('messages.error.over_payment', ['amount' => money($error_amount, $request['currency_code'], true)]);
            if($request['savePrint'] == 'savePrint'){
                return array('error' => true,'message'=> $message);
            }
            return response()->json([
                'success' => false,
                'error' => true,
                'data' => [
                    'amount' => $error_amount
                ],
                'message' => $message,
                'html' => 'null',
            ]);
        } elseif ($amount_check == $total_amount_check) {
            $bill->bill_status_code = 'paid';
        } else {
            $bill->bill_status_code = 'partial';
        }

        $bill->save();

        $bill_payment = dispatch(new CreateBillPayment($request, $bill));

        // Upload attachment
        if ($request->file('attachment')) {
            $media = $this->getMedia($request->file('attachment'), 'bills');

            $bill_payment->attachMedia($media, 'attachment');
        }

        $message = trans('messages.success.added', ['type' => trans_choice('general.payments', 1)]);
        if($request['savePrint'] == 'savePrint'){
            return array('error' => false,'message'=> $message);
        }
        return response()->json([
            'success' => true,
            'error' => false,
            'data' => $bill_payment,
            'message' => $message,
            'html' => 'null',
        ]);
    }
}
