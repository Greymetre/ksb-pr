<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class NotificationManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('notification-management.index', [
            'states' => State::where('active', 'Y')->orderBy('state_name')->get(['id', 'state_name']),
            'districts' => District::where('active', 'Y')->orderBy('district_name')->get(['id', 'district_name']),
            'cities' => City::where('active', 'Y')->orderBy('city_name')->get(['id', 'city_name']),
            'users' => $this->usersQuery()->orderBy('name')->get(['id', 'name', 'mobile']),
            'recipientCount' => $this->usersQuery()->whereNotNull('notification_id')
                ->where('notification_id', '!=', '')->count(),
        ]);
    }

    public function filters(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $filters = $request->validate([
            'state_id' => 'nullable|integer|exists:states,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $districts = District::where('active', 'Y')
            ->when($filters['state_id'] ?? null, fn ($query, $id) => $query->where('state_id', $id))
            ->orderBy('district_name')->get(['id', 'district_name']);

        $cities = City::where('active', 'Y')
            ->when($filters['state_id'] ?? null, fn ($query, $id) => $query->where('state_id', $id))
            ->when($filters['district_id'] ?? null, fn ($query, $id) => $query->where('district_id', $id))
            ->orderBy('city_name')->get(['id', 'city_name']);

        $users = $this->usersQuery($filters)->orderBy('name')->get(['id', 'name', 'mobile'])
            ->map(function ($user) {
                $user->display_name = $user->name . ($user->mobile ? ' - ' . $user->mobile : '');
                return $user;
            });

        return response()->json([
            'districts' => $districts,
            'cities' => $cities,
            'users' => $users,
            'recipient_count' => $this->usersQuery($filters)->whereNotNull('notification_id')
                ->where('notification_id', '!=', '')->count(),
        ]);
    }

    public function send(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data = $request->validate([
            'state_id' => 'nullable|integer|exists:states,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $recipients = $this->usersQuery($data)->whereNotNull('notification_id')
            ->where('notification_id', '!=', '')->get(['id']);

        $sent = 0;
        foreach ($recipients as $recipient) {
            if (SendPushNotification($recipient->id, $data['message'], 'general_notification')) {
                $sent++;
            }
        }

        $failed = $recipients->count() - $sent;
        $message = "Notification sent successfully to {$sent} user(s).";
        if ($failed > 0) {
            $message .= " {$failed} delivery attempt(s) failed.";
        }

        return redirect()->route('notification-management.index')->with(
            $sent > 0 ? 'success' : 'error',
            $recipients->isEmpty() ? 'No selected user has a valid FCM token.' : $message
        );
    }

    private function usersQuery(array $filters = [])
    {
        return User::query()->where('active', 'Y')
            ->when($filters['user_id'] ?? null, fn ($query, $id) => $query->where('users.id', $id))
            ->when(
                ($filters['state_id'] ?? null) || ($filters['district_id'] ?? null) || ($filters['city_id'] ?? null),
                function ($query) use ($filters) {
                    $query->whereIn('users.id', function ($subQuery) use ($filters) {
                        $subQuery->select('user_city_assigns.userid')->from('user_city_assigns')
                            ->join('cities', 'cities.id', '=', 'user_city_assigns.city_id')
                            ->when($filters['state_id'] ?? null, fn ($q, $id) => $q->where('cities.state_id', $id))
                            ->when($filters['district_id'] ?? null, fn ($q, $id) => $q->where('cities.district_id', $id))
                            ->when($filters['city_id'] ?? null, fn ($q, $id) => $q->where('cities.id', $id));
                    });
                }
            );
    }
}
