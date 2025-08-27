<?php

// File: config/livewire.php

use Livewire\Livewire;

return [

    /*
    |---------------------------------------------------------------------------
    | Class Namespace
    |---------------------------------------------------------------------------
    |
    | This value sets the root class namespace for all of your Livewire
    | components. It is important that this value is set correctly
    | so that Livewire can discover your components properly.
    |
    */

    'class_namespace' => 'App\\Livewire',

    /*
    |---------------------------------------------------------------------------
    | View Path
    |---------------------------------------------------------------------------
    |
    | This value sets the path to your Livewire component view files.
    | It is important that this value is set correctly so that
    | Livewire can find your component views properly.
    |
    */

    'view_path' => resource_path('views/livewire'),

    /*
    |---------------------------------------------------------------------------
    | Layout
    |---------------------------------------------------------------------------
    |
    | This value, if set, will be used as the default layout for all your
    | Livewire components. This can be useful for defining a master
    | layout file that all of your components can share.
    |
    */

    'layout' => 'components.layouts.app',
    
    /*
    |--------------------------------------------------------------------------
    | Temporary File Uploads
    |--------------------------------------------------------------------------
    |
    | Livewire handles file uploads by storing uploads in a temporary directory
    | before the file is stored permanently. All file uploads are directed
    | to a global endpoint for temporary storage. By default, Livewire
    | will use your default filesystem disk.
    |
    */

    // INI ADALAH BAGIAN PERBAIKANNYA
    'temporary_file_upload' => [
        'disk' => 'local', // Gunakan disk 'local' untuk file sementara
        'rules' => 'file|mimes:png,jpg,pdf|max:102400', // Aturan validasi global
        'directory' => 'livewire-tmp',
        'middleware' => null,
        'preview_mimes' => [
            'png', 'jpeg', 'jpg', 'gif', 'bmp', 'svg', 'webp',
        ],
    ],


    /*
    |---------------------------------------------------------------------------
    | Render On Redirect
    |---------------------------------------------------------------------------
    |
    | This value determines if Livewire will render a component when it is
    | redirected to. If this is set to false, Livewire will not render
    | the component until the next Livewire request is made.
    |
    */

    'render_on_redirect' => false,

    /*
    |---------------------------------------------------------------------------
    | Eloquent Model Binding
    |---------------------------------------------------------------------------
    |
    | This value determines if Livewire will automatically bind an Eloquent
    | model to a component. If this is set to false, you will need to
    | manually bind the model to the component.
    |
    */

    'legacy_model_binding' => false,

    /*
    |---------------------------------------------------------------------------
    | Lazy Loading
    |---------------------------------------------------------------------------
    |
    | This value determines if Livewire will lazy load components.
    | If this is set to true, Livewire will not load the
    | component until it is visible on the page.
    |
    */

    'lazy' => true,

];
