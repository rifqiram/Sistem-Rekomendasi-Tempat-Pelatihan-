<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PelatihanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'deskripsi' => $this->deskripsi,
            'kategori' => $this->kategori,
            'level' => $this->level,
            'durasi' => $this->durasi,
            'sertifikat' => $this->sertifikat,
            'interest_category' => $this->interest_category,
            'method' => $this->method,
            'location' => $this->location,
            'required_skill' => $this->required_skill,
            'priority' => $this->priority,
            'approved_enrollments_count' => $this->approved_enrollments_count,
            'training_center_id' => $this->training_center_id,
            'training_center' => $this->whenLoaded('trainingCenter'),
            'tanggal_mulai' => $this->tanggal_mulai?->toDateString(),
            'tanggal_selesai' => $this->tanggal_selesai?->toDateString(),
            'is_active' => (bool) $this->is_active,
            'status' => $this->status,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
