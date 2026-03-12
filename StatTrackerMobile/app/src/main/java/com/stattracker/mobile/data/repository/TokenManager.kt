package com.stattracker.mobile.data.repository

import android.content.Context
import androidx.datastore.core.DataStore
import androidx.datastore.preferences.core.*
import androidx.datastore.preferences.preferencesDataStore
import com.stattracker.mobile.util.Constants
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.map

private val Context.dataStore: DataStore<Preferences> by preferencesDataStore(name = Constants.PREF_NAME)

/**
 * Gestor de almacenamiento local para el token JWT y datos de sesión
 */
class TokenManager(private val context: Context) {

    private val tokenKey = stringPreferencesKey(Constants.KEY_TOKEN)
    private val userNameKey = stringPreferencesKey(Constants.KEY_USER_NAME)

    val token: Flow<String?> = context.dataStore.data.map { preferences ->
        preferences[tokenKey]
    }

    val userName: Flow<String?> = context.dataStore.data.map { preferences ->
        preferences[userNameKey]
    }

    suspend fun saveAuthData(token: String, name: String) {
        context.dataStore.edit { preferences ->
            preferences[tokenKey] = token
            preferences[userNameKey] = name
        }
    }

    suspend fun clearAuthData() {
        context.dataStore.edit { preferences ->
            preferences.remove(tokenKey)
            preferences.remove(userNameKey)
        }
    }
}
