package com.stattracker.mobile.util

import com.stattracker.mobile.BuildConfig

/**
 * Constantes de configuracion de la aplicacion.
 */
object Constants {
    // Retrofit anade el prefijo "api/" en StatTrackerApi.
    // La URL base debe apuntar a la raiz web, no directamente a /api.
    val BASE_URL: String = BuildConfig.STATTRACKER_BASE_URL.ensureTrailingSlash()

    // Preferences
    const val PREF_NAME = "stattracker_prefs"
    const val KEY_TOKEN = "jwt_token"
    const val KEY_USER_ID = "user_id"
    const val KEY_USER_EMAIL = "user_email"
    const val KEY_USER_NAME = "user_name"

    // Pantallas
    const val SCREEN_LOGIN = "login"
    const val SCREEN_REGISTER = "register"
    const val SCREEN_DASHBOARD = "dashboard"
    const val SCREEN_PROFILE = "profile"
    const val SCREEN_ADD_METRIC = "add_metric"

    // Request codes
    const val REQUEST_PERMISSION = 1001
}

private fun String.ensureTrailingSlash(): String = if (endsWith("/")) this else "$this/"
