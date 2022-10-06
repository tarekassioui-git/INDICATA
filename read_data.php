<?php 

    function parse_registration($content)
    {
        $parsed_registration = Array();

        $parsed_registration['targa'] = $content->identifier;
        $parsed_registration['telaio'] = $content->providerData->providerGroups[0]->entries[0]->value;
        $parsed_registration['marca'] = $content->make->name;
        $parsed_registration['modello'] = $content->model->name;
        $parsed_registration['versione'] = explode(',', $content->providerData->providerGroups[0]->entries[1]->value);
        
        for ($i=0 ; $i < sizeof($parsed_registration['versione']); $i++)
        {
            $temp = explode(' - ', $parsed_registration['versione'][$i]);
            var_dump($temp);
            $parsed_registration['versione'] = $temp[1];
            $parsed_registration['versione']['codice'] = $temp[0];
        }

        $parsed_registration['cambio'] = $content->transmission->description[0]->value;
        $parsed_registration['immatricolazione'] = $content->regDate->name;
        $parsed_registration['carburante'] = $content->engine->description[0]->value; 
        $parsed_registration['potenza'] = $content->engine->description[4]->value . 'KW - ' . $content->engine->description[2]->value . 'Cv';
        $parsed_registration['trazione'] = $content->wheelDrive->description[0]->value;

        
        var_dump($parsed_registration);

        /* Popolamento form */ 
        fill_registrationData($parsed_registration);
        
        foreach($parsed_registration as $value)
        {
            if(!isset($value))
                return;
        }
        /* Salvataggio su database */
        store_to_db($parsed_registration);
    }

?>