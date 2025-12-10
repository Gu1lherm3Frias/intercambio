<?php

namespace App\Actions;

use App\Models\Pedido;

class SumConversaoAction
{
    /**
    * retorna array - contendo as somas da conversão dos créditos
    * das disciplinas
     */
    public static function handle(Pedido $pedido)
    {
        $disciplinas = $pedido->disciplinas;
        $totalLivres = $disciplinas->where('tipo', 'Optativa Livre')->sum(function ($item) {
            return $item->getRawOriginal('conversao');
        });
        $totalEletivas = $disciplinas->where('tipo', 'Optativa Eletiva')->sum(function ($item) {
            return $item->getRawOriginal('conversao');
        });
        $total = $disciplinas->sum(function ($item) {
            return $item->getRawOriginal('conversao');
        });

        return [
            'livres' => str_replace('.', ',', $totalLivres),
            'eletivas' => str_replace('.', ',', $totalEletivas),
            'total' => str_replace('.', ',', $total)
        ];
    }
}
