package com.stattracker.mobile.data.api

import com.stattracker.mobile.data.model.*
import retrofit2.Response
import retrofit2.http.*

/**
 * Interfaz de Retrofit para la API de StatTracker
 */
interface StatTrackerApi {

    // --- Autenticación ---
    @POST("api/auth/register")
    suspend fun register(@Body request: RegisterRequest): Response<RegisterResponse>

    @POST("api/auth/login")
    suspend fun login(@Body request: LoginRequest): Response<LoginResponse>

    @POST("api/auth/logout")
    suspend fun logout(@Header("Authorization") token: String): Response<ApiResponse>

    // --- Métricas ---
    @GET("api/metrics")
    suspend fun getMetrics(@Header("Authorization") token: String): Response<MetricsResponse>

    @GET("api/metrics/{id}")
    suspend fun getMetric(
        @Header("Authorization") token: String,
        @Path("id") id: Int
    ): Response<MetricResponse>

    @POST("api/metrics")
    suspend fun createMetric(
        @Header("Authorization") token: String,
        @Body request: MetricRequest
    ): Response<MetricResponse>

    @PUT("api/metrics/{id}")
    suspend fun updateMetric(
        @Header("Authorization") token: String,
        @Path("id") id: Int,
        @Body request: MetricRequest
    ): Response<MetricResponse>

    @DELETE("api/metrics/{id}")
    suspend fun deleteMetric(
        @Header("Authorization") token: String,
        @Path("id") id: Int
    ): Response<ApiResponse>

    // --- Perfil ---
    @GET("api/profile")
    suspend fun getProfile(@Header("Authorization") token: String): Response<ProfileResponse>
}
