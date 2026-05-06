package com.stattracker.mobile.ui.screens.register

import android.widget.Toast
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.text.KeyboardOptions
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
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
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.stattracker.mobile.ui.ViewModelFactory
import com.stattracker.mobile.ui.components.BrandHeader
import com.stattracker.mobile.ui.components.GlassPanel
import com.stattracker.mobile.ui.components.GlassTextField
import com.stattracker.mobile.ui.components.StatPrimary
import com.stattracker.mobile.ui.components.StatPrimaryButton
import com.stattracker.mobile.ui.components.StatTrackerBackground
import com.stattracker.mobile.util.ServiceLocator

@Composable
fun RegisterScreen(
    navController: NavController,
    viewModel: RegisterViewModel = viewModel(
        factory = ViewModelFactory(
            ServiceLocator.provideRepository(LocalContext.current)
        )
    )
) {
    var nombre by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }

    val uiState by viewModel.uiState.collectAsState()
    val context = LocalContext.current

    LaunchedEffect(uiState) {
        when (uiState) {
            is RegisterUiState.Success -> {
                Toast.makeText(context, "Registro exitoso. Inicia sesion.", Toast.LENGTH_SHORT).show()
                navController.popBackStack()
            }
            is RegisterUiState.Error -> {
                Toast.makeText(context, (uiState as RegisterUiState.Error).message, Toast.LENGTH_SHORT).show()
            }
            else -> {}
        }
    }

    StatTrackerBackground(modifier = Modifier.fillMaxSize()) {
        Column(
            modifier = Modifier
                .fillMaxSize()
                .verticalScroll(rememberScrollState())
                .imePadding()
                .padding(24.dp),
            horizontalAlignment = Alignment.CenterHorizontally,
            verticalArrangement = Arrangement.Center
        ) {
            BrandHeader(
                title = "Crea tu cuenta",
                subtitle = "Comienza tu camino hacia una mejor gestion de salud"
            )

            Spacer(modifier = Modifier.height(24.dp))

            GlassPanel(modifier = Modifier.fillMaxWidth()) {
                Column(modifier = Modifier.fillMaxWidth()) {
                    Text(
                        text = "Nuevo usuario",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                    Text(
                        text = "La cuenta servira tambien para acceder desde la web.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )

                    Spacer(modifier = Modifier.height(22.dp))

                    GlassTextField(
                        value = nombre,
                        onValueChange = { nombre = it },
                        label = "Nombre",
                        placeholder = "Ej: Maria",
                        enabled = uiState !is RegisterUiState.Loading
                    )

                    Spacer(modifier = Modifier.height(14.dp))

                    GlassTextField(
                        value = email,
                        onValueChange = { email = it },
                        label = "Email",
                        placeholder = "correo@ejemplo.com",
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
                        enabled = uiState !is RegisterUiState.Loading
                    )

                    Spacer(modifier = Modifier.height(14.dp))

                    GlassTextField(
                        value = password,
                        onValueChange = { password = it },
                        label = "Contrasena",
                        placeholder = "Minimo 8 caracteres",
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                        visualTransformation = PasswordVisualTransformation(),
                        enabled = uiState !is RegisterUiState.Loading
                    )

                    Spacer(modifier = Modifier.height(24.dp))

                    if (uiState is RegisterUiState.Loading) {
                        CircularProgressIndicator(
                            modifier = Modifier.align(Alignment.CenterHorizontally),
                            color = StatPrimary
                        )
                    } else {
                        StatPrimaryButton(
                            text = "Registrarse",
                            onClick = {
                                viewModel.register(
                                    nombre.trim(),
                                    email.trim(),
                                    password
                                )
                            }
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(18.dp))

            TextButton(
                onClick = { navController.popBackStack() },
                enabled = uiState !is RegisterUiState.Loading
            ) {
                Text("Ya tengo cuenta. Volver al login", color = StatPrimary)
            }
        }
    }
}
