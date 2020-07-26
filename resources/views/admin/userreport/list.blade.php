@extends('layouts.app', ['showAds' => false, 'title' => __('User reports')])

@section('header-title')
    {{ __('View User Reports') }}
@endsection

<?php
/** @var $models \Illuminate\Support\Collection */
// eager load the classification
//dd($models);
?>

@section('scripts')
    <script type="text/javascript">
        $(function () {
            $('#admin_user_reports_table').DataTable({});
        });
    </script>
@endsection

@section('content')
    <table id="admin_user_reports_table" class="tablesorter default_table table-striped">
        <thead>
        <tr>
            <th width="10%">{{ __('Id') }}</th>
            <th width="10%">{{ __('Author name') }}</th>
            <th width="10%">{{ __('Category') }}</th>
            <th width="40%">{{ __('Message') }}</th>
            <th width="10%">{{ __('Contact at') }}</th>
            <th width="10%">{{ __('Created at') }}</th>
            <th width="10%">{{ __('Actions') }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($models as $report)
            <?php /** @var $user \App\Models\UserReport */?>
            <tr>
                <td>{{ $report->id }}</td>
                <td>{{ $report->user->name }}</td>
                <td>{{ $report->category }}</td>
                <td>{{ $report->message }}</td>
                <td>{{ $report->contact_ok ? $report->user->email : '-' }}</td>
                <td>{{ $report->created_at }}</td>
                <td>Mark as handled</td>
            </tr>
        @endforeach
        </tbody>

    </table>
@endsection