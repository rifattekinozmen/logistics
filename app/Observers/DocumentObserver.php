<?php

namespace App\Observers;

use App\Core\Services\CalendarService;
use App\Models\Document;

class DocumentObserver
{
    public function __construct(
        protected CalendarService $calendarService
    ) {}

    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        if ($document->valid_until && $document->valid_until->isFuture()) {
            $data = [
                'title' => $document->name.' - Süre Sonu',
                'description' => 'Belge süre sonu: '.$document->valid_until->format('d.m.Y'),
                'event_type' => 'document',
                'related_type' => Document::class,
                'related_id' => $document->id,
                'start_date' => $document->valid_until,
                'is_all_day' => true,
                'priority' => $this->calculatePriority($document->valid_until),
                'color' => $this->getColorByDate($document->valid_until),
                'status' => 'pending',
            ];
            $data['company_id'] = $this->resolveCompanyIdForDocument($document);
            $data['created_by'] = auth()->id() ?? $document->uploaded_by ?? \App\Models\User::query()->value('id');
            $this->calendarService->createEvent($data);
        }
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        if ($document->isDirty('valid_until') && $document->valid_until) {
            $existingEvent = $document->calendarEvents()->first();

            if ($existingEvent) {
                $this->calendarService->updateEvent($existingEvent, [
                    'start_date' => $document->valid_until,
                    'priority' => $this->calculatePriority($document->valid_until),
                ]);
            } elseif ($document->valid_until->isFuture()) {
                $data = [
                    'title' => $document->name.' - Süre Sonu',
                    'description' => 'Belge süre sonu: '.$document->valid_until->format('d.m.Y'),
                    'event_type' => 'document',
                    'related_type' => Document::class,
                    'related_id' => $document->id,
                    'start_date' => $document->valid_until,
                    'is_all_day' => true,
                    'priority' => $this->calculatePriority($document->valid_until),
                    'color' => $this->getColorByDate($document->valid_until),
                    'status' => 'pending',
                ];
                $data['company_id'] = $this->resolveCompanyIdForDocument($document);
                $data['created_by'] = auth()->id() ?? $document->uploaded_by ?? \App\Models\User::query()->value('id');
                $this->calendarService->createEvent($data);
            }
        }
    }

    protected function calculatePriority($date): string
    {
        $daysUntil = now()->diffInDays($date, false);
        if ($daysUntil < 0) {
            return 'high';
        }
        if ($daysUntil <= 7) {
            return 'high';
        }
        if ($daysUntil <= 30) {
            return 'medium';
        }

        return 'low';
    }

    protected function resolveCompanyIdForDocument(Document $document): ?int
    {
        $document->loadMissing('documentable');
        $related = $document->documentable;
        if ($related === null) {
            return null;
        }
        if (isset($related->company_id)) {
            return (int) $related->company_id;
        }
        if (method_exists($related, 'branch') && $related->branch) {
            return $related->branch->company_id ?? null;
        }

        return null;
    }

    protected function getColorByDate($date): string
    {
        $daysUntil = now()->diffInDays($date, false);
        if ($daysUntil < 0) {
            return '#DC2626';
        }
        if ($daysUntil <= 7) {
            return '#F59E0B';
        }
        if ($daysUntil <= 30) {
            return '#FBBF24';
        }

        return '#10B981';
    }
}
