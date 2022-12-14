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
     * @callbacks parse_registration($content), store_to_db($content)
     * 
     * @return void
     */
    function call_registration_number_api($form, $plate)
    {
        
        $time_start = microtime(true);

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
        if(!isset($response))
        {
            GFCommon::log_debug( __METHOD__ . '(): error: ' . $response);
            return $form;
        }

        
        $content= json_decode($response->getBody());

        /* Lettura e gestione della risposta */ 
        parse_registration($form, $content);

        $time_end = microtime(true);

        GFCommon::log_debug( __METHOD__ . ' execution time : ' . ($time_end - $time_start)/60 );

        return $form;
    }

    	


    /**
    *   Chiama l'api di valutazione da indicata
    *
    *   Controllando di essere sulla seconda pagina del form, questa funzione chiama l'api di valutazione di indicata
    *   Ottenendo anche i chilometri inseriti dall'utente per avere una valutazione pi?? accurata
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
        $time_start = microtime(true); 
        

        /* Ottengo la pagina corrente */
        $current_page = GFFormDisplay::get_current_page('53');    

        /* Controllo di essere nella pagina giusta */
        if ( $current_page != 2) {
            GFCommon::log_debug( __METHOD__ . '(): current page: ' . GFFormDisplay::get_current_page( $form['id'] ) );
            return $form;
        }
        GFCommon::log_debug( __METHOD__ . '(): init');

        /* Ottengo la targa */
        $plate = $_GET['targa'];
        GFCommon::log_debug( __METHOD__ . '(): plate obtained ' . $plate );

        /* Prendo i dati dal database */ 
        $data = check_db($plate);


        /* L'operatore ternario non va, non si sa perch?? */
        /* Controllo se siano presenti i dati sul database */
        if($data[0])
        {
            $data = $data[1];
            GFCommon::log_debug( __METHOD__ . '(): data found: ' . $data );
        }
        else
        {
            GFCommon::log_debug( __METHOD__ . '(): data not found: ');
            return $form;
        }

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

        /* Chiamo la funzione che legger?? i dati ottenuti */
        $data = parse_valuation($content);

        //Add a placeholder to field id 8, is not used with multi-select or radio, will overwrite placeholder set in form editor.
        //Replace 8 with your actual field id.

        foreach( $form['fields'] as $field ) {
            if ( $field->id == 22 ) {
                //$field->text = $data['retail_100'];
                $_POST['input_22'] = $data['retail_100'];
            }
            if ( $field->id == 176 ) {
               // $field->text = 98;
                $_POST['input_176'] = 98;
            }
            if ( $field->id == 206 ) {
                //$field->text = $data['mds']['overall'];

                $_POST['input_206'] =  $data['mds']['overall'];
            }   

            if($field->id == 214) {
                $_POST['input_214'] = $data['max_purchase_price'];
            }
        }

        $time_end = microtime(true);
        
        GFCommon::log_debug( __METHOD__ . ' execution time : ' . ($time_end - $time_start)/60 );
        
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

        if(!isset($response))
        {
            GFCommon::log_debug( __METHOD__ . '(): error: ' . $response);
            return;
        }

        $content= json_decode($response->getBody());

        GFCommon::log_debug( __METHOD__ . '(): pdf link parsed succesfully');

        GFCommon::log_debug( __METHOD__ . '(): PDF ' . print_r($response->getStatusCode(), TRUE));
        
        /* Ottengo il link di download */ 
        $url = $content->links[0]->href;

        GFCommon::log_debug( __METHOD__ . '(): download link: ' . $url);

        
        /**
         *  
         * Perch?? questo controllo?
         * Questo controllo viene effettuato perch?? su alcuni veicoli quando viene effettuata la prima chiamata all'api 
         * viene comunque restituito un link per ottenere il link di download e non il link di download direttamente.
         * 
         * Sappiamo che i link per ottenere i link di download finiscono per status, dunque controllando questa cosa 
         * possiamo semplicemente chiamare ricorsivamente la funzione finch?? non otteniamo il link di download
         * 
         *  */ 
        $test = explode('/',$url);
        if(array_pop($test) == 'status')
        {   
            str_replace('/status', '', $url);
            getPdf($url);
        }
        else
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
            
            $marca = str_replace('/', '-', $_POST['input_24']);
            $modello = str_replace('/', '-',$_POST['input_3']);
            $targa = str_replace('/', '-',$_POST['input_1']);
            //$date = str_replace('/', '-',date('d-m-Y h'));
            
            /**
             *
             * Fonte Alex: https://stackoverflow.com/questions/20288789/php-date-with-timezone 
             *
            */
            $tz = 'Europe/Rome';
            $timestamp = time();
            $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
            $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
            $dt =  $dt->format('d.m.Y, H'); 
            
            if(!isset($marca) || !isset($modello) || !isset($targa) || !isset($dt))
            {
                GFCommon::log_debug( __METHOD__ . '() error creating file');
                return;
            }

            /* path di salvataggio del PDF */
            $filename = $dt . ' ' . $targa .  ' - ' . $marca  . ' ' . $modello;
            $path = __DIR__ . '/pdf/' . $filename . '.pdf';

            GFCommon::log_debug( __METHOD__ . '() Predicted file path : ' . $path );

            /* Apro il file */
            $file_path = fopen($path,'w');

            

            GFCommon::log_debug( __METHOD__ . '() fopen executed ');

            /* Creo il client */
            $client = new \GuzzleHttp\Client();

            /* Assegno gli headers */
            $headers = [
                'Accept'        => 'application/pdf; charset=UTF-8',
                'Authorization' => 'Basic ' . TOKEN_INDICATA,
                'Accept-Language'  => 'it-IT',
                'Accept-Encoding' => 'gzip'
            ];

            GFCommon::log_debug( __METHOD__ . '() trying to download file ');

            /* Tolgo gli spazi all'url */
            //$url = trim($url);

            /* Effettuo la chiamata  */
            $client->request('GET', $url, ['headers' => $headers, 'sink' => $file_path]); 
            
            /* Chiudo il file */
            fclose($file_path);
            GFCommon::log_debug( __METHOD__ . '() pdf downloaded name: ' . basename($path));

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
            return $response;
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
    }



?>
