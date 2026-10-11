<?php
// C:\Users\Asim\sekkeiShoLrvlAPI\app\Http\Controllers\UserController.php
// コントローラー: リクエストからクリーンなデータを受信し、サービスに「先に進んでください」と指示します。
// その後サービスから結果を受け取り、適切なbladeを呼び出します。

namespace App\Http\Controllers;

// --- モデル ---
use App\Models\TUser;

// --- システム・フレームワーク関連 ---
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Exception;

// --- フォームリクエスト (バリデーション) ---
use App\Http\Requests\User\SearchUserRequest;
use App\Http\Requests\User\RegistUserRequest;
use App\Http\Requests\User\EditUserRequest;

// --- サービスクラス (ビジネスロジック) ---
use App\Services\User\UserSearchService;
use App\Services\User\UserRegisterService;
use App\Services\User\UserEditService;
use App\Services\User\UserDeleteService;
use App\Contracts\User\UserExportInterface;

class UserController extends Controller
{
    /**
     * ユーザー一覧画面の表示および検索処理
     *
     * @param \App\Http\Requests\User\SearchUserRequest $request
     * @param \App\Services\User\UserSearchService $service
     * @return \Illuminate\Http\JsonResponse ユーザー一覧画面
     */
    public function search(SearchUserRequest $request, UserSearchService $service): JsonResponse
    {
        $searchData = $request->searchData();
        $users = $service->searchService($searchData);

        return response()->json([
            'data' => $users->items(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page'    => $users->lastPage(),
                'per_page'     => $users->perPage(),
                'total'        => $users->total(),
            ],
            'searchData' => $searchData,
        ]);
    }

    /**
     * ユーザー登録処理(POST)
     *
     * @param RegistUserRequest $request
     * @param UserRegisterService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(RegistUserRequest $request, UserRegisterService $service)
    {
        try {
            $service->registerService($request->registerData());

            return response()->json([
                'message' => 'ユーザー登録が完了しました。',
            ], 201);
        } catch (\Exception $e) {
            Log::error('User Registration Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'ユーザーの登録に失敗しました。',
            ], 500);
        }
    }

    /**
     * ユーザー編集画面の表示 (GET)
     *
     * @param int|string $user_id
     * @return \Illuminate\View\View
     */
    public function editPage($user_id)
    {
        $user = TUser::with('userBelongs')->findOrFail($user_id);

        return response()->json([
            'user' => $user,
            'belongs' => $user->userBelongs,
        ]);
    }

    /**
     * ユーザー情報の更新処理 (PUT)
     *
     * @param \App\Http\Requests\User\EditUserRequest $request
     * @param \App\Services\User\UserEditService $service
     * @param int|string $user_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit(EditUserRequest $request, UserEditService $service, $user_id)
    {
        try {
            // 編集対象ユーザー取得
            $user = TUser::findOrFail($user_id);

            // 更新処理
            $service->editService($user, $request->editData());

            return response()->json([
                'message' => 'ユーザー編集が完了しました。',
                'user_id' => $user->user_id,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'ユーザーが見つかりません。',
            ], 404);
        } catch (\Throwable $e) {
            Log::error('User Edit Error', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'ユーザーの編集に失敗しました。',
            ], 500);
        }
    }

    /**
     * ユーザー削除処理 (DELETE)
     *
     * @param int|string $user_id
     * @param \App\Services\User\UserDeleteService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($user_id, UserDeleteService $service)
    {
        if (!$user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'ユーザーIDが指定されていません'
            ], 400);
        }

        $result = $service->userDeleteService($user_id);

        if ($result['status'] === 'success') {
            return response()->json([
                'status' => 'success'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message']
        ], 409);
    }

    /**
     * ユーザー情報のCSVエクスポート処理
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Contracts\User\UserExportInterface $exportInterface
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     *
     * メソッドがインターフェースを実装する場合、その名前はインターフェースと一致する必要があります。
     */
    public function export(Request $request, UserExportInterface $exportInterface): StreamedResponse
    {
        return $exportInterface->export($request->all());
    }
}
