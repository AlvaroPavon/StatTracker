package com.stattracker.mobile.ui.screens.add_metric

import android.widget.Toast
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.ArrowBack
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.stattracker.mobile.ui.ViewModelFactory
import com.stattracker.mobile.util.ServiceLocator

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun AddMetricScreen(
    navController: NavController,
    viewModel: AddMetricViewModel = viewModel(
        factory = ViewModelFactory(
            ServiceLocator.provideRepository(LocalContext.current)
        )
    )
) {
    var peso by remember { mutableStateOf("") }
    var altura by remember { mutableStateOf("") }
    
    val uiState by viewModel.uiState.collectAsState()
    val context = LocalContext.current

    LaunchedEffect(uiState) {
        when (uiState) {
            is AddMetricUiState.Success -> {
                Toast.makeText(context, "Registro guardado correctamente", Toast.LENGTH_SHORT).show()
                navController.popBackStack()
            }
            is AddMetricUiState.Error -> {
                Toast.makeText(context, (uiState as AddMetricUiState.Error).message, Toast.LENGTH_SHORT).show()
                viewModel.resetState()
            }
            else -> {}
        }
    }

    Scaffold(
        topBar = {
            TopAppBar(
                title = { Text("Nuevo Registro") },
                navigationIcon = {
                    IconButton(onClick = { navController.popBackStack() }) {
                        Icon(Icons.Default.ArrowBack, contentDescription = "Atrás")
                    }
                }
            )
        }
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .padding(16.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            OutlinedTextField(
                value = peso,
                onValueChange = { peso = it },
                label = { Text("Peso (kg)") },
                modifier = Modifier.fillMaxWidth(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
                enabled = uiState !is AddMetricUiState.Loading,
                placeholder = { Text("Ej: 75.5") }
            )

            Spacer(modifier = Modifier.height(16.dp))

            OutlinedTextField(
                value = altura,
                onValueChange = { altura = it },
                label = { Text("Altura (m)") },
                modifier = Modifier.fillMaxWidth(),
                keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
                enabled = uiState !is AddMetricUiState.Loading,
                placeholder = { Text("Ej: 1.75") }
            )

            Spacer(modifier = Modifier.height(32.dp))

            if (uiState is AddMetricUiState.Loading) {
                CircularProgressIndicator()
            } else {
                Button(
                    onClick = { 
                        viewModel.addMetric(peso.replace(",", "."), altura.replace(",", ".")) 
                    },
                    modifier = Modifier.fillMaxWidth(),
                    enabled = peso.isNotBlank() && altura.isNotBlank()
                ) {
                    Text("Guardar Registro")
                }
            }
            
            Spacer(modifier = Modifier.height(16.dp))
            
            Text(
                text = "El IMC se calculará automáticamente en el servidor.",
                style = MaterialTheme.typography.bodySmall,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
    }
}
