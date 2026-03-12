package com.stattracker.mobile.data.repository

import com.stattracker.mobile.data.api.StatTrackerApi
import com.stattracker.mobile.data.model.*
import kotlinx.coroutines.flow.first
import retrofit2.Response

class StatTrackerRepository(
    private val api: StatTrackerApi,
    private val tokenManager: TokenManager
) {
    private suspend fun getAuthHeader(): String {
        val token = tokenManager.token.first()
        return if (token != null) "Bearer $token" else ""
    }

    // Auth
    suspend fun login(request: LoginRequest): Response<LoginResponse> = api.login(request)
    suspend fun register(request: RegisterRequest): Response<RegisterResponse> = api.register(request)
    suspend fun logout(): Response<ApiResponse> = api.logout(getAuthHeader())

    // Metrics
    suspend fun getMetrics(): Response<MetricsResponse> = api.getMetrics(getAuthHeader())
    suspend fun getMetric(id: Int): Response<MetricResponse> = api.getMetric(getAuthHeader(), id)
    suspend fun createMetric(request: MetricRequest): Response<MetricResponse> = api.createMetric(getAuthHeader(), request)
    suspend fun updateMetric(id: Int, request: MetricRequest): Response<MetricResponse> = api.updateMetric(getAuthHeader(), id, request)
    suspend fun deleteMetric(id: Int): Response<ApiResponse> = api.deleteMetric(getAuthHeader(), id)

    // Profile
    suspend fun getProfile(): Response<ProfileResponse> = api.getProfile(getAuthHeader())
}
