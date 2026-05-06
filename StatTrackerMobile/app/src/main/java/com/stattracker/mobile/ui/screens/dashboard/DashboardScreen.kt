package com.stattracker.mobile.ui.screens.dashboard

import androidx.compose.foundation.Canvas
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.WindowInsets
import androidx.compose.foundation.layout.asPaddingValues
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBars
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.Add
import androidx.compose.material.icons.filled.Person
import androidx.compose.material.icons.filled.Refresh
import androidx.compose.material3.Button
import androidx.compose.material3.CircularProgressIndicator
import androidx.compose.material3.FloatingActionButton
import androidx.compose.material3.Icon
import androidx.compose.material3.IconButton
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.geometry.Offset
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.lifecycle.viewmodel.compose.viewModel
import androidx.navigation.NavController
import com.stattracker.mobile.data.model.Metric
import com.stattracker.mobile.ui.ViewModelFactory
import com.stattracker.mobile.ui.components.GlassMetricSurface
import com.stattracker.mobile.ui.components.StatInfoCard
import com.stattracker.mobile.ui.components.StatMutedLight
import com.stattracker.mobile.ui.components.StatPrimary
import com.stattracker.mobile.ui.components.StatSecondary
import com.stattracker.mobile.ui.components.StatTrackerBackground
import com.stattracker.mobile.util.Constants
import com.stattracker.mobile.util.ServiceLocator
import kotlin.math.max

@Composable
fun DashboardScreen(
    navController: NavController,
    viewModel: DashboardViewModel = viewModel(
        factory = ViewModelFactory(
            ServiceLocator.provideRepository(LocalContext.current)
        )
    )
) {
    val uiState by viewModel.uiState.collectAsState()

    LaunchedEffect(Unit) {
        viewModel.loadMetrics()
    }

    StatTrackerBackground(modifier = Modifier.fillMaxSize()) {
        Scaffold(
            containerColor = Color.Transparent,
            floatingActionButton = {
                FloatingActionButton(
                    onClick = { navController.navigate(Constants.SCREEN_ADD_METRIC) },
                    containerColor = StatPrimary,
                    contentColor = Color.White,
                    shape = RoundedCornerShape(16.dp)
                ) {
                    Icon(Icons.Default.Add, contentDescription = "Anadir")
                }
            }
        ) { paddingValues ->
            Column(
                modifier = Modifier
                    .fillMaxSize()
                    .padding(paddingValues)
                    .padding(WindowInsets.statusBars.asPaddingValues())
                    .padding(horizontal = 18.dp)
            ) {
                DashboardHeader(
                    onRefresh = { viewModel.loadMetrics() },
                    onProfile = { navController.navigate(Constants.SCREEN_PROFILE) }
                )

                Box(
                    modifier = Modifier.fillMaxSize(),
                    contentAlignment = Alignment.Center
                ) {
                    when (val state = uiState) {
                        is DashboardUiState.Loading -> CircularProgressIndicator(color = StatPrimary)
                        is DashboardUiState.Error -> DashboardError(
                            message = state.message,
                            onRetry = { viewModel.loadMetrics() }
                        )
                        is DashboardUiState.Success -> DashboardContent(metrics = state.metrics)
                    }
                }
            }
        }
    }
}

@Composable
private fun DashboardHeader(
    onRefresh: () -> Unit,
    onProfile: () -> Unit
) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(top = 16.dp, bottom = 18.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = "Tu Progreso",
                style = MaterialTheme.typography.headlineMedium,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.onBackground
            )
            Text(
                text = "Registra y visualiza tu IMC",
                style = MaterialTheme.typography.bodyMedium,
                color = MaterialTheme.colorScheme.onSurfaceVariant
            )
        }
        IconButton(onClick = onRefresh) {
            Icon(Icons.Default.Refresh, contentDescription = "Actualizar", tint = StatPrimary)
        }
        IconButton(onClick = onProfile) {
            Icon(Icons.Default.Person, contentDescription = "Perfil", tint = StatPrimary)
        }
    }
}

@Composable
private fun DashboardContent(metrics: List<Metric>) {
    LazyColumn(
        modifier = Modifier.fillMaxSize(),
        contentPadding = PaddingValues(bottom = 96.dp),
        verticalArrangement = Arrangement.spacedBy(14.dp)
    ) {
        item {
            Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                StatInfoCard(
                    label = "Registros",
                    value = metrics.size.toString(),
                    modifier = Modifier.weight(1f)
                )
                StatInfoCard(
                    label = "IMC medio",
                    value = if (metrics.isEmpty()) "--" else String.format("%.1f", metrics.map { it.imc }.average()),
                    modifier = Modifier.weight(1f),
                    accent = StatSecondary
                )
            }
        }

        item {
            GlassMetricSurface(modifier = Modifier.fillMaxWidth()) {
                Column(modifier = Modifier.padding(18.dp)) {
                    Text(
                        text = "Evolucion de tu IMC",
                        style = MaterialTheme.typography.titleLarge,
                        fontWeight = FontWeight.Bold,
                        color = MaterialTheme.colorScheme.onSurface
                    )
                    Text(
                        text = "Lecturas recientes sincronizadas con StatTracker",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant
                    )
                    Spacer(modifier = Modifier.height(16.dp))
                    ImcTrendChart(metrics = metrics)
                }
            }
        }

        item {
            Text(
                text = "Registros de Peso",
                style = MaterialTheme.typography.titleLarge,
                fontWeight = FontWeight.Bold,
                color = MaterialTheme.colorScheme.onBackground,
                modifier = Modifier.padding(top = 8.dp)
            )
        }

        if (metrics.isEmpty()) {
            item {
                GlassMetricSurface(modifier = Modifier.fillMaxWidth()) {
                    Text(
                        text = "No hay registros aun. Anade el primero con el boton inferior.",
                        style = MaterialTheme.typography.bodyMedium,
                        color = MaterialTheme.colorScheme.onSurfaceVariant,
                        modifier = Modifier.padding(18.dp)
                    )
                }
            }
        } else {
            items(metrics) { metric ->
                MetricItem(metric)
            }
        }
    }
}

@Composable
private fun DashboardError(message: String, onRetry: () -> Unit) {
    GlassMetricSurface(modifier = Modifier.fillMaxWidth()) {
        Column(
            modifier = Modifier.padding(20.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Text(message, color = MaterialTheme.colorScheme.error)
            Spacer(modifier = Modifier.height(12.dp))
            Button(onClick = onRetry) {
                Text("Reintentar")
            }
        }
    }
}

@Composable
private fun ImcTrendChart(metrics: List<Metric>) {
    if (metrics.isEmpty()) {
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .height(180.dp),
            contentAlignment = Alignment.Center
        ) {
            Text("Sin datos para mostrar", color = MaterialTheme.colorScheme.onSurfaceVariant)
        }
        return
    }

    val values = metrics.asReversed().map { it.imc }
    val minValue = values.minOrNull() ?: 0.0
    val maxValue = values.maxOrNull() ?: 0.0
    val range = max(1.0, maxValue - minValue)
    val gridColor = MaterialTheme.colorScheme.outline.copy(alpha = 0.25f)

    Canvas(
        modifier = Modifier
            .fillMaxWidth()
            .height(190.dp)
    ) {
        val left = 8.dp.toPx()
        val right = size.width - 8.dp.toPx()
        val top = 10.dp.toPx()
        val bottom = size.height - 22.dp.toPx()
        val chartHeight = bottom - top
        val stepX = if (values.size == 1) 0f else (right - left) / (values.size - 1)

        repeat(4) { index ->
            val y = top + chartHeight * index / 3f
            drawLine(
                color = gridColor,
                start = Offset(left, y),
                end = Offset(right, y),
                strokeWidth = 1.dp.toPx()
            )
        }

        val points = values.mapIndexed { index, value ->
            val x = if (values.size == 1) (left + right) / 2f else left + stepX * index
            val normalized = ((value - minValue) / range).toFloat()
            val y = bottom - normalized * chartHeight
            Offset(x, y)
        }

        points.zipWithNext().forEach { (start, end) ->
            drawLine(
                color = StatPrimary,
                start = start,
                end = end,
                strokeWidth = 4.dp.toPx(),
                cap = StrokeCap.Round
            )
        }

        points.forEach { point ->
            drawCircle(color = StatSecondary, radius = 5.dp.toPx(), center = point)
            drawCircle(color = Color.White, radius = 2.dp.toPx(), center = point)
        }
    }

    Row(
        modifier = Modifier.fillMaxWidth(),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(
            text = "Min ${String.format("%.1f", minValue)}",
            style = MaterialTheme.typography.labelMedium,
            color = StatMutedLight
        )
        Text(
            text = "Max ${String.format("%.1f", maxValue)}",
            style = MaterialTheme.typography.labelMedium,
            color = StatMutedLight
        )
    }
}

@Composable
fun MetricItem(metric: Metric) {
    val category = imcCategory(metric.imc)

    GlassMetricSurface(modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.padding(16.dp)) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.Top
            ) {
                Column(modifier = Modifier.weight(1f)) {
                    Text(
                        text = "IMC: ${String.format("%.2f", metric.imc)}",
                        style = MaterialTheme.typography.titleLarge,
                        fontWeight = FontWeight.Bold,
                        color = StatPrimary
                    )
                    Text(
                        text = category.first,
                        style = MaterialTheme.typography.labelLarge,
                        color = category.second
                    )
                }
                Text(
                    text = metric.fechaRegistro,
                    style = MaterialTheme.typography.bodyMedium,
                    color = MaterialTheme.colorScheme.onSurfaceVariant
                )
            }

            Spacer(modifier = Modifier.height(12.dp))

            Row(verticalAlignment = Alignment.CenterVertically) {
                StatValue(label = "Peso", value = "${metric.peso} kg")
                Spacer(modifier = Modifier.width(18.dp))
                StatValue(label = "Altura", value = "${metric.altura} m")
            }
        }
    }
}

@Composable
private fun StatValue(label: String, value: String) {
    Column {
        Text(
            text = label,
            style = MaterialTheme.typography.labelMedium,
            color = MaterialTheme.colorScheme.onSurfaceVariant
        )
        Text(
            text = value,
            style = MaterialTheme.typography.bodyLarge,
            color = MaterialTheme.colorScheme.onSurface
        )
    }
}

private fun imcCategory(imc: Double): Pair<String, Color> {
    return when {
        imc < 18.5 -> "Bajo Peso" to Color(0xFF2563EB)
        imc < 25.0 -> "Peso Normal" to Color(0xFF059669)
        imc < 30.0 -> "Sobrepeso" to Color(0xFFD97706)
        imc < 35.0 -> "Obesidad I" to Color(0xFFEA580C)
        imc < 40.0 -> "Obesidad II" to Color(0xFFDC2626)
        else -> "Obesidad III" to Color(0xFF991B1B)
    }
}
