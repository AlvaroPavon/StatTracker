package com.stattracker.mobile.ui.screens.add_metric

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.stattracker.mobile.data.model.MetricRequest
import com.stattracker.mobile.data.repository.StatTrackerRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale

sealed class AddMetricUiState {
    object Idle : AddMetricUiState()
    object Loading : AddMetricUiState()
    object Success : AddMetricUiState()
    data class Error(val message: String) : AddMetricUiState()
}

class AddMetricViewModel(
    private val repository: StatTrackerRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow<AddMetricUiState>(AddMetricUiState.Idle)
    val uiState: StateFlow<AddMetricUiState> = _uiState.asStateFlow()

    fun addMetric(pesoStr: String, alturaStr: String) {
        val peso = pesoStr.toDoubleOrNull()
        val altura = alturaStr.toDoubleOrNull()

        if (peso == null || altura == null) {
            _uiState.value = AddMetricUiState.Error("Por favor, introduce valores numéricos válidos")
            return
        }

        if (peso <= 0 || altura <= 0) {
            _uiState.value = AddMetricUiState.Error("Los valores deben ser mayores que cero")
            return
        }

        viewModelScope.launch {
            _uiState.value = AddMetricUiState.Loading
            try {
                val sdf = SimpleDateFormat("yyyy-MM-dd", Locale.getDefault())
                val currentDate = sdf.format(Date())
                
                val request = MetricRequest(peso, altura, currentDate)
                val response = repository.createMetric(request)
                
                if (response.isSuccessful && response.body()?.success == true) {
                    _uiState.value = AddMetricUiState.Success
                } else {
                    _uiState.value = AddMetricUiState.Error("Error al guardar el registro")
                }
            } catch (e: Exception) {
                _uiState.value = AddMetricUiState.Error("Error de red: ${e.localizedMessage}")
            }
        }
    }
    
    fun resetState() {
        _uiState.value = AddMetricUiState.Idle
    }
}
