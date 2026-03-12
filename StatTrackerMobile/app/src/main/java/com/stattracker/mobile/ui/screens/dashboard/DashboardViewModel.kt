package com.stattracker.mobile.ui.screens.dashboard

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.stattracker.mobile.data.model.Metric
import com.stattracker.mobile.data.repository.StatTrackerRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

sealed class DashboardUiState {
    object Loading : DashboardUiState()
    data class Success(val metrics: List<Metric>) : DashboardUiState()
    data class Error(val message: String) : DashboardUiState()
}

class DashboardViewModel(
    private val repository: StatTrackerRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow<DashboardUiState>(DashboardUiState.Loading)
    val uiState: StateFlow<DashboardUiState> = _uiState.asStateFlow()

    init {
        loadMetrics()
    }

    fun loadMetrics() {
        viewModelScope.launch {
            _uiState.value = DashboardUiState.Loading
            try {
                val response = repository.getMetrics()
                if (response.isSuccessful && response.body()?.success == true) {
                    _uiState.value = DashboardUiState.Success(response.body()?.metrics ?: emptyList())
                } else {
                    _uiState.value = DashboardUiState.Error("Error al cargar las métricas")
                }
            } catch (e: Exception) {
                _uiState.value = DashboardUiState.Error("Error de red: ${e.localizedMessage}")
            }
        }
    }
}
