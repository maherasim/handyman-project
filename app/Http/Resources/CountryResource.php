<?php 

namespace App\Http\Resources;

use App\Models\State;
use App\Models\City;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'id'   => $this->id,
            'name' => $this->name,
        ];

        // Optionally include states
        if ($request->has('include_states') && $request->get('include_states') == true) {
            $data['states'] = State::where('country_id', $this->id)->get(['id', 'name']);
        }

        return $data;
    }

    // Static method to get states by country_id
    public static function states($country_id)
    {
        return State::where('country_id', $country_id)->get(['id', 'name']);
    }

    // Static method to get cities by state_id
    public static function cities($state_id)
    {
        return City::where('state_id', $state_id)->get(['id', 'name']);
    }
}

?>