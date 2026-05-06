package com.stattracker.mobile.ui.screens.login

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
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Visibility
import androidx.compose.material.icons.filled.VisibilityOff
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
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
import androidx.compose.ui.text.input.VisualTransformation
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
import com.stattracker.mobile.util.Constants
import com.stattracker.mobile.util.ServiceLocator

@Composable
fun LoginScreen(
    navController: NavController,
    viewModel: LoginViewModel = viewModel(
        factory = ViewModelFactory(
            ServiceLocator.provideRepository(LocalContext.current),
            ServiceLocator.provideTokenManager(LocalContext.current)
        )
    )
) {
    var email by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var passwordVisible by remember { mutableStateOf(false) }

    val uiState by viewModel.uiState.collectAsState()
    val context = LocalContext.current

    LaunchedEffect(uiState) {
        when (uiState) {
            is LoginUiState.Success -> {
                navController.navigate(Constants.SCREEN_DASHBOARD) {
                    popUpTo(Constants.SCREEN_LOGIN) { inclusive = true }
                }
            }
            is LoginUiState.Error -> {
                Toast.makeText(context, (uiState as LoginUiState.Error).message, Toast.LENGTH_SHORT).show()
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
                title = "Bienvenido",
                subtitle = "Inicia sesion para registrar y visualizar tu IMC"
            )

            Spacer(modifier = Modifier.height(28.dp))

            GlassPanel(modifier = Modifier.fillMaxWidth()) {
                Column(modifier = Modifier.fillMaxWidth()) {
                    Text(
                        text = "Acceso seguro",
                        style = MaterialTheme.typography.titleMedium,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                    Text(
                        text = "Usa las mismas credenciales de StatTracker web.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )

                    Spacer(modifier = Modifier.height(22.dp))

                    GlassTextField(
                        value = email,
                        onValueChange = { email = it },
                        label = "Email",
                        placeholder = "correo@ejemplo.com",
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Email),
                        enabled = uiState !is LoginUiState.Loading
                    )

                    Spacer(modifier = Modifier.height(14.dp))

                    GlassTextField(
                        value = password,
                        onValueChange = { password = it },
                        label = "Contrasena",
                        placeholder = "********",
                        keyboardOptions = KeyboardOptions(keyboardType = KeyboardType.Password),
                        visualTransformation = if (passwordVisible) {
                            VisualTransformation.None
                        } else {
                            PasswordVisualTransformation()
                        },
                        enabled = uiState !is LoginUiState.Loading,
                        trailingIcon = {
                            IconButton(onClick = { passwordVisible = !passwordVisible }) {
                                Icon(
                                    imageVector = if (passwordVisible) {
                                        Icons.Filled.Visibility
                                    } else {
                                        Icons.Filled.VisibilityOff
                                    },
                                    contentDescription = null,
                                    tint = StatPrimary
                                )
                            }
                        }
                    )

                    Spacer(modifier = Modifier.height(24.dp))

                    if (uiState is LoginUiState.Loading) {
                        CircularProgressIndicator(
                            modifier = Modifier.align(Alignment.CenterHorizontally),
                            color = StatPrimary
                        )
                    } else {
                        StatPrimaryButton(
                            text = "Iniciar Sesion",
                            onClick = { viewModel.login(email.trim(), password) }
                        )
                    }
                }
            }

            Spacer(modifier = Modifier.height(18.dp))

            TextButton(
                onClick = { navController.navigate(Constants.SCREEN_REGISTER) },
                enabled = uiState !is LoginUiState.Loading
            ) {
                Text("No tienes cuenta? Registrate", color = StatPrimary)
            }
        }
    }
}
