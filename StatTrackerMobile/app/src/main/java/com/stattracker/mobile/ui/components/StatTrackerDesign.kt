package com.stattracker.mobile.ui.components

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.BoxScope
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Scale
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.text.input.VisualTransformation
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp

val StatPrimary = Color(0xFF4A90E2)
val StatSecondary = Color(0xFF50E3C2)
val StatTextLight = Color(0xFF333333)
val StatTextDark = Color(0xFFF9FAFB)
val StatMutedLight = Color(0xFF6B7280)
val StatMutedDark = Color(0xFFD1D5DB)

@Composable
fun StatTrackerBackground(
    modifier: Modifier = Modifier,
    content: @Composable BoxScope.() -> Unit
) {
    val dark = isSystemInDarkTheme()
    val colors = if (dark) {
        listOf(Color(0xFF0F172A), Color(0xFF1F2937), Color(0xFF0F3A42))
    } else {
        listOf(Color(0xFFF8F9FA), Color(0xFFDBEAFE), Color(0xFFCFFAFE))
    }

    Box(
        modifier = modifier.background(
            Brush.linearGradient(colors)
        ),
        content = content
    )
}

@Composable
fun GlassPanel(
    modifier: Modifier = Modifier,
    content: @Composable BoxScope.() -> Unit
) {
    val dark = isSystemInDarkTheme()
    val shape = RoundedCornerShape(12.dp)
    val background = if (dark) {
        Color(0xFF0F172A).copy(alpha = 0.68f)
    } else {
        Color.White.copy(alpha = 0.68f)
    }
    val border = if (dark) {
        Color.White.copy(alpha = 0.14f)
    } else {
        Color.White.copy(alpha = 0.56f)
    }

    Box(
        modifier = modifier
            .shadow(18.dp, shape, clip = false)
            .background(background, shape)
            .border(BorderStroke(1.dp, border), shape)
            .padding(18.dp),
        content = content
    )
}

@Composable
fun BrandHeader(
    title: String,
    subtitle: String,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier,
        horizontalAlignment = Alignment.CenterHorizontally
    ) {
        Row(
            verticalAlignment = Alignment.CenterVertically,
            horizontalArrangement = Arrangement.Center
        ) {
            Surface(
                modifier = Modifier.size(46.dp),
                shape = CircleShape,
                color = StatPrimary.copy(alpha = 0.12f),
                border = BorderStroke(1.dp, StatPrimary.copy(alpha = 0.22f))
            ) {
                Box(contentAlignment = Alignment.Center) {
                    Icon(
                        imageVector = Icons.Filled.Scale,
                        contentDescription = null,
                        tint = StatPrimary,
                        modifier = Modifier.size(27.dp)
                    )
                }
            }
            Spacer(modifier = Modifier.size(10.dp))
            Text(
                text = "StatTracker",
                style = MaterialTheme.typography.headlineSmall,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.onBackground
            )
        }

        Spacer(modifier = Modifier.height(14.dp))
        Text(
            text = title,
            style = MaterialTheme.typography.titleMedium.copy(fontSize = 20.sp),
            fontWeight = FontWeight.Bold,
            color = MaterialTheme.colorScheme.onBackground,
            textAlign = TextAlign.Center
        )
        Text(
            text = subtitle,
            style = MaterialTheme.typography.bodyMedium,
            color = if (isSystemInDarkTheme()) StatMutedDark else StatMutedLight,
            textAlign = TextAlign.Center
        )
    }
}

@Composable
fun GlassTextField(
    value: String,
    onValueChange: (String) -> Unit,
    label: String,
    modifier: Modifier = Modifier,
    placeholder: String = "",
    enabled: Boolean = true,
    visualTransformation: VisualTransformation = VisualTransformation.None,
    keyboardOptions: KeyboardOptions = KeyboardOptions.Default,
    trailingIcon: @Composable (() -> Unit)? = null
) {
    val dark = isSystemInDarkTheme()
    OutlinedTextField(
        value = value,
        onValueChange = onValueChange,
        label = { Text(label) },
        placeholder = if (placeholder.isNotBlank()) {
            { Text(placeholder) }
        } else {
            null
        },
        modifier = modifier.fillMaxWidth(),
        enabled = enabled,
        singleLine = true,
        shape = RoundedCornerShape(8.dp),
        visualTransformation = visualTransformation,
        keyboardOptions = keyboardOptions,
        trailingIcon = trailingIcon,
        colors = OutlinedTextFieldDefaults.colors(
            focusedBorderColor = StatPrimary,
            unfocusedBorderColor = if (dark) Color.White.copy(alpha = 0.18f) else Color(0xFFE1E8ED),
            focusedContainerColor = if (dark) Color.White.copy(alpha = 0.06f) else Color.White.copy(alpha = 0.54f),
            unfocusedContainerColor = if (dark) Color.White.copy(alpha = 0.04f) else Color.White.copy(alpha = 0.42f),
            cursorColor = StatPrimary,
            focusedLabelColor = StatPrimary
        )
    )
}

@Composable
fun StatPrimaryButton(
    text: String,
    onClick: () -> Unit,
    modifier: Modifier = Modifier,
    enabled: Boolean = true
) {
    Button(
        onClick = onClick,
        enabled = enabled,
        modifier = modifier
            .fillMaxWidth()
            .height(48.dp),
        shape = RoundedCornerShape(8.dp),
        colors = ButtonDefaults.buttonColors(
            containerColor = StatPrimary,
            contentColor = Color.White,
            disabledContainerColor = StatPrimary.copy(alpha = 0.36f),
            disabledContentColor = Color.White.copy(alpha = 0.72f)
        )
    ) {
        Text(text = text, fontWeight = FontWeight.Bold)
    }
}

@Composable
fun GlassMetricSurface(
    modifier: Modifier = Modifier,
    content: @Composable () -> Unit
) {
    val dark = isSystemInDarkTheme()
    Surface(
        modifier = modifier,
        shape = RoundedCornerShape(12.dp),
        color = if (dark) Color(0xFF111827).copy(alpha = 0.68f) else Color.White.copy(alpha = 0.70f),
        border = BorderStroke(
            1.dp,
            if (dark) Color.White.copy(alpha = 0.12f) else Color.White.copy(alpha = 0.58f)
        ),
        tonalElevation = 0.dp,
        shadowElevation = 4.dp,
        content = content
    )
}

@Composable
fun StatInfoCard(
    label: String,
    value: String,
    modifier: Modifier = Modifier,
    accent: Color = StatPrimary
) {
    Surface(
        modifier = modifier,
        shape = RoundedCornerShape(12.dp),
        color = if (isSystemInDarkTheme()) {
            Color(0xFF111827).copy(alpha = 0.64f)
        } else {
            Color.White.copy(alpha = 0.66f)
        },
        border = BorderStroke(1.dp, accent.copy(alpha = 0.20f))
    ) {
        Column(modifier = Modifier.padding(14.dp)) {
            Text(
                text = label,
                style = MaterialTheme.typography.labelMedium,
                color = if (isSystemInDarkTheme()) StatMutedDark else StatMutedLight
            )
            Text(
                text = value,
                style = MaterialTheme.typography.titleLarge,
                fontWeight = FontWeight.Bold,
                color = accent
            )
        }
    }
}
