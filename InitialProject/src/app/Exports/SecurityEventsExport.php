<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Barryvdh\DomPDF\Facade\Pdf;

class SecurityEventsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $events;
    protected $format;

    public function __construct($events, $format = 'csv')
    {
        $this->events = $events;
        $this->format = $format;
    }

    public function collection()
    {
        return $this->events;
    }

    public function headings(): array
    {
        return [
            'Time',
            'Event Type',
            'User',
            'IP Address',
            'Details',
            'Threat Level'
        ];
    }

    public function map($event): array
    {
        return [
            $event->created_at->format('Y-m-d H:i:s'),
            ucwords(str_replace('_', ' ', $event->event_type)),
            $event->user_id ? ($event->user->fname_en . ' ' . $event->user->lname_en) : 'N/A',
            $event->ip_address,
            $event->details,
            ucfirst($event->threat_level)
        ];
    }

    public function download()
    {
        if ($this->format === 'pdf') {
            return $this->downloadPDF();
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            $this,
            'security-events-' . now()->format('Y-m-d-His') . '.' . $this->format
        );
    }

    protected function downloadPDF()
    {
        $pdf = PDF::loadView('admin.security.export-pdf', [
            'events' => $this->events
        ]);

        return $pdf->download('security-events-' . now()->format('Y-m-d-His') . '.pdf');
    }
} 