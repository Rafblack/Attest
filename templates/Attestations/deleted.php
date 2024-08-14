<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<form\App\Model\Entity\Attestation> $attestations
 */
?>
<section class="content-header">
    <h1>Attestation Désactivées </h1>
</section>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
<form class="form-inline my-2 my-lg-0" action="<?= $this->Url->build(['action' => 'deleted']) ?>" method="get">
    <div class="search-form">
        <div class="input-group">
            <?= $this->Form->text('search', ['class' => 'form-control', 'placeholder' => 'Rechercher...']) ?>
            <span class="input-group-btn">
                <!-- Use an image from webroot as the submit button -->
                <?= $this->Html->image('search.png', [
    'alt' => 'Rechercher',
    'class' => 'search-icon',
    'style' => 'cursor: pointer; height: 30px;',
    'onclick' => 'this.closest("form").submit();'   
]) ?>            </span>
        </div>
    </div>
</form>



</nav>

<div class="content">

<div class="box">
    
        <?php $i =1; ?>
        <div class="table-responsive">

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>Numero </th>
                    <th>Client </th>

                    <th>Désactivée le</th>

        <th> Date</th>
        <th> Type</th>
        <th class="actions"><?= __('Actions') ?></th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($attestations as $attestation): ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><?= h($attestation->numero) ?>
                    <td><?= h($attestation->intitule) ?>

                    <td><?= h($attestation->del_at) ?></td>
                    <td><?= h($attestation->date) ?></td>
                    <td><?= h($STAT[$attestation->type]) ?></td>

                    <td class="actions">
                        <?=$this->Html->image("afficher-btn.png", [
                            'height' => '15', 'width' => '27',
                            "alt" => "Modifier",'title' => 'Afficher',
                            'url' => ['controller' => 'Attestations', 'action' => 'view',$attestation->id]
                        ]);?>

                        <?=$this->Html->image("modifier-btn.png", [
                            'height' => '15', 'width' => '27',
                            "alt" => "Modifier",'title' => 'Modifier',
                            'url' => ['controller' => 'Attestations', 'action' => 'edit',$attestation->id]
                        ]);?>

<?= $this->Form->postLink(
                            $this->Html->image(
                                "restorer.png",
                                ["alt" => "Restorer", 'class' => 'action-link','title' => 'Restorer','height' => '15', 'width' => '27']
                            ),
                            ['controller' => 'Attestations', 'action' => 'restore', $attestation->id],
                            ['escape' => false, 'confirm' => __('Voulez-vous restorer cette attestation?' . $attestation->numero, $attestation->id)]
                        );
                        ?>

                        
                    </td>
                </tr>
                <?php ++$i;endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('Premier')) ?>
            <?= $this->Paginator->prev('< ' . __('Précédent')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('Suivant') . ' >') ?>
            <?= $this->Paginator->last(__('Dernier') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} sur {{pages}}, Affichage {{current}} enregsitrement(s) sur {{count}} au total')) ?></p>
    </div>
</div>
