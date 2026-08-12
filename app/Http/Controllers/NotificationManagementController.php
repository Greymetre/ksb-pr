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
        ]);
    }

    public function filters(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $filters = $request->validate([
            'state_id' => 'nullable|integer|exists:states,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'city_id' => 'nullable|integer|exists:cities,id',
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
        ]);
    }

    public function send(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data = $request->validate([
            'state_id' => 'nullable|integer|exists:states,id',
            'district_id' => 'nullable|integer|exists:districts,id',
            'city_id' => 'nullable|integer|exists:cities,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|distinct|exists:users,id',
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        $imageUrl = $request->hasFile('image')
            ? fileupload($request->file('image'), 'notifications', 'notification_')
            : null;

        $recipients = $this->usersQuery($data)->get(['id', 'notification_id']);

        $sent = 0;
        foreach ($recipients as $recipient) {
            if (SendPushNotification(
                $recipient->id,
                $data['message'],
                'general_notification',
                null,
                $data['title'],
                null,
                $imageUrl
            )) {
                $sent++;
            }
        }

        $stored = $recipients->count();
        $pushEligible = $recipients->filter(
            fn ($recipient) => !empty(trim((string) $recipient->notification_id))
        )->count();
        $pushFailed = $pushEligible - $sent;
        $withoutToken = $stored - $pushEligible;

        $message = "News saved successfully for {$stored} user(s). Push notification sent to {$sent} user(s).";
        if ($withoutToken > 0) {
            $message .= " {$withoutToken} user(s) had no FCM token and can read it from the mobile notification screen.";
        }
        if ($pushFailed > 0) {
            $message .= " {$pushFailed} push delivery attempt(s) failed; the news is still available in the mobile notification screen.";
        }

        return redirect()->route('notification-management.index')->with(
            $stored > 0 ? 'success' : 'error',
            $recipients->isEmpty() ? 'No active users matched the selected filters.' : $message
        );
    }

    private function usersQuery(array $filters = [])
    {
        return User::query()->where('active', 'Y')
            ->when(!empty($filters['user_ids']), fn ($query) => $query->whereIn('users.id', $filters['user_ids']))
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
