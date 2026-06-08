<?php

namespace App\Services\Presensi;

use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LemburUpdateService
{
    private const STATUS_COLUMNS = ['status_approval', 'approval_status', 'status'];
    private const NOTE_COLUMNS = ['catatan', 'keterangan', 'notes'];

    /**
     * @return array{status_field:?string, status_options:array<int, string>, note_field:?string}
     */
    public function metadata(): array
    {
        $statusField = $this->firstExistingColumn(self::STATUS_COLUMNS);
        $noteField = $this->firstExistingColumn(self::NOTE_COLUMNS);

        return [
            'status_field' => $statusField,
            'status_options' => $statusField ? $this->statusOptions($statusField) : [],
            'note_field' => $noteField,
        ];
    }

    /**
     * @param  array{jam_mulai:string, jam_selesai?:?string, status?:?string, note?:?string}  $data
     */
    public function update(Lembur $lembur, array $data): void
    {
        $metadata = $this->metadata();
        $tanggal = Carbon::parse($lembur->tanggal)->toDateString();
        $timezone = (string) config('app.timezone');

        $values = [
            'jam_mulai_lembur' => Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$data['jam_mulai']}", $timezone)->seconds(0),
            'jam_pulang_lembur' => ! empty($data['jam_selesai'])
                ? Carbon::createFromFormat('Y-m-d H:i', "{$tanggal} {$data['jam_selesai']}", $timezone)->seconds(0)
                : null,
        ];

        if ($metadata['status_field'] && array_key_exists('status', $data)) {
            $values[$metadata['status_field']] = $data['status'] ?: null;
        }

        if ($metadata['note_field'] && array_key_exists('note', $data)) {
            $values[$metadata['note_field']] = $data['note'] ?: null;
        }

        $lembur->forceFill($values)->save();
    }

    private function firstExistingColumn(array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn('lembur', $column)) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function statusOptions(string $column): array
    {
        $enumValues = $this->enumValues($column);
        if ($enumValues !== []) {
            return $enumValues;
        }

        try {
            return Lembur::query()
                ->whereNotNull($column)
                ->where($column, '!=', '')
                ->distinct()
                ->orderBy($column)
                ->pluck($column)
                ->map(fn ($value) => trim((string) $value))
                ->filter()
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array<int, string>
     */
    private function enumValues(string $column): array
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return [];
        }

        try {
            $row = DB::selectOne("SHOW COLUMNS FROM `lembur` WHERE Field = ?", [$column]);
            $type = is_object($row) ? (string) ($row->Type ?? '') : '';

            if (! str_starts_with($type, 'enum(')) {
                return [];
            }

            preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $type, $matches);

            return collect($matches[1] ?? [])
                ->map(fn ($value) => str_replace("\\'", "'", (string) $value))
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }
}
