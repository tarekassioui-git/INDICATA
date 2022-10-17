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


    add_action( 'gform_post_paging_53', 'call_valuation_api', 10, 3 );
    function alert_user(  ) {

    }

    function call_valuation_api($form, $source_page_number, $current_page_number)
    {        
        if ( $current_page_number != 2 ) {
            return;
        }

        $plate = $_GET['targa'];
        echo "rhuam";
        /* Prendo i dati dal database */ 
        $data = check_db($plate);

        /* L'operatore ternario non va, non si sa perché */ 
        if($data[0])
        {
            $data = $data[1];
            echo "prova";
        }
        else
            echo "C'è stato un errore nell'ottenimento dei dati";
        
        /* Costruzione url */
        	
        
        
    }



?>
