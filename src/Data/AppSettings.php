<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppInventar\Data;

use Hwkdo\IntranetAppBase\Data\Attributes\Description;
use Hwkdo\IntranetAppBase\Data\BaseAppSettings;

class AppSettings extends BaseAppSettings
{
    public function __construct(
        #[Description('Kommagetrennte E-Mail-Adressen für Inventar-Meldungen')]
        public string $mailEmpfaenger = 'asset@hwk-do.de',

        #[Description('Spatie-Rollennamen für zusätzliche Mail-Empfänger (z. B. Inventar-Empfaenger)')]
        public array $mailEmpfaengerRollen = ['Inventar-Empfaenger'],

        #[Description('Testmodus: Meldungs-E-Mails nur an den Melder senden (nicht an Asset-Team oder Vorgesetzte). Standard: aus.')]
        public bool $testing = false,

        #[Description('Anlagenabgang: in Seventhings eindeutig auffindbare Objekte (Itexia-ID/Barcode) automatisch archivieren. Standard: aus.')]
        public bool $seventhingsArchivierenBeiAnlagenabgang = true,

        #[Description('Inventurhinweis in Seventhings an bestehenden Text anhängen statt überschreiben (Standortwechsel und Anlagenabgang). Standard: an.')]
        public bool $inventurhinweisAnhaengen = true,

        #[Description('Feld-Labels für E-Mails und Formulare')]
        public array $fieldLabels = self::DEFAULT_FIELD_LABELS,
    ) {}

    public const DEFAULT_FIELD_LABELS = [
        'datum' => 'Der Schaden / Verlust / Anlagenabgang wurde festgestellt am',
        'schaetzwert' => 'Einschätzung des aktuellen Wiederbeschaffungswertes / Neu-Anschaffungswertes in EURO',
        'oeffentliche_mittel' => 'Wurde die Anschaffung des betroffenen Gegenstands durch öffentliche Mittel gefördert?',
        'totalschade_oder_reparatur' => 'Besteht ein Totalschaden/Verlust oder ein Reparaturschaden?',
        'grund1' => 'Grund des Schadens / Verlustes / Anlagenabgangs',
        'gruende' => [
            'grund_ist_bedienerfehler_mitarbeiter_hwk' => 'Bedienerfehler durch Mitarbeiter der HWK',
            'grund_ist_bedienerfehler_dritte' => 'Bedienerfehler durch Dritte / Teilnehmer',
            'grund_ist_unachtsamkeit_mitarbeiter_hwk' => 'Unachtsamkeit durch Mitarbeiter der HWK',
            'grund_ist_unachtsamkeit_dritte' => 'Äußere Einflüsse (Stromausfall, Wasserschaden, Sturm, ...)',
            'grund_ist_diebstahl' => 'Diebstahl',
            'grund_ist_einbruchdiebstahl' => 'Einbruchdiebstahl',
            'grund_ist_abnutzung' => 'Abnutzung',
            'grund_ist_verkauf' => 'Verkauf/Verschrottung',
            'grund_ist_anderer_grund' => 'Anderer Grund',
        ],
        'grund2_schaden_in_kursnr' => 'Kursnr.',
        'grund2_schaden_kurs_ausbilder' => 'Ausbilder (Name)',
        'grund2_alter_des_gegenstands' => '(Geschätztes) Alter des Gegenstands in Jahren',
        'grund2_verkauf_datum' => 'Verkauf zum (Datum)',
        'grund2_verkauf_preis' => 'Verkaufspreis',
        'grund2_anderer_grund' => 'Bitte hier beschreiben:',
        'oeffentliche_mittel_bindungsfrist' => 'Ist die Bindungsfrist der Förderung bereits abgelaufen?',
        'art_abgang' => 'Art des Anlagenabgangs',
    ];
}
