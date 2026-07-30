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
                                <label for="user_id">User</label>
                                <select class="form-control select2 notification-filter" name="user_id" id="user_id">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}{{ $user->mobile ? ' - '.$user->mobile : '' }}</option>
                                    @endforeach
                                </select>
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
            const state = $('#state_id'), district = $('#district_id'), city = $('#city_id'), user = $('#user_id');
            let loading = false;
            function replaceOptions(element, rows, placeholder, labelKey, selected) {
                element.empty().append(new Option(placeholder, ''));
                rows.forEach(row => element.append(new Option(row[labelKey], row.id, false, String(row.id) === String(selected))));
                element.trigger('change.select2');
            }
            function refreshFilters(changedId) {
                if (loading) return;
                loading = true;
                if (changedId === 'state_id') { district.val(''); city.val(''); user.val(''); }
                else if (changedId === 'district_id') { city.val(''); user.val(''); }
                else if (changedId === 'city_id') user.val('');
                const values = {state_id: state.val(), district_id: district.val(), city_id: city.val(), user_id: user.val()};
                $.get(filterUrl, values).done(function (response) {
                    replaceOptions(district, response.districts, 'All Districts', 'district_name', values.district_id);
                    replaceOptions(city, response.cities, 'All Cities', 'city_name', values.city_id);
                    replaceOptions(user, response.users, 'All Users', 'display_name', values.user_id);
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
