<x-app-layout>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header card-header-icon card-header-theme">
                    <div class="card-icon">
                        <i class="material-icons">add</i>
                    </div>
                    <h4 class="card-title">
                        Create New Invoice
                    </h4>
                </div>

                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Error!</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('new-invoices.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Step 1: Customer Selection -->
                        <div class="form-group">
                            <label class="col-form-label">
                                <strong>Select Customer</strong>
                                <span class="text-danger">*</span>
                            </label>
                            <select id="secondary_customer_id" name="secondary_customer_id" 
                                class="form-control select2" required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                    {{ old('secondary_customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->shop_name }} - {{ $customer->owner_name }} ({{ $customer->mobile_number }})
                                </option>
                                @endforeach
                            </select>
                            @error('secondary_customer_id')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Step 2: Customer Details (Display Only) -->
                        <div id="customerDetailsDiv" style="display:none;">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="col-form-label"><strong>Customer Name</strong></label>
                                        <input type="text" id="owner_name" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="col-form-label"><strong>Mobile Number</strong></label>
                                        <input type="text" id="mobile_number" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="col-form-label"><strong>Shop Name</strong></label>
                                        <input type="text" id="shop_name" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Invoice Details -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        <strong>Invoice Number</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="invoice_number" id="invoice_number" 
                                        class="form-control @error('invoice_number') is-invalid @enderror" 
                                        value="{{ old('invoice_number') }}" placeholder="e.g., INV-001" required>
                                    @error('invoice_number')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        <strong>Invoice Date</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="invoice_date" id="invoice_date" 
                                        class="form-control datepicker @error('invoice_date') is-invalid @enderror" 
                                        value="{{ old('invoice_date', date('Y-m-d')) }}" 
                                        autocomplete="off" readonly required>
                                    @error('invoice_date')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        <strong>Pre-GST Amount</strong>
                                        <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Rs.</span>
                                        </div>
                                        <input type="number" name="amount" id="amount" 
                                            class="form-control @error('amount') is-invalid @enderror" 
                                            value="{{ old('amount') }}" placeholder="0.00" 
                                            step="0.01" min="0.01" required>
                                    </div>
                                    @error('amount')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="col-form-label">
                                        <strong>Points</strong>
                                    </label>
                                    <input type="number" name="points" id="points"
                                        class="form-control @error('points') is-invalid @enderror"
                                        value="{{ old('points', 0) }}" placeholder="0.00"
                                        step="0.01" min="0">
                                    @error('points')
                                    <small class="form-text text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Attachments -->
                        <div class="form-group">
                            <label class="col-form-label">
                                <strong>Attachments</strong>
                                <small class="text-muted">(Optional - Images or PDF files, max 10MB each)</small>
                            </label>

                            <!-- Drag and Drop Upload Area -->
                            <div id="attachment-upload-area" class="card border-dashed">
                                <div class="card-body text-center py-5">
                                    <div class="upload-icon mb-3">
                                        <i class="material-icons" style="font-size: 48px; color: #9c27b0;">cloud_upload</i>
                                    </div>
                                    <h5 class="card-title mb-2">Drag & Drop Files Here</h5>
                                    <p class="card-text text-muted mb-3">or click to browse files</p>
                                    <button type="button" class="btn btn-outline-primary" id="browse-files-btn">
                                        <i class="material-icons">folder_open</i> Browse Files
                                    </button>
                                    <p class="text-xs text-muted mt-2">Supported: JPG, PNG, GIF, PDF (Max 10MB each)</p>
                                </div>
                            </div>

                            <!-- Hidden File Input -->
                            <input type="file" name="attachments[]" id="attachments" 
                                class="d-none @error('attachments') is-invalid @enderror" 
                                multiple accept=".jpg,.jpeg,.png,.gif,.pdf">

                            <!-- File Preview Area -->
                            <div id="file-preview-container" class="mt-3" style="display: none;">
                                <h6 class="mb-3">Selected Files:</h6>
                                <div id="file-previews" class="row"></div>
                            </div>

                            @error('attachments')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                            @error('attachments.*')
                            <small class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="material-icons">check</i> Create Invoice
                                </button>
                                <a href="{{ route('new-invoices.index') }}" class="btn btn-secondary">
                                    <i class="material-icons">cancel</i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Initialize datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        // Initialize Select2
        $('#secondary_customer_id').select2({
            placeholder: 'Select a customer...',
            width: '100%'
        });

        // Load customer details when customer is selected
        $('#secondary_customer_id').on('change', function() {
            const customerId = $(this).val();
            
            if (!customerId) {
                $('#customerDetailsDiv').hide();
                $('#owner_name').val('');
                $('#mobile_number').val('');
                $('#shop_name').val('');
                return;
            }

            // Find customer in data and populate fields
            const customers = {!! json_encode($customers) !!};
            const customer = customers.find(c => c.id == customerId);

            if (customer) {
                $('#owner_name').val(customer.owner_name);
                $('#mobile_number').val(customer.mobile_number);
                $('#shop_name').val(customer.shop_name);
                $('#customerDetailsDiv').show();
            }
        });

        // Initialize on page load if customer is pre-selected (edit mode)
        if ($('#secondary_customer_id').val()) {
            $('#secondary_customer_id').trigger('change');
        }

        // File Upload Functionality
        let selectedFiles = [];
        const maxFileSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];

        // Drag and Drop functionality
        const uploadArea = $('#attachment-upload-area');
        const fileInput = $('#attachments');
        const filePreviewContainer = $('#file-preview-container');
        const filePreviews = $('#file-previews');

        // Browse files button
        $('#browse-files-btn').on('click', function() {
            fileInput.click();
        });

        // Drag over
        uploadArea.on('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        // Drag leave
        uploadArea.on('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        // Drop files
        uploadArea.on('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;
            handleFiles(files);
        });

        // File input change
        fileInput.on('change', function() {
            handleFiles(this.files);
        });

        function handleFiles(files) {
            Array.from(files).forEach(file => {
                if (validateFile(file)) {
                    selectedFiles.push(file);
                    renderFilePreview(file);
                }
            });
            updateFileInput();
            togglePreviewContainer();
        }

        function validateFile(file) {
            // Check file type
            if (!allowedTypes.includes(file.type)) {
                showError(`Invalid file type: ${file.name}. Only images and PDF files are allowed.`);
                return false;
            }

            // Check file size
            if (file.size > maxFileSize) {
                showError(`File too large: ${file.name}. Maximum size is 10MB.`);
                return false;
            }

            // Check for duplicates
            const isDuplicate = selectedFiles.some(f => f.name === file.name && f.size === file.size);
            if (isDuplicate) {
                showError(`Duplicate file: ${file.name} is already selected.`);
                return false;
            }

            return true;
        }

        function renderFilePreview(file) {
            const fileId = Date.now() + Math.random();
            file.fileId = fileId;
            const isImage = file.type.startsWith('image/');
            const fileSizeFormatted = formatFileSize(file.size);

            const previewHtml = `
                <div class="col-md-6 col-lg-4 mb-3" data-file-id="${fileId}">
                    <div class="file-preview-card card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="file-icon ${isImage ? 'image' : 'pdf'} mr-3">
                                <i class="material-icons">${isImage ? 'image' : 'description'}</i>
                            </div>
                            <div class="file-info flex-grow-1">
                                <div class="file-name" title="${file.name}">${file.name}</div>
                                <div class="file-size">${fileSizeFormatted}</div>
                            </div>
                            <button type="button" class="remove-file-btn ml-2" data-file-id="${fileId}">
                                <i class="material-icons">close</i>
                            </button>
                        </div>
                        <div class="upload-progress" style="width: 100%"></div>
                    </div>
                </div>
            `;

            filePreviews.append(previewHtml);

            // Add remove functionality
            $(`[data-file-id="${fileId}"] .remove-file-btn`).on('click', function() {
                removeFile(fileId);
            });
        }

        function removeFile(fileId) {
            // Remove from selectedFiles array
            const index = selectedFiles.findIndex(f => f.fileId === fileId);
            if (index > -1) {
                selectedFiles.splice(index, 1);
            }

            // Remove preview
            $(`[data-file-id="${fileId}"]`).remove();

            // Update file input
            updateFileInput();

            // Hide container if no files
            togglePreviewContainer();
        }

        function updateFileInput() {
            // Create new DataTransfer object
            const dt = new DataTransfer();

            // Add remaining files
            selectedFiles.forEach((file, index) => {
                file.fileId = file.fileId || Date.now() + Math.random();
                dt.items.add(file);
            });

            // Update the file input
            fileInput[0].files = dt.files;
        }

        function togglePreviewContainer() {
            if (selectedFiles.length > 0) {
                filePreviewContainer.show();
            } else {
                filePreviewContainer.hide();
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function showError(message) {
            // You can replace this with a toast notification or alert
            alert(message);
        }
    });
    </script>

    <style>
    .border-dashed {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .border-dashed:hover {
        border-color: #9c27b0;
        background-color: #f8f5ff;
    }

    .border-dashed.dragover {
        border-color: #9c27b0;
        background-color: #f3e8ff;
        transform: scale(1.02);
    }

    .file-preview-card {
        position: relative;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .file-preview-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .file-preview-card .card-body {
        padding: 12px;
    }

    .file-icon {
        width: 40px;
        height: 40px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .file-icon.image { background-color: #e8f5e8; color: #2e7d32; }
    .file-icon.pdf { background-color: #ffebee; color: #c62828; }

    .file-info {
        flex: 1;
        min-width: 0;
    }

    .file-name {
        font-size: 14px;
        font-weight: 500;
        color: #333;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 2px;
    }

    .file-size {
        font-size: 12px;
        color: #666;
    }

    .remove-file-btn {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        padding: 4px;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .remove-file-btn:hover {
        background-color: #f8d7da;
        transform: scale(1.1);
    }

    .upload-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background-color: #9c27b0;
        transition: width 0.3s ease;
    }

    .file-error {
        border-color: #dc3545 !important;
        background-color: #f8d7da !important;
    }

    .file-error .file-name {
        color: #dc3545;
    }
    </style>
</x-app-layout>
