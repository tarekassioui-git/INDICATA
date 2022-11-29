<?php

// Da cambiare in produzione
    require '/var/www/vhosts/vroomauto.it/stggestionale.vroomauto.it/vendor/autoload.php';

    include 'call_api.php';
    include 'fill_form.php';
    include 'db_manager.php';
    include 'read_data.php';


    /* Credentials for INDICATA connection */
    define('USERNAME_INDICATA', 'vroom_ws@dev.null');
    define('PASSWORD_INDICATA', 'N9qAckhq');
    
    define('TOKEN_INDICATA', base64_encode(USERNAME_INDICATA . ':' . PASSWORD_INDICATA));

    /* Credentials for DATABASE connection */
    define('NAME_DB', 'localhost:3306');
    define('USERNAME_DB', 'ibisr_vroomauto');
    define('PASSWORD_DB', '8Sn6Ks!7@%195');

    /* APIs to be called */ 
    define('REGISTRATION_NUMBER_URL_START', 'https://ws.indicata.com/vivi/v2/IT/reg/');
    define('REGISTRATION_NUMBER_URL_END', '?assumption=FULL&vrs-raw-link=true');

    define('VALUTATION_API', 'https://ws.indicata.com/vivi/v2/IT/variant:trim:seats/valuation/RETAIL_100,SUPPLY_DEMAND,MAX_PURCHASE_PRICE_100,PDF,COMPETITIVE_SET?regdate=2021-02&odometer=km');


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
    function indicata_workflow($form)
    {   


        $time_start = microtime(true);

        $plate = $_GET['targa'];




        /* Controllo della presenza di una targa nell'url */
        if(!isset($plate))
            return $form;

        /* Ottenimento dello slug dell'url */
        $slug = $_SERVER['REQUEST_URI'];
        $slug = explode('?', $slug);
        
        /* Controllo se siamo sulla pagina giusta */
        if($slug[0] != '/nuova-acquisizione/')
            return $form;

        /* Ottengo la pagina corrente */
        $current_page = GFFormDisplay::get_current_page('53');    

        GFCommon::log_debug( __METHOD__ . '(): current page: ' . $current_page);

        /* Controllo di essere nella pagina giusta */
        if ( $current_page != 1) {
            GFCommon::log_debug( __METHOD__ . '(): current page: ' . GFFormDisplay::get_current_page( $form['id'] ) );

            if($current_page == 2)
                call_valuation_api($form);
            
            return $form;
        }
                

        GFCommon::log_debug( __METHOD__ . '(): calling check_db');
        $checked = check_db($plate);

        if(!$checked[0])
            /* Chiamata API per ottenere i dati tecnici */
            call_registration_number_api($form, $plate);
        else
            fill_registrationData($form, $checked[1]);
    

        $time_end = microtime(true);

        GFCommon::log_debug( __METHOD__ . ' execution time : ' . ($time_end - $time_start)/60);

        return $form;

    }   

    add_action( 'gform_pre_render_53','indicata_workflow' );
    

?>

