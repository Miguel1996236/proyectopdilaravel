<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizComparisonController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizInvitationController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\StudentGroupController;
use App\Http\Controllers\SurveyAccessController;
use App\Http\Controllers\SurveyResponseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController as AdminUserController;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('quizzes', QuizController::class);
    Route::get('quizzes-crear/desde-plantilla', [QuizController::class, 'createFromTemplate'])->name('quizzes.create-from-template');
    Route::post('quizzes-crear/desde-plantilla', [QuizController::class, 'storeFromTemplate'])->name('quizzes.store-from-template');
    Route::post('quizzes/{quiz}/publish', [QuizController::class, 'publish'])->name('quizzes.publish');
    Route::post('quizzes/{quiz}/close', [QuizController::class, 'close'])->name('quizzes.close');
    Route::post('quizzes/{quiz}/analysis', [QuizController::class, 'analyze'])->name('quizzes.analyze');
    Route::get('quizzes/{quiz}/analysis', [QuizController::class, 'analysis'])->name('quizzes.analysis.show');
    Route::get('quizzes/{quiz}/analysis/export', [QuizController::class, 'exportAnalysis'])->name('quizzes.analysis.export');
    Route::resource('quizzes.questions', QuestionController::class)->except(['index', 'show']);
    Route::resource('quizzes.invitations', QuizInvitationController::class)->only(['store', 'update', 'destroy']);

    Route::resource('admin/users', AdminUserController::class)->except(['show'])->names('admin.users');

    // Grupos de estudiantes
    Route::resource('groups', StudentGroupController::class);
    Route::get('groups/{group}/export', [StudentGroupController::class, 'exportExcel'])->name('groups.export');

    // Recordatorios
    Route::get('reminders', [ReminderController::class, 'create'])->name('reminders.create');
    Route::post('reminders', [ReminderController::class, 'send'])->name('reminders.send');

    // Comparación de encuestas
    Route::get('comparisons', [QuizComparisonController::class, 'index'])->name('comparisons.index');
    Route::get('comparisons/{comparison}', [QuizComparisonController::class, 'show'])->name('comparisons.show');
    Route::post('comparisons', [QuizComparisonController::class, 'compare'])->name('comparisons.compare');
    Route::post('comparisons/ai', [QuizComparisonController::class, 'analyzeWithAI'])->name('comparisons.ai');

    // Exportaciones a Excel
    Route::get('exports/surveys', [ExportController::class, 'exportSurveys'])->name('exports.surveys');
    Route::get('exports/students', [ExportController::class, 'exportStudents'])->name('exports.students');
    Route::get('exports/quiz/{quiz}/responses', [ExportController::class, 'exportQuizResponses'])->name('exports.quiz.responses');

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('summary', [ReportsController::class, 'summary'])->name('summary');
        Route::get('students', [ReportsController::class, 'students'])->name('students');
        Route::get('surveys', function () {
            return redirect()->route('reports.summary');
        })->name('surveys');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('web')->group(function () {
    Route::get('ingresar-codigo', [SurveyAccessController::class, 'showLinkForm'])->name('surveys.access.form');
    Route::post('ingresar-codigo', [SurveyAccessController::class, 'verifyCode'])->name('surveys.access.verify');
    Route::get('responder/{code}', [SurveyResponseController::class, 'showSurvey'])->name('surveys.respond.show');
    Route::post('responder/{code}', [SurveyResponseController::class, 'submitSurvey'])->name('surveys.respond.submit');
    Route::get('encuesta-completada', fn () => view('surveys.thankyou'))->name('surveys.thankyou');
});

require __DIR__.'/auth.php';
