package com.stattracker.mobile.ui.screens.profile

import android.widget.Toast
import androidx.compose.foundation.layout.*
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material.icons.filled.ExitToApp
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.stattracker.mobile.ui.ViewModelFactory
import com.stattracker.mobile.util.Constants
import com.stattracker.mobile.util.ServiceLocator

@OptIn(ExperimentalMaterial3Api::class)
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
    val context = LocalContext.current

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Mi Perfil") },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Atrás")
                    }
                },
                actions = {
                    IconButton(onClick = { viewModel.loadProfile() }) {
                        Icon(Icons.Default.Refresh, contentDescription = "Actualizar")
                    }
                    IconButton(onClick = { 
                        viewModel.logout {
                            navController.navigate(Constants.SCREEN_LOGIN) {
                                popUpTo(0)
                            }
                        }
                    }) {
                        Icon(Icons.Default.ExitToApp, contentDescription = "Cerrar Sesión")
                    }
                }
            )
        }
    ) { padding ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding),
            contentAlignment = Alignment.Center
        ) {
            when (val state = uiState) {
                is ProfileUiState.Loading -> CircularProgressIndicator()
                is ProfileUiState.Error -> {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(state.message, color = MaterialTheme.colorScheme.error)
                        Button(onClick = { viewModel.loadProfile() }) {
                            Text("Reintentar")
                        }
                    }
                }
                is ProfileUiState.Success -> {
                    Column(
                        modifier = Modifier
                            .fillMaxSize()
                            .padding(16.dp),
                        horizontalAlignment = Alignment.CenterHorizontally
                    ) {
                        // Información básica
                        Card(modifier = Modifier.fillMaxWidth()) {
                            Column(modifier = Modifier.padding(16.dp)) {
                                Text("Datos Personales", style = MaterialTheme.typography.titleMedium)
                                Divider(modifier = Modifier.padding(vertical = 8.dp))
                                Text("Nombre: ${state.profile.nombre} ${state.profile.apellidos}")
                                Text("Email: ${state.profile.email}")
                            }
                        }

                        Spacer(modifier = Modifier.height(16.dp))

                        // Estadísticas
                        Card(modifier = Modifier.fillMaxWidth()) {
                            Column(modifier = Modifier.padding(16.dp)) {
                                Text("Resumen de Salud", style = MaterialTheme.typography.titleMedium)
                                Divider(modifier = Modifier.padding(vertical = 8.dp))
                                ProfileStatRow("Registros totales:", state.stats.total_registros.toString())
                                ProfileStatRow("Peso Promedio:", "${String.format("%.1f", state.stats.peso_promedio ?: 0.0)} kg")
                                ProfileStatRow("IMC Promedio:", String.format("%.1f", state.stats.imc_promedio ?: 0.0))
                                ProfileStatRow("Peso Min/Max:", "${state.stats.peso_min ?: 0.0} / ${state.stats.peso_max ?: 0.0} kg")
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun ProfileStatRow(label: String, value: String) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 4.dp),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(label, style = MaterialTheme.typography.bodyMedium)
        Text(value, style = MaterialTheme.typography.bodyLarge, color = MaterialTheme.colorScheme.primary)
    }
}
