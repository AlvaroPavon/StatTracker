package com.stattracker.mobile.ui.screens.register

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.stattracker.mobile.data.model.RegisterRequest
import com.stattracker.mobile.data.repository.StatTrackerRepository
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import org.json.JSONObject

sealed class RegisterUiState {
    object Idle : RegisterUiState()
    object Loading : RegisterUiState()
    object Success : RegisterUiState()
    data class Error(val message: String) : RegisterUiState()
}

class RegisterViewModel(
    private val repository: StatTrackerRepository
) : ViewModel() {

    private val _uiState = MutableStateFlow<RegisterUiState>(RegisterUiState.Idle)
    val uiState: StateFlow<RegisterUiState> = _uiState.asStateFlow()

    fun register(nombre: String, email: String, password: String) {
        if (nombre.isBlank() || email.isBlank() || password.isBlank()) {
            _uiState.value = RegisterUiState.Error("Todos los campos son obligatorios")
            return
        }

        viewModelScope.launch {
            _uiState.value = RegisterUiState.Loading
            try {
                val response = repository.register(RegisterRequest(nombre, "", email, password))
                if (response.isSuccessful) {
                    val body = response.body()
                    if (body?.success == true) {
                        _uiState.value = RegisterUiState.Success
                    } else {
                        _uiState.value = RegisterUiState.Error(body?.message ?: "Error en el registro")
                    }
                } else {
                    val errorJson = response.errorBody()?.string()
                    val message = try {
                        JSONObject(errorJson!!).getString("message")
                    } catch (e: Exception) {
                        "Error ${response.code()}: ${response.message()}"
                    }
                    _uiState.value = RegisterUiState.Error(message)
                }
            } catch (e: Exception) {
                _uiState.value = RegisterUiState.Error("Error de red: ${e.localizedMessage}")
            }
        }
    }
}
