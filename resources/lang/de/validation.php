<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Das Attribut :attribute akzeptiert werden.',
    'accepted_if' => 'Das :attribute muss akzeptiert werden, wenn :other :value ist.',
    'active_url' => 'Das Attribut :attribute ist keine gültige URL.',
    'after'                => ':attribute muss ein Datum nach dem :date sein.',
    'after_or_equal'       => ':attribute muss ein Datum nach dem oder am :date sein.',
    'alpha'                => ':attribute darf nur Buchstaben enthalten.',
    'alpha_dash'           => ':attribute darf nur Buchstaben, Zahlen und Bindestriche enthalten.',
    'alpha_num'            => ':attribute darf nur Buchstaben und Zahlen enthalten.',
    'array'                => ':attribute muss eine Liste sein.',
    'before'               => ':attribute muss ein Datum vor dem :date sein.',
    'before_or_equal'      => ':attribute muss ein Datum vor dem oder am :date sein.',
    'between'              => [
        'numeric' => ':attribute muss zwischen :min und :max sein.',
        'file'    => ':attribute muss zwischen :min und :max Kilobytes sein.',
        'string'  => ':attribute muss zwischen :min und :max Zeichen sein.',
        'array'   => ':attribute muss zwischen :min und :max Einträge haben.',
    ],
    'boolean'              => ':attribute muss wahr oder falsch sein.',
    'confirmed'            => 'Die :attribute-Bestätigung stimmt nicht überein.',
    'date'                 => ':attribute ist kein gültiges Datum.',
    'date_format'          => ':attribute entspricht nicht dem Format: :format.',
    'different'            => ':attribute und :other müssen verschieden sein.',
    'digits'               => ':attribute muss :digits Ziffern lang sein.',
    'digits_between'       => ':attribute muss zwischen :min und :max Ziffern lang sein.',
    'dimensions'           => ':attribute hat inkorrekte Bild-Dimensionen.',
    'distinct'             => ':attribute hat einen doppelten Wert.',
    'email'                => ':attribute muss eine korrekte E-Mail-Adresse sein.',
    'exists'               => 'Ausgewählte(s) :attribute ist inkorrekt.',
    'file'                 => ':attribute muss eine Datei sein.',
    'filled'               => ':attribute muss ausgefüllt werden.',
    'image'                => ':attribute muss ein Bild sein.',
    'in'                   => 'Ausgewählte(s) :attribute ist inkorrekt.',
    'in_array'             => ':attribute existiert nicht in :other.',
    'integer'              => ':attribute muss eine Ganzzahl sein.',
    'ip'                   => ':attribute muss eine korrekte IP-Adresse sein.',
    'ipv4'                 => ':attribute muss eine korrekte IPv4-Adresse sein.',
    'ipv6'                 => ':attribute muss eine korrekte IPv6-Adresse sein.',
    'json'                 => ':attribute muss ein korrekter JSON-String sein.',
    'max'                  => [
        'numeric' => ':attribute darf nicht größer als :max sein.',
        'file'    => ':attribute darf nicht größer als :max Kilobytes sein.',
        'string'  => ':attribute darf nicht länger als :max Zeichen sein.',
        'array'   => ':attribute darf nicht mehr als :max Einträge enthalten.',
    ],
    'mimes'                => ':attribute muss eine Datei in folgendem Format sein: :values.',
    'mimetypes'            => ':attribute muss eine Datei in folgendem Format sein: :values.',
    'min'                  => [
        'numeric' => ':attribute muss mindestens :min sein.',
        'file'    => ':attribute muss mindestens :min Kilobytes groß sein.',
        'string'  => ':attribute muss mindestens :min Zeichen lang sein.',
        'array'   => ':attribute muss mindestens :min Einträge haben..',
    ],
    'not_in'               => 'Ausgewählte(s) :attribute ist inkorrekt.',
    'numeric'              => ':attribute muss eine Zahl sein.',
    'present'              => ':attribute muss vorhanden sein.',
    'regex'                => 'Das :attribute-Format ist inkorrekt.',
    'required'             => ':attribute field wird benötigt.',
    'required_if'          => ':attribute field wird benötigt wenn :other einen Wert von :value hat.',
    'required_unless'      => ':attribute field wird benötigt außer :other ist in den Werten :values enthalten.',
    'required_with'        => ':attribute field wird benötigt wenn :values vorhanden ist.',
    'required_with_all'    => ':attribute field wird benötigt wenn :values vorhanden ist.',
    'required_without'     => ':attribute field wird benötigt wenn :values nicht vorhanden ist.',
    'required_without_all' => ':attribute field wird benötigt wenn keine der Werte :values vorhanden ist.',
    'same'                 => ':attribute und :other müssen gleich sein.',
    'size'                 => [
        'numeric' => ':attribute muss :size groß sein.',
        'file'    => ':attribute muss :size Kilobytes groß sein.',
        'string'  => ':attribute muss :size Zeichen lang sein.',
        'array'   => ':attribute muss :size Einträge enthalten.',
    ],
    'string'               => ':attribute muss Text sein.',
    'timezone'             => ':attribute muss eine korrekte Zeitzone sein.',
    'unique'               => ':attribute wurde bereits verwendet.',
    'uploaded'             => 'Der Upload von :attribute schlug fehl.',
    'url'                  => 'Das :attribute-Format ist inkorrekt.',
    'uuid' => 'Das Attribut :attribute muss eine gültige UUID sein.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'userNotFound' => 'Wir können keinen Benutzer mit dieser E-Mail-Adresse finden.',
    'projectNotFound' => 'Wir können das Projekt nicht finden.',
    'musntBeYou' => 'Der Benutzer darf nicht Sie selbst sein.',
    'musntBeYourProject' => 'Das Projekt darf nicht von Ihnen sein.',
    'alreadyExists' => 'Bereits vorhanden.',
    'roleInUse' => 'Es gibt immer noch Benutzer mit dieser Rolle.',
    'userCannotAcceptFriendship' => 'Dieser Benutzer kann keine Freundschaftsanfragen annehmen.',
    'projectNotAuthorized' => 'Dieses Projekt ist noch nicht genehmigt worden.',
    'reachedPreferenceLimit' => 'Du hast bereits die maximal Anzahl an Projektwünschen ausgeschöpft und kannst keine weiteren hinzufügen',
    'reachedFriendsLimit' => 'Du hast bereits die maximal Anzahl an Freunden bzw. Freundschaftsanfragen ausgeschöpft und kannst keine weiteren hinzufügen',
    'userCannotBePromoted' => 'Dieser Benutzer kann nicht zum Assistenten befördert werden.',
    'userCannotBeDemoted' => 'Dieser Benutzer kann nicht vom Assistenten Rank degradiert werden.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
