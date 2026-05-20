# Handwerker erstellen - Felder

## Gesamt

Sichtbare Felder: 25 fuer Admin, 24 fuer Provider

Pflichtfelder: 14

Optionale Felder: 11

Versteckte Felder: 2

## Pflichtfelder

| Feld | Typ | Optionen / Hinweis |
| --- | --- | --- |
| Vorname | Text | `first_name` |
| Nachname | Text | `last_name` |
| Benutzername | Text | `username` |
| E-Mail | E-Mail | `email` |
| Umsatzsteuer-ID / VAT Number | Text | `vat_number` |
| Sprachen | Mehrfach-Dropdown | `languages[]`; Optionen aus Sprachliste |
| Passwort | Passwort | `password`; nur beim Erstellen |
| Handwerker-Provision | Zahl | `handyman_commission`; 1 bis 99, Dezimalwerte erlaubt |
| Anbieter-Adresse | Dropdown | `service_address_id`; Adressen des Providers |
| Land | Dropdown | `country_id`; Laenderliste |
| Bundesland / Staat | Dropdown | `state_id`; abhaengig vom Land |
| Stadt | Dropdown | `city_id`; abhaengig vom Bundesland/Staat |
| Telefonnummer | Text | `contact_number` |
| Status | Dropdown | `status`; Aktiv / Inaktiv |

## Optionale Felder

| Feld | Typ | Optionen / Hinweis |
| --- | --- | --- |
| Firmenname | Text | `company_name` |
| Faehigkeiten | Text | `skills` |
| Ausbildung | Text | `education` |
| Zertifizierung | Text | `certification` |
| Verfuegbarkeit | Dropdown | `availability`; Vollzeit / Teilzeit |
| Mobilitaet | Text | `mobility` |
| Provider | Dropdown | `provider_id`; nur fuer Admin sichtbar |
| Profilbild | Datei | `profile_image`; Bilddatei |
| Erfahrung | Textbereich | `experience` |
| Ueber mich | Textbereich | `about_me` |
| Adresse | Textbereich | `address` |

## Versteckte Felder

| Feld | Wert |
| --- | --- |
| `id` | Leer bei neuer Erstellung, User-ID beim Bearbeiten |
| `user_type` | `handyman` |

## Dropdown-Optionen

### Sprachen

Feld: `languages[]`

Mehrfachauswahl: Ja

Beispiele:

| Wert | Anzeige |
| --- | --- |
| `english` | English |
| `german` | German |
| `french` | French |
| `spanish` | Spanish |
| `italian` | Italian |
| `portuguese` | Portuguese |
| `turkish` | Turkish |
| `urdu` | Urdu |
| `arabic` | Arabic |
| `hindi` | Hindi |

### Verfuegbarkeit

Feld: `availability`

| Wert | Anzeige |
| --- | --- |
| `full_time` | Full-time |
| `part_time` | Part-time |

### Status

Feld: `status`

| Wert | Anzeige |
| --- | --- |
| `1` | Aktiv |
| `0` | Inaktiv |

### Provider

Feld: `provider_id`

Nur fuer Admin sichtbar.

Optionen: Aktive Provider.

### Anbieter-Adresse

Feld: `service_address_id`

Optionen: Aktive Adressen des ausgewaehlten Providers.

### Land

Feld: `country_id`

Optionen: Laenderliste.

### Bundesland / Staat

Feld: `state_id`

Optionen: Bundeslaender/Staaten des ausgewaehlten Landes.

### Stadt

Feld: `city_id`

Optionen: Staedte des ausgewaehlten Bundeslands/Staats.
