<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Mail;

use Hwkdo\IntranetAppInventar\Data\SeventhingsSyncSummaryLine;
use Hwkdo\IntranetAppInventar\Models\Meldung;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InventarMeldungMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<SeventhingsSyncSummaryLine>  $syncSummary
     */
    public function __construct(
        public Meldung $meldung,
        public array $syncSummary = [],
        public ?string $raumIstLabel = null,
        public ?string $raumSollLabel = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Intranet - Inventar - '.ucfirst($this->meldung->typ->value),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'intranet-app-inventar::mail.'.$this->meldung->typ->value,
            with: [
                'meldung' => $this->meldung,
                'syncSummary' => $this->syncSummary,
                'raumIstLabel' => $this->raumIstLabel,
                'raumSollLabel' => $this->raumSollLabel,
            ],
        );
    }
}
