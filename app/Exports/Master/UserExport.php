<?php
namespace App\Exports\Master;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::with('wilayah')->withTrashed()->orderBy('name');

        if (!empty($this->filters['search'])) {
            $s = $this->filters['search'];
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('username', 'like', "%{$s}%");
            });
        }
        if (!empty($this->filters['role'])) {
            $query->where('role', $this->filters['role']);
        }
        if (!empty($this->filters['wilayah_id'])) {
            $query->where('wilayah_id', $this->filters['wilayah_id']);
        }
        if (!empty($this->filters['status'])) {
            if ($this->filters['status'] === 'aktif') {
                $query->whereNull('deleted_at');
            } elseif ($this->filters['status'] === 'nonaktif') {
                $query->whereNotNull('deleted_at');
            }
        }

        return $query->get()->values()->map(fn($u, $i) => [
            $i + 1,
            $u->name,
            $u->username ?? '-',
            $u->email ?? '-',
            $u->no_hp ?? '-',
            ucfirst(str_replace('_', ' ', $u->role)),
            $u->wilayah->nama ?? '-',
            $u->trashed() ? 'Nonaktif' : 'Aktif',
            $u->created_at?->format('Y-m-d H:i'),
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'Username', 'Email', 'No HP', 'Role', 'Wilayah', 'Status', 'Dibuat'];
    }

    public function title(): string
    {
        return 'Master User';
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E3F2FD']]]];
    }
}
