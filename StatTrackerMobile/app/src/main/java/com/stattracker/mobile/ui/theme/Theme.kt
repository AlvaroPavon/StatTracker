package com.stattracker.mobile.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

private val DarkColorScheme = darkColorScheme(
    primary = Color(0xFF4A90E2),
    secondary = Color(0xFF50E3C2),
    tertiary = Color(0xFF93C5FD),
    background = Color(0xFF1F2937),
    surface = Color(0xFF111827),
    onPrimary = Color.White,
    onSecondary = Color(0xFF0F172A),
    onBackground = Color(0xFFF9FAFB),
    onSurface = Color(0xFFF9FAFB),
    onSurfaceVariant = Color(0xFFD1D5DB),
    outline = Color(0xFF4B5563)
)

private val LightColorScheme = lightColorScheme(
    primary = Color(0xFF4A90E2),
    secondary = Color(0xFF50E3C2),
    tertiary = Color(0xFF2563EB),
    background = Color(0xFFF8F9FA),
    surface = Color.White,
    onPrimary = Color.White,
    onSecondary = Color(0xFF0F172A),
    onBackground = Color(0xFF333333),
    onSurface = Color(0xFF333333),
    onSurfaceVariant = Color(0xFF6B7280),
    outline = Color(0xFFE1E8ED)
)

@Composable
fun StatTrackerMobileTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit
) {
    MaterialTheme(
        colorScheme = if (darkTheme) DarkColorScheme else LightColorScheme,
        content = content
    )
}
