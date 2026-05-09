<x-app-layout>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header card-header-icon card-header-theme">
                <div class="card-icon">
                    <i class="material-icons">build_circle</i>
                </div>
                <h4 class="card-title">
                    Edit Mechanic
                    <span class="pull-right">
                        <a href="{{ route('mechanics.index') }}" class="btn btn-just-icon btn-theme">
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

                {!! Form::model($customer, [
                    'route' => ['mechanics.update', $customer],
                    'method' => 'PUT',
                    'files' => true,
                    'id' => 'mechanicEditForm'
                ]) !!}

                {!! Form::hidden('type', $type) !!}

                <div class="row">
                    <div class="col-md-4">
                        <label>Type</label>
                        <input type="text" class="form-control" value="MECHANIC" disabled>
                    </div>
                    <div class="col-md-4">
                        <label>Select Sub Type <span class="text-danger">*</span></label>
                        {!! Form::select('sub_type', [
                            '' => 'Select Sub Type',
                            'Two-Wheeler Mechanic' => 'Two-Wheeler Mechanic',
                            'Car / 4W Mechanic' => 'Car / 4W Mechanic',
                            'HCV-LCV Mechanic' => 'HCV-LCV Mechanic',
                            'Tractor / Agri Machine' => 'Tractor / Agri Machine',
                            'Diesel/FIP Mechanic' => 'Diesel/FIP Mechanic'
                        ], old('sub_type', $customer->sub_type), [
                            'class' => 'form-control select2',
                            'required'
                        ]) !!}
                    </div>
                    <div class="col-md-4">
                        <label>Owner Name <span class="text-danger">*</span></label>
                        {!! Form::text('owner_name', old('owner_name', $customer->owner_name), [
                            'class' => 'form-control',
                            'required'
                        ]) !!}
                    </div>
                     
                </div>

                <!-- <div class="row mt-3">
                    <div class="col-md-6">
                        <label>Shop Name <span class="text-danger">*</span></label>
                        {!! Form::text('shop_name', old('shop_name', $customer->shop_name), [
                            'class' => 'form-control',
                            'required'
                        ]) !!}
                    </div>
                    <div class="col-md-6">
                        <label>Mobile Number <span class="text-danger">*</span></label>
                        {!! Form::text('mobile_number', old('mobile_number', $customer->mobile_number), [
                            'class' => 'form-control',
                            'required'
                        ]) !!}
                    </div>
                </div> -->

                @include('secondary_customers.partials.common_fields')

                <div class="row mt-4">
    <div class="col-md-4">
        <label>Saathi Awareness Status <span class="text-danger">*</span></label>
        {!! Form::select('saathi_awareness_status', [
            'Not Done' => 'Not Done',
            'Done' => 'Done'
        ], old('saathi_awareness_status', $customer->saathi_awareness_status ?? null), [
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
                    <button type="submit" class="btn btn-theme">Update Mechanic</button>
                    <a href="{{ route('mechanics.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

<script>
    $('.select2').select2();

    // File input preview initialize (agar Jasny use kar rahe ho)
    $('.fileinput').fileinput();
</script>
</x-app-layout>