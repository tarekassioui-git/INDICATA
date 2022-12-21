<?php  


    /**
     * Popola i campi del Form Nuovo MAV
     * 
     * La funzione gestisce le chiamate dei filtri per il popolamento dei campi su gravity forms 
     * passando i dati a populate posts, in modo da riempire più di un campo per volta
     * 
     * @access private
     * 
     * @author Tarek Assioui
     * 
     * @param parsed_registration array caratteristiche macchina
     * 
     * @return populate_posts
     */
    function fill_registrationData($form, $data)
    {
        GFCommon::log_debug( __METHOD__ . '(): starting to fill...');
        $location_form_id ='53';

        populate_posts($form, $data);

        populate_fields($form, $data);

        add_filter( 'gform_pre_validation_'.$location_form_id, function($form) use ( $data ) {
            return populate_posts( $form, $data ); 
        },10,3);
        add_filter( 'gform_pre_submission_filter_'.$location_form_id, function($form) use ($data ) {
            return populate_posts( $form, $data ); 
        },10,3);
        add_filter( 'gform_admin_pre_render_'.$location_form_id, function($form) use ( $data ) {
            return populate_posts( $form, $data ); 
        },10,3);

        add_filter( 'gform_pre_render_'.$location_form_id, function($form) use ( $data ) {
            return populate_posts( $form, $data ); 
        },10,3);
    }

    /**
     * Popola le scelte dropdown delle versioni auto
     * 
     * @param form oggetto form gravity form
     * @param data array contenete tutte le informazioni sull'auto
     * 
     * @return form oggetto form gravity form con modifica versioni
     * 
     */
    function populate_posts( $form, $data ) {
        
        GFCommon::log_debug( __METHOD__ . '(): versions');
        //the select feild id you want the versions to load
        $field_ID = '12';

        //Go through each form fields
        foreach ( $form['fields'] as $field ) {
            //check if field type is a select dropdown and id is 2
            if ( $field->type == 'select' && $field->id == $field_ID) {
                //add name and value to the option
                foreach($data['versione'] as $single_name){
                    $choices[] = array('text' => $single_name['versione'], 'value' => $single_name['codice'] );
                }
                //Add a place holder
                $field->placeholder = "Seleziona l'allestimento";
                //Add the new names to the form choices
                $field->choices = $choices;
            }
        }
        return $form; //return form
    }



    /**
     * Popola i field del modulo 
     * 
     * @access private
     * @author Tarek Assioui
     * 
     * @param value array associativo field => value
     * @param field il campo da popolare (inutilizzato)
     * @param name il nome del campo da popolare
     * @param data array contenente i dati con cui pooplare
     * 
     * 
     * @return values array contenente i valori e i field da popolare
     * 
     */
    function populate_fields( $form, $data ) {
        $values = array(
            'targa' => $data['targa'],
            'marca'   => $data['marca'],
            'modello'   => $data['modello'],
            'carburante' => $data['carburante'],
            'potenza' => $data['potenza'],
            'cambio' => $data['cambio'],
            'immatricolazione' => $data['immatricolazione'],
            'trazione' => $data['trazione'],
            'telaio' => $data['telaio'],
            'tipo-veicolo' => $data['type']
        );


        GFCommon::log_debug( __METHOD__ . '(): post array: ' . print_r($_POST, true));
        

        foreach ($form['fields'] as $field)
        {
            switch($field->id)
            {
                case 1 : $field->defaultValue = $data['targa']; break;
                case 24 : $field->defaultValue = $data['marca']; break;
                case 3 : $field->defaultValue = $data['modello'];break;
                case 6 : $field->defaultValue = $data['carburante']; break;
                case 9 : $field->defaultValue = $data['potenza']; break;
                case 7 : $field->defaultValue = $data['cambio']; break;
                case 5 : $field->defaultValue = $data['immatricolazione']; break;
                case 8 : $field->defaultValue = $data['trazione']; break;
                case 10 : $field->defaultValue = $data['telaio']; break;
                case 212 : $field->defaultValue = $data['types']; break;
            }
        }
        
    }



    /**
     * 
     * Gestisce il riempimento dei campi nella seconda pagina del form
     * 
     * @author Tarek Assioui
     * 
     * @param data i dati con cui riempire
     * 
     * @return value
     */
    function fill_valuation($data)
    {

        add_filter('gform_field_value',function( $value, $field, $name ) use ( $data ) {
            return populate_valuation( $value, $field, $name, $data);}, 10, 3);
    }


    /**
     * 
     * Riempie i campi della seconda pagina del form
     * 
     * @author Tarek Assioui
     * 
     * @param value array di dati da riempire 
     * 
     * @param data array di dati con cui riempire
     * 
     * @return value array per riempire i fields su gravity forms
     *     
     */
    function populate_valuation($value, $field, $name, $data)
    {
        foreach ($data as $value)
        {
            if(!isset($value))
                $value = "Error";
        }

        $values = array(
            'valore-commerciale' => $data['retail_100'],
            'percentuale-valore-consigliato' => $data['mds']['overall'],
        );

        return isset( $values[ $name ] ) ? $values[ $name ] : $value;

    }
?>