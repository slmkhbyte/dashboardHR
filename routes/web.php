<?php

use App\Http\Controllers\EmployeeDocumentImageController;
use App\Http\Controllers\EmployeeDocumentHistoryImageController;
use App\Http\Controllers\HguMarkerPhotoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/admin/employee-documents/{employeeDocument}/image', [EmployeeDocumentImageController::class, 'show'])
    ->name('employee-documents.image');

Route::get('/admin/employee-document-histories/{employeeDocumentHistory}/image/{version}', [EmployeeDocumentHistoryImageController::class, 'show'])
    ->name('employee-document-histories.image');

Route::get('/admin/hgu-marker-photos/{photo}/image', [HguMarkerPhotoController::class, 'show'])
    ->name('hgu-marker-photos.show');
