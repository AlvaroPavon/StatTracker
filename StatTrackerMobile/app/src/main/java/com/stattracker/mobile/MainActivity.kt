package com.stattracker.mobile

import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.ui.Modifier
import androidx.lifecycle.lifecycleScope
import androidx.navigation.compose.rememberNavController
import com.stattracker.mobile.ui.navigation.SetupNavGraph
import com.stattracker.mobile.ui.theme.StatTrackerMobileTheme
import com.stattracker.mobile.util.Constants
import com.stattracker.mobile.util.SecurityCheck
import kotlinx.coroutines.delay
import kotlinx.coroutines.isActive
import kotlinx.coroutines.launch
import kotlin.system.exitProcess

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // --- MSTG-RESILIENCE IMPLEMENTATION ---
        
        // MSTG-RES-2: Anti-Debugging y comprobación periódica
        startSecurityChecks()

        // MSTG-RES-1: Detección de Root
        if (SecurityCheck.isDeviceRooted(this)) {
            showSecurityError("Dispositivo rooteado detectado. Entorno inseguro.")
        }

        // MSTG-RES-3: Verificación de Integridad
        if (!SecurityCheck.checkAppIntegrity(this)) {
            showSecurityError("La aplicación ha sido manipulada.")
        }

        setContent {
            StatTrackerMobileTheme {
                Surface(
                    modifier = Modifier.fillMaxSize(),
                    color = MaterialTheme.colorScheme.background
                ) {
                    val navController = rememberNavController()
                    SetupNavGraph(
                        navController = navController,
                        startDestination = Constants.SCREEN_LOGIN
                    )
                }
            }
        }
    }

    private fun startSecurityChecks() {
        lifecycleScope.launch {
            while (isActive) {
                if (SecurityCheck.isDebuggerConnected()) {
                    showSecurityError("Depurador detectado. Por seguridad la app se cerrará.")
                }
                delay(5000) // Comprobar cada 5 segundos
            }
        }
    }

    private fun showSecurityError(message: String) {
        Toast.makeText(this, message, Toast.LENGTH_LONG).show()
        finishAffinity()
        exitProcess(0)
    }
}
