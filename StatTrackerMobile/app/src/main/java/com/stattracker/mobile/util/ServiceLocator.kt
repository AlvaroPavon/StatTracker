package com.stattracker.mobile.util

import android.content.Context
import com.stattracker.mobile.data.api.StatTrackerApi
import com.stattracker.mobile.data.repository.StatTrackerRepository
import com.stattracker.mobile.data.repository.TokenManager
import okhttp3.OkHttpClient
import okhttp3.logging.HttpLoggingInterceptor
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

/**
 * Service Locator simple para gestionar dependencias de forma manual
 */
object ServiceLocator {
    
    private var database: StatTrackerApi? = null
    private var repository: StatTrackerRepository? = null
    private var tokenManager: TokenManager? = null

    private fun provideRetrofit(): Retrofit {
        val logging = HttpLoggingInterceptor().apply {
            level = HttpLoggingInterceptor.Level.BODY
        }
        
        val client = OkHttpClient.Builder()
            .addInterceptor(logging)
            .build()

        return Retrofit.Builder()
            .baseUrl(Constants.BASE_URL)
            .addConverterFactory(GsonConverterFactory.create())
            .client(client)
            .build()
    }

    private fun provideApiService(): StatTrackerApi {
        return database ?: provideRetrofit().create(StatTrackerApi::class.java).also {
            database = it
        }
    }

    fun provideTokenManager(context: Context): TokenManager {
        return tokenManager ?: TokenManager(context.applicationContext).also {
            tokenManager = it
        }
    }

    fun provideRepository(context: Context): StatTrackerRepository {
        return repository ?: StatTrackerRepository(
            provideApiService(),
            provideTokenManager(context)
        ).also {
            repository = it
        }
    }
}
