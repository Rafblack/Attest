<?php
declare(strict_types=1);

namespace App\Controller;
use Cake\Filesystem\File;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Style\Paragraph;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * Attestations Controller
 *
 * @property \App\Model\Table\AttestationsTable $Attestations
 * @method \App\Model\Entity\Attestation[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class AttestationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {


        $query = $this->Attestations->find("active");

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $conditions = [
                'OR' => [
                    ['numero LIKE' => '%' . $search . '%'],
                    ['date LIKE' => '%' . $search . '%'],
                    ['currency LIKE' => '%' . $search . '%'],
                    ['Attestations.template LIKE' => '%' . $search . '%'],
                    ['intitule LIKE' => '%' . $search . '%'],

                  
                ]
            ];
 // Add manual conditions based on similar terms
 if (stripos($search, 'non') !== false) { // Check if 'leve' is part of the search term
    $conditions['OR'][] = ['type' => 2];
}
else if (stripos($search, 'ban') !== false) { // Check if 'encour' is part of the search term
    $conditions['OR'][] = ['type' => 1];
}
         

            $query->where($conditions);
        }



        $attestations = $this->paginate($query);
       $STAT =[];
       $this->loadModel("Types");
      $types = $this->Types->find("all");
      foreach($types as $type){
        $STAT[$type->id] = $type->label;    // we get an array that has all the id->label relationships to send to our view 


      }
        $this->set(compact('attestations','STAT'));
        
    }
    public function deleted()
    {
        $query = $this->Attestations->find("deleted");

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $conditions = [
                'OR' => [
                    ['numero LIKE' => '%' . $search . '%'],
                    ['date LIKE' => '%' . $search . '%'],
                    ['currency LIKE' => '%' . $search . '%'],
                    ['Attestations.template LIKE' => '%' . $search . '%'],
                    ['intitule LIKE' => '%' . $search . '%'],

                  
                ]
            ];
 // Add manual conditions based on similar terms
 if (stripos($search, 'non') !== false) { // Check if 'leve' is part of the search term
    $conditions['OR'][] = ['type' => 2];
}
else if (stripos($search, 'ban') !== false) { // Check if 'encour' is part of the search term
    $conditions['OR'][] = ['type' => 1];
}
         

            $query->where($conditions);
        }



        $attestations = $this->paginate($query);
       $STAT =[];
       $this->loadModel("Types");
      $types = $this->Types->find("all");
      foreach($types as $type){
        $STAT[$type->id] = $type->label;    // we get an array that has all the id->label relationships to send to our view 


      }
        $this->set(compact('attestations','STAT'));
    }

    /**
     * View method
     *
     * @param string|null $id Attestation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $attestation = $this->Attestations->get($id);
        if (!$attestation) {
            throw new NotFoundException(__('Attestation not found'));
        }

        $filename = $attestation->template;
        $filePath = WWW_ROOT . 'attestations' . DS . $filename;

        $file = new File($filePath);
        if (!$file->exists()) {
            throw new NotFoundException(__('File not found'));
        }

        $response = $this->response->withFile(
            $filePath,
            ['download' => true, 'name' => $filename]
        );
        return $response;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */


     public function add()
     {


         $attestation = $this->Attestations->newEmptyEntity();
         $this->set(compact('attestation'));
     
         if ($this->request->is('post')) {
             $attestationData = $this->request->getData();
     
             // Extracting the accounts data
             $accounts = $attestationData['accounts'];
             $soldes = [];
             $client_numeros = [];
             $currencies = [];
             $currency2 = [];
             $finalSoldes0= [];
             $finalSoldes = [];
     
             foreach ($accounts as $account) {
                $account["currency"] = trim($account["currency"]);   // get everything trimmed before proccessing 
                $account["number"] = trim($account["number"]);

                
                 $client_numeros[] =$account["currency"]. " " .$account['number'];
                 $currencies[] = $account['currency'];
     
                 // Aggregate balances by currency
                 if (!isset($soldes[$account['currency']])) {
                     $soldes[$account['currency']] = 0;
                 }
                 $soldes[$account['currency']] += $account['balance'];
             }
             
     
             // Prepare the new soldes and currency2 arrays
             $soldesAggregated = [];
             foreach ($soldes as $currency => $balance) {
                 $soldesAggregated[] = $balance;
                 $currency2[] = trim($currency);
                 $finalSoldes0[]= trim($currency ). " " . $balance;
             }
             $numberToWords = new \Numbers_Words();  // convert to words 
             $soldesInWords = array_map(function($solde) use ($numberToWords) {
                 return $numberToWords->toWords($solde, 'fr');
             }, $soldesAggregated);

             for( $i = 0 ; $i < count($finalSoldes0); $i ++){
                  
                $finalSoldes[] = $finalSoldes0[$i] . " (" . $soldesInWords[$i] . ")";


             }


     
             $attestation = $this->Attestations->patchEntity($attestation, $attestationData);
             $attestation->date = $attestationData['date'];

             $attestation->template ='attestation_' . $attestation->numero . '.docx';

             if ($this->Attestations->save($attestation)) {
                
                $this->Flash->success(__('Attestation '. $attestation->numero. ' saved successfully.'));
                 $description = "";    // these are the sentences we will dynamically fill out and pkace inside the template file 
                 $description1 = "";
                 $agence = $attestation->agence;
                 $add1 = "";
                 $add2 ="";
                 $add3 = "";
                 $contrevaleur0 ="";
                 $list = "";

                 if($attestation->contrevaleur != null && trim((string)$attestation->contrevaleur) != ''){
                    $contre =  $numberToWords->toWords($attestation->contrevaleur, 'fr');
                    $contrevaleur0 = ', soit la contrevaleur ';

                    $contrevaleur =  $attestation->currency. " ". trim((string)$attestation->contrevaleur). " (". $contre . ")";

              
                 }
                 else{
                    $contrevaleur = "";
                 }
                 if($agence == "CLIPRO"){
                   
                    $add1.= ' notre client, ';
                    $add2.= ' gérés par Monsieur/Madame '. trim($attestation->gerant). ',';

                 }
                 elseif($agence = "CLIPRI"){

                    $add3 .= " Monsieur/Madame ";
                 }

               

                 // Generate the Word Document
                 $templateFile = WWW_ROOT . 'templates/template'.$attestation->type.'.docx';

                 $outputFile = WWW_ROOT . 'attestations/' . 'attestation_' . $attestation->numero . '.docx';
     
                 try {
                     $templateProcessor = new TemplateProcessor($templateFile);
                     // Define global font settings
                    Settings::setCompatibility(false);
                    Settings::setDefaultFontName('Times New Roman');
                    Settings::setDefaultFontSize(14);
                     $phpWord = new PhpWord();
                     $paragraphStyle = [
                        'alignment' => 'left', // Set alignment to left
                        'textAlignment'=> 'center', 
                        'spaceAfter' => Converter::pointToTwip(0), // Convert points to twips for spacing
                        'spacing' => 0, // No extra spacing between lines
                        'lineHeight' => 1.0, // Normal line height
                    ];
                    $phpWord->addParagraphStyle('Style', array('align'=>'left', Converter::pointToTwip(0)));

                
                    $descriptionRun = new TextRun( $paragraphStyle);   //repair1
                    $description1Run = new TextRun(  $paragraphStyle);
 

    

                    
  if($attestation->type ==1){
    $fontStyle = [
        'name' => 'Times New Roman',
        'size' => 14
    ];
                 
                 if(count($client_numeros) ==1){    // there is only one numero 
                 $description.= "Attestons que". $add1 . $add3  . trim($attestation->intitule) . $add2 . " est ". $attestation->titularity. " d'un compte en ". trim($accounts[0]["currency"]).
                " ouvert dans nos livres sous le n° ". trim($accounts[0]['number']) . ".";

              
// Initialize a new TextRun
                    $descriptionRun->addText("Attestons que ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                    $descriptionRun->addText($add1, array_merge($fontStyle, ['bold' => false]));  //  text
                    $descriptionRun->addText($add3, array_merge($fontStyle, ['bold' => true]));  // Bold text

                    $descriptionRun->addText(trim($attestation->intitule), array_merge($fontStyle, ['bold' => true]));  // Bold text
                    $descriptionRun->addText($add2, array_merge($fontStyle, ['bold' => true]));  // Non-bold text
                    $descriptionRun->addText(" est ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                    $descriptionRun->addText($attestation->titularity, array_merge($fontStyle, ['bold' => false]));  //  text
                    $descriptionRun->addText(" d'un compte en ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                    $descriptionRun->addText(trim($accounts[0]["currency"]), array_merge($fontStyle, ['bold' => true]));  // Bold text
                    $descriptionRun->addText(" ouvert dans nos livres sous le n° ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                    $descriptionRun->addText(trim($accounts[0]['number']), array_merge($fontStyle, ['bold' => true]));  // Bold text
                    
                $description1 .= "A ce jour, ce compte présente un solde créditeur de ". $finalSoldes[0] . $contrevaleur0. $contrevaleur. "." ;


                    $description1Run->addText("A ce jour, ce compte présente un solde créditeur de ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                    $description1Run->addText($finalSoldes[0], array_merge($fontStyle, ['bold' => true]));  // Bold text
                    $description1Run->addText($contrevaleur0, array_merge($fontStyle, ['bold' => false]));  // Non-bold text

                    $description1Run->addText($contrevaleur, array_merge($fontStyle, ['bold' => true]));  // -bold text
                    $description1Run->addText(".", array_merge($fontStyle, ['bold' => false]));  // 
                 }
                 else {  // theres more than one numero 
                    $description.= "Attestons que". $add1 . $add3  .trim( $attestation->intitule) . $add2 . " est ". $attestation->titularity. " des comptes ouverts dans nos livres sous les numeros".
                       " " . implode(", ", $client_numeros);

                       $descriptionRun->addText("Attestons que ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText($add1, array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText($add3, array_merge($fontStyle, ['bold' => true]));  // Bold text
                $descriptionRun->addText(trim($attestation->intitule), array_merge($fontStyle, ['bold' => true]));  // Bold text
                $descriptionRun->addText($add2, array_merge($fontStyle, ['bold' => true]));  // Non-bold text
                $descriptionRun->addText(" est ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText($attestation->titularity, array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText(" des comptes ouverts dans nos livres sous les numeros ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText(implode(", ", $client_numeros), array_merge($fontStyle, ['bold' => true]));  // Bold text

                $descriptionRun->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                       

                    if(count($finalSoldes)  == 1){   // one solde
                    $description1 .= "A ce jour, ces comptes présentent un solde créditeur global ". trim($finalSoldes[0]) .$contrevaleur0. $contrevaleur. "." ;
                 
                    $description1Run->addText("A ce jour, ces comptes présentent un solde créditeur global ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                    $description1Run->addText(trim($finalSoldes[0]), array_merge($fontStyle, ['bold' => true]));  // Bold text
                    $description1Run->addText($contrevaleur0, array_merge($fontStyle, ['bold' => false]));  

                    $description1Run->addText($contrevaleur, array_merge($fontStyle, ['bold' => true]));  // Bold text
                    $description1Run->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                    }
                    else{   // multiple soldes
                            //$description1 = 'A ce jour, ces comptes présentent les soldes créditeurs suivants :' . '</w:t><w:br/><w:t>' . implode('</w:t><w:br/><w:t>', $finalSoldes);
                            $description1Run->addText("A ce jour, ces comptes présentent les soldes créditeurs suivants:", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                            // Create a list object
                            $description1Run->addTextBreak(1);
    
    
                            foreach ($finalSoldes as $solde) {
                                // Add each balance as a new paragraph with a bullet point
                                $description1Run->addTextBreak(1);

                                $description1Run->addText("• " . $solde, array_merge($fontStyle, ['bold' => true]));
    
    
                            }

                            // debug($description1Run);    // repair2
    
                            $description1Run->addText($contrevaleur0, array_merge($fontStyle, ['bold' => false]));  
    
    
                            $description1Run->addText($contrevaleur, array_merge($fontStyle, ['bold' => true]));  // Bold text
                            $description1Run->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text

                                            }


/**/



                 }
             
                }
                elseif($attestation->type == 2){   // non - engagement 
                    $fontStyle = [
                        'name' => 'Calibri',
                        'size' => 12
                    ];
                $responsable = trim($attestation->responsable);
                $filiere = trim($attestation->filiere);
                $role = $attestation->role;
                $add = "";
                $description = "";
                $description1 = "";
                if($role == "personne"){
                    $add.= "Monsieur/Madame";
                }
                else if($role == "societe"){
                    $add.="la société";
                }


                if(count($client_numeros) ==1){
                    $description .= "Attestons par la présente que " . $add. " ". trim($attestation->intitule) . " est " . $attestation->titularity . " d’un compte ouvert dans nos livres sous le numéro "
               . $client_numeros[0];
                $descriptionRun->addText("Attestons par la présente que ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText($add, array_merge($fontStyle, ['bold' => true]));  // -bold text
                $descriptionRun->addText(" " . trim($attestation->intitule), array_merge($fontStyle, ['bold' => false]));  //  
                $descriptionRun->addText(" est ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText($attestation->titularity, array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText(" d’un compte ouvert dans nos livres sous le numéro ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText($client_numeros[0], array_merge($fontStyle, ['bold' => true]));  // Bold text

                $descriptionRun->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text

                }
                else{

                 $description .="Attestons par la présente que " . $add. " ". trim($attestation->intitule) . " est " . $attestation->titularity . " des comptes ouverts dans nos livres sous les numéros ".
                 " " . implode(", ", $client_numeros);
                 $descriptionRun->addText("Attestons par la présente que ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                 $descriptionRun->addText($add, array_merge($fontStyle, ['bold' => true]));  // -bold text
                 $descriptionRun->addText(" " . trim($attestation->intitule), array_merge($fontStyle, ['bold' => true]));  // Bold text
                 $descriptionRun->addText(" est ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                 $descriptionRun->addText($attestation->titularity, array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                 $descriptionRun->addText(" des comptes ouverts dans nos livres sous les numéros ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                 $descriptionRun->addText(implode(", ", $client_numeros), array_merge($fontStyle, ['bold' => true]));  // Bold text
                 $descriptionRun->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                   
                    

                }
           
                $description1.= "A ce jour, " . $add . " " . trim($attestation->intitule). " est libre de tout engagement dans nos livres.";

                $description1Run->addText("A ce jour, ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $description1Run->addText($add, array_merge($fontStyle, ['bold' => true]));  // -bold text
                $description1Run->addText(" " . trim($attestation->intitule), array_merge($fontStyle, ['bold' => true]));  // Bold text
                $description1Run->addText(" est libre de tout engagement dans nos livres.", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                }
     
                     // Debug the data being set
                    //  debug($attestation->object);
                    //  debug($attestation->libelle);
                    //  debug(date('d/m/Y', strtotime($attestation->date)));
                    //  debug($currency2);
                    //  debug($currencies);
                    //  debug($soldes);
                    //  debug($soldesInWords);

     
                     // Replace placeholders with data from the attestation
                     if($attestation->type == 1){
                     $templateProcessor->setValue('object', trim($attestation->object));
                     $templateProcessor->setValue('agence', trim($attestation->agence));
                     $templateProcessor->setValue('numero',trim( $attestation->numero));
                    //  $templateProcessor->setValue('soldes',trim( $list));

                     $date = date('Y', strtotime($attestation->date));
                     $date = substr((string)$date,2,2); // only wantthe last two chars
                     $templateProcessor->setValue('year',  $date);


                     }
                     elseif($attestation->type== 2) {
                       
                        $templateProcessor->setValue('responsable', trim($attestation->responsable));
                        $templateProcessor->setValue('filiere', trim($attestation->filiere));



                     }

                    
                     $templateProcessor->setValue('date', date('d/m/Y', strtotime($attestation->date)));



                      // dynamic descriptions 
                    //   debug($descriptionRun->getElements()); // Output the elements of $descriptionRun

                    $templateProcessor->setComplexValue('description', $descriptionRun);
                    $templateProcessor->setComplexValue('description1', $description1Run);


     
                     // Save the processed template to a new file
                     $templateProcessor->saveAs($outputFile);
     
                     // Provide the generated file as a download response
                     $response = new Response();
                     $response = $response->withFile($outputFile, ['download' => true, 'name' => 'attestation_' . $attestation->numero . '.docx']);

                     
                     $response = $response->withType('docx'); // Optional: Set the content type explicitly
                     
                   
                     return $response; //chose download over redirect plus user can download multiple at once 
                 } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                     $this->Flash->error(__('Error generating attestation document: {0}', $e->getMessage()));
                     debug($e->getMessage()); // Debug the specific PHPWord exception message
                 }
             } else {
                 $this->Flash->error(__('Failed to save attestation.'));
                //  debug($attestation->getErrors()); // Debug validation errors
             }
         }
     
     }

     public function download($response){
      return $response;
     }
    /**
     * Edit method
     *
     * @param string|null $id Attestation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {

        $referer = $this->request->getSession()->read('referercopy');

        // var_dump($referer);

// If referer is not set (first access), store it
if (!$referer) {
    $referer = $this->request->referer();
    $this->request->getSession()->write('referercopy', $referer);
}
        $attestation = $this->Attestations->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $attestationData = $this->request->getData();
     
            // Extracting the accounts data
            $accounts = $attestationData['accounts'];
            $soldes = [];
            $client_numeros = [];
            $currencies = [];
            $currency2 = [];
            $finalSoldes0= [];
            $finalSoldes = [];
    
            foreach ($accounts as $account) {
               $account["currency"] = trim($account["currency"]);   // get everything trimmed before proccessing 
               $account["number"] = trim($account["number"]);

               
                $client_numeros[] =$account["currency"]. " " .$account['number'];
                $currencies[] = $account['currency'];
    
                // Aggregate balances by currency
                if (!isset($soldes[$account['currency']])) {
                    $soldes[$account['currency']] = 0;
                }
                $soldes[$account['currency']] += $account['balance'];
            }
            
    
            // Prepare the new soldes and currency2 arrays
            $soldesAggregated = [];
            foreach ($soldes as $currency => $balance) {
                $soldesAggregated[] = $balance;
                $currency2[] = trim($currency);
                $finalSoldes0[]= trim($currency ). " " . $balance;
            }
            $numberToWords = new \Numbers_Words();  // convert to words 
            $soldesInWords = array_map(function($solde) use ($numberToWords) {
                return $numberToWords->toWords($solde, 'fr');
            }, $soldesAggregated);

            for( $i = 0 ; $i < count($finalSoldes0); $i ++){
                 
               $finalSoldes[] = $finalSoldes0[$i] . " (" . $soldesInWords[$i] . ")";


            }


    
            $attestation = $this->Attestations->patchEntity($attestation, $attestationData);
            $attestation->date = $attestationData['date'];

            $attestation->template ='attestation_' . $attestation->numero . '.docx';

            if ($this->Attestations->save($attestation)) {
               
                $this->Flash->success(__('Attestation '. $attestation->numero. ' saved successfully.'));
                $description = "";    // these are the sentences we will dynamically fill out and pkace inside the template file 
                $description1 = "";
                $agence = $attestation->agence;
                $add1 = "";
                $add2 ="";
                $add3 = "";
                $contrevaleur0 ="";

                if($attestation->contrevaleur != null && trim((string)$attestation->contrevaleur) != ''){
                   $contre =  $numberToWords->toWords($attestation->contrevaleur, 'fr');
                   $contrevaleur0 = ', soit la contrevaleur ';

                   $contrevaleur =  $attestation->currency. " ". trim((string)$attestation->contrevaleur). " (". $contre . ")";

             
                }
                else{
                   $contrevaleur = "";
                }
                if($agence == "CLIPRO"){
                  
                   $add1.= ' notre client, ';
                   $add2.= ' gérés par Monsieur/Madame '. trim($attestation->gerant). ',';

                }
                elseif($agence = "CLIPRI"){

                   $add3 .= " Monsieur/Madame ";
                }

              

                // Generate the Word Document
                $templateFile = WWW_ROOT . 'templates/template'.$attestation->type.'.docx';

                $outputFile = WWW_ROOT . 'attestations/' . 'attestation_' . $attestation->numero . '.docx';
    
                try {
                    $templateProcessor = new TemplateProcessor($templateFile);
                    // Define global font settings
                   Settings::setCompatibility(false);
                   Settings::setDefaultFontName('Times New Roman');
                   Settings::setDefaultFontSize(14);
                    $phpWord = new PhpWord();
                    
                    $paragraphStyle = [
                       'alignment' => "left",
                       'spaceAfter' => Converter::pointToTwip(0), // Convert points to twips for spacing
                       'spacing' => 0, // No extra spacing between lines
                       'lineHeight' => 1.0, // Normal line height
                   ];

                   $phpWord->addParagraphStyle('Style', array('align'=>'left', Converter::pointToTwip(0)));

                
                   $descriptionRun = new TextRun("style");
                   $description1Run = new TextRun("style");

   

                   
 if($attestation->type ==1){
   $fontStyle = [
       'name' => 'Times New Roman',
       'size' => 14
   ];
                
                if(count($client_numeros) ==1){    // there is only one numero 
                $description.= "Attestons que". $add1 . $add3  . trim($attestation->intitule) . $add2 . " est ". $attestation->titularity. " d'un compte en ". trim($accounts[0]["currency"]).
               " ouvert dans nos livres sous le n° ". trim($accounts[0]['number']) . ".";

             
// Initialize a new TextRun
                   $descriptionRun->addText("Attestons que ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                   $descriptionRun->addText($add1, array_merge($fontStyle, ['bold' => false]));  //  text
                   $descriptionRun->addText($add3, array_merge($fontStyle, ['bold' => true]));  // Bold text

                   $descriptionRun->addText(trim($attestation->intitule), array_merge($fontStyle, ['bold' => true]));  // Bold text
                   $descriptionRun->addText($add2, array_merge($fontStyle, ['bold' => true]));  // Non-bold text
                   $descriptionRun->addText(" est ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                   $descriptionRun->addText($attestation->titularity, array_merge($fontStyle, ['bold' => false]));  //  text
                   $descriptionRun->addText(" d'un compte en ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                   $descriptionRun->addText(trim($accounts[0]["currency"]), array_merge($fontStyle, ['bold' => true]));  // Bold text
                   $descriptionRun->addText(" ouvert dans nos livres sous le n° ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                   $descriptionRun->addText(trim($accounts[0]['number']), array_merge($fontStyle, ['bold' => true]));  // Bold text
                   
               $description1 .= "A ce jour, ce compte présente un solde créditeur de ". $finalSoldes[0] . $contrevaleur0. $contrevaleur. "." ;


                   $description1Run->addText("A ce jour, ce compte présente un solde créditeur de ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                   $description1Run->addText($finalSoldes[0], array_merge($fontStyle, ['bold' => true]));  // Bold text
                   $description1Run->addText($contrevaleur0, array_merge($fontStyle, ['bold' => false]));  // Non-bold text

                   $description1Run->addText($contrevaleur, array_merge($fontStyle, ['bold' => true]));  // -bold text
                   $description1Run->addText(".", array_merge($fontStyle, ['bold' => false]));  // 
                }
                else {  // theres more than one numero 
                   $description.= "Attestons que". $add1 . $add3  .trim( $attestation->intitule) . $add2 . " est ". $attestation->titularity. " des comptes ouverts dans nos livres sous les numeros".
                      " " . implode(", ", $client_numeros);

                      $descriptionRun->addText("Attestons que ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText($add1, array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText($add3, array_merge($fontStyle, ['bold' => true]));  // Bold text
               $descriptionRun->addText(trim($attestation->intitule), array_merge($fontStyle, ['bold' => true]));  // Bold text
               $descriptionRun->addText($add2, array_merge($fontStyle, ['bold' => true]));  // Non-bold text
               $descriptionRun->addText(" est ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText($attestation->titularity, array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText(" des comptes ouverts dans nos livres sous les numeros ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText(implode(", ", $client_numeros), array_merge($fontStyle, ['bold' => true]));  // Bold text

               $descriptionRun->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                      

                   if(count($finalSoldes)  == 1){
                   $description1 .= "A ce jour, ces comptes présentent un solde créditeur global ". trim($finalSoldes[0]) .$contrevaleur0. $contrevaleur. "." ;
                
                   $description1Run->addText("A ce jour, ces comptes présentent un solde créditeur global ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                   $description1Run->addText(trim($finalSoldes[0]), array_merge($fontStyle, ['bold' => true]));  // Bold text
                   $description1Run->addText($contrevaleur0, array_merge($fontStyle, ['bold' => false]));  

                   $description1Run->addText($contrevaleur, array_merge($fontStyle, ['bold' => true]));  // Bold text
                   $description1Run->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                   }
                   else{
                       
                    
                        $description1 = 'A ce jour, ces comptes présentent les soldes créditeurs suivants :' . '</w:t><w:br/><w:t>' . implode('</w:t><w:br/><w:t>', $finalSoldes);
                        $description1Run->addText("A ce jour, ces comptes présentent les soldes créditeurs suivants:", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                        // Create a list object
                        $description1Run->addTextBreak(1); // Add a line break after each balance

                        $description1Run->setAlignment(Jc::LEFT);

                        foreach ($finalSoldes as $solde) {
                            // Add each balance as a new paragraph with a bullet point
                            $description1Run->addTextBreak(1); // Add a line break after each balance

                            $description1Run->addText("• " . $solde, array_merge($fontStyle, ['bold' => true]));
                            $description1Run->setAlignment(Jc::LEFT);


                        }

                        $description1Run->addText($contrevaleur0, array_merge($fontStyle, ['bold' => false]));  


                        $description1Run->addText($contrevaleur, array_merge($fontStyle, ['bold' => true]));  // Bold text
                        $description1Run->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                                           }



                }
            
               }
               elseif($attestation->type == 2){   // non - engagement 
                   $fontStyle = [
                       'name' => 'Calibri',
                       'size' => 12
                   ];
               $responsable = trim($attestation->responsable);
               $filiere = trim($attestation->filiere);
               $role = $attestation->role;
               $add = "";
               $description = "";
               $description1 = "";
               if($role == "personne"){
                   $add.= "Monsieur/Madame";
               }
               else if($role == "societe"){
                   $add.="la société";
               }


               if(count($client_numeros) ==1){
                   $description .= "Attestons par la présente que " . $add. " ". trim($attestation->intitule) . " est " . $attestation->titularity . " d’un compte ouvert dans nos livres sous le numéro "
              . $client_numeros[0];
// Add text using PHPWord's addText method
               $descriptionRun->addText("Attestons par la présente que ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText($add, array_merge($fontStyle, ['bold' => true]));  // -bold text
               $descriptionRun->addText(" " . trim($attestation->intitule), array_merge($fontStyle, ['bold' => false]));  //  
               $descriptionRun->addText(" est ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText($attestation->titularity, array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText(" d’un compte ouvert dans nos livres sous le numéro ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $descriptionRun->addText($client_numeros[0], array_merge($fontStyle, ['bold' => true]));  // Bold text

               $descriptionRun->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text

               }
               else{

                $description .="Attestons par la présente que " . $add. " ". trim($attestation->intitule) . " est " . $attestation->titularity . " des comptes ouverts dans nos livres sous les numéros ".
                " " . implode(", ", $client_numeros);
                $descriptionRun->addText("Attestons par la présente que ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText($add, array_merge($fontStyle, ['bold' => true]));  // -bold text
                $descriptionRun->addText(" " . trim($attestation->intitule), array_merge($fontStyle, ['bold' => true]));  // Bold text
                $descriptionRun->addText(" est ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText($attestation->titularity, array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText(" des comptes ouverts dans nos livres sous les numéros ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                $descriptionRun->addText(implode(", ", $client_numeros), array_merge($fontStyle, ['bold' => true]));  // Bold text
                $descriptionRun->addText(".", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
                  
                   

               }
          
               $description1.= "A ce jour, " . $add . " " . trim($attestation->intitule). " est libre de tout engagement dans nos livres.";

               $description1Run->addText("A ce jour, ", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               $description1Run->addText($add, array_merge($fontStyle, ['bold' => true]));  // -bold text
               $description1Run->addText(" " . trim($attestation->intitule), array_merge($fontStyle, ['bold' => true]));  // Bold text
               $description1Run->addText(" est libre de tout engagement dans nos livres.", array_merge($fontStyle, ['bold' => false]));  // Non-bold text
               }
    
                    // Debug the data being set
                   //  debug($attestation->object);
                   //  debug($attestation->libelle);
                   //  debug(date('d/m/Y', strtotime($attestation->date)));
                   //  debug($currency2);
                   //  debug($currencies);
                   //  debug($soldes);
                   //  debug($soldesInWords);

    
                    // Replace placeholders with data from the attestation
                    if($attestation->type == 1){
                    $templateProcessor->setValue('object', trim($attestation->object));
                    $templateProcessor->setValue('agence', trim($attestation->agence));
                    $templateProcessor->setValue('numero',trim( $attestation->numero));
                    $date = date('Y', strtotime($attestation->date));
                    $date = substr($date,2,2); // only wantthe last two chars
                    $templateProcessor->setValue('year', $date);



                    }
                    elseif($attestation->type== 2) {
                      
                       $templateProcessor->setValue('responsable', trim($attestation->responsable));
                       $templateProcessor->setValue('filiere', trim($attestation->filiere));



                    }

                   
                    $templateProcessor->setValue('date', date('d/m/Y', strtotime($attestation->date)));



                     // dynamic descriptions 
                   //   debug($descriptionRun->getElements()); // Output the elements of $descriptionRun

                   $templateProcessor->setComplexValue('description', $descriptionRun);
                   $templateProcessor->setComplexValue('description1', $description1Run);


    
                    // Save the processed template to a new file
                    $templateProcessor->saveAs($outputFile);
    
                    // Provide the generated file as a download response
                    $response = new Response();
                    $response = $response->withFile($outputFile, ['download' => true, 'name' => 'attestation_' . $attestation->numero . '.docx']);

                    
                    $response = $response->withType('docx'); // Optional: Set the content type explicitly
                    if ($referer) {
                        // Clear the session variable once used, if needed
                        $this->request->getSession()->delete('referercopy');
                        // debug($referer);
            
                         $this->redirect($referer);
                    } else {
            
                       
                         $this->redirect(['controller' => 'Attestations', 'action' => 'index']);
                    }
                    // return $response; // Send the response to initiate download   here would make more sense to just redirect 
                } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                    $this->Flash->error(__('Error generating attestation document: {0}', $e->getMessage()));
                    debug($e->getMessage()); // Debug the specific PHPWord exception message
                }
            } else {
                $this->Flash->error(__('Failed to save attestation.'));
               //  debug($attestation->getErrors()); // Debug validation errors
            }
        }
        $this->set(compact('attestation'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Attestation id.
     * @return \Cake\Http\Response|null|void Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {

        $referer = $this->request->getSession()->read('referercopy');

// If referer is not set (first access), store it
if (!$referer) {
    $referer = $this->request->referer();
    $this->request->getSession()->write('referercopy', $referer);
}
        $this->request->allowMethod(['post', 'delete']);
        $attestation = $this->Attestations->get($id);
        if ($this->Attestations->delete($attestation)) {
            $this->Flash->success(__('The attestation '.$attestation->numero. ' has been desacativated.'));
        } else {
            $this->Flash->error(__('The attestation '.$attestation->numero. 'could not be desacativated. Please, try again.'));
        }

        if ($referer) {
            // Clear the session variable once used, if needed
            $this->request->getSession()->delete('referercopy');
            // debug($referer);

             $this->redirect($referer);
        } else {

           
             $this->redirect(['controller' => 'Attestations', 'action' => 'index']);
        }    }


    public function restore($id = null)
    {

        $referer = $this->request->getSession()->read('referercopy');

// If referer is not set (first access), store it
if (!$referer) {
    $referer = $this->request->referer();
    $this->request->getSession()->write('referercopy', $referer);
}
        $this->request->allowMethod(['post', 'restore']);
        $attestation = $this->Attestations->get($id);
        $attestation->del = 0;   // restore
        if ($this->Attestations->save($attestation)) {
            $this->Flash->success(__('The attestation '.$attestation->numero. ' has been activated.'));
        } else {
            $this->Flash->error(__('The attestation '.$attestation->numero. 'could not be activated. Please, try again.'));
        }

        if ($referer) {
            // Clear the session variable once used, if needed
            $this->request->getSession()->delete('referercopy');
            // debug($referer);

             $this->redirect($referer);
        } else {

           
             $this->redirect(['controller' => 'Attestations', 'action' => 'deleted']);
        }    }
}
