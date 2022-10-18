<?php
    use Guzzle\Http\Exception\ClientErrorResponseException;
    use GuzzleHttp\Exception\ServerException;
    use GuzzleHttp\Exception\ClientException;
    use GuzzleHttp\Exception\BadResponseException;
    
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

        GFCommon::log_debug( __METHOD__ . '(): init');

        $current_page = GFFormDisplay::get_current_page('53');    

        if ( $current_page != 2) {
            GFCommon::log_debug( __METHOD__ . '(): current page: ' . GFFormDisplay::get_current_page( $form['id'] ) );
            return $form;
        }

        $plate = $_GET['targa'];
        GFCommon::log_debug( __METHOD__ . '(): plate obtained ' . $plate );
        /* Prendo i dati dal database */ 
        $data = check_db($plate);


        /* L'operatore ternario non va, non si sa perché */ 
        if($data[0])
        {
            $data = $data[1];
            GFCommon::log_debug( __METHOD__ . '(): data found: ' . $data );
        }
        else
            GFCommon::log_debug( __METHOD__ . '(): data not found: ');
        
        /*Inizio chiamata*/ 
        
        /* Creazione client Guzzle */
        $client = new GuzzleHttp\Client();

        $url = preg_replace("/\{[^)]+\}/", "", $data['valuation_url']);
        //$url = trim($url);

        $km = $_POST['input_13'];  

        $url = $url . '&odometer=' . $km; 

        GFCommon::log_debug( __METHOD__ . '(): url: ' . $url);

        $credentials = base64_encode(USERNAME_INDICATA . ':' . PASSWORD_INDICATA);

        // GET with basic auth and headers
        $headers = [
            'Accept'        => 'application/json; charset=UTF-8',
            'Authorization' => 'Basic ' . $credentials,
            'Accept-Language'  => 'it-IT',
            'Accept-Encoding' => 'gzip'
        ];

        GFCommon::log_debug( __METHOD__ . '(): calling api: ');

        try{
            $response = $client->request('GET', $url, [
                'headers'   => $headers
            ]); 
            GFCommon::log_debug( __METHOD__ . '(): api called succesfully');
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }
        catch (GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }
        catch (GuzzleHttp\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }


        GFCommon::log_debug( __METHOD__ . '(): response recieved and decoded');

        $content= json_decode($response->getBody()); 

        parse_valuation($content);

        return $form;
    }



    function getPdf($url)
    {

        GFCommon::log_debug( __METHOD__ . '(): init');

        $client = new GuzzleHttp\Client();

        $credentials = base64_encode(USERNAME_INDICATA . ':' . PASSWORD_INDICATA);

        // GET with basic auth and headers
        $headers = [
            'Accept'        => 'application/json; charset=UTF-8',
            'Authorization' => 'Basic ' . $credentials,
            'Accept-Language'  => 'it-IT',
            'Accept-Encoding' => 'gzip'
        ];


        try{
            $response = $client->request('GET', $url, [
                'headers'   => $headers
            ]); 
            GFCommon::log_debug( __METHOD__ . '(): api called succesfully');
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }
        catch (GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }
        catch (GuzzleHttp\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }


        
        $content= json_decode($response->getBody()); 
        GFCommon::log_debug( __METHOD__ . '(): pdf link parsed succesfully');

        $url = $content->links->href;


        GFCommon::log_debug( __METHOD__ . '(): trying pdf download');
        try{
            $response = $client->request('GET', $url, [
                'headers'   => $headers
            ]); 
            GFCommon::log_debug( __METHOD__ . '(): pdf download called succesfully');
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }
        catch (GuzzleHttp\Exception\ServerException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }
        catch (GuzzleHttp\Exception\BadResponseException $e) {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }

        GFCommon::log_debug( __METHOD__ . '(): API error: ' . $response);

        $content= json_decode($response->getBody());

        GFCommon::log_debug( __METHOD__ . '(): pdf download decoded succesfully');

    }



?>
