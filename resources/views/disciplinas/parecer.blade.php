@extends('main')

@section('content')

    <div class="row">
        <div class="col-sm">

          <div class="card">
            <div class="card-header">
              Informações do pedido
            </div>
            <ul class="list-group list-group-flush">

              <li class="list-group-item">
                <b>Número USP discente:</b> {{ $disciplina->pedido->user->codpes }}
              </li>

              <li class="list-group-item">
                <b>Nome discente:</b> {{ $disciplina->pedido->nome }}
              </li>

              <li class="list-group-item">
                <b>Tipo do Intercâmbio:</b> {{ $disciplina->pedido->tipo }}
              </li>

              <li class="list-group-item">
                <b>Curso:</b> {{ $disciplina->pedido->curso }}
              </li>

              <li class="list-group-item">
                <b>Instituição:</b> {{ $disciplina->pedido->instituicao->nome_instituicao }}
              </li>

              <li class="list-group-item">
                <b>Disciplina na Instituição:</b> {{ $disciplina->nome }}
              </li>

              <li class="list-group-item">
                <b>Disciplina USP:</b> {{ $disciplina->codigo }} - {{$disciplinas_usp[$disciplina->codigo] ?? ''}}
              </li>

              <li class="list-group-item">
                <b>Nota:</b> {{ $disciplina->nota }}
              </li>

              <li class="list-group-item">
                <b>Carga horária semestral:</b> {{ $disciplina->carga_horaria }}
              </li>

              <li class="list-group-item">
                <b>Créditos Obtidos:</b> {{ $disciplina->creditos }}
              </li>

              <li class="list-group-item">
                <b>Boletim:</b> <a href="/pedidos/{{ $disciplina->pedido->id }}/showfile"><i class="far fa-file-pdf"></i></a>
              </li>

              <li class="list-group-item">
                <b>Ementa:</b> <a href="/disciplinas/{{ $disciplina->id }}/showfile"><i class="far fa-file-pdf"></i></a>
              </li>

            </ul>
          </div>

          <br>

          <div class="card">
            <div class="card-header">
              <b>Histórico de comentários:</b>
            </div>
            <ul class="list-group list-group-flush">
              @foreach($disciplina->statuses as $status)
                @if(!empty($status->reason))
                  <li class="list-group-item">
                    <b> {{\Carbon\Carbon::parse( $status->created_at)->format('d/m/Y H:i') }} -
                      {{ explode(' ', \App\Models\User::find($status->user_id)->name)[0] }}:
                    </b> {{ $status->reason }}
                  </li>
                @endif
              @endforeach
            </ul>
          </div>

          <br><br>

          <div class="text-center">
            <b>Decisão:</b>
          </div>
          <br>
          <form method="POST" enctype="multipart/form-data" action="/store_parecer/{{ $disciplina->id }}">
              @csrf

              <div class="row border align-items-center">

                <div class="col-sm text-center">
                  <button type="submit" class="btn btn-success" name="parecer" value="deferir" onclick="return confirm('Tem certeza que deseja deferir?');"> Deferir</button>
                </div>

                <div class="col-sm text-center">
                  <b>Se desejar, indique outro(a) docente para realização do parecer:</b>
                  <select class="form-control" name="codpes">
                    <option value="" selected="">- Selecione -</option>
                    @foreach ($docentes as $docente)
                      <option value="{{ $docente['codpes'] }}" @if(old('codpes') == $docente['codpes']) ) selected @endif>
                        {{ $docente['nompes'] }}
                      </option>
                    @endforeach
                  </select>
                  <br>
                  <button type="submit" class="btn btn-success" name="parecer" value="indicar" onclick="return confirm('Tem certeza que deseja indicar outro(a) docente?');">Indicar</button>
                </div>

                <div class="col-sm text-center">
                  <b> Em caso de indeferimento, justifique:</b><br>
                  <textarea  class="form-control" rows="3" name="comentario">{{ old('comentario') }}</textarea>
                  <br>
                  <button type="submit" class="btn btn-danger" name="parecer" value="indeferir" onclick="return confirm('Tem certeza que deseja indeferir?');">Indeferir</button>
                </div>
              </div>

          </form>

        </div>
    </div>


@endsection
