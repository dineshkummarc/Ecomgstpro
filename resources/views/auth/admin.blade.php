{{-- resources/views/layouts/admin.blade.php --}}

@include('admin.layout.header')

<div style="display:flex; flex-direction:column; min-height:100vh;">

    {{-- Sidebar --}}
    @include('admin.layout.sidebar')

    {{-- Main Content — pushed right by sidebar width --}}
    <div style="
        margin-left: 240px;
        margin-top: 64px;
        flex: 1;
        min-width: 0;
        width: calc(100% - 240px);
        background: #f0f2f8;
        display: flex;
        flex-direction: column;
        min-height: calc(100vh - 64px);
        box-sizing: border-box;
        overflow-x: hidden;
    ">
        {{-- Page Content --}}
        <div style="flex:1;">
            @yield('content')
        </div>

        {{-- Footer INSIDE main content area --}}
        @include('admin.layout.footer')
    </div>

</div>

{{-- Mobile responsive --}}
<style>
@media (max-width: 960px) {
    .admin-main-wrap {
        margin-left: 0 !important;
        width: 100% !important;
    }
}
</style>
