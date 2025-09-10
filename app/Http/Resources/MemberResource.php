<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\FamilyResource;
use App\Http\Resources\GroupResource;
use App\Http\Resources\AttendanceResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'family_id' => $this->family_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth ? $this->date_of_birth->format('Y-m-d') : null,
            'age' => $this->date_of_birth ? $this->date_of_birth->age : null,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => [
                'street' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'zip' => $this->zip,
                'country' => $this->country,
                'formatted' => $this->getFormattedAddress(),
            ],
            'profile_photo_url' => $this->profile_photo ? asset('storage/' . $this->profile_photo) : null,
            'membership_status' => $this->membership_status,
            'membership_date' => $this->membership_date ? $this->membership_date->format('Y-m-d') : null,
            'status' => $this->status,
            'custom_fields' => $this->custom_fields ?? [],
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return new UserResource($this->user);
            }),
            'family' => $this->whenLoaded('family', function () {
                return new FamilyResource($this->family);
            }),
            'groups' => $this->whenLoaded('groups', function () {
                return GroupResource::collection($this->groups);
            }),
            'attendance' => $this->whenLoaded('attendance', function () {
                return AttendanceResource::collection($this->attendance);
            }),
        ];
    }
    
    /**
     * Get the formatted address
     *
     * @return string|null
     */
    protected function getFormattedAddress(): ?string
    {
        $parts = [];
        
        if ($this->address) $parts[] = $this->address;
        if ($this->city) $parts[] = $this->city;
        if ($this->state) $parts[] = $this->state;
        if ($this->zip) $parts[] = $this->zip;
        if ($this->country) $parts[] = $this->country;
        
        return !empty($parts) ? implode(', ', $parts) : null;
    }
}
