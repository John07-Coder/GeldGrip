<?php

return [
    'required' => 'Vul :attribute in.', 'email' => 'Vul een geldig e-mailadres in.',
    'unique' => 'Dit :attribute is al in gebruik.', 'confirmed' => 'De bevestiging van :attribute komt niet overeen.',
    'string' => ':Attribute moet tekst zijn.', 'numeric' => 'Vul een geldig bedrag in.',
    'boolean' => 'Kies actief of inactief.', 'integer' => ':Attribute moet een geheel getal zijn.',
    'date_format' => ':Attribute heeft niet het juiste datumformaat.',
    'after_or_equal' => 'De einddatum mag niet vóór de startdatum liggen.',
    'enum' => 'Kies een geldige waarde voor :attribute.', 'regex' => 'Gebruik maximaal twee decimalen, zonder duizendtalscheiding.',
    'current_password' => 'Je huidige wachtwoord is niet correct.',
    'gt' => ['numeric' => ':Attribute moet groter zijn dan :value.'],
    'between' => ['numeric' => ':Attribute moet tussen :min en :max liggen.'],
    'min' => ['string' => ':Attribute moet minimaal :min tekens bevatten.'],
    'max' => ['string' => ':Attribute mag maximaal :max tekens bevatten.', 'numeric' => ':Attribute mag maximaal :max zijn.'],
    'attributes' => ['name' => 'naam', 'email' => 'e-mailadres', 'password' => 'wachtwoord',
        'amount' => 'bedrag', 'frequency' => 'frequentie', 'start_date' => 'startdatum', 'end_date' => 'einddatum',
        'category' => 'categorie', 'payment_day' => 'betaaldag', 'current_password' => 'huidig wachtwoord',
        'month' => 'maand', 'search' => 'zoekopdracht', 'is_active' => 'status'],
];
