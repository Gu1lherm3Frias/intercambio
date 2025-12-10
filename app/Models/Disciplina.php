<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pedido;
use App\Traits\HasStatuses;

class Disciplina extends Model
{
    use HasFactory;
    use HasStatuses;

    protected $guarded = ['id'];

    public function pedido(){
        return $this->belongsTo(Pedido::class);
    }

    const status = [
        'Em elaboração',
        'Análise',
        'Deferido',
        'Indeferido',
        'Comissão de Graduação',
        'Serviço de Graduação'
    ];

    const tipos = [
        'Obrigatória',
        'Optativa Livre',
        'Optativa Eletiva',
    ];

    const deferimento_docente = [
        'Sim',
        'Não'
    ];

    public function setCreditosAttribute($value)
    {
        $this->attributes['creditos'] = str_replace(",", ".", $value);
    }

    public function getCreditosAttribute($value)
    {
        return $this->attributes['creditos'] = str_replace(".", ",", $value);
    }

    public function setConversaoAttribute($value)
    {
        $this->attributes['conversao'] = str_replace(",", ".", $value);
    }

    public function getConversaoAttribute($value)
    {
        if ( $value ) {
            return $this->attributes['conversao'] = str_replace(".", ",", $value);
        }
    }
}
