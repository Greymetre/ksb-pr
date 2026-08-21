<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserLiveLocation;
use Carbon\Carbon;

class UpdateLiveLocationAddress extends Command
{
    protected $signature = 'locations:update-address {--days=2 : Only look at rows created within this many days} {--limit=200 : Maximum rows to geocode in one run}';

    protected $description = 'Fill the address column of user_live_locations rows that were stored without one';

    public function handle()
    {
        $limit = (int) $this->option('limit');
        $days = (int) $this->option('days');

        $locations = UserLiveLocation::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->where(function ($query) {
                $query->whereNull('address')->orWhere('address', '=', '');
            })
            ->when($days > 0, function ($query) use ($days) {
                $query->where('created_at', '>=', Carbon::now()->subDays($days));
            })
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get(['id', 'latitude', 'longitude']);

        $updated = 0;
        $cache = [];

        foreach ($locations as $location) {
            $cacheKey = round((float) $location->latitude, 4) . ',' . round((float) $location->longitude, 4);

            if (!array_key_exists($cacheKey, $cache)) {
                try {
                    // the helper expects longitude first - same call order used on punch in / punch out
                    $cache[$cacheKey] = mb_substr((string) getLatLongToAddress($location->longitude, $location->latitude), 0, 450);
                } catch (\Exception $e) {
                    $this->error("Lookup failed for location {$location->id}: {$e->getMessage()}");
                    $cache[$cacheKey] = '';
                }
            }

            if ($cache[$cacheKey] === '') {
                continue;
            }

            UserLiveLocation::where('id', $location->id)->update(['address' => $cache[$cacheKey]]);
            $updated++;
        }

        $this->info("Updated address for $updated of {$locations->count()} user_live_locations records.");

        return Command::SUCCESS;
    }
}
