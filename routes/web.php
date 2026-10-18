<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ConceptoController;
use App\Http\Controllers\ConvenioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\EmpleadoDocumentoController;
use App\Http\Controllers\EmpleadoFamiliarController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FeriadoController;
use App\Http\Controllers\FichadaController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\LicenciaController;
use App\Http\Controllers\LiquidacionController;
use App\Http\Controllers\PuestoController;
use App\Http\Controllers\TipoLicenciaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VacacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'empresa.activa'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Solo superadmin (el middleware deja pasar siempre al superadmin)
    Route::middleware('rol:superadmin')->group(function () {
        Route::resource('empresas', EmpresaController::class)->except('show');
        Route::patch('empresas/{empresa}/toggle', [EmpresaController::class, 'toggle'])->name('empresas.toggle');
    });

    // Superadmin y administradores de RRHH
    Route::middleware('rol:admin_rrhh')->group(function () {
        Route::resource('usuarios', UsuarioController::class)
            ->except('show')
            ->parameters(['usuarios' => 'usuario']);
    });

    // Módulos por empresa (administrador de RRHH)
    Route::middleware(['rol:admin_rrhh', 'requiere.empresa'])->group(function () {
        // Estructura
        Route::resource('areas', AreaController::class)->except('show');
        Route::resource('puestos', PuestoController::class)->except('show');
        Route::resource('convenios', ConvenioController::class)->except('show');
        Route::resource('convenios.categorias', CategoriaController::class)
            ->except('show')
            ->shallow();
        Route::post('convenios/{convenio}/aumento', [CategoriaController::class, 'aumento'])
            ->name('convenios.aumento');

        // Legajos
        Route::resource('empleados', EmpleadoController::class);
        Route::patch('empleados/{empleado}/baja', [EmpleadoController::class, 'baja'])->name('empleados.baja');
        Route::patch('empleados/{empleado}/reincorporar', [EmpleadoController::class, 'reincorporar'])->name('empleados.reincorporar');

        Route::post('empleados/{empleado}/familiares', [EmpleadoFamiliarController::class, 'store'])
            ->name('empleados.familiares.store');
        Route::put('empleados/{empleado}/familiares/{familiar}', [EmpleadoFamiliarController::class, 'update'])
            ->name('empleados.familiares.update');
        Route::delete('empleados/{empleado}/familiares/{familiar}', [EmpleadoFamiliarController::class, 'destroy'])
            ->name('empleados.familiares.destroy');

        Route::post('empleados/{empleado}/documentos', [EmpleadoDocumentoController::class, 'store'])
            ->name('empleados.documentos.store');
        Route::get('empleados/{empleado}/documentos/{documento}/ver', [EmpleadoDocumentoController::class, 'ver'])
            ->name('empleados.documentos.ver');
        Route::get('empleados/{empleado}/documentos/{documento}/descargar', [EmpleadoDocumentoController::class, 'descargar'])
            ->name('empleados.documentos.descargar');
        Route::delete('empleados/{empleado}/documentos/{documento}', [EmpleadoDocumentoController::class, 'destroy'])
            ->name('empleados.documentos.destroy');

        // Asistencia
        Route::get('asistencia/parte', [AsistenciaController::class, 'parte'])->name('asistencia.parte');
        Route::get('asistencia/resumen', [AsistenciaController::class, 'resumen'])->name('asistencia.resumen');
        Route::get('asistencia/resumen/exportar', [AsistenciaController::class, 'exportarResumen'])->name('asistencia.resumen.exportar');
        Route::get('asistencia/empleados/{empleado}', [AsistenciaController::class, 'empleado'])->name('asistencia.empleado');

        Route::get('fichadas/importar', [FichadaController::class, 'importar'])->name('fichadas.importar');
        Route::post('fichadas/importar', [FichadaController::class, 'procesarImportacion'])->name('fichadas.importar.procesar');
        Route::resource('fichadas', FichadaController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::resource('horarios', HorarioController::class)->except('show');

        Route::get('feriados', [FeriadoController::class, 'index'])->name('feriados.index');
        Route::post('feriados', [FeriadoController::class, 'store'])->name('feriados.store');
        Route::post('feriados/cargar-fijos', [FeriadoController::class, 'cargarFijos'])->name('feriados.cargar-fijos');
        Route::delete('feriados/{feriado}', [FeriadoController::class, 'destroy'])->name('feriados.destroy');

        // Licencias y vacaciones
        Route::get('licencias/calendario', [LicenciaController::class, 'calendario'])->name('licencias.calendario');
        Route::get('licencias/calcular-dias', [LicenciaController::class, 'calcularDias'])->name('licencias.calcular-dias');
        Route::resource('licencias', LicenciaController::class)->except('show');
        Route::patch('licencias/{licencia}/aprobar', [LicenciaController::class, 'aprobar'])->name('licencias.aprobar');
        Route::patch('licencias/{licencia}/rechazar', [LicenciaController::class, 'rechazar'])->name('licencias.rechazar');
        Route::patch('licencias/{licencia}/cancelar', [LicenciaController::class, 'cancelar'])->name('licencias.cancelar');
        Route::get('licencias/{licencia}/certificado', [LicenciaController::class, 'certificado'])->name('licencias.certificado');

        Route::get('vacaciones', [VacacionController::class, 'index'])->name('vacaciones.index');
        Route::post('vacaciones/generar', [VacacionController::class, 'generar'])->name('vacaciones.generar');
        Route::put('vacaciones/{empleado}/{anio}', [VacacionController::class, 'update'])->name('vacaciones.update')->whereNumber('anio');

        Route::post('tipos-licencia/predeterminados', [TipoLicenciaController::class, 'predeterminados'])->name('tipos-licencia.predeterminados');
        Route::resource('tipos-licencia', TipoLicenciaController::class)->except('show')->parameters(['tipos-licencia' => 'tipo']);

        // Sueldos
        Route::post('conceptos/predeterminados', [ConceptoController::class, 'predeterminados'])->name('conceptos.predeterminados');
        Route::resource('conceptos', ConceptoController::class)->except('show');

        Route::resource('liquidaciones', LiquidacionController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy'])
            ->parameters(['liquidaciones' => 'liquidacion']);
        Route::post('liquidaciones/{liquidacion}/calcular', [LiquidacionController::class, 'calcular'])->name('liquidaciones.calcular');
        Route::patch('liquidaciones/{liquidacion}/cerrar', [LiquidacionController::class, 'cerrar'])->name('liquidaciones.cerrar');
        Route::patch('liquidaciones/{liquidacion}/reabrir', [LiquidacionController::class, 'reabrir'])->name('liquidaciones.reabrir');
        Route::get('liquidaciones/{liquidacion}/exportar', [LiquidacionController::class, 'exportar'])->name('liquidaciones.exportar');
        Route::get('liquidaciones/{liquidacion}/imprimir', [LiquidacionController::class, 'imprimir'])->name('liquidaciones.imprimir');
        Route::get('liquidaciones/{liquidacion}/recibos/{recibo}', [LiquidacionController::class, 'recibo'])->name('liquidaciones.recibo');
        Route::post('liquidaciones/{liquidacion}/novedades', [LiquidacionController::class, 'guardarNovedad'])->name('liquidaciones.novedades.store');
        Route::delete('liquidaciones/{liquidacion}/novedades/{novedad}', [LiquidacionController::class, 'eliminarNovedad'])->name('liquidaciones.novedades.destroy');
    });

    // Paso 8: portal de autogestión del empleado
});
