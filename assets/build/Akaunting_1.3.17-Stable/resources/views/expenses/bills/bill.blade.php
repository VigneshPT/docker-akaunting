@extends('layouts.bill')

@section('title', trans_choice('general.bills', 1) . ': ' . $bill->bill_number)

@section('content')
<div class="row header">
    <div class="col-1">
        @if ($logo)
        <img src="{{ $logo }}" class="logo" />
        @endif
    </div>
    <div class="col-1">
        <div class="text">
            <strong>{{ setting('general.company_name') }}</strong><br>
            {!! nl2br(setting('general.company_address')) !!}<br>
            @if (setting('general.company_tax_number'))
                {{ trans('general.tax_number') }}: {{ setting('general.company_tax_number') }}<br>
            @endif
            <br>
            @if (setting('general.company_phone'))
                {{ setting('general.company_phone') }}<br>
            @endif
            {{ setting('general.company_email') }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-1">
        <div class="text">
            {{ trans('bills.bill_from') }}<br><br>
            @stack('name_input_start')
            <strong>{{ $bill->vendor_name }}</strong><br>
            @stack('name_input_end')
            @stack('address_input_start')
            {!! nl2br($bill->vendor_address) !!}<br>
            @stack('address_input_end')
            @stack('tax_number_input_start')
            @if ($bill->vendor_tax_number)
                {{ trans('general.tax_number') }}: {{ $bill->vendor_tax_number }}<br>
            @endif
            @stack('tax_number_input_end')
            <br>
            @stack('phone_input_start')
            @if ($bill->vendor_phone)
                {{ $bill->vendor_phone }}<br>
            @endif
            @stack('phone_input_end')
            @stack('email_start')
            {{ $bill->vendor_email }}
            @stack('email_input_end')
        </div>
    </div>
    <div class="col-1">
        <div class="text">
            <table>
                <tbody>
                    @stack('bill_number_input_start')
                    <tr>
                        <th>{{ trans('bills.bill_number') }}:</th>
                        <td class="text-right">{{ $bill->bill_number }}</td>
                    </tr>
                    @stack('bill_number_input_end')
                    @stack('order_number_input_start')
                    @if ($bill->order_number)
                    <tr>
                        <th>{{ trans('bills.order_number') }}:</th>
                        <td class="text-right">{{ $bill->order_number }}</td>
                    </tr>
                    @endif
                    @stack('order_number_input_end')
                    @stack('cheque_number_input_start')
                    @if ($bill->cheque_number)
                    <tr>
                        <th></th>
                        <td class="text-right">{{ $bill->cheque_number }}</td>
                    </tr>
                    @endif
                    @stack('cheque_number_input_end')
                    @stack('ic_input_start')
                    <tr>
                        <th>{{ trans('bills.ic') }}:</th>
                        <td class="text-right">{{ $bill->ic }}</td>
                    </tr>
                    @stack('ic_input_end')
                    @stack('billed_at_input_start')
                    <tr>
                        <th>{{ trans('bills.bill_date') }}:</th>
                        <td class="text-right">{{ Date::parse($bill->billed_at)->format('H:iA '.$date_format) }}</td>
                    </tr>
                    @stack('billed_at_input_end')
                    @stack('due_at_input_start')
                    <tr>
                        <th>{{ trans('bills.payment_due') }}:</th>
                        <td class="text-right">{{ Date::parse($bill->due_at)->format($date_format) }}</td>
                    </tr>
                    @stack('due_at_input_end')
                </tbody>
            </table>
        </div>
    </div>
</div>

<table class="table small-table">
    <thead>
        <tr>
            @stack('actions_th_start')
            @stack('actions_th_end')
            @stack('name_th_start')
            <th class="item">{{ trans_choice('general.items', 1) }}</th>
            @stack('name_th_end')
            @stack('quantity_th_start')
            <th class="quantity">{{ trans('bills.quantity') }}</th>
            @stack('quantity_th_end')
            @stack('price_th_start')
            <th class="price">{{ trans('bills.price') }}</th>
            @stack('price_th_end')
            @stack('taxes_th_start')
            @stack('taxes_th_end')
            @stack('total_th_start')
            <th class="total">{{ trans('bills.total') }}</th>
            @stack('total_th_end')
        </tr>
    </thead>
    <tbody>
        @foreach($bill->items as $item)
        <tr>
            @stack('actions_td_start')
            @stack('actions_td_end')
            @stack('name_td_start')
            <td class="item">
                {{ $item->name }}
            </td>
            @stack('name_td_end')
            @stack('quantity_td_start')
            <td class="quantity">{{ $item->quantity }}</td>
            @stack('quantity_td_end')
            @stack('price_td_start')
            <td class="style-price price">@money($item->price, $bill->currency_code, true)</td>
            @stack('price_td_end')
            @stack('taxes_td_start')
            @stack('taxes_td_end')
            @stack('total_td_start')
            <td class="style-price total">@money($item->total, $bill->currency_code, true)</td>
            @stack('total_td_end')
        </tr>
        @endforeach
    </tbody>
</table>

<div class="row">
    <div class="col-1">
        @stack('notes_input_start')
        @if ($bill->notes)
        <table class="text" style="page-break-inside: avoid;">
            <tr><th>{{ trans_choice('general.notes', 2) }}</th></tr>
            <tr><td>{{ $bill->notes }}</td></tr>
        </table>
        @endif
        @stack('notes_input_end')
    </div>
    <div class="col-1">
        <table class="text" style="page-break-inside: avoid;">
            <tbody>
            @foreach ($bill->totals as $total)
                @if ($total->code != 'total')
                    @stack($total->code . '_td_start')
                    <tr>
                        <th>{{ trans($total->title) }}:</th>
                        <td class="style-price text-right">@money($total->amount, $bill->currency_code, true)</td>
                    </tr>
                    @stack($total->code . '_td_end')
                @else
                    @if ($bill->paid)
                        <tr class="text-success">
                            <th>{{ trans('invoices.paid') }}:</th>
                            <td class="style-price text-right">- @money($bill->paid, $bill->currency_code, true)</td>
                        </tr>
                    @endif
                    @stack('grand_total_td_start')
                    <tr>
                        <th>{{ trans($total->name) }}:</th>
                        <td class="style-price text-right">@money($total->amount - $bill->paid, $bill->currency_code, true)</td>
                    </tr>
                    @stack('grand_total_td_end')
                @endif
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
