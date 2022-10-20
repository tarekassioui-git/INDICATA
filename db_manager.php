<?php 

    /**
     * Controllo della targa su database
     * 
     * Questa funzione controlla se nel database la targa inserita è già presente
     * nel database, se lo è questo eviterà di chiamare l'api a vuoto.
     * Restituisce un array[2] dove l'indice 0 indica l'esito del controllo e 
     * l'indice 1 contiene gli eventuali dati del database
     * 
     * @access private
     * 
     * @author Tarek Assioui
     * 
     * @param plate string targa del veicolo
     * 
     * @return array[2] dove l'indice 0 indica l'esito del controllo e 
     * l'indice 1 contiene gli eventuali dati del database
     */
    function check_db($plate)
    {

        // Query per ottenere targa
        $sql = '
        SELECT * FROM gestionale.wp_cars WHERE targa="' . $plate . '"';
        $sqlVersioni =  'SELECT versione, codice FROM gestionale.wp_versions WHERE targa="' . $plate . '"';



        // Connessione al database
        $conn =  mysqli_connect(NAME_DB, USERNAME_DB, PASSWORD_DB);

        // Controllo se la connessione è andata a buon fine
        if (!$conn) {
         return;
        }
        
        // Eseguo la query e salvo i dati su $result
        $result = mysqli_query($conn, $sql) or die( mysqli_error($conn));
        $responseVersioni = mysqli_query($conn, $sqlVersioni) or die( mysqli_error($conn));

        // Controllo se la query ha restituito almeno una riga
        if (mysqli_num_rows($result) == 0 || mysqli_num_rows($responseVersioni) == 0) 
        {
            // La query non ha prodotto risultati, la targa non è presente

            // Chiudo la connessione
            mysqli_close($conn);

            $result = array(false, '');
            return;
        }
        else 
        {
            // La query ha trovato la targa, restituisco i dati della targa

            // Ottengo l'array contenente i dati utilizzando mysqli_fetch_assoc
            $result = mysqli_fetch_assoc( $result);

            // Chiudo la connessione
            mysqli_close($conn);

            $result = array(true, $result);

        }
        
        $arrayVersioni = array();

        while($row = mysqli_fetch_array($responseVersioni)){

            array_push($arrayVersioni, array('versione' => $row[0], 'codice' => $row[1]));

        }

        $result[1]['versione'] = $arrayVersioni;
        
        return $result;
	
    }

    /**
     * Salva dati su db
     * 
     * La funzione salva nel database la macchina in modotale che
     * in futuro non serva chiamare nuovamente l'api per la stessa macchina
     * 
     * @access private
     * 
     * @author Tarek Assioui
     * 
     * @param data array caratteristiche macchina
     * 
     * @return void
     */
    function store_to_db($data)
    {	

        $before = microtime(true);



        // Apro la connessione al database
        $conn = new mysqli(NAME_DB, USERNAME_DB, PASSWORD_DB);

        
        // Controllo se la connessione ha avuto successo
        if ($conn->connect_error) 
        {
            //debug_to_console("DB connection failed = > " . $conn);
            return;
        }
        
        

        // Popolo la query con i dati della macchina
        $sql = 'INSERT INTO `gestionale`.wp_cars (targa, marca, modello, carburante, potenza, cambio, immatricolazione, trazione, telaio, valuation_url)
        VALUES ("' . $data['targa'] . '", 
            "' . $data['marca'] . '",
            "' . $data['modello'] . '",
            "' . $data['carburante'] . '",
            "' . $data['potenza'] . '",
            "' . $data['cambio'] . '",
            "' . $data['immatricolazione'] . '",
            "' . $data['trazione'] . '",
            "' . $data['telaio'] . '",
            "' . $data['valuation_url'] . '")';
        




        // Eseguo la query e ne controllo l'esito
        if ($conn->query($sql) === TRUE) 
        {
            ////debug_to_console("Inserted into DB = > " . $data['targa']);
        } 
        else 
        {
        //debug_to_console("store_to_db - Error: " . $sql . "<br>" . $conn->error);
        }


        for($i = 0; $i < sizeof($data['versione']); $i++)
        {

            $sql = 'INSERT INTO `gestionale`.wp_versions (targa, versione, codice)
            VALUES ("' . $data['targa'] . '", 
                "' . $data['versione'][$i]['versione'] . '",
                "' . $data['versione'][$i]['codice'] . '")';

                GFCommon::log_debug( __METHOD__ . '(): inserting : ' . $sql );

            // Eseguo la query e ne controllo l'esito
            if ($conn->query($sql) === TRUE) 
            {
                //echo "Inserted into DB = > " . $data['versioni'];
            } 
            else 
            {
                //echo "store_to_db - Error: " . $sql . "<br>" . $conn->error;
            }
        }
        // Chiudo la connessione
        $conn->close();
        $after = microtime(true);
        //debug_to_console("store_to_db = > " . ($after-$before));

        return;
    }

?>