package com.stattracker.mobile.ui.screens.login

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.stattracker.mobile.data.model.LoginRequest
import com.stattracker.mobile.data.repository.StatTrackerRepository
import com.stattracker.mobile.data.repository.TokenManager
import com.stattracker.mobile.util.ApiErrorParser
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

sealed class LoginUiState {
    object Idle : LoginUiState()
    object Loading : LoginUiState()
    data class Success(val token: String) : LoginUiState()
    data class Error(val message: String) : LoginUiState()
}

class LoginViewModel(
    private val repository: StatTrackerRepository,
    private val tokenManager: TokenManager
) : ViewModel() {

    private val _uiState = MutableStateFlow<LoginUiState>(LoginUiState.Idle)
    val uiState: StateFlow<LoginUiState> = _uiState.asStateFlow()

    fun login(email: String, password: String) {
        if (email.isBlank() || password.isBlank()) {
            _uiState.value = LoginUiState.Error("Email y contrasena son obligatorios")
            return
        }

        viewModelScope.launch {
            _uiState.value = LoginUiState.Loading
            try {
                val response = repository.login(LoginRequest(email, password))
                if (response.isSuccessful) {
                    val loginResponse = response.body()
                    if (loginResponse?.success == true && !loginResponse.token.isNullOrBlank()) {
                        tokenManager.saveAuthData(
                            token = loginResponse.token,
                            name = loginResponse.user?.nombre ?: ""
                        )
                        _uiState.value = LoginUiState.Success(loginResponse.token)
                    } else {
                        _uiState.value = LoginUiState.Error("Credenciales invalidas o cuenta no activa")
                    }
                } else {
                    val errorMessage = ApiErrorParser.parse(
                        response = response,
                        fallbackByCode = mapOf(
                            401 to "Email o contrasena incorrectos",
                            404 to "Servicio no encontrado. Verifica la URL base",
                            500 to "Error interno del servidor"
                        )
                    )
                    _uiState.value = LoginUiState.Error(errorMessage)
                }
            } catch (e: Exception) {
                _uiState.value = LoginUiState.Error("Error de conexion: ${e.localizedMessage}")
            }
        }
    }
}
