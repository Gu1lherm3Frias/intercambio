<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Pedido;
use App\Models\Disciplina;
use Uspdev\Replicado\Pessoa;
use App\Http\Controllers\Redirect;
use Illuminate\Support\Facades\URL;

use Mail;
use App\Mail\email_analise_aluno;
use App\Mail\email_em_elaboracao_aluno;
use App\Mail\email_analise_ccint;
use App\Mail\email_indeferido;
use App\Mail\email_deferido;
use App\Mail\email_docente;
use App\Mail\email_cg;
use App\Mail\email_sg;
use App\Service\Utils;

class WorkflowController extends Controller
{
    public function updatePedidoStatus(Request $request, Pedido $pedido){

        if($pedido->status == 'Análise') {
            $this->authorize('admin');
        }

        if($pedido->status == 'Comissão de Graduação') {
            $this->authorize('cg');
        }

        if($pedido->status == 'Serviço de Graduação') {
            $this->authorize('sg');
        }

        if($pedido->status == 'Finalizado') {
            $this->authorize('admin');
        }

        $request->validate([
            'status' => Rule::in(['Em elaboração', 'Análise', 'Comissão de Graduação','Serviço de Graduação','Deferido']),
        ]);

        // se tiver comentário, a disciplina referente ao comentário deve ser especificada
        if(!is_null($request->comentario) && !empty($request->comentario)){
            $request->validate([
                'comentario_disciplina' => 'required',
            ]);
        }

        // Obrigatório comentário quando devolve-se para o aluno/a
        if($request->status == 'Em elaboração'){
            $request->validate([
                'comentario' => 'required',
            ]);
        }

        if($request->status == 'Comissão de Graduação'){
            $this->authorize('admin');
            foreach($pedido->disciplinas as $disciplina) {
                if($disciplina->tipo != 'Obrigatória' && !is_numeric($disciplina->getRawOriginal('conversao'))){
                    request()->session()->flash('alert-danger',"A disciplina {$disciplina->nome} não teve os créditos convertidos");
                    return back();
                }
            }
            Mail::queue(new email_cg($pedido));
        }

        if($request->status == 'Serviço de Graduação'){
            $this->authorize('cg');
            foreach($pedido->disciplinas as $disciplina) {
                if($disciplina->status == 'Indeferido'){
                    request()->session()->flash('alert-danger',"A disciplina {$disciplina->nome} foi indeferida e não pode ser enviada para o Serviço de Graduação");
                    return back();
                }
            }
            Mail::queue(new email_sg($pedido));
        }

        // Finalizado é deferido
        if($request->status == 'Deferido'){
            $this->authorize('sg');
            Mail::queue(new email_deferido($pedido));
        }

        // troca o status de todas disciplinas
        foreach($pedido->disciplinas as $disciplina) {
            if(!empty($request->comentario_disciplina) && $disciplina->id == $request->comentario_disciplina) {
                $disciplina->setStatus($request->status, $request->comentario);
            } else {
                $disciplina->setStatus($request->status);
            }

        }

        Utils::updatePedidoStatus($pedido);

        if($request->status =='Em elaboração') {
            Mail::queue(new email_em_elaboracao_aluno($pedido));
        }else if($request->status=='Análise') {
            Mail::queue(new email_analise_aluno($pedido));
            Mail::queue(new email_analise_ccint($pedido));
        }
        return redirect("/pedidos/$pedido->id");
    }

    public function salvardocente(Request $request, Disciplina $disciplina)
    {
        $this->authorize('cg');
        // TODO: Validar se é um docente
        $request->validate([
            "codpes_docente" => "required|integer"
        ]);

        $disciplina->codpes_docente = $request->codpes_docente;
        $disciplina->save();
        Mail::queue(new email_docente($disciplina, $request->codpes_docente));

        return redirect("/pedidos/$disciplina->pedido_id");
    }

    public function docente(Request $request)
    {
        $this->authorize('docente');
        $disciplinas = Disciplina::where('codpes_docente',auth()->user()->codpes)
                                  ->whereNull('deferimento_docente')->get();

        return view('disciplinas.docente',[
            'disciplinas' => $disciplinas,
        ]);
    }

    public function show_parecer(Disciplina $disciplina, Request $request)
    {
        // TODO: verificar se o docente em questão é o owner do parecer
        $this->authorize('docente');
        return view('disciplinas.parecer',[
            'docentes'   =>  $docentes = Pessoa::listarDocentes(),
            'disciplina' => $disciplina,
        ]);
    }

    public function store_parecer(Disciplina $disciplina, Request $request)
    {
        // TODO: verificar se o docente em questão é o owner do parecer
        $this->authorize('docente');

        if($request->parecer == 'indicar'){
            $request->validate([
                "codpes" => "required"
            ]);
            $disciplina->setStatus('Comissão de Graduação');
            $disciplina->codpes_docente = $request->codpes;
            $disciplina->save();
            Mail::queue(new email_docente($disciplina, $request->codpes));
            request()->session()->flash('alert-info',"Obrigado pela indicação");
            return redirect("/docente");
        }

        if($request->parecer == 'indeferir'){
            $request->validate([
                "comentario" => "required"
            ]);

            $disciplina->setStatus('Indeferido', $request->comentario);
            $disciplina->save();
            Mail::queue(new email_indeferido($disciplina));
            request()->session()->flash('alert-info',"Indeferimento realizado com sucesso");
            return redirect("/docente");
        }

        $disciplina->deferimento_docente = 'Sim';
        $disciplina->setStatus('Comissão de Graduação', $request->comentario);
        $disciplina->save();

        request()->session()->flash('alert-info',"Parecer realizado com sucesso");
        return redirect("/docente");
    }

    public function cg(Request $request)
    {
        $this->authorize('cg');

        $pedidos = Pedido::where('status','Comissão de Graduação');
        return view ('pedidos.cg',[
            'pedidos' => $pedidos->get(),
        ]);
    }

    public function sg(Request $request)
    {
        $this->authorize('sg');
        $pedidos = Pedido::where('status','Serviço de Graduação');
        return view ('pedidos.sg',[
            'pedidos' => $pedidos->get(),
        ]);
    }
}
