package com.stattracker.mobile.util

import android.content.Context
import android.content.pm.PackageManager
import android.os.Build
import android.util.Base64
import com.scottyab.rootbeer.RootBeer
import com.stattracker.mobile.BuildConfig
import java.security.MessageDigest

/**
 * Utilidad para implementar requisitos de MSTG-RESILIENCE
 */
object SecurityCheck {

    // MSTG-RES-1: Deteccion de Root
    fun isDeviceRooted(context: Context): Boolean {
        val rootBeer = RootBeer(context)
        return rootBeer.isRooted
    }

    // MSTG-RES-2: Anti-Debugging
    fun isDebuggerConnected(): Boolean {
        return android.os.Debug.isDebuggerConnected() ||
            android.os.Debug.waitingForDebugger()
    }

    // MSTG-RES-3: Verificacion de Integridad (Firma)
    // Los hashes esperados se configuran por build type desde Gradle.
    private val expectedSignatureHashes: Set<String>
        get() = BuildConfig.EXPECTED_SIGNATURE_HASHES
            .split(',')
            .map { it.trim() }
            .filter { it.isNotEmpty() }
            .toSet()

    fun checkAppIntegrity(context: Context): Boolean {
        try {
            val trustedHashes = expectedSignatureHashes
            if (trustedHashes.isEmpty()) {
                return BuildConfig.DEBUG
            }

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
                    val currentHash = Base64.encodeToString(md.digest(), Base64.NO_WRAP).trim()

                    if (currentHash in trustedHashes) return true
                }
            }
        } catch (e: Exception) {
            // No loguear errores sensibles en produccion
            return false
        }
        return false
    }
}
