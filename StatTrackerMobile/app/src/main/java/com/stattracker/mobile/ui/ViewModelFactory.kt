package com.stattracker.mobile.ui

import androidx.lifecycle.ViewModel
import androidx.lifecycle.ViewModelProvider
import com.stattracker.mobile.data.repository.StatTrackerRepository
import com.stattracker.mobile.data.repository.TokenManager
import com.stattracker.mobile.ui.screens.add_metric.AddMetricViewModel
import com.stattracker.mobile.ui.screens.dashboard.DashboardViewModel
import com.stattracker.mobile.ui.screens.login.LoginViewModel
import com.stattracker.mobile.ui.screens.profile.ProfileViewModel
import com.stattracker.mobile.ui.screens.register.RegisterViewModel

class ViewModelFactory(
    private val repository: StatTrackerRepository,
    private val tokenManager: TokenManager? = null
) : ViewModelProvider.Factory {
    @Suppress("UNCHECKED_CAST")
    override fun <T : ViewModel> create(modelClass: Class<T>): T {
        return when {
            modelClass.isAssignableFrom(LoginViewModel::class.java) -> {
                LoginViewModel(repository, tokenManager!!) as T
            }
            modelClass.isAssignableFrom(RegisterViewModel::class.java) -> {
                RegisterViewModel(repository) as T
            }
            modelClass.isAssignableFrom(ProfileViewModel::class.java) -> {
                ProfileViewModel(repository, tokenManager!!) as T
            }
            modelClass.isAssignableFrom(DashboardViewModel::class.java) -> {
                DashboardViewModel(repository) as T
            }
            modelClass.isAssignableFrom(AddMetricViewModel::class.java) -> {
                AddMetricViewModel(repository) as T
            }
            else -> throw IllegalArgumentException("Unknown ViewModel class: ${modelClass.name}")
        }
    }
}
