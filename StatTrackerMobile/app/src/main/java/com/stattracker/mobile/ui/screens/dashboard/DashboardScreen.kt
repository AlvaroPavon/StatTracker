package com.stattracker.mobile.ui.screens.dashboard

import androidx.compose.foundation.layout.*
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Person
import androidx.compose.material3.*
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import com.stattracker.mobile.data.model.Metric
import com.stattracker.mobile.util.Constants

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun DashboardScreen(
    navController: NavController
) {
    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Tus Estadísticas") },
                actions = {
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
        // Ejemplo de lista (luego vendrá del ViewModel)
        val dummyMetrics = listOf<Metric>() 

        if (dummyMetrics.isEmpty()) {
            Box(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues),
                contentAlignment = androidx.compose.ui.Alignment.Center
            ) {
                Text("No hay registros aún. ¡Añade el primero!")
            }
        } else {
            LazyColumn(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues)
                    .padding(16.dp),
                verticalArrangement = Arrangement.spacedBy(8.dp)
            ) {
                items(dummyMetrics) { metric ->
                    MetricItem(metric)
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
                Text(text = "IMC: ${metric.imc}", style = MaterialTheme.typography.titleLarge)
                Text(text = metric.fechaRegistro, style = MaterialTheme.typography.bodyMedium)
            }
            Text(text = "Peso: ${metric.peso} kg • Altura: ${metric.altura} m")
        }
    }
}
