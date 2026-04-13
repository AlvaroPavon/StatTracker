package com.stattracker.mobile.util

import android.content.Context
import android.content.pm.PackageManager
import android.os.Build
import android.util.Base64
import com.scottyab.rootbeer.RootBeer
import java.security.MessageDigest

/**
 * Utilidad para implementar requisitos de MSTG-RESILIENCE
 */
object SecurityCheck {

    // MSTG-RES-1: Detección de Root
    fun isDeviceRooted(context: Context): Boolean {
        val rootBeer = RootBeer(context)
        return rootBeer.isRooted
    }

    // MSTG-RES-2: Anti-Debugging
    fun isDebuggerConnected(): Boolean {
        return android.os.Debug.isDebuggerConnected()
    }

    // MSTG-RES-3: Verificación de Integridad (Firma)
    // Nota: Reemplazar el HASH_ORIGINAL por el real de tu firma tras la primera compilación
    private const val EXPECTED_SIGNATURE_HASH = "PON_AQUI_TU_HASH_REAL" 

    fun checkAppIntegrity(context: Context): Boolean {
        try {
            val packageManager = context.packageManager
            val packageName = context.packageName
            
            val signatures = if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.P) {
                val packageInfo = packageManager.getPackageInfo(packageName, PackageManager.GET_SIGNING_CERTIFICATES)
                packageInfo.signingInfo?.apkContentsSigners
            } else {
                @Suppress("DEPRECATION")
                val packageInfo = packageManager.getPackageInfo(packageName, PackageManager.GET_SIGNATURES)
                @Suppress("DEPRECATION")
                packageInfo.signatures
            }

            if (signatures != null) {
                for (signature in signatures) {
                    val md = MessageDigest.getInstance("SHA-256")
                    md.update(signature.toByteArray())
                    val currentHash = Base64.encodeToString(md.digest(), Base64.DEFAULT).trim()
                    
                    android.util.Log.d("SecurityCheck", "Signature Hash: $currentHash")
                    
                    // if (currentHash == EXPECTED_SIGNATURE_HASH) return true
                }
            }
        } catch (e: Exception) {
            android.util.Log.e("SecurityCheck", "Error verificando integridad: ${e.message}")
            return false
        }
        return true // Temporalmente true hasta configurar el hash real
    }
}
