<?php
// C:\Users\Asim\sekkeiShoLrvlAPI\routes\api.php
// api.php (Dispatcher): 必要な「アドレス」を見つけて、xxxRequest.phpに　バリデーションを実行し、対応するコントローラーのメソッドにルーティングします

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// --- 検索 ---
Route::get('users/search', [UserController::class, 'search'])->name('users.search');

// --- 登録 ---
Route::post('/users/register', [UserController::class, 'register'])->name('users.register');

// --- 編集 ---
// 編集画面表示
Route::get('/users/{user_id}/editPage', [UserController::class, 'editPage'])->name('users.editPage');
// 編集実行
Route::put('/users/{user_id}/edit', [UserController::class, 'edit'])->name('users.edit');

// --- 削除 ---
Route::delete('/users/{user_id}', [UserController::class, 'delete'])->name('users.delete');

// --- CSVなど出力 ---
Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
