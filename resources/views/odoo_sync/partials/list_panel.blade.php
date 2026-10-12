{{--
| Odoo sync panel on a master list (Category List, Sub Category List...).
| Params: $odooLastRun, $odooCounts [total, active], $odooSubtitle, $odooNoun,
| $odooIcon, $odooSyncUrl (POST, returns the sync summary JSON).
--}}
        @include('odoo_sync.partials.assets')
        <style>
          .cat-odoo { padding: 18px 20px 4px; }
          .cat-odoo-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 14px; }
          .cat-odoo-title { font-size: 15px; font-weight: 600; color: var(--fk-list-heading, #fff); display: flex; align-items: center; gap: 8px; }
          .cat-odoo-title .material-icons { font-size: 20px; color: var(--fk-list-accent, #5ad1f0); }
          .cat-odoo-sub { font-size: 12.5px; color: var(--fk-list-muted, #7d8fbf); margin-top: 2px; }
          .cat-odoo .os-stats { margin-bottom: 14px; gap: 12px; }
          .cat-odoo .os-stat { padding: 12px 16px; display: flex; align-items: center; gap: 14px; }
          .cat-odoo .os-stat-icon { width: 38px; height: 38px; flex: 0 0 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: rgba(90,209,240,.12); color: var(--fk-list-accent, #5ad1f0); }
          .cat-odoo .os-stat-icon .material-icons { font-size: 20px; }
          .cat-odoo .os-stat-label { font-size: 10.5px; }
          .cat-odoo .os-stat-value { font-size: 20px; margin-top: 2px; }
          .cat-odoo .os-stat-value.is-small { font-size: 15px; padding-top: 2px; }
          .cat-odoo .os-stat-note { font-size: 11.5px; margin-top: 2px; }
          .cat-odoo .os-btn { white-space: nowrap; }
        </style>
        <div class="os-page cat-odoo">
          <div class="cat-odoo-head">
            <div>
              <div class="cat-odoo-title"><span class="material-icons">sync_alt</span>Synced from Odoo</div>
              <div class="cat-odoo-sub">{{ $odooSubtitle }}</div>
            </div>
            <button type="button" class="os-btn" id="odooSyncNow" data-url="{{ $odooSyncUrl }}"><span class="material-icons">sync</span><span class="os-btn-label">Sync now</span></button>
          </div>
          <div class="os-stats is-3">
            <div class="os-stat">
              <div class="os-stat-icon"><span class="material-icons">schedule</span></div>
              <div>
                <div class="os-stat-label">Last sync</div>
                <div class="os-stat-value is-small">{{ $odooLastRun ? \Carbon\Carbon::parse($odooLastRun->created_at)->diffForHumans() : 'Not synced yet' }}</div>
                <div class="os-stat-note {{ $odooLastRun && $odooLastRun->status_code >= 300 ? 'is-danger' : '' }}">
                  @if($odooLastRun)
                    {{ \Carbon\Carbon::parse($odooLastRun->created_at)->format('d M Y, h:i A') }}{{ $odooLastRun->status_code === 502 ? ' · Failed, see Odoo Sync › Sync Overview' : '' }}
                  @else
                    Click Sync now or wait for the cron
                  @endif
                </div>
              </div>
            </div>
            <div class="os-stat">
              <div class="os-stat-icon"><span class="material-icons">{{ $odooIcon }}</span></div>
              <div>
                <div class="os-stat-label">Synced {{ $odooNoun }}</div>
                <div class="os-stat-value">{{ number_format($odooCounts['total']) }}</div>
                <div class="os-stat-note">{{ number_format($odooCounts['active']) }} active</div>
              </div>
            </div>
            <div class="os-stat">
              <div class="os-stat-icon"><span class="material-icons">cloud_done</span></div>
              <div>
                <div class="os-stat-label">{{ ucfirst($odooNoun) }} in Odoo</div>
                <div class="os-stat-value">{{ $odooLastRun && $odooLastRun->status_code !== 502 ? number_format($odooLastRun->received_count) : '—' }}</div>
                <div class="os-stat-note">Received in the last sync</div>
              </div>
            </div>
          </div>
        </div>

<script type="text/javascript">
  // Pull from Odoo now (cron also runs twice a day)
  $(function () {
    $('#odooSyncNow').on('click', function () {
      var $btn = $(this).prop('disabled', true).addClass('is-busy');
      $btn.find('.os-btn-label').text('Syncing…');
      $.ajax({
        url: $btn.data('url'),
        type: 'POST',
        data: {_token: $("meta[name='csrf-token']").attr("content")},
        success: function (r) {
          window.osToast('Synced: ' + r.created + ' created, ' + r.updated + ' updated, ' + r.skipped + ' unchanged, ' + r.failed + ' failed');
          setTimeout(function() { location.reload(); }, 1400);
        },
        error: function () {
          window.osToast('Sync failed. See Odoo Sync › Sync Overview for the error.');
          $btn.prop('disabled', false).removeClass('is-busy').find('.os-btn-label').text('Sync now');
        }
      });
    });
  });
</script>
