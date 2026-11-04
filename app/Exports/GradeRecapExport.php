<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GradeRecapExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, User>  $students
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(
        private Collection $students,
        private array $rows,
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
        return ['NIS', 'Nama', 'Kelas', 'Nilai Tugas', 'UTS', 'UAS', 'Nilai Akhir', 'Huruf', 'Catatan Guru'];
    }

    /**
     * @param  User  $student
     * @return array<int, mixed>
     */
    public function map($student): array
    {
        $row = $this->rows[$student->id] ?? [];

        return [
            $student->nis ?? '-',
            $student->name,
            $student->kelas?->name ?? '-',
            $row['tugas'] !== null ? number_format((float) $row['tugas'], 2) : '-',
            $row['uts'] !== null ? number_format((float) $row['uts'], 2) : '-',
            $row['uas'] !== null ? number_format((float) $row['uas'], 2) : '-',
            $row['final'] !== null ? number_format((float) $row['final'], 2) : '-',
            $row['letter'] ?? '-',
            $row['notes'] ?? '-',
        ];
    }
}
