<x-app-layout>
    <style>
        body.fk-shell #user_ids + .select2-container .select2-selection--multiple {
            height: var(--fk-form-control-h) !important;
            min-height: var(--fk-form-control-h) !important;
            overflow: hidden !important;
            padding: 3px 10px !important;
        }

        body.fk-shell #user_ids + .select2-container .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 5px !important;
            width: 100% !important;
            height: 34px !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            white-space: nowrap !important;
            scrollbar-width: thin;
            scrollbar-color: var(--fk-form-border) transparent;
        }

        body.fk-shell #user_ids + .select2-container .select2-selection__choice,
        body.fk-shell #user_ids + .select2-container .select2-search--inline {
            flex: 0 0 auto !important;
            margin-top: 0 !important;
        }

        body.fk-shell #user_ids + .select2-container .select2-selection__choice,
        body.fk-shell #user_ids + .select2-container .select2-search__field {
            color: var(--fk-list-text) !important;
            font-size: 13px !important;
            font-weight: 300 !important;
        }

        body.fk-shell #user_ids + .select2-container .select2-search__field::placeholder {
            color: var(--fk-form-placeholder) !important;
            opacity: 1 !important;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon"><i class="material-icons">notifications_active</i></div>
                    <h4 class="card-title">News Management</h4>
                </div>
                <div class="card-body">
                    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

                    <form method="POST" action="{{ route('notification-management.send') }}" id="notificationForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            @foreach([['state_id','State','All States',$states,'state_name'],['district_id','District','All Districts',$districts,'district_name'],['city_id','City','All Cities',$cities,'city_name']] as $filter)
                                <div class="col-md-3">
                                    <label for="{{ $filter[0] }}">{{ $filter[1] }}</label>
                                    <select class="form-control select2 notification-filter" name="{{ $filter[0] }}" id="{{ $filter[0] }}">
                                        <option value="">{{ $filter[2] }}</option>
                                        @foreach($filter[3] as $row)
                                            <option value="{{ $row->id }}">{{ $row->{$filter[4]} }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                            <div class="col-md-3">
                                <label for="user_ids">Users</label>
                                <select class="form-control select2" name="user_ids[]" id="user_ids" multiple
                                    data-placeholder="All Users" data-close-on-select="false" style="width: 100%;">
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', [])))>
                                            {{ $user->name }}{{ $user->mobile ? ' - '.$user->mobile : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Select one or more users. Leave empty for all matching users.</small>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <label for="title">Notification Title</label>
                                <input class="form-control" id="title" name="title" type="text" maxlength="150"
                                    placeholder="Enter the notification title..." value="{{ old('title') }}" required>
                                <small class="text-muted">Maximum 150 characters.</small>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label for="message">Notification Message</label>
                                <textarea class="form-control" id="message" name="message" rows="5" maxlength="1000" placeholder="Enter the message to send..." required>{{ old('message') }}</textarea>
                                <small class="text-muted">Maximum 1000 characters.</small>
                            </div>
                            <div class="col-md-6 mt-3">
                                <label for="image">Notification Image <span class="text-muted">(optional)</span></label>
                                <input class="form-control" id="image" name="image" type="file"
                                    accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">JPG, PNG or WebP, maximum 5 MB.</small>
                            </div>
                            <div class="col-md-6 mt-3">
                                <img id="notificationImagePreview" alt="Notification image preview"
                                    class="img-fluid rounded d-none" style="max-height: 180px;">
                            </div>
                            <div class="col-md-12 mt-3">
                                <button type="submit" class="btn btn-theme" id="sendButton">
                                    <i class="material-icons">send</i> Send News
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterUrl = @json(route('notification-management.filters'));
            const state = $('#state_id'), district = $('#district_id'), city = $('#city_id');
            let loading = false;
            function replaceOptions(element, rows, placeholder, labelKey, selected) {
                element.empty().append(new Option(placeholder, ''));
                rows.forEach(row => element.append(new Option(row[labelKey], row.id, false, String(row.id) === String(selected))));
                element.trigger('change.select2');
            }
            function replaceUsers(rows) {
                const userSelect = $('#user_ids').empty();
                rows.forEach(row => userSelect.append(new Option(row.display_name, row.id)));
                userSelect.val(null).trigger('change');
            }
            function refreshFilters(changedId) {
                if (loading) return;
                loading = true;
                if (changedId === 'state_id') { district.val(''); city.val(''); }
                else if (changedId === 'district_id') city.val('');
                const values = {state_id: state.val(), district_id: district.val(), city_id: city.val()};
                $.get(filterUrl, values).done(function (response) {
                    replaceOptions(district, response.districts, 'All Districts', 'district_name', values.district_id);
                    replaceOptions(city, response.cities, 'All Cities', 'city_name', values.city_id);
                    replaceUsers(response.users);
                }).always(() => loading = false);
            }
            $('.notification-filter').on('change', function () { refreshFilters(this.id); });
            $('#image').on('change', function () {
                const file = this.files && this.files[0];
                const preview = $('#notificationImagePreview');
                if (!file) {
                    preview.attr('src', '').addClass('d-none');
                    return;
                }
                preview.attr('src', URL.createObjectURL(file)).removeClass('d-none');
            });
            $('#notificationForm').on('submit', function () { $('#sendButton').prop('disabled', true).text('Sending...'); });
        });
    </script>
</x-app-layout>
