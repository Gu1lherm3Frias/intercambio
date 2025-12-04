
<table width=100% class="table table-bordered">
  <thead>
    <tr align="center">

      <th scope="col">Nome</th>   
      <th scope="col">Nota</th>
      <th scope="col">Créditos</th>
      <th scope="col">Créditos Convertidos</th>
      <th scope="col">Carga Horária Semestral</th>
      <th scope="col">Código USP</th>
      <th scope="col">Ementa</th>
      <th scope="col">Comentários</th>
      <th scope="col">Tipo</th>
      
      <th scope="col">Status</th>

      @if($pedido->status == "Em elaboração")
        <th scope="col">Alterar Disciplina</th>
      @else
        @can('admin')
          <th scope="col">Alterar Disciplina</th>
        @else
          <th scope="col" style="display: none;"></th>
        @endcan
      @endif

    </tr>
  </thead>
  <tbody>
    @foreach($pedido->disciplinas->sortBy('tipo') as $disciplina)  
    <tr>
      <td>{{ $disciplina->nome }}</td>
      <td align="center">{{ $disciplina->nota }}</td>
      <td align="center">{{ $disciplina->creditos }}</td>
      <td scope="col" align="center">
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
      {{-- reverter a migration. O que preciso fazer: pegar o nome da disciplina com base no codigo dela --}}
      <td align="center">{{ $disciplina->carga_horaria }}</td>
      <td align="center">
        {{ $disciplina->codigo }} 
        - {{ $disciplina->nome_materia_usp ?? \App\Service\Utils::nome_disciplina_usp($disciplina->codigo) }}
      </td>
      <td align="center">
          @if(!empty($disciplina->path))
          <a href="/disciplinas/{{ $disciplina->id }}/showfile"><i class="far fa-file-pdf"></i></a> 
          @endif
      </td>
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

      <td scope="col" aling="center">{{ $disciplina->status }}</td>
    
      @if($pedido->status == "Em elaboração")
        <td align="center">
        <a href="/disciplinas/{{$disciplina->id}}/edit"><i class="fas fa-pencil-alt" color="#007bff"></i></a>
          <form method="POST" action="/disciplinas/{{$disciplina->id}}"> 
            @csrf
            @method('delete')
            <button type="submit" onclick="return confirm('Tem certeza que deseja excluir a Disciplina?');" style="background-color: transparent;border: none;"><i class="far fa-trash-alt" color="#007bff"></i></button>  
          </form> 
        </td>
      @elseif($pedido->status == "Análise")
      @can('admin')
        <td align="center">
          <a href="/disciplinas/{{$disciplina->id}}/edit"><i class="fas fa-pencil-alt" color="#007bff"></i></a>
        </td>
        @endcan
      @else
        <td style="display: none;"></td>
      @endif
    </tr>
    @endforeach
  </tbody>
</table>