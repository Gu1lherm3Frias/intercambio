<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Country;
use App\Models\Instituicao;
use App\Models\Relatorio;
use Illuminate\Http\Request;
use App\Http\Requests\PedidoRequest;
use Uspdev\Replicado\Graduacao;
use Fflch\LaravelFflchStepper\Stepper;
use Uspdev\Replicado\Pessoa;
use App\Service\Utils;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Service\GeneralSettings;
use App\Actions\SumConversaoAction;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin');
        $pedidos = Pedido::orderBy('id', 'DESC');
        if(isset($request->buscastatus)) {
            $pedidos =  $pedidos->where('status',$request->buscastatus)
                                ->orderBy('id', 'DESC');
        }

        if(isset($request->search)) {
            $pedidos =  $pedidos->where('codpes','LIKE',"%{$request->search}%")
                                ->orWhere('nome','LIKE',"%{$request->search}%")
                                ->orderBy('id', 'DESC');
        }

        return view ('pedidos.index',[
            'pedidos' => $pedidos->paginate(10),
        ]);
    }

    public function create()
    {
        $this->authorize('grad');
        $tipos = explode(PHP_EOL,app(GeneralSettings::class)->tipos_pedido);

        return view('pedidos.create',[
            'countries' => Country::all()->sortBy('nome'),
            'instituicoes' => array(),
            'tipos' => $tipos,
        ]);
    }

    public function getinstituicao(Request $request)
    {
        if($request->has('search')) {
            $instituicoes = Instituicao::where('country_id', $request->search)
                      ->orderby('nome_instituicao','asc')->get();
        }
        $response = array();
        foreach($instituicoes as $insti){
            $response[] = array(
                "id" => $insti->id,
                "nome_instituicao" => $insti->nome_instituicao,
            );
        }
        return response()->json($response);
    }


    public function store(PedidoRequest $request)
    {
        if (empty(Graduacao::curso(auth()->user()->codpes, env('REPLICADO_CODUNDCLG')))){
            return back()->with('alert-danger','Esta operação só pode ser executada por um aluno matrículado.');
        }

        $this->authorize('grad');
        $validated = $request->validated();
        $validated['user_id'] = auth()->user()->id;
        $validated['original_name'] = $request->file('file')->getClientOriginalName();
        $validated['path'] = $request->file('file')->store('.');
        $pedido = Pedido::create($validated);

        request()->session()->flash('alert-danger','O preenchimento do relatório é obrigatorio para dar continuidade ao pedido');
        return redirect("/relatorios/$pedido->id/create");
    }

    public function show(Pedido $pedido, Stepper $stepper)
    {
        $relatorio = Relatorio::where("pedido_id", $pedido->id)->first();
        if(!$relatorio && $pedido->status == 'Em Elaboração'){
            request()->session()->flash('alert-danger','O preenchimento do relatório é obrigatorio para dar continuidade ao pedido');
            return redirect("/relatorios/$pedido->id");
        }

        if($pedido->status == 'Comissão de Graduação'){
            $docentes = Pessoa::listarDocentes();
        } else {
            $docentes = [1];
        }

        $this->authorize('owner', $pedido);

        $stepper->setCurrentStepName($pedido->status);

        $coddis = $pedido->disciplinas->pluck('codigo')->toArray();
        $disciplinas_usp = Utils::nomeDisciplinaUsp($coddis);

        return view('pedidos.show',[
            'pedido' => $pedido,
            'docentes' => $docentes,
            'disciplinas' => Utils::disciplinas(auth()->user()->codpes),
            'disciplinas_usp' => collect($disciplinas_usp)->pluck('nomdis','coddis'),
            'totais' => SumConversaoAction::handle($pedido),
            'stepper' => $stepper->render()

        ]);
    }

    public function edit(Pedido $pedido)
    {
        $this->authorize('owner',$pedido);
        $tipos = explode(PHP_EOL,app(GeneralSettings::class)->tipos_pedido);

        $countries = Country::all()->pluck('nome', 'id')->sortBy('nome');
        $instituicao = Instituicao::find($pedido->instituicao_id);
        $instituicoes = Instituicao::where('country_id', $instituicao->country_id)
                        ->pluck('nome_instituicao', 'id')
                        ->sortBy('nome_instituicao');

        return view('pedidos.edit',[
            'pedido' => $pedido,
            'countries' => $countries,
            'country_id' => $instituicao->country_id,
            'instituicoes' => $instituicoes,
            'tipos' => $tipos,
        ]);
    }

    public function update(PedidoRequest $request, Pedido $pedido)
    {
        $this->authorize('owner',$pedido);
        $validated = $request->validated();
        if($request->file('file') != null){
            Storage::delete($pedido->path);
            $validated['original_name'] = $request->file('file')->getClientOriginalName();
            $validated['path'] = $request->file('file')->store('.');
        }
        $pedido->update($validated);
        request()->session()->flash('alert-info','Pedido atualizado com sucesso.');
        return redirect("/pedidos/{$pedido->id}");
    }

    public function destroy(Pedido $pedido)
    {
        //desabilitado no form
        $this->authorize('owner',$pedido);

        # o aluno só pode deletar enquanto estiver em elaboração
        if($pedido->status != 'Em elaboração' & !Gate::allows('admin')){
            request()->session()->flash('alert-danger','O Pedido não pode ser excluído
                porque não está mais em elaboração');
            return redirect('/meus_pedidos');
        }

        Storage::delete($pedido->path);
        foreach($pedido->disciplinas as $disciplina){
            Storage::delete($disciplina->path);
            $disciplina->delete();
        }
        $pedido->delete();
        return redirect('/');
    }

    public function meus_pedidos(){
        $this->authorize('grad');
        $pedidos = Pedido::where('user_id',auth()->user()->id)->paginate(10);
        return view('pedidos.meus_pedidos',[
            'pedidos' => $pedidos
        ]);
    }

    public function showfile(Pedido $pedido)
    {
        $this->authorize('owner',$pedido);
        return Storage::download($pedido->path, $pedido->original_name);
    }

}
