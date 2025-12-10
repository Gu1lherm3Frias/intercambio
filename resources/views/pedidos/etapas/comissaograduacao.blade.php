<h2>Obrigatórias</h2>
<table width=100% class="table table-bordered">
  <thead>
    <tr align="center">
    <th scope="col">Nome</th>
    <th scope="col">Código USP</th>
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
        @if($disciplina->tipo == 'Obrigatória')
          <td>{{ $disciplina->nome }}</td>
          <td>{{ $disciplina->codigo }} - {{$disciplinas_usp[$disciplina->codigo] ?? ''}}
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
              @can('cg')
                <form method="post" action="salvardocente/{{ $disciplina->id }}">
                  @csrf
                  <select class="form-control" name="codpes_docente">
                    <option value="" selected="">- Selecione -</option>
                    @foreach ($docentes as $docente)
                      <option value="{{ $docente['codpes'] }}" @if(old('codpes') == $docente['codpes']) ) selected @endif>
                        {{ $docente['nompes'] }}
                      </option>
                    @endforeach
                  </select>
                  <div class="form-group">
                      @if(empty($disciplina->codpes_docente))
                        <button type="submit" onclick="return confirm('Tem certeza que deseja enviar para docente? ');" class="btn btn-success">Enviar para docente</button>
                      @else
                        @if($disciplina->deferimento_docente != 'Sim')
                          <br><button type="submit" onclick="return confirm('Tem certeza que deseja enviar para docente? ');" class="btn btn-success">Substituir parecerista</button>
                        @endif
                      @endif
                  </div>
                </form>
              @endcan('cg')
            @endif
          </td>
        @endif
      </tr>
    @endforeach
  </tbody>
</table>

<h2>Optativas</h2>
<table width=100% class="table table-bordered">
  <thead>
    <tr align="center">
      <th scope="col">Nome</th>
      <th scope="col">Nota</th>
      <th scope="col">Carga Horária Semestral</th>
      <th scope="col">Ementa</th>
      <th scope="col">Status</th>
      <th scope="col">Comentários</th>
      <th scope="col">Tipo</th>
      <th scope="col">Créditos Convertidos</th>
    </tr>
  </thead>
  <tbody>
    @foreach($pedido->disciplinas->sortBy('tipo') as $disciplina)
      <tr>
          @if($disciplina->tipo != 'Obrigatória')
            <td>{{ $disciplina->nome }}</td>
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
            <td align="center">{{ $disciplina->tipo }}</td>
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
          @endif
      </tr>
    @endforeach
  </tbody>
</table>

<br>
@can('cg')
  <form method="POST" action="/update_status_pedido/{{$pedido->id}}">
    @csrf
    <input type="hidden" name="status" value="Serviço de Graduação">
    <button type="submit" onclick="return confirm('Tem certeza que deseja enviar para comissão de graduação? ');" class="btn btn-success p-2">
      Enviar TODAS disciplinas para cadastro no Serviço de Graduação
    </button>
  </form>

  <br><br>
  <form method="POST" action="/update_status_pedido/{{$pedido->id}}">
      @csrf
      <input type="hidden" name="status" value="Análise">
      <br>
      <div class="form-group">
          <label for="comentario_disciplina"> <b>Comentário</b> (Obrigatório caso seja devolvido para análise): </label>
          <select class="form-control" id="comentario_disciplina" name="comentario_disciplina">
          <option value="" selected=""> Selecione a disciplina a qual o comentário se refere</option>
          @foreach($pedido->disciplinas as $disciplina)
              <option value="{{ $disciplina->id }}">{{ $disciplina->nome }}</option>
          @endforeach
          </select>
      </div>

      <textarea  class="form-control" rows="3" name="comentario"></textarea>
      <br>
      <button type="submit" onclick="return confirm('Tem certeza que deseja retornar o pedido para análise (ccint)? ');" class="btn btn-danger p-2">
        Retornar o pedido para análise (ccint)
      </button>
  </form>
@endcan('cg')
