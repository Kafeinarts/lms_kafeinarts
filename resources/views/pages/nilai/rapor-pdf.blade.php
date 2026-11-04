<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Rapor {{ $student->name }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2933;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #1f2933;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 18px;
            margin: 0 0 3px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header p {
            margin: 0;
            font-size: 11px;
            color: #52606d;
        }

        h2 {
            font-size: 14px;
            text-align: center;
            text-transform: uppercase;
            margin: 18px 0 14px;
            letter-spacing: 1px;
        }

        .identity {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }

        .identity td {
            padding: 3px 0;
            vertical-align: top;
        }

        .identity .label {
            width: 130px;
            font-weight: bold;
        }

        .grades {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .grades th,
        .grades td {
            border: 1px solid #9aa5b1;
            padding: 7px 8px;
        }

        .grades th {
            background: #e4e7eb;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        .grades td.center,
        .grades th.center {
            text-align: center;
        }

        .grades tfoot th {
            background: #f5f7fa;
        }

        .summary {
            margin-bottom: 24px;
            font-size: 12px;
        }

        .summary strong {
            font-size: 14px;
        }

        .signature {
            width: 100%;
            margin-top: 36px;
            border-collapse: collapse;
        }

        .signature td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }

        .signature .space {
            height: 62px;
        }

        .muted {
            color: #616e7c;
            font-size: 10px;
            text-align: center;
            margin-top: 28px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $school->name }}</h1>
        @if ($school->full_address)
            <p>{{ $school->full_address }}</p>
        @endif
        @if ($school->phone || $school->email)
            <p>
                @if ($school->phone) Telp. {{ $school->phone }} @endif
                @if ($school->phone && $school->email) &middot; @endif
                @if ($school->email) {{ $school->email }} @endif
            </p>
        @endif
    </div>

    <h2>Laporan Hasil Belajar (Rapor)</h2>

    <table class="identity">
        <tr>
            <td class="label">Nama Siswa</td>
            <td>: {{ $student->name }}</td>
            <td class="label">Tahun Ajaran</td>
            <td>: {{ $academicYear->label }}</td>
        </tr>
        <tr>
            <td class="label">NIS</td>
            <td>: {{ $student->nis ?: '-' }}</td>
            <td class="label">Kelas</td>
            <td>: {{ $student->kelas?->name ?? '-' }}</td>
        </tr>
    </table>

    <table class="grades">
        <thead>
            <tr>
                <th class="center" style="width: 36px;">No</th>
                <th style="width: 80px;">Kode</th>
                <th>Mata Pelajaran</th>
                <th class="center" style="width: 90px;">Nilai Akhir</th>
                <th class="center" style="width: 60px;">Huruf</th>
                <th>Catatan Guru</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($finalGrades as $index => $grade)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $grade->mapel?->code ?? '-' }}</td>
                    <td>{{ $grade->mapel?->name ?? '-' }}</td>
                    <td class="center">{{ number_format((float) $grade->final_score, 2) }}</td>
                    <td class="center">{{ $grade->grade_letter }}</td>
                    <td>{{ $grade->teacher_notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Belum ada nilai akhir untuk tahun ajaran ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($finalGrades->isNotEmpty())
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align: right;">Rata-rata Nilai</th>
                    <th class="center">{{ number_format($average, 2) }}</th>
                    <th colspan="2"></th>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="summary">
        Rata-rata nilai akhir: <strong>{{ $average !== null ? number_format($average, 2) : '-' }}</strong>
        dari {{ $finalGrades->count() }} mata pelajaran.
    </div>

    <table class="signature">
        <tr>
            <td>
                Orang Tua / Wali
                <div class="space"></div>
                <strong>.......................................</strong>
            </td>
            <td>
                {{ $school->city ?: 'Wali Kelas' }}, {{ now()->translatedFormat('d F Y') }}
                <div class="space"></div>
                <strong>{{ $school->headmaster_name ?: '.......................................' }}</strong>
            </td>
        </tr>
    </table>

    <p class="muted">Dicetak melalui MYSCHOOL LMS pada {{ now()->translatedFormat('d F Y H:i') }}.</p>
</body>

</html>
