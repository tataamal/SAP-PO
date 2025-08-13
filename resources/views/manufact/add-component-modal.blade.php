<!--add-component-modal.blade.php -->
<div id="modal-add-component" class="fixed inset-0 z-50 items-center justify-center p-4 bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Add Component</h2>
                <button onclick="closeModalAddComponent()" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form id="add-component-form" method="POST" action="{{ route('manufact.add.component') }}">
                @csrf
                <input type="hidden" id="add-component-aufnr" name="iv_aufnr">
                <input type="hidden" id="add-component-vornr" name="iv_vornr">
                <input type="hidden" id="add-component-plant" name="iv_werks">
                
                <div class="space-y-4">
                    <!-- Production Order (Read Only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Production Order</label>
                        <input type="text" id="display-aufnr" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                    </div>

                    <!-- Operation Number (Read Only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Operation Number</label>
                        <input type="text" id="display-vornr" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                    </div>

                    <!-- Plant (Read Only) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Plant</label>
                        <input type="text" id="display-plant" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                    </div>

                    <!-- Material Number -->
                    <div>
                        <label for="add-component-matnr" class="block text-sm font-medium text-gray-700 mb-1">Material Number *</label>
                        <input type="text" id="add-component-matnr" name="iv_matnr" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter material number" required>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label for="add-component-bdmng" class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                        <input type="number" id="add-component-bdmng" name="iv_bdmng" step="0.001" min="0.001"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter quantity" required>
                    </div>

                    <!-- Unit of Measure -->
                    <div>
                        <label for="add-component-meins" class="block text-sm font-medium text-gray-700 mb-1">Unit of Measure *</label>
                        <select id="add-component-meins" name="iv_meins" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select Unit</option>
                            <option value="PC">PC - Piece</option>
                            <option value="KG">KG - Kilogram</option>
                            <option value="M">M - Meter</option>
                            <option value="L">L - Liter</option>
                            <option value="M2">M2 - Square Meter</option>
                            <option value="M3">M3 - Cubic Meter</option>
                            <option value="SET">SET - Set</option>
                            <option value="PAA">PAA - Pair</option>
                            <option value="ROL">ROL - Roll</option>
                            <option value="BTL">BTL - Bottle</option>
                        </select>
                    </div>

                    <!-- Storage Location -->
                    <div>
                        <label for="add-component-lgort" class="block text-sm font-medium text-gray-700 mb-1">Storage Location *</label>
                        <input type="text" id="add-component-lgort" name="iv_lgort"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter storage location" required>
                    </div>

                    <!-- Item Category (Hidden - always L) -->
                    <input type="hidden" name="iv_postp" value="L">
                </div>

                <div class="flex justify-between pt-6 border-t border-gray-200 mt-6">
                    <button type="button" onclick="closeModalAddComponent()"
                            class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" id="add-component-submit-btn"
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Add Component
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModalAddComponent(aufnr, vornr, plant) {
    // Set values in form
    document.getElementById('add-component-aufnr').value = aufnr;
    document.getElementById('add-component-vornr').value = vornr;
    document.getElementById('add-component-plant').value = plant;
    
    // Set display values
    document.getElementById('display-aufnr').value = aufnr;
    document.getElementById('display-vornr').value = vornr;
    document.getElementById('display-plant').value = plant;
    
    // Clear input fields
    document.getElementById('add-component-matnr').value = '';
    document.getElementById('add-component-bdmng').value = '';
    document.getElementById('add-component-meins').value = '';
    document.getElementById('add-component-lgort').value = '';
    
    // Show modal
    const modal = document.getElementById('modal-add-component');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModalAddComponent() {
    const modal = document.getElementById('modal-add-component');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// UPDATED: Handle form submission with AJAX for real-time update
document.getElementById('add-component-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission
    
    const submitBtn = document.getElementById('add-component-submit-btn');
    const originalText = submitBtn.textContent;
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';
    
    // Get form data
    const formData = new FormData(this);
    
    // Send AJAX request
    fetch(this.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification(data.message, 'success');
            
            // Close modal
            closeModalAddComponent();
            
            // Update the component table if it's currently displayed
            const aufnr = document.getElementById('add-component-aufnr').value;
            updateComponentTable(aufnr, data.components);
            
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menambahkan komponen.', 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Function to update component table
function updateComponentTable(aufnr, components) {
    const componentTableContainer = document.getElementById(`tdata4-${aufnr}`);
    if (componentTableContainer) {
        // Find the tbody in the component table
        const tbody = componentTableContainer.querySelector('tbody');
        if (tbody) {
            // Clear existing rows
            tbody.innerHTML = '';
            
            // Add new rows
            components.forEach((bom, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="border px-3 py-2 text-center">
                        <input type="checkbox" 
                               class="component-select-${aufnr}" 
                               data-aufnr="${aufnr}"
                               data-rspos="${bom.RSPOS}"
                               data-material="${bom.MATNR ? ltrimZeros(bom.MATNR) : '-'}"
                               onchange="handleComponentSelect('${aufnr}')">
                    </td>
                    <td class="border px-3 py-2 text-center">${index + 1}</td>
                    <td class="border px-3 py-2">${bom.MATNR ? ltrimZeros(bom.MATNR) : '-'}</td>
                    <td class="border px-3 py-2">${bom.MAKTX || '-'}</td>
                    <td class="border px-3 py-2">${bom.BDMNG || '-'}</td>
                    <td class="border px-3 py-2">${bom.KALAB || '-'}</td>
                    <td class="border px-3 py-2">${bom.LTEXT && bom.LTEXT.trim() !== '' ? bom.LTEXT : 'in house production'}</td>
                `;
                tbody.appendChild(row);
            });
            
            // Clear any existing selections
            clearComponentSelections(aufnr);
        }
    }
}

// Function to show notifications
function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}
</script>