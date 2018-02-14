<?php
/**
 * Author: Francis Addai <me@faddai.com>
 * Date: 15/02/2017
 * Time: 14:29
 */
?>
<div class="panel tab-pane" id="guarantors">
    <div class="table-responsive">
        <table class="table">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Relationship with Client</th>
                <th>Years known Client</th>
                <th>Personal contact</th>
                <th>Work contact</th>
                <th>Company of employment</th>
                <th>Job title</th>
            </tr>
            </thead>
            <tbody>
            @if($loan->guarantors->count())
                @foreach($loan->guarantors as $guarantor)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $guarantor->name or 'n/a' }}</td>
                        <td>{{ $guarantor->relationship or 'n/a' }}</td>
                        <td>{{ $guarantor->years_known or 'n/a' }}</td>
                        <td>{{ $guarantor->personal_phone or 'n/a' }}</td>
                        <td>{{ $guarantor->work_phone or 'n/a' }}</td>
                        <td>{{ $guarantor->employer or 'n/a' }}</td>
                        <td>{{ $guarantor->job_title or 'n/a' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="9">
                        <h5 class="text-center">There are no guarantors for this loan</h5>
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
</div>