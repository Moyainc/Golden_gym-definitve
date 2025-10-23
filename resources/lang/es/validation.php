<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de validación personalizados
    |--------------------------------------------------------------------------
    */

    'password' => [
        'min' => 'La contraseña debe tener al menos :min caracteres.',
        'letters' => 'La contraseña debe contener al menos una letra.',
        'mixed' => 'La contraseña debe incluir mayúsculas y minúsculas.',
        'numbers' => 'La contraseña debe contener al menos un número.',
        'symbols' => 'La contraseña debe incluir al menos un carácter especial.',
        'uncompromised' => 'La contraseña ingresada aparece en una filtración de datos. Por favor elige otra diferente.',
    ],

    'confirmed' => 'Las contraseñas no coinciden.',
    'required' => 'El campo :attribute es obligatorio.',
    'unique' => 'El valor del campo :attribute ya está registrado.',
];
