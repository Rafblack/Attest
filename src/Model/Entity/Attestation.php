<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Attestation Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenTime $date
 * @property string $client_numeros
 * @property string $numero
 * @property string $soldes
 * @property string $currencies
 * @property string $currency
 * @property int $contrevaleur
 * @property string $agence
* @property string $date




 * 
 * @property int $type
 * @property string|null $template
 * @property int|null $del
 * @property int|null $del_at
 */
class Attestation extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected $_accessible = [
        '*'=> true,
        'date'=>true,
        'id'=>false

    ];


    // protected function _setClientNumeros($value)
    // {
    //     return json_encode($value);
    // }

    // protected function _getClientNumeros($value)
    // {
    //     return json_decode($value, true);
    // }

    // protected function _setSoldes($value)
    // {
    //     return json_encode($value);
    // }

    // protected function _getSoldes($value)
    // {
    //     return json_decode($value, true);
    // }

    // protected function _setCurrencies($value)
    // {
    //     return json_encode($value);
    // }

    // protected function _getCurrencies($value)
    // {
    //     return json_decode($value, true);
    // }
}
