<head>
    @stack('head_start')

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8; charset=ISO-8859-1"/>

    <title>@yield('title') - @setting('general.company_name')</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <link rel="stylesheet" href="{{ asset('public/css/invoice.css?v=' . version('short')) }}">

    <style type="text/css">
        * {
            font-family: DejaVu Sans;
        }

        @media screen and (max-width: 189px)  {
            *{
                font-size: 10px;
            }

            div .small-table{
                text-align: center;
                
            }

            .table>tbody>tr>td, .table>tbody>tr>th, .table>tfoot>tr>td, .table>tfoot>tr>th, .table>thead>tr>td, .table>thead>tr>th {
                padding: 1px;
            }

        }
    </style>

    @stack('css')

    @stack('stylesheet')

    @stack('js')

    @stack('scripts')

    @stack('head_end')
</head>
