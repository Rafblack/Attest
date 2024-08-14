<tr?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Evaluation $attestation
 * @var \Cake\Collection\CollectionInterface|string[] $attestations
 */
?>

<section class="content-header">
    <h1>Générer  - Attestation</h1>
</section>
<div class="content">
    <?= $this->Form->create($attestation) ?>
    <table class="table table-bordered">
        <tr>
            <td style="width: 50%;">
                <div class="form-group">
                    <?php
                      $agences =[
                        'CLIPRI' => 'CLIPRI',
                        'CLICOM'=>'CLICOM',
                        'CLIPRO'=>'CLIPRO',
   
   
   
                       ];
                    $types = [
                        1 => 'Bancaire',
                        2 => 'Non-engagement',
                    ];
                    echo $this->Form->control('numero', ['label'=>'Numero-Attestation','empty' => true, 'required' => true, 'class' => 'form-control']);
                    echo $this->Form->control('type', ['empty' => true, 'value' => 1, 'required' => true, 'options' => $types, 'class' => 'form-control']);
                    echo $this->Form->control('object', ['empty' => true, 'required' => false, 'class' => 'form-control']);
                    echo $this->Form->control('agence', ['label'=> "", 'options'=>$agences])

                    ?>
                    <div id = "gerant">
                        </div>
                </div>
            </td>
            <td style="width: 50%;">
                <div class="form-group">
                    <?php
                    $titularities = [
                      'titulaire'=>'titulaire',
                      'cotitulaire'=>"cotitulaire",

                    ];
                    echo $this->Form->control('intitule', ['label' => 'Intitule du client/ensemble', 'empty' => true, 'class' => 'form-control', 'required' => true]);
                    echo $this->Form->control('titularity', ['value'=> "titulaire",'options'=> $titularities,'label' => '', 'class' => 'form-control', 'required' => true]);


                    echo $this->Form->control('date', ['label' => 'Date', 'empty' => true, 'type' => 'date', 'class' => 'form-control', 'required' => true]);
                    ?>
                    <?php
                    echo $this->Form->control('contrevaleur', ['empty' => true, 'required' => false, 'class' => 'form-control']);
                    $currencies = [
                        'GNF'=>'GNF',

                        'USD' => 'USD',
                        'EUR' => 'EUR',
                        'XOF' => 'XOF',
                    ];
                  
                     echo $this->Form->control('currency', ['label'=>'Devise(contrevaleur)','empty' => true,"options"=>$currencies, 'required' => false, 'class' => 'form-control']);
                    
                    ?>



                    <div id="responsable">
                


                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- New section for account numbers and balances -->
    <div id="accounts-section">
        <fieldset>
            <legend>Comptes</legend>
            <div id="account-entries">
                <!-- Placeholder for dynamically added account entries -->
            </div>
            <button type="button" class="btn btn-secondary" id="add-account">Ajouter un compte</button>
        </fieldset>
    </div>

    <div style="width:45%">
        <?= $this->Form->button(__('Créer'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<script>
      function gere(typeField){
            const selectedType = typeField.value;
            const responsableDiv = document.getElementById('gerant');

            if (selectedType === 'CLIPRO') {
                responsableDiv.innerHTML = `
                    <div class="form-group">
                        <label for="responsable-nom">Nom du gerant</label>
                        <input type="text" name="gerant" id="gerant-nom" class="form-control" required>
                    </div>
                  
                `;
            } else {
                responsableDiv.innerHTML = ''; // Clear the content if type is not '2'
            }
   }
   function resp(typeField){
            const selectedType = typeField.value;
            const responsableDiv = document.getElementById('responsable');

            if (selectedType === '2') {
                responsableDiv.innerHTML = `
                    <div class="form-group">
                        <label for="responsable-nom">Nom du responsable</label>
                        <input type="text" value = "BARRY Diaraye" name="responsable" id="responsable-nom" class="form-control" required>

                    </div>

                    <div class = "form-group">

                    
                               <label for="responsable-description">Description</label>
                        <input type="text" value= "Responsable des Operations" name="filiere" id="responsable-filiere" class="form-control" required>
                        </div>
  
                             <?php
                     $roles =[
                        "personne"=> "personne",
                        "societe" => "societe",
                     ];
                           echo $this->Form->control('role', ['label'=>'Client_type','empty' => true,"options"=>$roles,'value'=> "personne", 'required' => true, 'class' => 'form-control']);

                     
                     ?>
                        
                `;
            } else {
                responsableDiv.innerHTML = ''; // Clear the content if type is not '2'
            }
   }

      window.onload= function() { 
        val = document.getElementById("agence")
        gere(val)
        val1 = document.getElementById('type')
        resp(val1);
  
};
document.addEventListener('DOMContentLoaded', function () {
    const currencies = ['USD', 'EUR', 'GBP']; 

    function addAccountEntry() {
        const index = document.querySelectorAll('.account-entry').length ;
        const accountEntry = document.createElement('div');
        accountEntry.className = 'account-entry';
        accountEntry.innerHTML = `
            <div class="form-group">
                <label for="account-number-${index}">Numéro de compte</label>
                <input type="text" name="accounts[${index}][number]" id="account-number-${index}" class="form-control" required>
            </div>
            <div class="form-group row">
                <div class="col">
                    <label for="balance-${index}">Solde</label>
                    <input type="number" name="accounts[${index}][balance]" id="balance-${index}" class="form-control" required>
                </div>
                <div class="col">
                    <label for="currency-${index}">Devise</label>
                    <select name="accounts[${index}][currency]" id="currency-${index}" class="form-control" required>
                        <option value="">Sélectionner</option>
                        ${currencies.map(currency => `<option value="${currency}">${currency}</option>`).join('')}
                    </select>
                </div>
            </div>
            ${index > 0 ? '<button type="button" class="btn btn-danger remove-account">X</button><hr>' : ''}
            <hr>
        `;
        document.getElementById('account-entries').appendChild(accountEntry);

        // Add event listener to the remove button
        if (index > 0) {
            accountEntry.querySelector('.remove-account').addEventListener('click', function () {
                accountEntry.remove();
            });
        }
    }

    document.getElementById('add-account').addEventListener('click', addAccountEntry);

    // Add the first account entry on page load
    addAccountEntry();



    // Function to add responsible form if type is 2 ('non-engagement')

  
 
    document.getElementById('type').addEventListener('change', function () {
           resp(document.getElementById('type'));
        });

        
    document.getElementById('agence').addEventListener('change', function () {
           gere(document.getElementById('agence'));
        });
    

    

    // Call the function to add responsible form on DOMContentLoaded
});
</script>
