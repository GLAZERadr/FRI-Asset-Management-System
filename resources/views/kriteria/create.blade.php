@extends('layouts.app')

@section('header', 'Asset Management')

@section('content')

<div class="container mx-auto">

    <!-- Download Template Button -->
    <div class="mb-6">
        <a href="{{ route('pengajuan.template.download') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
            Download Template Excel
        </a>
    </div>

    <!-- Pilih Kriteria Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Pilih Kriteria</h2>
            <button id="addCriteriaBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                Tambah Kriteria
            </button>
        </div>
        <div id="criteriaContainer" class="space-y-4">
            @foreach($criteria as $index => $criterion)
            <div class="criteria-row flex items-center space-x-4" data-criteria-id="{{ $criterion->kriteria_id }}">
                <input type="text" value="{{ $criterion->nama_kriteria }}" 
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md bg-gray-100" readonly>
                <select class="px-3 py-2 border border-gray-300 rounded-md bg-gray-100 w-32" disabled>
                    <option value="benefit" {{ $criterion->tipe_kriteria == 'benefit' ? 'selected' : '' }}>Benefit</option>
                    <option value="cost" {{ $criterion->tipe_kriteria == 'cost' ? 'selected' : '' }}>Cost</option>
                </select>
                <button type="button" class="delete-criteria bg-red-500 hover:bg-red-600 text-white p-2 rounded-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
            @endforeach
            
            <!-- New criteria input row (initially hidden) -->
            <div id="newCriteriaRow" class="criteria-row flex items-center space-x-4" style="display: none;">
                <input type="text" id="newCriteriaName" placeholder="Masukan Kriteria" 
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select id="newCriteriaType" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 w-32">
                    <option value="">Pilih Tipe Kriteria</option>
                    <option value="benefit">Benefit</option>
                    <option value="cost">Cost</option>
                </select>
                <button type="button" id="saveCriteriaBtn" class="bg-green-500 hover:bg-green-600 text-white p-2 rounded-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </button>
                <button type="button" id="cancelCriteriaBtn" class="bg-gray-500 hover:bg-gray-600 text-white p-2 rounded-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Pairwise Comparison Section -->
    <div class="bg-white rounded-lg shadow-md p-6" id="comparisonSection">
        <h2 class="text-xl font-semibold mb-4">Perbandingan berpasangan</h2>
        <p class="text-sm text-gray-600 mb-4">Pilih yang lebih penting (Semakin besar semakin penting)</p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <tbody id="comparisonTableBody">
                    <!-- Dynamic content will be inserted here -->
                </tbody>
            </table>
        </div>
        <div class="mt-6 flex justify-end space-x-4">
            <button id="calculateBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-md">
                Mulai Hitung
            </button>
        </div>
    </div>

    <!-- Results Section -->
    <div id="resultsSection" class="bg-white rounded-lg shadow-md p-6 mt-6" style="display: none;">
        <h2 class="text-xl font-semibold mb-4">Hasil Perhitungan</h2>
        
        <!-- Comparison Matrix -->
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Matriks Perbandingan</h3>
            <div class="overflow-x-auto">
                <table id="comparisonMatrix" class="min-w-full border-collapse border border-gray-300">
                    <!-- Dynamic content -->
                </table>
            </div>
        </div>

        <!-- Normalized Matrix -->
        <div class="mb-6">
            <h3 class="text-lg font-medium mb-2">Matriks Nilai Kriteria</h3>
            <div class="overflow-x-auto">
                <table id="normalizedMatrix" class="min-w-full border-collapse border border-gray-300">
                    <!-- Dynamic content -->
                </table>
            </div>
        </div>

        <!-- Consistency Check -->
        <div class="mb-6">
            <div id="consistencyResult" class="p-4 rounded-md">
                <!-- Dynamic content -->
            </div>
        </div>

        <div class="flex justify-end">
            <button id="continueBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md" style="display: none;">
                Lanjut
            </button>
            <button id="recalculateBtn" class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-md" style="display: none;">
                Hitung Ulang
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let criteriaData = @json($criteria);
    let calculationResult = null; // Store AHP calculation result
    
    // Add Criteria Button - Show inline form
    const addCriteriaBtn = document.getElementById('addCriteriaBtn');
    const newCriteriaRow = document.getElementById('newCriteriaRow');
    const saveCriteriaBtn = document.getElementById('saveCriteriaBtn');
    const cancelCriteriaBtn = document.getElementById('cancelCriteriaBtn');
    const newCriteriaName = document.getElementById('newCriteriaName');
    const newCriteriaType = document.getElementById('newCriteriaType');

    addCriteriaBtn.addEventListener('click', () => {
        newCriteriaRow.style.display = 'flex';
        addCriteriaBtn.style.display = 'none';
        newCriteriaName.focus();
    });

    cancelCriteriaBtn.addEventListener('click', () => {
        newCriteriaRow.style.display = 'none';
        addCriteriaBtn.style.display = 'block';
        newCriteriaName.value = '';
        newCriteriaType.value = '';
    });

    // Save New Criteria
    saveCriteriaBtn.addEventListener('click', async () => {
        const nama = newCriteriaName.value.trim();
        const tipe = newCriteriaType.value;

        if (!nama) {
            alert('Nama kriteria harus diisi');
            newCriteriaName.focus();
            return;
        }

        if (!tipe) {
            alert('Tipe kriteria harus dipilih');
            newCriteriaType.focus();
            return;
        }

        const formData = new FormData();
        formData.append('nama_kriteria', nama);
        formData.append('tipe_kriteria', tipe);
        
        try {
            const response = await fetch('{{ route("kriteria.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                location.reload(); // Reload to show new criteria
            } else {
                alert('Error adding criteria');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error adding criteria');
        }
    });

    // Allow Enter key to save criteria
    newCriteriaName.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            saveCriteriaBtn.click();
        }
    });

    newCriteriaType.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            saveCriteriaBtn.click();
        }
    });

    // Delete Criteria Function
    document.addEventListener('click', async (e) => {
        if (e.target.closest('.delete-criteria')) {
            const criteriaRow = e.target.closest('.criteria-row');
            const criteriaId = criteriaRow.dataset.criteriaId;
            
            if (confirm('Apakah Anda yakin ingin menghapus kriteria ini?')) {
                try {
                    const response = await fetch(`{{ url('kriteria') }}/${criteriaId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        criteriaRow.remove();
                        updateComparisons();
                    }
                } catch (error) {
                    console.error('Error:', error);
                }
            }
        }
    });

    // Generate Pairwise Comparisons
    function updateComparisons() {
        const criteriaRows = document.querySelectorAll('.criteria-row[data-criteria-id]');
        const tableBody = document.getElementById('comparisonTableBody');
        tableBody.innerHTML = '';

        const activeCriteria = Array.from(criteriaRows).map(row => ({
            id: row.dataset.criteriaId,
            name: row.querySelector('input').value
        }));

        console.log('Active criteria:', activeCriteria);

        // Generate pairwise comparisons (only upper triangle of matrix)
        for (let i = 0; i < activeCriteria.length; i++) {
            for (let j = i + 1; j < activeCriteria.length; j++) {
                const row = document.createElement('tr');
                row.className = 'border-b border-gray-200';
                
                // Create the comparison scale (9 to 1 to 9)
                let scaleHTML = '';
                
                // Left side (9-2)
                for (let k = 9; k >= 2; k--) {
                    scaleHTML += `
                        <td class="px-2 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <input type="radio" name="comparison_${i}_${j}" value="${activeCriteria[i].id}_${k}" 
                                    class="comparison-radio mb-1" ${k === 2 ? 'checked' : ''}>
                                <span class="text-xs">${k}</span>
                            </div>
                        </td>
                    `;
                }
                
                // Center (1)
                scaleHTML += `
                    <td class="px-2 py-4 text-center">
                        <div class="flex flex-col items-center">
                            <input type="radio" name="comparison_${i}_${j}" value="equal_1" 
                                class="comparison-radio mb-1">
                            <span class="text-xs">1</span>
                        </div>
                    </td>
                `;
                
                // Right side (2-9)
                for (let k = 2; k <= 9; k++) {
                    scaleHTML += `
                        <td class="px-2 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <input type="radio" name="comparison_${i}_${j}" value="${activeCriteria[j].id}_${k}" 
                                    class="comparison-radio mb-1">
                                <span class="text-xs">${k}</span>
                            </div>
                        </td>
                    `;
                }
                
                row.innerHTML = `
                    <td class="px-4 py-4 font-medium text-right">${activeCriteria[i].name}</td>
                    ${scaleHTML}
                    <td class="px-4 py-4 font-medium text-left">${activeCriteria[j].name}</td>
                `;
                
                // Add data attributes for processing
                row.setAttribute('data-criteria1', activeCriteria[i].id);
                row.setAttribute('data-criteria2', activeCriteria[j].id);
                
                tableBody.appendChild(row);
            }
        }

        console.log(`Generated ${activeCriteria.length * (activeCriteria.length - 1) / 2} pairwise comparisons`);
    }

    // Calculate AHP
    document.getElementById('calculateBtn').addEventListener('click', async () => {
        const comparisons = [];
        const criteriaRows = document.querySelectorAll('.criteria-row[data-criteria-id]');
        const activeCriteria = Array.from(criteriaRows).map(row => ({
            id: row.dataset.criteriaId,
            name: row.querySelector('input').value
        }));

        // Collect pairwise comparison data from the new format
        const comparisonRows = document.querySelectorAll('#comparisonTableBody tr');
        
        comparisonRows.forEach(row => {
            const criteria1 = row.getAttribute('data-criteria1');
            const criteria2 = row.getAttribute('data-criteria2');
            
            // Find selected radio button
            const selectedRadio = row.querySelector('input[type="radio"]:checked');
            
            if (selectedRadio) {
                const value = selectedRadio.value;
                
                if (value === 'equal_1') {
                    // Equal importance
                    comparisons.push({
                        criteria_1: criteria1,
                        criteria_2: criteria2,
                        value: 1
                    });
                } else {
                    // Parse the value (format: criteriaId_importance or equal_1)
                    const parts = value.split('_');
                    const selectedCriteria = parts[0];
                    const importance = parseInt(parts[1]);
                    
                    if (selectedCriteria === criteria1) {
                        // criteria1 is more important
                        comparisons.push({
                            criteria_1: criteria1,
                            criteria_2: criteria2,
                            value: importance
                        });
                    } else {
                        // criteria2 is more important
                        comparisons.push({
                            criteria_1: criteria2,
                            criteria_2: criteria1,
                            value: importance
                        });
                    }
                }
            }
        });

        // Add diagonal comparisons (self-comparison = 1)
        activeCriteria.forEach(criterion => {
            comparisons.push({
                criteria_1: criterion.id,
                criteria_2: criterion.id,
                value: 1
            });
        });

        console.log('Final comparisons to send:', comparisons);

        try {
            const response = await fetch('{{ route("kriteria.calculate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ comparisons })
            });
            
            // ADD THIS MISSING PART:
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('AHP calculation result:', result);
            
            if (result.error) {
                alert(`Error: ${result.error}`);
                return;
            }
            
            // Store the calculation result
            calculationResult = result;
            displayResults(result);
            // END OF MISSING PART
            
        } catch (error) {
            console.error('Error details:', error);
            
            let errorMessage = 'Error calculating AHP';
            if (error.message) {
                errorMessage += ': ' + error.message;
            }
            
            alert(errorMessage);
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
            errorDiv.innerHTML = `
                <strong>Calculation Error:</strong> ${errorMessage}<br>
                <small>Check browser console for more details.</small>
            `;
            
            const comparisonSection = document.getElementById('comparisonSection');
            comparisonSection.insertBefore(errorDiv, comparisonSection.firstChild);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }
    });

    function displayResults(result) {
        document.getElementById('resultsSection').style.display = 'block';
        
        // Display comparison matrix
        displayMatrix('comparisonMatrix', result.matrix, result.criteria);
        
        // Display normalized matrix with weights and totals
        displayNormalizedMatrix('normalizedMatrix', result.normalized_matrix, result.criteria, result.weights);
        
        // Display consistency check
        const consistencyDiv = document.getElementById('consistencyResult');
        const isConsistent = result.consistency_ratio <= 0.1;
        
        // Calculate RI (Random Index) - you should get this from your backend
        const n = result.criteria.length;
        const randomIndex = result.random_index || 0; // Get RI from backend response
        
        consistencyDiv.className = `p-4 rounded-md ${isConsistent ? 'consistency-good' : 'consistency-bad'}`;
        consistencyDiv.innerHTML = `
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <p><strong>Lambda Max:</strong> ${result.lambda_max.toFixed(3)}</p>
                    <p><strong>Consistency Index (CI):</strong> ${result.consistency_index.toFixed(3)}</p>
                </div>
                <div>
                    <p><strong>Random Index (RI):</strong> ${randomIndex.toFixed(3)}</p>
                    <p><strong>Consistency Ratio (CR):</strong> ${(result.consistency_ratio * 100).toFixed(1)}%</p>
                </div>
                <div>
                    <p><strong>Formula:</strong></p>
                    <p>CR = CI / RI</p>
                    <p>${result.consistency_index.toFixed(3)} / ${randomIndex.toFixed(3)} = ${result.consistency_ratio.toFixed(3)}</p>
                </div>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold ${isConsistent ? 'text-green-600' : 'text-red-600'}">
                    ${isConsistent 
                        ? 'Nilai Consistency Ratio kurang Dari 10%, Kriteria Konsisten' 
                        : 'Nilai Consistency Ratio Lebih Dari 10%, Mohon Ubah Nilai Perbandingan'}
                </p>
            </div>
        `;
        
        // Show appropriate button
        if (isConsistent) {
            document.getElementById('continueBtn').style.display = 'inline-block';
            document.getElementById('recalculateBtn').style.display = 'none';
        } else {
            document.getElementById('continueBtn').style.display = 'none';
            document.getElementById('recalculateBtn').style.display = 'inline-block';
        }
    }

    function displayNormalizedMatrix(tableId, matrix, criteria, weights) {
        const table = document.getElementById(tableId);
        table.innerHTML = '';
        table.className = 'min-w-full border-collapse border border-gray-300 result-matrix';
        
        // Header row
        const headerRow = table.insertRow();
        headerRow.insertCell().innerHTML = '';
        criteria.forEach(criterion => {
            const cell = headerRow.insertCell();
            cell.innerHTML = criterion.nama_kriteria;
            cell.className = 'border border-gray-300 px-2 py-1 bg-gray-100 text-center font-medium';
        });
        
        // Add headers for totals and weights
        let cell = headerRow.insertCell();
        cell.innerHTML = 'Jumlah';
        cell.className = 'border border-gray-300 px-2 py-1 bg-gray-100 text-center font-medium';
        
        cell = headerRow.insertCell();
        cell.innerHTML = 'Bobot Kriteria';
        cell.className = 'border border-gray-300 px-2 py-1 bg-gray-100 text-center font-medium';
        
        // Data rows
        matrix.forEach((row, i) => {
            const tr = table.insertRow();
            const nameCell = tr.insertCell();
            nameCell.innerHTML = criteria[i].nama_kriteria;
            nameCell.className = 'border border-gray-300 px-2 py-1 bg-gray-100 font-medium';
            
            // Matrix values
            let rowSum = 0;
            row.forEach(value => {
                const cell = tr.insertCell();
                cell.innerHTML = value.toFixed(3);
                cell.className = 'border border-gray-300 px-2 py-1 text-center';
                rowSum += value;
            });
            
            // Row sum
            const sumCell = tr.insertCell();
            sumCell.innerHTML = rowSum.toFixed(3);
            sumCell.className = 'border border-gray-300 px-2 py-1 text-center font-medium';
            
            // Weight
            const weightCell = tr.insertCell();
            weightCell.innerHTML = weights[i].toFixed(3);
            weightCell.className = 'border border-gray-300 px-2 py-1 text-center font-medium bg-yellow-50';
        });
    }

    function displayMatrix(tableId, matrix, criteria, weights = null) {
        const table = document.getElementById(tableId);
        table.innerHTML = '';
        
        // Header row
        const headerRow = table.insertRow();
        headerRow.insertCell().innerHTML = '';
        criteria.forEach(criterion => {
            const cell = headerRow.insertCell();
            cell.innerHTML = criterion.nama_kriteria;
            cell.className = 'border border-gray-300 px-2 py-1 bg-gray-100 text-center font-medium';
        });
        
        if (weights) {
            const cell = headerRow.insertCell();
            cell.innerHTML = 'Bobot Kriteria';
            cell.className = 'border border-gray-300 px-2 py-1 bg-gray-100 text-center font-medium';
        }
        
        // Data rows
        matrix.forEach((row, i) => {
            const tr = table.insertRow();
            const nameCell = tr.insertCell();
            nameCell.innerHTML = criteria[i].nama_kriteria;
            nameCell.className = 'border border-gray-300 px-2 py-1 bg-gray-100 font-medium';
            
            row.forEach(value => {
                const cell = tr.insertCell();
                cell.innerHTML = typeof value === 'number' ? value.toFixed(3) : value;
                cell.className = 'border border-gray-300 px-2 py-1 text-center';
            });
            
            if (weights) {
                const weightCell = tr.insertCell();
                weightCell.innerHTML = weights[i].toFixed(3);
                weightCell.className = 'border border-gray-300 px-2 py-1 text-center font-medium';
            }
        });
    }

    // Continue Button - Store AHP weights and trigger TOPSIS calculation
    document.getElementById('continueBtn').addEventListener('click', async () => {
        if (!calculationResult) {
            alert('No calculation result available');
            return;
        }
        
        try {
            // Show loading state
            const btn = document.getElementById('continueBtn');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Menyimpan bobot...';
            
            // Store the AHP weights in session/database
            const storeResponse = await fetch('{{ route("kriteria.store-weights") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    criteria: calculationResult.criteria,
                    weights: calculationResult.weights,
                    consistency_ratio: calculationResult.consistency_ratio
                })
            });
            
            if (!storeResponse.ok) {
                throw new Error('Failed to store weights');
            }
            
            btn.textContent = 'Menghitung prioritas TOPSIS...';
            
            // Trigger TOPSIS calculation
            const topsisResponse = await fetch('{{ route("pengajuan.topsis.calculate") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            });
            
            const topsisData = await topsisResponse.json();
            
            if (topsisData.success) {
                // Show success message
                alert('Bobot AHP berhasil disimpan dan prioritas TOPSIS telah dihitung!');
                
                // Redirect to pengajuan create page
                window.location.href = '{{ route("pengajuan.create") }}';
            } else {
                throw new Error(topsisData.message || 'Failed to calculate TOPSIS');
            }
            
        } catch (error) {
            console.error('Error:', error);
            alert('Error: ' + error.message);
            
            // Reset button state
            document.getElementById('continueBtn').disabled = false;
            document.getElementById('continueBtn').textContent = 'Lanjut';
        }
    });

    // Recalculate button
    document.getElementById('recalculateBtn').addEventListener('click', () => {
        document.getElementById('resultsSection').style.display = 'none';
        calculationResult = null;
    });

    // Initialize comparisons
    updateComparisons();
});
</script>

@endsection