<?php

namespace App\Service;

use Uspdev\Replicado\Graduacao;
use App\Models\Pedido;
use App\Models\Disciplina;
use Uspdev\Replicado\Pessoa;


class Utils
{
    public static function disciplinas($codpes)
    {
        $curso = Graduacao::curso($codpes,env('REPLICADO_CODUNDCLG'));
        if(!empty($curso)) {
            return Graduacao::listarDisciplinasGradeCurricular($curso['codcur'], $curso['codhab']);
        } 
        return [];
    }

    public static function nome_disciplina_usp($coddis){
        if($coddis){
            return Graduacao::nomeDisciplina($coddis);
        }
        return [];
    }

    public static function updatePedidoStatus(Pedido $pedido){
        # Se nesse pedido não tem nenhuma disciplina: 'Em elaboração'
        $disciplinas = $pedido->disciplinas;

        if($disciplinas->isEmpty()) {
            $pedido->status = 'Em elaboração';
            $pedido->save();
            return;
        } 
        
        foreach($disciplinas as $disciplina){

            # Se nesse pedido existir alguma disciplina em elaboração status do pedido será em 'Em elaboração'
            if($disciplina->status=='Em elaboração') {
                $pedido->status = 'Em elaboração';
                $pedido->save();
                return;
            }

            # Se nesse pedido existir alguma disciplina em análise status do pedido será em 'Análise'
            if($disciplina->status=='Análise') {
                $pedido->status = 'Análise';
                $pedido->save();
                return;
            }

            if($disciplina->status=='Comissão de Graduação') {
                $pedido->status = 'Comissão de Graduação';
                $pedido->save();
                return;
            }
            if($disciplina->status=='Serviço de Graduação') {
                $pedido->status = 'Serviço de Graduação';
                $pedido->save();
                return;
            }
        }

        $pedido->status = 'Finalizado';
        $pedido->save();
        return;
    }
}