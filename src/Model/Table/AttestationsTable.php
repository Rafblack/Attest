<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Attestations Model
 *
 * @method \App\Model\Entity\Attestation newEmptyEntity()
 * @method \App\Model\Entity\Attestation newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Attestation[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Attestation get($primaryKey, $options = [])
 * @method \App\Model\Entity\Attestation findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Attestation patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Attestation[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Attestation|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Attestation saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Attestation[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Attestation[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Attestation[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Attestation[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AttestationsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('attestations');
        $this->setDisplayField('numero');
        $this->setPrimaryKey('id');
    }

    public function delete(EntityInterface $entity, $options = []): bool
    {
        $event = new Event('Model.beforeDelete', $this, [
            'entity' => $entity,
            'options' => $options
        ]);
       
   
        // Set the 'deleted' field to true
        // $entity->numero = $entity->numero.$entity->id."DEL";   
        $entity->del = 1;
        $entity->del_at = date('Y-m-d H:i:s');

        

        // Save the entity to persist the soft deletion
         parent::save($entity, $options);
         return true;
    }

    public function findActive(Query $query, array $options)
    {
        return $query->where([$this->aliasField('del') => 0]);
    }

    public function findDeleted(Query $query, array $options)
    {
        return $query->where([$this->aliasField('del') => 1]);
    }

 
    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        
        $validator
            ->date('date')
            ->requirePresence('date', 'create')
            ->notEmptyDate('date');

            $validator
            ->scalar('agence')
            ->requirePresence('agence', 'create')
            ->notEmptyString('agence');

            $validator
            ->scalar('titularity')
            ->requirePresence('titularity', 'create')
            ->notEmptyString('titularity');

            $validator
            ->add('object', 'custom', [
                'rule' => function ($value, $context) {
                    if (isset($context['data']['type']) && $context['data']['type'] == 1) {
                        return !empty(trim($value));
                    }
                    return true;
                },
                'message' => 'Object vide !',
            ])


            ->add('responsable', 'custom', [
                'rule' => function ($value, $context) {
                    if (isset($context['data']['type']) && $context['data']['type'] == 2) {
                        return !empty(trim($value));
                    }
                    return true;
                },
                'message' => 'nom du responsable vide !',
            ])
            ->add('filiere', 'custom', [
                'rule' => function ($value, $context) {
                    if (isset($context['data']['type']) && $context['data']['type'] == 2) {
                        return !empty(trim($value));
                    }
                    return true;
                },
                'message' => 'Description vide !',
            ])
            ->add('role', 'custom', [
                'rule' => function ($value, $context) {
                    if (isset($context['data']['type']) && $context['data']['type'] == 2) {
                        return !empty(trim($value));
                    }
                    return true;
                },
                'message' => 'Description du client vide!',
            ]);
    
            $validator
            ->add('currency', 'custom', [
                'rule' => function ($value, $context) {
                    if (!empty(trim($context['data']['contrevaleur']))) {
                        return !empty(trim($value));
                    }
                    return true;
                },
                'message' => 'Choisissez la devise !',
            ])


            ->add('gerant', 'custom', [
                'rule' => function ($value, $context) {
                    if (isset($context['data']['agence']) && $context['data']['agence'] === 'CLIPRO') {
                        return !empty(trim($value));
                    }
                    return true;
                },
                'message' => 'nom du gerant qui manque !',
            ]);

       

     

        $validator
            ->integer('type')
            ->requirePresence('type', 'create')
            ->notEmptyString('type');

        $validator
        
            ->scalar('template')
            ->maxLength('template', 100)
            ->allowEmptyString('template');

            $validator
            ->integer('contrevaleur')
            ->maxLength('contrevaleur', 15)

            ->allowEmptyString('contrevaleur'); 

        
        $validator
            ->allowEmptyString('del');

        $validator
            ->allowEmptyString('del_at');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(\Cake\Datasource\RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['id']), ['errorField' => 'id']);
        $rules->add($rules->isUnique(['numero']), ['errorField' => 'numero',"message"=> "Le numero existe !"]);

        return $rules;
    }
}
