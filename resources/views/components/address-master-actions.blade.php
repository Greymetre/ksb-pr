@php
    $canUpload = $uploadPermission ? auth()->user()->can([$uploadPermission]) : true;
    $canDownload = $downloadPermission ? auth()->user()->can([$downloadPermission]) : true;
    $canTemplate = $templatePermission ? auth()->user()->can([$templatePermission]) : true;
    $canCreate = $createPermission ? auth()->user()->can([$createPermission]) : true;
    $importInputId = $module . '-import-file';
@endphp

<span class="">
  <div class="btn-group header-frm-btn address-master-actions">
    <div class="next-btn">
      @if($canTemplate)
      <a href="{{ $templateUrl }}" class="btn btn-just-icon btn-theme" title="{!! trans('panel.global.template') !!} {!! $titleSingular !!}">
        <i class="material-icons">description</i>
      </a>
      @endif

      @if($canUpload)
      <form action="{{ $uploadUrl }}" class="form-horizontal d-inline-block address-master-import-form" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}
        <input id="{{ $importInputId }}" class="d-none address-master-import-input" type="file" name="import_file" required accept=".xls,.xlsx" onchange="this.form.submit()">
        <label for="{{ $importInputId }}" class="btn btn-just-icon btn-theme mb-0" title="{!! trans('panel.global.upload') !!} {!! $title !!}">
          <i class="material-icons">cloud_upload</i>
        </label>
      </form>
      @endif

      @if($canDownload)
      <a href="{{ $downloadUrl }}" class="btn btn-just-icon btn-theme" title="{!! trans('panel.global.download') !!} {!! $title !!}">
        <i class="material-icons">cloud_download</i>
      </a>
      @endif

      @if($canCreate)
      <a data-toggle="modal" data-target="{{ $createTarget }}" class="btn btn-just-icon btn-theme create" title="{!! trans('panel.global.add') !!} {!! $titleSingular !!}">
        <i class="material-icons">add_circle</i>
      </a>
      @endif
    </div>
  </div>
</span>
