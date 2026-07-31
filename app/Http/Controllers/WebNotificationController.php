<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WebNotificationController extends Controller
{
    public function page(Request $request): View
    {
        $notifications = Notification::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('web-notifications.index', compact('notifications'));
    }

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $notifications = Notification::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit(20)
            ->get(['id', 'type', 'data', 'image', 'read', 'model', 'model_id', 'created_at'])
            ->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'title' => $notification->type ?: 'FieldKonnect',
                'message' => $notification->data,
                'image' => $notification->image,
                'read' => (bool) $notification->read,
                'created_at' => $notification->created_at?->diffForHumans(),
                'url' => route('web-notifications.open', $notification),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => Notification::where('user_id', $userId)->where('read', false)->count(),
        ]);
    }

    public function open(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);

        if (!$notification->read) {
            $notification->update(['read' => true]);
        }

        return redirect()->to($this->destination($notification));
    }

    public function readAll(Request $request): JsonResponse
    {
        Notification::where('user_id', $request->user()->id)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json(['status' => 'success']);
    }

    public function download(Request $request, Notification $notification): BinaryFileResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 404);
        abort_if(empty($notification->image), 404);

        $urlPath = rawurldecode((string) parse_url($notification->image, PHP_URL_PATH));
        $uploadPosition = strpos($urlPath, '/uploads/notifications/');
        abort_if($uploadPosition === false, 404);

        $relativePath = ltrim(substr($urlPath, $uploadPosition), '/');
        $filePath = realpath(public_path($relativePath));
        $allowedDirectory = realpath(public_path('uploads/notifications'));

        abort_unless(
            $filePath
            && $allowedDirectory
            && str_starts_with($filePath, $allowedDirectory . DIRECTORY_SEPARATOR)
            && is_file($filePath),
            404
        );

        return response()->download($filePath);
    }

    private function destination(Notification $notification): string
    {
        if (strtolower(trim((string) $notification->model)) === 'general_notification') {
            return route('web-notifications.page');
        }

        if (!$notification->model_id) {
            return url('dashboard');
        }

        return match (strtolower(trim((string) $notification->model))) {
            'order', 'order_history' => route('orders.show', $notification->model_id),
            'leave' => route('leaves.show', $notification->model_id),
            'tour', 'tour_plan' => route('tours.show', $notification->model_id),
            'expense', 'expense_management' => route('expenses.show', $notification->model_id),
            'lead' => route('leads.show', $notification->model_id),
            'task', 'task_management' => route('tasks.show', $notification->model_id),
            'opportunity' => route('lead-opportunities.show', $notification->model_id),
            'customer', 'distributor' => route('customers.show', $notification->model_id),
            'attendance' => url('attendances?notification_id=' . $notification->model_id),
            default => url('dashboard'),
        };
    }
}
