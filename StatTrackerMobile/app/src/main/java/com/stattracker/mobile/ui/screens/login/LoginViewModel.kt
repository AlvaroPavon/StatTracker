package com.stattracker.mobile.ui.screens.login

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.stattracker.mobile.data.model.LoginRequest
import com.stattracker.mobile.data.repository.StatTrackerRepository
import com.stattracker.mobile.data.repository.TokenManager
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import org.json.JSONObject

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
            _uiState.value = LoginUiState.Error("Email y contraseña son obligatorios")
            return
        }

        viewModelScope.launch {
            _uiState.value = LoginUiState.Loading
            try {
                val response = repository.login(LoginRequest(email, password))
                if (response.isSuccessful) {
                    val loginResponse = response.body()
                    if (loginResponse?.success == true) {
                        tokenManager.saveAuthData(
                            token = loginResponse.token ?: "",
                            name = loginResponse.user?.nombre ?: ""
                        )
                        _uiState.value = LoginUiState.Success(loginResponse.token ?: "")
                    } else {
                        // Aquí el servidor respondió con 200 pero success false
                        _uiState.value = LoginUiState.Error("Credenciales inválidas o cuenta no activa")
                    }
                } else {
                    // Aquí el servidor respondió con error (401, 404, 500...)
                    val errorJson = response.errorBody()?.string()
                    val errorMessage = try {
                        JSONObject(errorJson!!).getString("message")
                    } catch (e: Exception) {
                        when(response.code()) {
                            401 -> "Email o contraseña incorrectos"
                            404 -> "Servicio no encontrado (Verifica la URL en Constants.kt)"
                            500 -> "Error interno del servidor (Revisa los logs de XAMPP)"
                            else -> "Error ${response.code()}: ${response.message()}"
                        }
                    }
                    _uiState.value = LoginUiState.Error(errorMessage)
                }
            } catch (e: Exception) {
                _uiState.value = LoginUiState.Error("Error de conexión: ${e.localizedMessage}")
            }
        }
    }
}
