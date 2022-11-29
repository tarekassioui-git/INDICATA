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




            switch($field->id)
            {
                case '1' : $field->text = $data['targa']; break;
                case '24' : $field->text = $data['marca']; break;
                case '3' : $field->text =$data['modello']; break;
                case '22' : $field->text = $data['retail_100']; break;
                case '6' : $field->text =$data['carburante']; break;
                case '9' : $field->text =$data['potenza']; break;
                case '7' : $field->text =$data['cambio']; break;
                case '5' : $field->text =$data['immatricolazione']; break;
                case '8' : $field->text =$data['trazione']; break;
                case '10' : $field->text = $data['telaio']; break;
                case '212' : $field->text = $data['type']; break;
                
                default : GFCommon::log_debug( __METHOD__ . '(): not fillable: ' . $field->id); 

            }




        }

        return $form; //return form
     
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