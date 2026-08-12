<x-app-layout>
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
                                <label>Users</label>
                                <div class="border rounded p-2" style="max-height: 220px; overflow-y: auto;">
                                    <div class="form-check mb-2">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" id="select_all_users">
                                            <strong>Select All</strong>
                                            <span class="form-check-sign"><span class="check"></span></span>
                                        </label>
                                    </div>
                                    <div id="userCheckboxes">
                                    @foreach($users as $user)
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input class="form-check-input user-checkbox" type="checkbox" name="user_ids[]"
                                                    value="{{ $user->id }}" @checked(in_array($user->id, old('user_ids', [])))>
                                                {{ $user->name }}{{ $user->mobile ? ' - '.$user->mobile : '' }}
                                                <span class="form-check-sign"><span class="check"></span></span>
                                            </label>
                                        </div>
                                    @endforeach
                                    </div>
                                </div>
                                <small class="text-muted">Leave all unchecked to send to every matching user.</small>
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
                const container = $('#userCheckboxes').empty();
                rows.forEach(row => {
                    const wrapper = $('<div>', {class: 'form-check'});
                    const label = $('<label>', {class: 'form-check-label'});
                    $('<input>', {class: 'form-check-input user-checkbox', type: 'checkbox', name: 'user_ids[]', value: row.id}).appendTo(label);
                    label.append(document.createTextNode(' ' + row.display_name));
                    label.append('<span class="form-check-sign"><span class="check"></span></span>');
                    wrapper.append(label).appendTo(container);
                });
                $('#select_all_users').prop({checked: false, indeterminate: false});
            }
            function updateSelectAll() {
                const checkboxes = $('.user-checkbox');
                const checked = checkboxes.filter(':checked').length;
                $('#select_all_users').prop('checked', checkboxes.length > 0 && checked === checkboxes.length)
                    .prop('indeterminate', checked > 0 && checked < checkboxes.length);
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
            $('#select_all_users').on('change', function () { $('.user-checkbox').prop('checked', this.checked); updateSelectAll(); });
            $('#userCheckboxes').on('change', '.user-checkbox', updateSelectAll);
            updateSelectAll();
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
