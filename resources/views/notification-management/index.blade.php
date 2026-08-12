<x-app-layout>
    <style>
        .news-user-multiselect { position: relative; }
        .news-user-control {
            display: flex; align-items: center; width: 100%; height: var(--fk-form-control-h, 42px);
            padding: 4px 34px 4px 13px; overflow: hidden; cursor: pointer;
            border: 1px solid var(--fk-form-border); border-radius: var(--fk-form-radius, 12px);
            background: var(--fk-form-bg); color: var(--fk-list-text); box-shadow: none;
        }
        .news-user-control::after {
            content: ''; position: absolute; right: 16px; top: 19px;
            border: 4px solid transparent; border-top-color: var(--fk-list-soft);
        }
        .news-user-multiselect.open .news-user-control {
            border-color: var(--fk-form-border-focus); background: var(--fk-form-bg-strong);
            box-shadow: var(--fk-form-shadow);
        }
        .news-user-placeholder { color: var(--fk-list-text); font-size: 13px; font-weight: 300; white-space: nowrap; }
        .news-user-selected {
            display: flex; flex: 1; align-items: center; gap: 5px; min-width: 0;
            overflow-x: auto; overflow-y: hidden; white-space: nowrap; scrollbar-width: thin;
            scrollbar-color: var(--fk-form-border) transparent;
        }
        .news-user-chip {
            display: inline-flex; flex: 0 0 auto; align-items: center; gap: 6px; max-width: 260px;
            padding: 3px 7px; border: 1px solid var(--fk-form-border); border-radius: 6px;
            background: var(--fk-list-panel); color: var(--fk-list-text); font-size: 13px; font-weight: 300;
        }
        .news-user-chip-text { overflow: hidden; text-overflow: ellipsis; }
        .news-user-remove { border: 0; padding: 0; background: transparent; color: #ff5d7a; font-weight: 700; cursor: pointer; }
        .news-user-dropdown {
            display: none; position: absolute; z-index: 1051; top: calc(100% + 2px); left: 0; width: 100%;
            padding: 10px; overflow: hidden; border: 1px solid rgba(90, 130, 220, .34);
            border-radius: 14px; background: rgba(7, 18, 44, .98); box-shadow: 0 20px 48px rgba(0,0,0,.36);
        }
        .news-user-multiselect.open .news-user-dropdown { display: block; }
        body.fk-shell .content .news-user-search {
            width: 100% !important; height: 38px !important; padding: 0 12px !important;
            border: 1px solid var(--fk-form-border) !important; border-radius: 10px !important;
            background: var(--fk-form-bg) !important; color: var(--fk-list-text) !important;
            font-size: 13px !important; font-weight: 300 !important; letter-spacing: 0 !important;
            text-transform: none !important;
        }
        .news-user-search::placeholder { color: var(--fk-form-placeholder) !important; opacity: 1; }
        .news-user-options { max-height: 260px; margin-top: 8px; padding: 0 2px; overflow-y: auto; scrollbar-width: thin; }
        body.fk-shell .content form #userMultiselect .news-user-option {
            display: flex !important; align-items: center !important; gap: 10px !important;
            width: 100% !important; min-height: 42px !important; margin: 0 !important; padding: 8px 10px !important;
            border-radius: 9px !important; color: var(--fk-list-text) !important;
            font-size: 13px !important; font-weight: 300 !important; line-height: 1.35 !important;
            letter-spacing: 0 !important; text-transform: none !important; cursor: pointer;
        }
        .news-user-option:hover { background: var(--fk-list-panel); }
        body.fk-shell .content form #userMultiselect .news-user-option input[type="checkbox"] {
            appearance: none !important; -webkit-appearance: none !important; flex: 0 0 17px !important;
            width: 17px !important; height: 17px !important; min-height: 17px !important;
            margin: 0 !important; padding: 0 !important; border: 1px solid var(--fk-form-border) !important;
            border-radius: 4px !important; background: var(--fk-form-bg) !important; cursor: pointer;
        }
        body.fk-shell .content form #userMultiselect .news-user-option input[type="checkbox"]:checked {
            border-color: var(--fk-list-accent) !important; background-color: var(--fk-list-accent) !important;
            background-image: linear-gradient(135deg, transparent 45%, var(--fk-list-primary-text) 45%, var(--fk-list-primary-text) 58%, transparent 58%),
                linear-gradient(45deg, transparent 32%, var(--fk-list-primary-text) 32%, var(--fk-list-primary-text) 45%, transparent 45%) !important;
        }
        .news-user-option span {
            display: block; min-width: 0; overflow: hidden; color: inherit;
            letter-spacing: 0 !important; text-transform: none !important; text-overflow: ellipsis; white-space: nowrap;
        }
        .news-user-empty { padding: 12px; color: var(--fk-form-placeholder); font-size: 13px; }

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
                                <select name="user_ids[]" id="user_ids" multiple hidden>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', [])))>
                                            {{ $user->name }}{{ $user->mobile ? ' - '.$user->mobile : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="news-user-multiselect" id="userMultiselect">
                                    <div class="news-user-control" tabindex="0" role="combobox" aria-expanded="false">
                                        <span class="news-user-placeholder">All Users</span>
                                        <div class="news-user-selected"></div>
                                    </div>
                                    <div class="news-user-dropdown">
                                        <input type="search" class="news-user-search" placeholder="Search Users..." autocomplete="off">
                                        <div class="news-user-options"></div>
                                    </div>
                                </div>
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
            const userSelect = $('#user_ids'), userMultiselect = $('#userMultiselect');
            let loading = false;
            function replaceOptions(element, rows, placeholder, labelKey, selected) {
                element.empty().append(new Option(placeholder, ''));
                rows.forEach(row => element.append(new Option(row[labelKey], row.id, false, String(row.id) === String(selected))));
                element.trigger('change.select2');
            }
            function selectedUserIds() {
                return (userSelect.val() || []).map(String);
            }
            function renderSelectedUsers() {
                const selected = selectedUserIds();
                const container = userMultiselect.find('.news-user-selected').empty();
                userMultiselect.find('.news-user-placeholder').toggle(selected.length === 0);
                selected.forEach(id => {
                    const option = userSelect.find('option[value="' + id + '"]');
                    const chip = $('<span>', {class: 'news-user-chip'});
                    $('<span>', {class: 'news-user-chip-text', text: option.text().trim()}).appendTo(chip);
                    $('<button>', {type: 'button', class: 'news-user-remove', text: '×', 'aria-label': 'Remove user'})
                        .attr('data-user-id', id).appendTo(chip);
                    container.append(chip);
                });
            }
            function renderUserOptions(term = '') {
                const selected = selectedUserIds();
                const query = term.trim().toLowerCase();
                const container = userMultiselect.find('.news-user-options').empty();
                let matches = 0;
                userSelect.find('option').each(function () {
                    const text = $(this).text().trim();
                    if (query && !text.toLowerCase().includes(query)) return;
                    matches++;
                    const label = $('<label>', {class: 'news-user-option'});
                    $('<input>', {type: 'checkbox', value: this.value, checked: selected.includes(String(this.value))}).appendTo(label);
                    $('<span>', {text}).appendTo(label);
                    container.append(label);
                });
                if (!matches) container.append($('<div>', {class: 'news-user-empty', text: 'No users found'}));
            }
            function syncUserMultiselect() {
                renderSelectedUsers();
                renderUserOptions(userMultiselect.find('.news-user-search').val() || '');
            }
            function replaceUsers(rows) {
                userSelect.empty();
                rows.forEach(row => userSelect.append(new Option(row.display_name, row.id)));
                userSelect.val(null);
                userMultiselect.find('.news-user-search').val('');
                syncUserMultiselect();
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
            userMultiselect.find('.news-user-control').on('click keydown', function (event) {
                if (event.type === 'keydown' && !['Enter', ' ', 'ArrowDown'].includes(event.key)) return;
                event.preventDefault();
                const open = !userMultiselect.hasClass('open');
                userMultiselect.toggleClass('open', open);
                $(this).attr('aria-expanded', open);
                if (open) setTimeout(() => userMultiselect.find('.news-user-search').trigger('focus'), 0);
            });
            userMultiselect.find('.news-user-search').on('input', function () { renderUserOptions(this.value); });
            userMultiselect.on('change', '.news-user-option input', function () {
                const selected = selectedUserIds();
                const id = String(this.value);
                userSelect.val(this.checked ? [...new Set([...selected, id])] : selected.filter(value => value !== id));
                renderSelectedUsers();
            });
            userMultiselect.on('click', '.news-user-remove', function (event) {
                event.stopPropagation();
                const id = String($(this).data('user-id'));
                userSelect.val(selectedUserIds().filter(value => value !== id));
                syncUserMultiselect();
            });
            $(document).on('click', function (event) {
                if (!$(event.target).closest(userMultiselect).length) {
                    userMultiselect.removeClass('open').find('.news-user-control').attr('aria-expanded', false);
                }
            });
            syncUserMultiselect();
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
