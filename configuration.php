<?php

    require '/var/www/vhosts/vroomauto.it/stggestionale.vroomauto.it/vendor/autoload.php';

    include 'call_api.php';
    include 'fill_form.php';
    include 'db_manager.php';
    include 'read_data.php';


    /* Credentials for INDICATA connection */
    define('USERNAME_INDICATA', 'vroom_ws@dev.null');
    define('PASSWORD_INDICATA', 'N9qAckhq');

    /* Credentials for DATABASE connection */
    define('NAME_DB', 'localhost:3306');
    define('USERNAME_DB', 'ibisr_vroomauto');
    define('PASSWORD_DB', '8Sn6Ks!7@%195');

    /* APIs to be called */ 
    define('REGISTRATION_NUMBER_URL_START', 'https://ws.indicata.com/vivi/v2/IT/reg/');
    define('REGISTRATION_NUMBER_URL_END', '?assumption=FULL&vrs-raw-link=true');

    define('VALUTATION_API', 'https://ws.indicata.com/vivi/v2/IT/variant:trim:seats/valuation/RETAIL_100,SUPPLY_DEMAND,MAX_PURCHASE_PRICE_100,PDF,COMPETITIVE_SET?regdate=2021-02&odometer=km');


    function run_script_on_last_page($form) {
        if (!is_admin()) {
            $current_page = rgpost('gform_source_page_number_' . $form['id']) ? rgpost('gform_source_page_number_' . $form['id']) : 1;
            if($current_page == 1 || $current_page == 2)
            {
                indicata_workflow($current_page);
            }
        }
    }
    add_action('gform_enqueue_scripts_53', 'run_script_on_last_page');
    


    /**
     * Workflow delle chiamate API e tutto ciò che ne consegue
     * 
     * Chiamata in successione ed in ordine delle funzioni che permettono l'implementazione di indicata su gestionale
     * 
     * @author Tarek Assioui
     * 
     * @param void
     * 
     * @return void 
     * */ 
    function indicata_workflow($current_page)
    {   
        $plate = $_GET['targa'];

        if($current_page == 1)
        {
            /* Controllo della presenza di una targa nell'url */
            if(!isset($plate))
                return;

            /* Ottenimento dello slug dell'url */
            $slug = $_SERVER['REQUEST_URI'];
            $slug = explode('?', $slug);
            
            /* Controllo se siamo sulla pagina giusta */
            if($slug[0] != '/nuova-acquisizione/')
                return;
                    
                $checked = check_db($plate);

                if(!$checked[0])
                    /* Chiamata API per ottenere i dati tecnici */
                    call_registration_number_api($plate);
                else
                    fill_registrationData($checked[1]);
        }

        if($current_page == 2)
            call_valuation_api($plate);

    }   
    

?>