package com.stattracker.mobile.ui.screens.profile

import androidx.lifecycle.ViewModel
import androidx.lifecycle.viewModelScope
import com.stattracker.mobile.data.model.Profile
import com.stattracker.mobile.data.model.Stats
import com.stattracker.mobile.data.repository.StatTrackerRepository
import com.stattracker.mobile.data.repository.TokenManager
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

sealed class ProfileUiState {
    object Loading : ProfileUiState()
    data class Success(val profile: Profile, val stats: Stats) : ProfileUiState()
    data class Error(val message: String) : ProfileUiState()
}

class ProfileViewModel(
    private val repository: StatTrackerRepository,
    private val tokenManager: TokenManager
) : ViewModel() {

    private val _uiState = MutableStateFlow<ProfileUiState>(ProfileUiState.Loading)
    val uiState: StateFlow<ProfileUiState> = _uiState.asStateFlow()

    init {
        loadProfile()
    }

    fun loadProfile() {
        viewModelScope.launch {
            _uiState.value = ProfileUiState.Loading
            try {
                val response = repository.getProfile()
                if (response.isSuccessful && response.body()?.success == true) {
                    val profileResponse = response.body()!!
                    _uiState.value = ProfileUiState.Success(
                        profile = profileResponse.profile!!,
                        stats = profileResponse.stats ?: Stats(0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0)
                    )
                } else {
                    _uiState.value = ProfileUiState.Error("No se pudo cargar el perfil")
                }
            } catch (e: Exception) {
                _uiState.value = ProfileUiState.Error("Error de red: ${e.localizedMessage}")
            }
        }
    }

    fun logout(onSuccess: () -> Unit) {
        viewModelScope.launch {
            try {
                repository.logout()
            } catch (e: Exception) {
                // Ignorar error de red en logout
            }
            tokenManager.clearAuthData()
            onSuccess()
        }
    }
}
