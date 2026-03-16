package com.stattracker.mobile.util

/**
 * Constantes de configuración de la aplicación
 */
object Constants {
    // --- CONFIGURACIÓN PARA MÓVIL REAL (HOTSPOT) ---
    
    // IP del PC (Puerta de enlace del Hotspot)
    private const val MY_IP = "192.168.137.1"
    
    // Basado en los logs exitosos anteriores, la raíz es 'proyecto_imc'
    // El prefijo '/api/' ya lo añaden las funciones de la interfaz StatTrackerApi
    const val BASE_URL = "http://$MY_IP:8080/proyecto_imc/"
    
    // ----------------------------------------------

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
