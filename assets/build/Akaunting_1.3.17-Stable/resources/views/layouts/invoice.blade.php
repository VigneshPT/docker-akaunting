<html lang="{{ app()->getLocale() }}">
    @include('partials.invoice.head')

    <body onload="window.print();">
        @stack('body_start')
        <div class="text-center" style="margin-left: 0; page-break-after: always;">
            @yield('content')
        </div>
        @stack('body_end')
    </body>
</html>
