<table width=100% class="table table-bordered">
  <thead>
    <tr align="center">
    <th scope="col">Nome</th>
    <th scope="col">Código USP</th>
    <th scope="col">Tipo</th>
    <th scope="col">Créditos</th>
    <th scope="col">Créditos Convertidos</th>
    <th scope="col">Nota</th>
    <th scope="col">Carga Horária Semestral</th>
    <th scope="col">Ementa</th>
    <th scope="col">Status</th>
    <th scope="col">Comentários</th>
    <th scope="col">Parecer</th>
    </tr>
  </thead>

  <tbody>
    @foreach($pedido->disciplinas->sortBy('tipo') as $disciplina)
      <tr>
          <td>{{ $disciplina->nome }}</td>
          <td>{{ $disciplina->codigo }} - {{$disciplinas_usp[$disciplina->codigo] ?? ''}}
          </td>
          <td align="center">{{ $disciplina->tipo }}</td>
          <td align="center">{{ $disciplina->creditos  }}</td>

          <td align="center">
            @if( $disciplina->conversao === null)
              Não convertido
            @else
              @if($disciplina->conversao == 0) 
                <font color="red">Não será considerada - créditos zero</font>
              @else
                {{ $disciplina->conversao }}
              @endif  
            @endif
          </td>

          <td align="center">{{ $disciplina->nota }}</td>
         
          <td align="center">{{ $disciplina->carga_horaria }}</td>
          <td align="center">
            @if(!empty($disciplina->path))
              <a href="/disciplinas/{{ $disciplina->id }}/showfile"><i class="far fa-file-pdf"></i></a> 
            @endif
          </td>
          <td align="center">{{ $disciplina->status }}</td>
          <td class="expandir">
            @foreach($disciplina->statuses as $status)
              @if(!empty($status->reason))
                <b> {{\Carbon\Carbon::parse( $status->created_at)->format('d/m/Y H:i') }} - 
                  {{ explode(' ', \App\Models\User::find($status->user_id)->name)[0] }}:
                </b> {{ $status->reason }} <br>
              @endif
            @endforeach
          </td>

          <td align="center">
            @if($disciplina->deferimento_docente == 'Sim')
              Parecer favorável realizado por: <i>{{ \Uspdev\Replicado\Pessoa::nomeCompleto($disciplina->codpes_docente) }} </i>
            @else
              @if(!empty($disciplina->codpes_docente))
                @if($disciplina->status == 'Indeferido')
                  <b>Indeferido por: </b> {{ \Uspdev\Replicado\Pessoa::nomeCompleto($disciplina->codpes_docente) }} <br>
                @else
                  <b>Aguardando parecer de:</b> {{ \Uspdev\Replicado\Pessoa::nomeCompleto($disciplina->codpes_docente) }} <br>
                @endif
              @endif

            @endif 
          </td>
 
      </tr>
    @endforeach
  </tbody>
</table>

<br>

@can('sg')
  <form method="POST" action="/update_status_pedido/{{$pedido->id}}">
    @csrf
    <input type="hidden" name="status" value="Deferido">
    <button type="submit" onclick="return confirm('Tem certeza que deseja enviar para comissão de graduação? ');" class="btn btn-success p-2">
      Disciplinas já cadastradas no Jupiterweb. Finalizar pedido!
    </button>
  </form>

  <br><br>
  <form method="POST" action="/update_status_pedido/{{$pedido->id}}">
      @csrf
      <input type="hidden" name="status" value="Análise">
      <br>
      <div class="form-group">
          <label for="comentario_disciplina"> <b>Comentário</b> (Obrigatório caso seja devolvido para Análise): </label>
          <select class="form-control" id="comentario_disciplina" name="comentario_disciplina">
          <option value="" selected=""> Selecione a disciplina a qual o comentário se refere</option>
          @foreach($pedido->disciplinas as $disciplina)
              <option value="{{ $disciplina->id }}">{{ $disciplina->nome }}</option>
          @endforeach
          </select>
      </div>

      <textarea class="form-control" rows="3" name="comentario" required></textarea>
      <br>    
      <button type="submit" onclick="return confirm('Tem certeza que deseja retornar o pedido para análise (ccint)? ');" class="btn btn-danger p-2">
        Retornar o pedido para análise (ccint)
      </button>
  </form>
@endcan('sg')
