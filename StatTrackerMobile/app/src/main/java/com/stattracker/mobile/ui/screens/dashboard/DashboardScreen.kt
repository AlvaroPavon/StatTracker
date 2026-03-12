package com.stattracker.mobile.ui.screens.dashboard

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.stattracker.mobile.data.model.Metric
import com.stattracker.mobile.ui.ViewModelFactory
import com.stattracker.mobile.util.Constants
import com.stattracker.mobile.util.ServiceLocator

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DashboardScreen(
    navController: NavController,
    viewModel: DashboardViewModel = viewModel(
        factory = ViewModelFactory(
            ServiceLocator.provideRepository(LocalContext.current)
        )
    )
) {
    val uiState by viewModel.uiState.collectAsState()

    // Este efecto se ejecuta cada vez que la pantalla se vuelve a mostrar
    LaunchedEffect(Unit) {
        viewModel.loadMetrics()
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Mis Registros") },
                actions = {
                    IconButton(onClick = { viewModel.loadMetrics() }) {
                        Icon(Icons.Default.Refresh, contentDescription = "Actualizar")
                    }
                    IconButton(onClick = { navController.navigate(Constants.SCREEN_PROFILE) }) {
                        Icon(Icons.Default.Person, contentDescription = "Perfil")
                    }
                }
            )
        },
        floatingActionButton = {
            FloatingActionButton(onClick = { navController.navigate(Constants.SCREEN_ADD_METRIC) }) {
                Icon(Icons.Default.Add, contentDescription = "Añadir")
            }
        }
    ) { paddingValues ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues),
            contentAlignment = Alignment.Center
        ) {
            when (val state = uiState) {
                is DashboardUiState.Loading -> CircularProgressIndicator()
                is DashboardUiState.Error -> {
                    Column(horizontalAlignment = Alignment.CenterHorizontally) {
                        Text(state.message, color = MaterialTheme.colorScheme.error)
                        Button(onClick = { viewModel.loadMetrics() }) {
                            Text("Reintentar")
                        }
                    }
                }
                is DashboardUiState.Success -> {
                    if (state.metrics.isEmpty()) {
                        Text("No hay registros aún. ¡Añade el primero!")
                    } else {
                        LazyColumn(
                            modifier = Modifier
                                .fillMaxSize()
                                .padding(16.dp),
                            verticalArrangement = Arrangement.spacedBy(8.dp)
                        ) {
                            items(state.metrics) { metric ->
                                MetricItem(metric)
                            }
                        }
                    }
                }
            }
        }
    }
}

@Composable
fun MetricItem(metric: Metric) {
    Card(
        modifier = Modifier.fillMaxWidth(),
        elevation = CardDefaults.cardElevation(defaultElevation = 2.dp)
    ) {
        Column(modifier = Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween
            ) {
                Text(
                    text = "IMC: ${String.format("%.2f", metric.imc)}", 
                    style = MaterialTheme.typography.titleLarge,
                    color = MaterialTheme.colorScheme.primary
                )
                Text(
                    text = metric.fechaRegistro, 
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }
            Spacer(modifier = Modifier.height(8.dp))
            Text(
                text = "Peso: ${metric.peso} kg • Altura: ${metric.altura} m",
                style = MaterialTheme.typography.bodyLarge
            )
        }
    }
}
