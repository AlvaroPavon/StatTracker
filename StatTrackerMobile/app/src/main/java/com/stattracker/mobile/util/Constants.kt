package com.stattracker.mobile.util

/**
 * Constantes de configuración de la aplicación
 */
object Constants {
    // API Base URL
    // Para emulator Android: usar 10.0.2.2 en vez de localhost
    // Para dispositivo físico: usar la IP de tu máquina (ej: 192.168.1.XX)
    const val BASE_URL = "http://10.0.2.2:8000"
    
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
