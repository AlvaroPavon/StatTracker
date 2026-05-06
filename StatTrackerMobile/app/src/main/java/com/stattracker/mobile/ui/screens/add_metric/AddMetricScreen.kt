package com.stattracker.mobile.ui.screens.add_metric

import android.widget.Toast
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.asPaddingValues
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.statusBars
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.stattracker.mobile.ui.ViewModelFactory
import com.stattracker.mobile.ui.components.GlassPanel
import com.stattracker.mobile.ui.components.GlassTextField
import com.stattracker.mobile.ui.components.StatPrimary
import com.stattracker.mobile.ui.components.StatPrimaryButton
import com.stattracker.mobile.ui.components.StatTrackerBackground
import com.stattracker.mobile.util.ServiceLocator

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

    StatTrackerBackground(modifier = Modifier.fillMaxSize()) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(WindowInsets.statusBars.asPaddingValues())
                .verticalScroll(rememberScrollState())
                .padding(20.dp),
            verticalArrangement = Arrangement.spacedBy(18.dp)
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                verticalAlignment = Alignment.CenterVertically
            ) {
                IconButton(onClick = { navController.popBackStack() }) {
                    Icon(Icons.AutoMirrored.Filled.ArrowBack, contentDescription = "Atras", tint = StatPrimary)
                }
                Column {
                    Text(
                        text = "Registrar Nuevo Peso",
                        style = MaterialTheme.typography.headlineSmall,
                        fontWeight = FontWeight.Bold,
                        color = MaterialTheme.colorScheme.onBackground
                    )
                    Text(
                        text = "El IMC se calcula automaticamente en el servidor",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                }
            }

            GlassPanel(modifier = Modifier.fillMaxWidth()) {
                Column(modifier = Modifier.fillMaxWidth()) {
                    Text(
                        text = "Datos del registro",
                        style = MaterialTheme.typography.titleLarge,
                        fontWeight = FontWeight.Bold,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                    Spacer(modifier = Modifier.height(20.dp))

                    GlassTextField(
                        value = altura,
                        onValueChange = { altura = it },
                        label = "Altura (metros)",
                        placeholder = "Ej: 1.75",
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
                        enabled = uiState !is AddMetricUiState.Loading
                    )

                    Spacer(modifier = Modifier.height(14.dp))

                    GlassTextField(
                        value = peso,
                        onValueChange = { peso = it },
                        label = "Peso (kg)",
                        placeholder = "Ej: 70.5",
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Decimal),
                        enabled = uiState !is AddMetricUiState.Loading
                    )

                    Spacer(modifier = Modifier.height(24.dp))

                    if (uiState is AddMetricUiState.Loading) {
                        CircularProgressIndicator(
                            modifier = Modifier.align(Alignment.CenterHorizontally),
                            color = StatPrimary
                        )
                    } else {
                        StatPrimaryButton(
                            text = "Guardar Registro",
                            onClick = {
                                viewModel.addMetric(
                                    peso.replace(",", "."),
                                    altura.replace(",", ".")
                                )
                            },
                            enabled = peso.isNotBlank() && altura.isNotBlank()
                        )
                    }
                }
            }
        }
    }
}
