package com.stattracker.mobile

import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.ui.Modifier
import androidx.navigation.compose.rememberNavController
import com.stattracker.mobile.ui.navigation.SetupNavGraph
import com.stattracker.mobile.ui.theme.StatTrackerMobileTheme
import com.stattracker.mobile.util.Constants
import com.stattracker.mobile.util.SecurityCheck
import kotlin.system.exitProcess

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        
        // --- MSTG-RESILIENCE IMPLEMENTATION ---
        
        // MSTG-RES-2: Anti-Debugging
        if (SecurityCheck.isDebuggerConnected()) {
            Toast.makeText(this, "Depurador detectado. Por seguridad la app se cerrará.", Toast.LENGTH_LONG).show()
            finishAffinity()
            exitProcess(0)
        }

        // MSTG-RES-1: Detección de Root
        if (SecurityCheck.isDeviceRooted(this)) {
            Toast.makeText(this, "Dispositivo rooteado detectado. Entorno inseguro.", Toast.LENGTH_LONG).show()
            finishAffinity()
            exitProcess(0)
        }

        // MSTG-RES-3: Verificación de Integridad
        if (!SecurityCheck.checkAppIntegrity(this)) {
            Toast.makeText(this, "La aplicación ha sido manipulada.", Toast.LENGTH_LONG).show()
            finishAffinity()
            exitProcess(0)
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
}
