<x-app-layout>
  @include('odoo_sync.partials.assets')

  @php
    // Same detail row ProductSync writes MRP / GST to: primary first, then oldest
    $detail = $products->exists ? $products->productdetails->sortBy('id')->sortByDesc('isprimary')->first() : null;
    $categoryId = old('category_id', $products->category_id);
    $subcategoryId = old('subcategory_id', $products->subcategory_id);
    $unitId = old('unit_id', $products->unit_id);
    $active = old('active', $products->active ?: 'Y');
  @endphp

  <style>
    .pf-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 20px; }
    .pf-grid.is-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .pf-field.is-wide { grid-column: 1 / -1; }
    .pf-label { display: block; font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: var(--fk-list-muted, #7d8fbf); margin-bottom: 8px; font-weight: 600; }
    .pf-label .req { color: #f87171; }
    .pf-input { width: 100%; height: 44px; padding: 0 14px; border-radius: 10px; border: 1px solid var(--fk-list-border, rgba(120,160,255,.14)); background: var(--fk-list-row, rgba(13,28,64,.62)); color: var(--fk-list-heading, #fff); font-size: 14px; outline: none; transition: border-color .15s; }
    .pf-input:focus { border-color: var(--fk-list-accent, #22d3ee); }
    select.pf-input { appearance: auto; }
    select.pf-input option { background: #081a40; color: #fff; }
    .pf-affix { position: relative; }
    .pf-affix .pf-input { padding-left: 30px; }
    .pf-affix.is-suffix .pf-input { padding-left: 14px; padding-right: 30px; }
    .pf-affix span { position: absolute; top: 50%; transform: translateY(-50%); left: 14px; color: var(--fk-list-muted, #7d8fbf); font-size: 14px; pointer-events: none; }
    .pf-affix.is-suffix span { left: auto; right: 14px; }
    .pf-error { color: #f87171; font-size: 12px; margin-top: 6px; }
    .pf-note { display: flex; gap: 10px; align-items: flex-start; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(245,158,11,.3); background: rgba(245,158,11,.08); color: #fbbf24; font-size: 13px; margin-bottom: 18px; }
    .pf-note .material-icons { font-size: 18px; }
    .pf-actions { display: flex; justify-content: flex-end; gap: 10px; }
    @media (max-width: 767px) { .pf-grid, .pf-grid.is-3 { grid-template-columns: 1fr; } }
  </style>

  <div class="os-page">
    <div class="os-head">
      <div>
        <div class="os-breadcrumb">Product Management › <a href="{{ route('products.index') }}" class="os-muted">Product List</a> › <b>{{ $products->exists ? 'Edit' : 'Add New' }}</b></div>
        <h1 class="os-title"><span class="material-icons">{{ $products->exists ? 'edit' : 'add_box' }}</span>{{ $products->exists ? 'Edit Product' : 'Create Product' }}</h1>
        @if($products->odoo_product_id)
          <p class="os-subtitle">Odoo product <span class="os-pill os-pill-test os-pill-sm">#{{ $products->odoo_product_id }}</span></p>
        @endif
      </div>
      <div class="os-head-actions">
        <a href="{{ route('products.index') }}" class="os-btn os-btn-ghost"><span class="material-icons" style="font-size:16px">arrow_back</span> Back</a>
      </div>
    </div>

    @if($products->odoo_product_id)
      <div class="pf-note"><span class="material-icons">info</span>These fields come from Odoo. The next sync overwrites changes made here whenever this product is updated in Odoo.</div>
    @endif

    @if(count($errors) > 0)
      <div class="pf-note" style="border-color:rgba(248,113,113,.3);background:rgba(248,113,113,.08);color:#f87171">
        <span class="material-icons">error_outline</span>
        <div>@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
      </div>
    @endif

    {!! Form::model($products, [
      'route' => $products->exists ? ['products.update', encrypt($products->id)] : 'products.store',
      'method' => $products->exists ? 'PUT' : 'POST',
      'id' => 'productForm',
    ]) !!}

    <div class="os-panel">
      <div class="os-panel-title">Product</div>
      <div class="pf-grid">
        <div class="pf-field is-wide">
          <label class="pf-label" for="product_name">Product name <span class="req">*</span></label>
          <input type="text" name="product_name" id="product_name" class="pf-input" value="{{ old('product_name', $products->product_name) }}" maxlength="250" required>
          @error('product_name')<div class="pf-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf-field">
          <label class="pf-label" for="category_id">Category <span class="req">*</span></label>
          <select name="category_id" id="category_id" class="pf-input" required>
            <option value="">Select category</option>
            @foreach($categories as $category)
              <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->category_name }}</option>
            @endforeach
          </select>
          @error('category_id')<div class="pf-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf-field">
          <label class="pf-label" for="subcategory_id">Sub-category <span class="req">*</span></label>
          <select name="subcategory_id" id="subcategory_id" class="pf-input" required>
            <option value="">Select sub-category</option>
            @foreach($subcategories as $subcategory)
              <option value="{{ $subcategory->id }}" data-category="{{ $subcategory->category_id }}" @selected($subcategoryId == $subcategory->id)>{{ $subcategory->subcategory_name }}</option>
            @endforeach
          </select>
          @error('subcategory_id')<div class="pf-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf-field">
          <label class="pf-label" for="unit_id">Unit of measure</label>
          <select name="unit_id" id="unit_id" class="pf-input">
            <option value="">Select unit</option>
            @foreach($units as $unit)
              <option value="{{ $unit->id }}" @selected($unitId == $unit->id)>{{ $unit->unit_name }}</option>
            @endforeach
          </select>
          @error('unit_id')<div class="pf-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf-field">
          <label class="pf-label" for="active">Status <span class="req">*</span></label>
          <select name="active" id="active" class="pf-input" required>
            <option value="Y" @selected($active === 'Y')>Active</option>
            <option value="N" @selected($active === 'N')>Inactive</option>
          </select>
          @error('active')<div class="pf-error">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>

    <div class="os-panel">
      <div class="os-panel-title">Pricing</div>
      <div class="pf-grid">
        <div class="pf-field">
          <label class="pf-label" for="mrp">MRP <span class="req">*</span></label>
          <div class="pf-affix"><span>₹</span>
            <input type="number" name="mrp" id="mrp" class="pf-input" value="{{ old('mrp', optional($detail)->mrp) }}" min="0" step="0.01" required>
          </div>
          @error('mrp')<div class="pf-error">{{ $message }}</div>@enderror
        </div>
        <div class="pf-field">
          <label class="pf-label" for="gst">GST</label>
          <div class="pf-affix is-suffix"><span>%</span>
            <input type="number" name="gst" id="gst" class="pf-input" value="{{ old('gst', optional($detail)->gst) }}" min="0" max="90" step="0.01">
          </div>
          @error('gst')<div class="pf-error">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>

    <div class="pf-actions">
      <a href="{{ route('products.index') }}" class="os-btn os-btn-ghost">Cancel</a>
      <button type="submit" class="os-btn"><span class="material-icons">save</span>{{ $products->exists ? 'Save changes' : 'Create product' }}</button>
    </div>

    {!! Form::close() !!}
  </div>

  <script type="text/javascript">
    // Sub-category list follows the chosen category
    $(function () {
      var $category = $('#category_id'), $sub = $('#subcategory_id');
      function filterSubcategories() {
        var categoryId = $category.val();
        $sub.find('option[data-category]').each(function () {
          var match = !categoryId || String($(this).data('category')) === categoryId;
          $(this).prop('hidden', !match).prop('disabled', !match);
        });
        if ($sub.find('option:selected').prop('disabled')) $sub.val('');
      }
      $category.on('change', filterSubcategories);
      filterSubcategories();
    });
  </script>
</x-app-layout>
