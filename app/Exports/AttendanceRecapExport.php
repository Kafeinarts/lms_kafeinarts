<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceRecapExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, User>  $students
     * @param  array<int, array<string, int>>  $recap
     */
    public function __construct(
        private Collection $students,
        private array $recap,
        private ?string $kelasName,
    ) {}

    /**
     * @return Collection<int, User>
     */
    public function collection(): Collection
    {
        return $this->students;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        $headings = ['NIS', 'Nama', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alfa', 'Persentase Hadir'];

        if ($this->kelasName !== null) {
            $headings[] = 'Kelas Filter';
        }

        return $headings;
    }

    /**
     * @param  User  $student
     * @return array<int, mixed>
     */
    public function map($student): array
    {
        $statuses = $this->recap[$student->id] ?? [];
        $present = (int) ($statuses['H'] ?? 0);
        $total = array_sum($statuses);

        $row = [
            $student->nis ?? '-',
            $student->name,
            $student->kelas?->name ?? '-',
            $present,
            (int) ($statuses['I'] ?? 0),
            (int) ($statuses['S'] ?? 0),
            (int) ($statuses['A'] ?? 0),
            $total > 0 ? round($present / $total * 100, 2).'%' : '0%',
        ];

        if ($this->kelasName !== null) {
            $row[] = $this->kelasName;
        }

        return $row;
    }
}
