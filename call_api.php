<?php
    use Guzzle\Http\Exception\ClientErrorResponseException;
    /**
     * 
     * Chiamata api tramite targa
     * 
     * Chiamata all'API di indicata che ritorna molte informazioni sul veicolo a partire dalla targa
     * Tutta la chiamata HTTP viene gestita tramite Guzzle e nel rispetto delle regole HATEOAS
     * 
     * @param plate targa da esaminare
     * 
     * @chiamate parse_registration($content), store_to_db($content)
     * 
     * @return void
     */
    function call_registration_number_api($plate)
    {
        
        /* Creazione client Guzzle */
        $client = new GuzzleHttp\Client();

        $url = REGISTRATION_NUMBER_URL_START . $plate . REGISTRATION_NUMBER_URL_END;
        $credentials = base64_encode(USERNAME_INDICATA . ':' . PASSWORD_INDICATA);

        // GET with basic auth and headers
        $headers = [
            'Accept'        => 'application/json; charset=UTF-8',
            'Authorization' => 'Basic ' . $credentials,
            'Accept-Language'  => 'it-IT',
            'Accept-Encoding' => 'gzip'
        ];

        $response       = $client->request('GET', $url, [
            'headers'   => $headers
        ]);   

        $content= json_decode($response->getBody()); 

        /* Lettura e gestione della risposta */ 
        parse_registration($content);

        return;
    }


    
    add_filter( 'gform_pre_render_53', 'call_valuation_api' );

    function call_valuation_api($form)
    {  
        $current_page = GFFormDisplay::get_current_page( $form['id'] );      
        if ( $current_page != 2) {
            echo 'Incorrect page';
            return;
        }

        $plate = $_GET['targa'];
        echo "plate obtained";
        /* Prendo i dati dal database */ 
        $data = check_db($plate);

        /* L'operatore ternario non va, non si sa perché */ 
        if($data[0])
        {
            $data = $data[1];
            echo "Data obtained";
        }
        else
            echo "There's been an error obtaining the data";
        
        /* Costruzione url */
        	
        
        return $form;
    }



?>
