package com.stattracker.mobile.ui.navigation

import androidx.compose.runtime.Composable
import androidx.navigation.NavHostController
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import com.stattracker.mobile.ui.screens.add_metric.AddMetricScreen
import com.stattracker.mobile.ui.screens.dashboard.DashboardScreen
import com.stattracker.mobile.ui.screens.login.LoginScreen
import com.stattracker.mobile.ui.screens.profile.ProfileScreen
import com.stattracker.mobile.ui.screens.register.RegisterScreen
import com.stattracker.mobile.util.Constants

sealed class Screen(val route: String) {
    object Login : Screen(Constants.SCREEN_LOGIN)
    object Register : Screen(Constants.SCREEN_REGISTER)
    object Dashboard : Screen(Constants.SCREEN_DASHBOARD)
    object Profile : Screen(Constants.SCREEN_PROFILE)
    object AddMetric : Screen(Constants.SCREEN_ADD_METRIC)
}

@Composable
fun SetupNavGraph(
    navController: NavHostController,
    startDestination: String
) {
    NavHost(
        navController = navController,
        startDestination = startDestination
    ) {
        composable(route = Screen.Login.route) {
            LoginScreen(navController)
        }
        composable(route = Screen.Register.route) {
            RegisterScreen(navController)
        }
        composable(route = Screen.Dashboard.route) {
            DashboardScreen(navController)
        }
        composable(route = Screen.Profile.route) {
            ProfileScreen(navController)
        }
        composable(route = Screen.AddMetric.route) {
            AddMetricScreen(navController)
        }
    }
}
