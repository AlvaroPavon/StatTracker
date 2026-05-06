package com.stattracker.mobile.util

import org.json.JSONObject
import retrofit2.Response

object ApiErrorParser {
    fun parse(
        response: Response<*>,
        fallbackByCode: Map<Int, String> = emptyMap(),
        fallback: String = "Error ${response.code()}: ${response.message()}"
    ): String {
        val apiMessage = response.errorBody()?.string()?.let(::extractApiMessage)
        return apiMessage
            ?: fallbackByCode[response.code()]
            ?: fallback
    }

    private fun extractApiMessage(errorBody: String): String? {
        return try {
            val json = JSONObject(errorBody)
            json.optString("message").takeIf { it.isNotBlank() }
                ?: json.optString("error").takeIf { it.isNotBlank() }
        } catch (e: Exception) {
            null
        }
    }
}
