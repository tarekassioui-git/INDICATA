<?php
    use Guzzle\Http\Exception\ClientErrorResponseException;
    use GuzzleHttp\Exception\ServerException;
    use GuzzleHttp\Exception\ClientException;
    use GuzzleHttp\Exception\BadResponseException;
    use GuzzleHttp\RequestOptions;
    use GuzzleHttp\Client;
    
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

        // GET with basic auth and headers
        $headers = [
            'Accept'        => 'application/json; charset=UTF-8',
            'Authorization' => 'Basic ' . TOKEN_INDICATA,
            'Accept-Language'  => 'it-IT',
            'Accept-Encoding' => 'gzip'
        ];

        GFCommon::log_debug( __METHOD__ . '(): calling: ' . $url);
        $response = call_api('GET', $client, $url, ['headers' => $headers]);
        $content= json_decode($response->getBody());

        /* Lettura e gestione della risposta */ 
        parse_registration($content);

        return;
    }

    	
    // Filtro per chiamare l'api valutazione sulla seconda pagina del form
    add_filter( 'gform_pre_render_53', 'call_valuation_api' );


    /**
    *   Chiama l'api di valutazione da indicata
    *
    *   Controllando di essere sulla seconda pagina del form, questa funzione chiama l'api di valutazione di indicata
    *   Ottenendo anche i chilometri inseriti dall'utente per avere una valutazione più accurata
    *
    *   @author Tarek Assioui
    *
    *   @param form il form su cui viene chiamato il Filtro
    *
    *   @return form
    *
    * */
    function call_valuation_api($form)
    {  

        GFCommon::log_debug( __METHOD__ . '(): init');

        /* Ottengo la pagina corrente */
        $current_page = GFFormDisplay::get_current_page('53');    

        /* Controllo di essere nella pagina giusta */
        if ( $current_page != 2) {
            GFCommon::log_debug( __METHOD__ . '(): current page: ' . GFFormDisplay::get_current_page( $form['id'] ) );
            return $form;
        }

        /* Ottengo la targa */
        $plate = $_GET['targa'];
        GFCommon::log_debug( __METHOD__ . '(): plate obtained ' . $plate );

        /* Prendo i dati dal database */ 
        $data = check_db($plate);


        /* L'operatore ternario non va, non si sa perché */
        /* Controllo se siano presenti i dati sul database */
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

        /* Rimuovo la parte finale {/odometer ecc.} dall'url */
        $url = preg_replace("/\{[^)]+\}/", "", $data['valuation_url']);

        /* Ottengo i chilometri */
        $km = $_POST['input_13'];  

        /* Aggiungo i chilometri all'url */
        $url = $url . '&odometer=' . $km; 
        GFCommon::log_debug( __METHOD__ . '(): url: ' . $url);

        

        // Headers della richiesta
        $headers = [
            'Accept'        => 'application/json; charset=UTF-8',
            'Authorization' => 'Basic ' . TOKEN_INDICATA,
            'Accept-Language'  => 'it-IT',
            'Accept-Encoding' => 'gzip'
        ];

        /* Chiamata all'api */
        GFCommon::log_debug( __METHOD__ . '(): calling: ' . $url);
        $response = call_api('GET', $client,$url ,['headers' => $headers]);
        $content= json_decode($response->getBody());


        GFCommon::log_debug( __METHOD__ . '(): response recieved and decoded');

        /* Chiamo la funzione che leggerà i dati ottenuti */
        parse_valuation($content);

        return $form;
    }



    /**
    *   Funzione per ottenere il link di download del PDF
    *
    *   @author Tarek Assioui
    *
    *   @param url url su cui effettuare la chiamata api
    *
    *   @return void
    *
    * */
    function getPdf($url)
    {

        GFCommon::log_debug( __METHOD__ . '(): init');

        /* Creo il client */
        $client = new \GuzzleHttp\Client();

        /* Assegno gli headers */
        $headers = [
            'Accept'        => 'application/json; charset=UTF-8',
            'Authorization' => 'Basic ' . TOKEN_INDICATA,
            'Accept-Language'  => 'it-IT',
            'Accept-Encoding' => 'gzip'
        ];

        /* Chiamo l'api */
        GFCommon::log_debug( __METHOD__ . '(): calling: ' . $url);
        $response = call_api('GET', $client, $url, ['headers' => $headers]);
        $content= json_decode($response->getBody());

        GFCommon::log_debug( __METHOD__ . '(): pdf link parsed succesfully');

        GFCommon::log_debug( __METHOD__ . '(): PDF ' . print_r($response->getStatusCode()));

        $url = $content->links[0]->href;

        GFCommon::log_debug( __METHOD__ . '(): download link: ' . $url);

        /* Funzione per scaricare il PDF */
        downloadPDF($url);
    }


    /**
    *   Download del pdf di valutazione tramite link
    *
    *   @author Tarek Assioui
    *
    *   @param url url da cui scaricare il PDF
    *
    *   @return void
    *
    * */
    function downloadPDF($url)
    {
        try{
            /* path di salvataggio del PDF */
            $path = __DIR__ . '/pdf/' . basename($url) . '.pdf';

            /* Apro il file */
            $file_path = fopen($path,'w');

            

            GFCommon::log_debug( __METHOD__ . '() fopen executed ');

            /* Creo il client */
            $client = new \GuzzleHttp\Client();

            /* Assegno gli headers */
            $headers = [
                'Connection' => 'keep-alive',
                'Accept'        => 'application/pdf; charset=UTF-8',
                'Authorization' => 'Basic ' . TOKEN_INDICATA,
                'Accept-Language'  => 'it-IT',
                'Accept-Encoding' => 'gzip'
            ];

            GFCommon::log_debug( __METHOD__ . '() trying to download file ');

            /* Tolgo gli spazi all'url */
            $url = trim($url);

            /* Effettuo la chiamata  */
            $response = $client->request('GET', $url, ['headers' => $headers]); 

            GFCommon::log_debug( __METHOD__ . '() pdf : ' . $response);

            /* Scrivo il contenuto della risposta sul file */
            fwrite($file_path, $response);

            /* Chiudo il file */
            fclose($file_path);
            GFCommon::log_debug( __METHOD__ . '() pdf downloaded name: ' . basename($url));
        }
        catch (Exception $e)
        {
            GFCommon::log_debug( __METHOD__ . '() Exception' . $e);          
        }
    }

    /**
    *
    *   Effettua le chiamate api tramite guzzle in modo generico
    *
    *   I vari tipi di chiamata vengono specificati dalla funzione chiamante
    *
    *   @author Tarek Assioui
    *
    *   @param type tipo id richiesta GET, POST,
    *   @param client il client per la richiesta
    *   @param url url da chiamare
    *   @param headers gli headers da chiamare
    *
    *   @return response il risultato della chiamata
    *
    * */
    function call_api($type, $client, $url, $headers)
    {  
        try{
            /* Effettuo la chiamata */
            $response = $client->request($type, $url, $headers);
            GFCommon::log_debug( __METHOD__ . '(): api called succesfully');
        }
        /* Catturo le varie possibili eccezioni */
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
        catch (Exception $e)
        {
            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            GFCommon::log_debug( __METHOD__ . '(): API error: ' . $responseBodyAsString);
        }



        return $response;
    }



?>
