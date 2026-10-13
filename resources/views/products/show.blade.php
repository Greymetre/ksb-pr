<x-app-layout>
  @include('odoo_sync.partials.assets')

  @php
    // Same detail row ProductSync writes MRP / GST to: primary first, then oldest
    $detail = $products->productdetails->sortBy('id')->sortByDesc('isprimary')->first();
    $mrp = $detail->mrp ?? $odoo->mrp ?? null;
    $gst = $detail->gst ?? $odoo->gst_percent ?? null;
    $cost = $odoo->standard_price ?? null;
    $isDeleted = $odoo && $odoo->is_deleted;
    $isActive = $products->active === 'Y';
    $money = fn ($v) => $v === null || $v === '' ? '—' : '₹ ' . number_format((float) $v, 2);
    $date = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d M Y, h:i A') : '—';
  @endphp

  <style>
    .pd-hero { display: flex; gap: 18px; align-items: center; }
    .pd-hero-icon { width: 56px; height: 56px; border-radius: 14px; display: grid; place-items: center; background: rgba(34,211,238,.1); color: var(--fk-list-accent, #22d3ee); flex: none; }
    .pd-hero-icon .material-icons { font-size: 28px; }
    .pd-name { font-size: 22px; font-weight: 700; color: var(--fk-list-heading, #fff); margin: 0; line-height: 1.3; word-break: break-word; }
    .pd-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; align-items: center; }
    .pd-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
    .pd-field { padding: 12px 14px; border-radius: 12px; border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); background: var(--fk-list-row, rgba(13,28,64,.62)); min-width: 0; }
    .pd-label { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: var(--fk-list-muted, #7d8fbf); display: flex; align-items: center; gap: 6px; }
    .pd-label .material-icons { font-size: 15px; }
    .pd-value { font-size: 15px; font-weight: 600; color: var(--fk-list-heading, #fff); margin-top: 6px; word-break: break-word; }
    .pd-value.is-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 14px; }
    .pd-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    @media (max-width: 991px) { .pd-cols { grid-template-columns: 1fr; } .pd-hero { align-items: flex-start; } }
  </style>

  <div class="os-page">
    <div class="os-head">
      <div>
        <div class="os-breadcrumb">Product Management › <a href="{{ route('products.index') }}" class="os-muted">Product List</a> › <b>Product Details</b></div>
        <h1 class="os-title"><span class="material-icons">inventory_2</span>Product Details</h1>
      </div>
      <div class="os-head-actions">
        <a href="{{ route('products.index') }}" class="os-btn os-btn-ghost"><span class="material-icons" style="font-size:16px">arrow_back</span> Back</a>
      </div>
    </div>

    {{-- Name and status --}}
    <div class="os-panel">
      <div class="pd-hero">
        <div class="pd-hero-icon"><span class="material-icons">inventory_2</span></div>
        <div style="min-width:0">
          <h2 class="pd-name">{{ $products->product_name }}</h2>
          <div class="pd-tags">
            @if($isDeleted)
              <span class="os-pill os-pill-danger">Deleted in Odoo</span>
            @elseif($isActive)
              <span class="os-pill os-pill-success">Active</span>
            @else
              <span class="os-pill os-pill-neutral">Inactive</span>
            @endif
            @if($products->odoo_product_id)
              <span class="os-pill os-pill-test">Odoo #{{ $products->odoo_product_id }}</span>
            @endif
          </div>
        </div>
      </div>
    </div>

    {{-- Pricing --}}
    <div class="os-stats is-3">
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">sell</span>MRP</div>
        <div class="os-stat-value">{{ $money($mrp) }}</div>
        <div class="os-stat-note">Per {{ $products->unitmeasures->unit_name ?? $odoo->uom_name ?? 'unit' }}</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">payments</span>Cost price</div>
        <div class="os-stat-value">{{ $money($cost) }}</div>
        <div class="os-stat-note">Odoo standard price</div>
      </div>
      <div class="os-stat">
        <div class="os-stat-label"><span class="material-icons">percent</span>GST</div>
        <div class="os-stat-value">{{ $gst === null || $gst === '' ? '—' : rtrim(rtrim(number_format((float) $gst, 2), '0'), '.') . '%' }}</div>
        <div class="os-stat-note">Tax rate</div>
      </div>
    </div>

    <div class="pd-cols">
      {{-- Classification --}}
      <div class="os-panel">
        <div class="os-panel-title">Classification</div>
        <div class="pd-grid">
          <div class="pd-field">
            <div class="pd-label"><span class="material-icons">category</span>Category</div>
            <div class="pd-value">{{ $products->categories->category_name ?? '—' }}</div>
          </div>
          <div class="pd-field">
            <div class="pd-label"><span class="material-icons">account_tree</span>Sub-category</div>
            <div class="pd-value">{{ $products->subcategories->subcategory_name ?? '—' }}</div>
          </div>
          <div class="pd-field">
            <div class="pd-label"><span class="material-icons">straighten</span>Unit of measure</div>
            <div class="pd-value">{{ $products->unitmeasures->unit_name ?? $odoo->uom_name ?? '—' }}</div>
          </div>
        </div>
      </div>

      {{-- Odoo sync info --}}
      <div class="os-panel">
        <div class="os-panel-title">Odoo sync</div>
        <div class="pd-grid">
          <div class="pd-field">
            <div class="pd-label"><span class="material-icons">tag</span>Odoo product ID</div>
            <div class="pd-value is-mono">{{ $products->odoo_product_id ?? '—' }}</div>
          </div>
          <div class="pd-field">
            <div class="pd-label"><span class="material-icons">sync</span>Last synced</div>
            <div class="pd-value">{{ $date($odoo->updated_at ?? null) }}</div>
          </div>
          <div class="pd-field">
            <div class="pd-label"><span class="material-icons">edit_calendar</span>Updated in Odoo</div>
            <div class="pd-value">{{ $date($odoo->odoo_updated_at ?? null) }}</div>
          </div>
          <div class="pd-field">
            <div class="pd-label"><span class="material-icons">event</span>Created in Odoo</div>
            <div class="pd-value">{{ $date($odoo->odoo_created_at ?? null) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</x-app-layout>
