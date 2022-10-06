<?php 


    /**
     * Lettura del risultato dell'api 
     * 
     * Legge l'oggetto restituito dalla chiamata all'api riguardante i dati tecnici del veicolo
     * 
     * @author Tarek Assioui
     * 
     * @param content oggetto restituito come risposta dall'api di INDICATA
     * 
     * @return void
     */
    function parse_registration($content)
    {

        $pattern = '/\s*/m';
        $replace = '';

        $parsed_registration = Array();

        /* Lettura targa */
        $parsed_registration['targa'] = $content->identifier;

        /* Lettura telaio */
        $parsed_registration['telaio'] = $content->providerData->providerGroups[0]->entries[0]->value;

        /* Lettura marca */ 
        $parsed_registration['marca'] = $content->make->name;

        /* Lettura modello */
        $parsed_registration['modello'] = $content->model->name;

        /* Lettura versione divisione di stringa versioni e separazione della versione dal suo codice*/ 
        $parsed_registration['versione'] = explode(',', $content->providerData->providerGroups[0]->entries[1]->value);
        for ($i=0 ; $i < sizeof($parsed_registration['versione']); $i++)
        {
            /* Separazione versione dal codice */ 
            $temp = explode('-', $parsed_registration['versione'][$i]);

            /*Rimozione spazi vuoti*/ 
            $temp[0] = preg_replace( $pattern, $replace, $temp[0]);
            $parsed_registration['versione'][$i] = array('versione' => $temp[1], 'codice' => $temp[0]);
        }

        /*Lettura cambio*/ 
        $parsed_registration['cambio'] = $content->transmission->description[0]->value;
        if(!isset($parsed_registration['cambio']))
            $parsed_registration['cambio']= $content->providerData->providerGroups[2]->entries[6]->value;


        /* Lettura immatricolazione */
        $parsed_registration['immatricolazione'] = $content->regDate->name;

        /* Lettura carburante */
        $parsed_registration['carburante'] = $content->engine->description[0]->value; 
        if(!isset($parsed_registration['carburante']))
            $parsed_registration['carburante']= $content->providerData->providerGroups[2]->entries[9]->value;

        /* Lettura potenza */ 
        $parsed_registration['potenza'] = $content->engine->description[4]->value . 'KW - ' . $content->engine->description[2]->value . 'Cv';
        if($parsed_registration['potenza'] ==  'KW - Cv')
            $parsed_registration['potenza'] =$content->providerData->providerGroups[2]->entries[3]->value . 'KW - ' . $content->providerData->providerGroups[2]->entries[2]->value . 'Cv';
        
        /* Lettura trazione */
        $parsed_registration['trazione'] = $content->wheelDrive->description[0]->value;
        if(!isset($parsed_registration['trazione']))
            $parsed_registration['trazione']= $content->providerData->providerGroups[2]->entries[5]->value;


        str_replace('Trazione ', '', $parsed_registration['trazione']);

        /* Popolamento form */ 
        fill_registrationData($parsed_registration);
        
        /* Se almeno uno dei dati non è stato letto correttamente la vettura non viene salvata sul database*/
        foreach($parsed_registration as $value)
        {
            if(!isset($value))
                return;
        }
        /* Salvataggio su database */
        store_to_db($parsed_registration);
    }

?>