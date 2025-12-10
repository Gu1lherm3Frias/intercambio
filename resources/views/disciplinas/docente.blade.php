@extends('main')

@section('content')

  <table class="table table-striped">
    <thead>
        <tr>
          <th><b>Número USP</b></th>
          <th><b>Nome discente</b></th>
          <th><b>Tipo do Intercâmbio</b></th>
          <th><b>Curso</b></th>
          <th><b>Instituição</b></th>
          <th><b>Disciplina na Instituição</b></th>
          <th><b>Disciplina USP</b></th>
          <th><b>Status</b></th>
          <th><b>Parecer</b></th>
        </tr>
    </thead>

    <tbody>
      @foreach($disciplinas as $disciplina)
        <tr>
          <td>{{ $disciplina->pedido->user->codpes }}</td>
          <td>{{ $disciplina->pedido->nome }}</td>
          <td>{{ $disciplina->pedido->tipo }}</td>
          <td>{{ $disciplina->pedido->curso }}</td>
          <td>{{ $disciplina->pedido->instituicao->nome_instituicao }}</td>
          <td>{{ $disciplina->nome }}</td>
          <td>{{ $disciplina->codigo }} - {{$disciplinas_usp[$disciplina->codigo] ?? ''}}
          </td>
          <td>{{ $disciplina->status }}</td>
          <td>
            <a href="/show_parecer/{{ $disciplina->id }}" class="btn btn-success">
              @if($disciplina->status == 'Indeferido')
                Reenviar parecer
              @else
                Realizar parecer
              @endif
            </a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection
