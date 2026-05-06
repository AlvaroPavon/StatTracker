package com.stattracker.mobile.ui.screens.profile

import androidx.compose.foundation.BorderStroke
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.asPaddingValues
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBars
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ExitToApp
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.stattracker.mobile.data.model.Profile
import com.stattracker.mobile.data.model.Stats
import com.stattracker.mobile.ui.ViewModelFactory
import com.stattracker.mobile.ui.components.GlassMetricSurface
import com.stattracker.mobile.ui.components.StatInfoCard
import com.stattracker.mobile.ui.components.StatPrimary
import com.stattracker.mobile.ui.components.StatSecondary
import com.stattracker.mobile.ui.components.StatTrackerBackground
import com.stattracker.mobile.util.Constants
import com.stattracker.mobile.util.ServiceLocator

@Composable
fun ProfileScreen(
    navController: NavController,
    viewModel: ProfileViewModel = viewModel(
        factory = ViewModelFactory(
            ServiceLocator.provideRepository(LocalContext.current),
            ServiceLocator.provideTokenManager(LocalContext.current)
        )
    )
) {
    val uiState by viewModel.uiState.collectAsState()

    StatTrackerBackground(modifier = Modifier.fillMaxSize()) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(WindowInsets.statusBars.asPaddingValues())
                .verticalScroll(rememberScrollState())
                .padding(20.dp)
        ) {
            ProfileHeader(
                onBack = { navController.popBackStack() },
                onRefresh = { viewModel.loadProfile() },
                onLogout = {
                    viewModel.logout {
                        navController.navigate(Constants.SCREEN_LOGIN) {
                            popUpTo(0)
                        }
                    }
                }
            )

            Spacer(modifier = Modifier.height(18.dp))

            Box(
                modifier = Modifier.fillMaxWidth(),
                contentAlignment = Alignment.Center
            ) {
                when (val state = uiState) {
                    is ProfileUiState.Loading -> CircularProgressIndicator(color = StatPrimary)
                    is ProfileUiState.Error -> ProfileError(
                        message = state.message,
                        onRetry = { viewModel.loadProfile() }
                    )
                    is ProfileUiState.Success -> ProfileContent(
                        profile = state.profile,
                        stats = state.stats
                    )
                }
            }
        }
    }
}

@Composable
private fun ProfileHeader(
    onBack: () -> Unit,
    onRefresh: () -> Unit,
    onLogout: () -> Unit
) {
    Row(
        modifier = Modifier.fillMaxWidth(),
        verticalAlignment = Alignment.CenterVertically
    ) {
        IconButton(onClick = onBack) {
            Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atras", tint = StatPrimary)
        }
        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = "Perfil",
                style = MaterialTheme.typography.headlineSmall,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.onBackground
            )
            Text(
                text = "Datos personales y resumen de salud",
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
        IconButton(onClick = onRefresh) {
            Icon(Icons.Default.Refresh, contentDescription = "Actualizar", tint = StatPrimary)
        }
        IconButton(onClick = onLogout) {
            Icon(Icons.AutoMirrored.Filled.ExitToApp, contentDescription = "Cerrar Sesion", tint = StatPrimary)
        }
    }
}

@Composable
private fun ProfileContent(profile: Profile, stats: Stats) {
    Column(
        modifier = Modifier.fillMaxWidth(),
        verticalArrangement = Arrangement.spacedBy(14.dp)
    ) {
        GlassMetricSurface(modifier = Modifier.fillMaxWidth()) {
            Row(
                modifier = Modifier.padding(18.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                Surface(
                    modifier = Modifier.size(64.dp),
                    shape = CircleShape,
                    color = StatPrimary.copy(alpha = 0.14f),
                    border = BorderStroke(1.dp, StatPrimary.copy(alpha = 0.25f))
                ) {
                    Box(contentAlignment = Alignment.Center) {
                        Text(
                            text = profile.nombre.take(1).uppercase(),
                            style = MaterialTheme.typography.headlineMedium,
                            fontWeight = FontWeight.Bold,
                            color = StatPrimary
                        )
                    }
                }

                Column(modifier = Modifier.padding(start = 16.dp)) {
                    Text(
                        text = "${profile.nombre} ${profile.apellidos}".trim(),
                        style = MaterialTheme.typography.titleLarge,
                        fontWeight = FontWeight.Bold,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                    Text(
                        text = profile.email,
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            }
        }

        Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
            StatInfoCard(
                label = "Registros",
                value = stats.total_registros.toString(),
                modifier = Modifier.weight(1f)
            )
            StatInfoCard(
                label = "IMC medio",
                value = String.format("%.1f", stats.imc_promedio ?: 0.0),
                modifier = Modifier.weight(1f),
                accent = StatSecondary
            )
        }

        GlassMetricSurface(modifier = Modifier.fillMaxWidth()) {
            Column(modifier = Modifier.padding(18.dp)) {
                Text(
                    text = "Resumen de Salud",
                    style = MaterialTheme.typography.titleLarge,
                    fontWeight = FontWeight.Bold,
                    color = MaterialTheme.colorScheme.onSurface
                )
                Spacer(modifier = Modifier.height(12.dp))
                ProfileStatRow("Peso Promedio", "${String.format("%.1f", stats.peso_promedio ?: 0.0)} kg")
                ProfileStatRow("Peso Minimo", "${stats.peso_min ?: 0.0} kg")
                ProfileStatRow("Peso Maximo", "${stats.peso_max ?: 0.0} kg")
                ProfileStatRow("IMC Minimo", String.format("%.1f", stats.imc_min ?: 0.0))
                ProfileStatRow("IMC Maximo", String.format("%.1f", stats.imc_max ?: 0.0))
            }
        }
    }
}

@Composable
private fun ProfileError(message: String, onRetry: () -> Unit) {
    GlassMetricSurface(modifier = Modifier.fillMaxWidth()) {
        Column(
            modifier = Modifier.padding(20.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Text(message, color = MaterialTheme.colorScheme.error)
            Spacer(modifier = Modifier.height(12.dp))
            Button(onClick = onRetry) {
                Text("Reintentar")
            }
        }
    }
}

@Composable
fun ProfileStatRow(label: String, value: String) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 8.dp),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(
            text = label,
            style = MaterialTheme.typography.bodyMedium,
            color = MaterialTheme.colorScheme.onSurfaceVariant
        )
        Text(
            text = value,
            style = MaterialTheme.typography.bodyLarge,
            fontWeight = FontWeight.Bold,
            color = StatPrimary
        )
    }
}
