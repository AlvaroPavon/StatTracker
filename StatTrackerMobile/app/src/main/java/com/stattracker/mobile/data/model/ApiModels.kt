package com.stattracker.mobile.data.model

import com.google.gson.annotations.SerializedName

/**
 * Modelos de datos para las respuestas de la API
 */

// Respuestas de autenticación
data class LoginRequest(
    val email: String,
    val password: String
)

data class RegisterRequest(
    val nombre: String,
    val apellidos: String = "",
    val email: String,
    val password: String
)

data class LoginResponse(
    val success: Boolean,
    val token: String?,
    val token_type: String?,
    val expires_in: Int?,
    val user: User?
)

data class RegisterResponse(
    val success: Boolean,
    val message: String?,
    val user_id: Int?
)

data class ApiResponse(
    val success: Boolean,
    val message: String?,
    val error: String?
)

// Usuario
data class User(
    val id: Int,
    val nombre: String,
    val apellidos: String,
    val email: String
)

data class Profile(
    val id: Int,
    val nombre: String,
    val apellidos: String,
    val email: String,
    val profile_pic: String?,
    val created_at: String?,
    val updated_at: String?
)

data class ProfileResponse(
    val success: Boolean,
    val profile: Profile?,
    val stats: Stats?
)

data class Stats(
    val total_registros: Int,
    val peso_min: Double?,
    val peso_max: Double?,
    val peso_promedio: Double?,
    val imc_min: Double?,
    val imc_max: Double?,
    val imc_promedio: Double?
)

// Métricas
data class Metric(
    val id: Int,
    val peso: Double,
    val altura: Double,
    val imc: Double,
    @SerializedName("fecha_registro")
    val fechaRegistro: String,
    @SerializedName("created_at")
    val createdAt: String?
)

data class MetricRequest(
    val peso: Double,
    val altura: Double,
    @SerializedName("fecha_registro")
    val fechaRegistro: String
)

data class MetricsResponse(
    val success: Boolean,
    val count: Int?,
    val metrics: List<Metric>?
)

data class MetricResponse(
    val success: Boolean,
    val metric: Metric?
)
