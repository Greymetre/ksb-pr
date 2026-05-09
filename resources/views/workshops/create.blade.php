<x-app-layout>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header card-header-icon card-header-theme">
                <div class="card-icon">
                    <i class="material-icons">engineering</i>
                </div>
                <h4 class="card-title">
                    {{ isset($customer) && $customer->exists ? 'Edit' : 'Add New' }} Workshop
                    <span class="pull-right">
                        <a href="{{ route('workshops.index') }}" class="btn btn-just-icon btn-theme">
                            <i class="material-icons">next_plan</i>
                        </a>
                    </span>
                </h4>
            </div>

            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert">
                            <i class="material-icons">close</i>
                        </button>
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {!! Form::model($customer ?? new SecondaryCustomer, [
                    'route' => isset($customer) && $customer->exists 
                        ? ['workshops.update', $customer] 
                        : 'workshops.store',
                    'method' => isset($customer) && $customer->exists ? 'PUT' : 'POST',
                    'files' => true,
                    'id' => 'workshopForm'
                ]) !!}

                {{-- BEST PRACTICE: Dynamic type from controller --}}
                {!! Form::hidden('type', $type) !!}

                <div class="row">
                    <div class="col-md-4">
                        <label>Type</label>
                        <input type="text" class="form-control" value="WORKSHOP" disabled>
                    </div>
                    <div class="col-md-4">
                        <label>Select Sub Type <span class="text-danger">*</span></label>
                        {!! Form::select('sub_type', [
                            '' => 'Select Sub Type',
                            'Lube & Filter Change Workshop' => 'Lube & Filter Change Workshop',
                            'Two-Wheeler Service Workshop' => 'Two-Wheeler Service Workshop',
                            'Car Service Workshop' => 'Car Service Workshop',
                            'HCV - LCV Workshop' => 'HCV - LCV Workshop'
                        ], old('sub_type', $customer->sub_type ?? null), [
                            'class' => 'form-control select2',
                            'required'
                        ]) !!}
                    </div>
                    <div class="col-md-4">
                        <label>Owner Name <span class="text-danger">*</span></label>
                        {!! Form::text('owner_name', old('owner_name', $customer->owner_name ?? null), [
                            'class' => 'form-control',
                            'required'
                        ]) !!}
                    </div>
                </div>

                {{-- Workshop ke liye extra fields (jaise Retailer mein tha) --}}
                <!-- <div class="row mt-4">
                    <div class="col-md-6">
                        <label>Distributor Name <span class="text-danger">*</span></label>
                        {!! Form::select('distributor_name', [
                            '' => 'Select Distributor',
                            '200295 - SHIVAM ENTERPRISES' => '200295 - SHIVAM ENTERPRISES',
                            '200217 - CHOPRA MOTOR STORE' => '200217 - CHOPRA MOTOR STORE',
                            '200198 - BANKA DISTRIBUTORS' => '200198 - BANKA DISTRIBUTORS',
                            '200035 - L.B.DISTRIBUTOR' => '200035 - L.B.DISTRIBUTOR',
                            '201405 - GUPTA AUTO STORE' => '201405 - GUPTA AUTO STORE'
                        ], old('distributor_name', $customer->distributor_name ?? null), [
                            'class' => 'form-control select2',
                            'required'
                        ]) !!}
                    </div>
                    
                </div> -->

                <div class="row mt-4">

                <div class="col-md-6">
    <label>Distributor Name <span class="text-danger">*</span></label>
    {!! Form::select('distributor_name', $distributorOptions ?? [], 
        old('distributor_name', $customer->distributor_name ?? null), [
        'class' => 'form-control select2',
        'required',
        
    ]) !!}
</div>
  </div>

                @include('secondary_customers.partials.common_fields')

                <div class="row mt-4">
    <div class="col-md-4">
    <label>Nistha Awareness Status <span class="text-danger">*</span></label>
    {!! Form::select('nistha_awareness_status', [
        'Not Done' => 'Not Done',
        'Done' => 'Done'
    ], old('nistha_awareness_status', $customer->nistha_awareness_status ?? null), [
        'class' => 'form-control select2',
        'required'
    ]) !!}
</div>

    <div class="col-md-4">
        <label>Opportunity Status <span class="text-danger">*</span></label>
        {!! Form::select('opportunity_status', [
            'COLD' => 'COLD – Low interest / only enquiry',
            'WARM' => 'WARM – Interested but needs time',
            'HOT' => 'HOT – Very interested/almost confirm',
            'LOST' => 'LOST – Deal cancelled'
        ], old('opportunity_status', $customer->opportunity_status ?? null), [
            'class' => 'form-control select2',
            'required'
        ]) !!}
    </div>

    <div class="col-md-4">
        <label>Beat <span class="text-danger">*</span></label>
        {!! Form::select('beat_id', ['' => 'Select Beat'] + $beats->toArray(), 
            old('beat_id', $customer->beat_id ?? null), [
            'class' => 'form-control select2',
            'required'
        ]) !!}
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <label>GPS Location Updates</label>
        {!! Form::text('gps_location', old('gps_location', $customer->gps_location ?? null), [
            'class' => 'form-control',
            'placeholder' => 'e.g., 17.385044,78.486671'
        ]) !!}
    </div>
</div>

                <div class="card-footer text-right mt-5">
                    <button type="submit" class="btn btn-theme">
                        {{ isset($customer) && $customer->exists ? 'Update' : 'Save' }} Workshop
                    </button>
                    <a href="{{ route('workshops.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<script>
    $('.select2').select2();
    // Address AJAX chaining script here (same as others)
</script>
</x-app-layout>