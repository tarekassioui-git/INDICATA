##### Table of Contents  
[Headers](#headers)  
[Emphasis](#emphasis)  
...snip...    
<a name="headers"/>
## Headers


# INDICATA
# Progetto volto all'integrazione dei servizi offerti da INDICATA su GRAVITY FORM
#
# File del progetto
#   1. configuration.php Gestisce il workflow del progetto, ovvero l'ordine in cui vengono chiamate le funzioni. Contiene tutte le variabili globali (define)
#   2. call_api.php Gestisce tutte le comunicazioni con le API
#   3. db_manager.php Gestisce tutte le interazioni col database
#   4. fill_form.php Gestisce le interazioni con Gravity Form
#   5. read_data.php Legge le risposte delle API   
#
#
# Workflow
#   1. indicata_workflow()
#   2. check_db() controlla se nel database sia già presente il veicolo
#   3. call_registration_number_api() chiama l'API che fornisce i dati tecnici del veicolo a partire dalla targa
#   4. parse_registration() legge la risposta dell'API
#   5. fill_registrationData() riempe i field di Gravity Form con i dati letti
#   6. store_to_db() salva le informazioni su database
#   7. populate_posts() riempe i menù a tendina di gF
#   8. populate_fields() riempe i campi  di GF
#
# 
# Autore Tarek Assioui
#

