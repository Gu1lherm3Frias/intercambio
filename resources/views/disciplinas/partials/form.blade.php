<form method="POST" enctype="multipart/form-data" action="/disciplinas">
    @csrf

    <script>
    function flip(clicado) {
        if(clicado == 'Obrigatória'){
            document.getElementById("lista_obrigatorias").style = true;
            $("#conversao").css('display','none');
        } else {
            document.getElementById("lista_obrigatorias").style = "display: none;";
            $("#conversao").css('display','block');
        }
    }
    </script>

    <div class="card-header"><b>Adicione as informações da Disciplina</b></div>
        <div class="card-body">
            <div class="row">
                <div class="form-group col-sm">
                    <div class="form-group">
                        <label class="required"><b>Tipo: </b></label>
                        <br>
                        @foreach(\App\Models\Disciplina::tipos as $tipo)
                            <input type="radio" onclick="flip(this.value)" id="{{ $tipo }}" name="tipo" value="{{$tipo}}"
                            @if(old('tipo') == $tipo) checked @endif>
                            <label for="{{ $tipo }}">{{$tipo}}</label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group col-sm-3">
                    <div class="form-group">
                        <label for="nome" class="required"><b>Nome: </b></label>
                        <input type="text" class="form-control" id="nome" name="nome" value="{{ old('nome') }}" placeholder="Insira o nome da matéria">
                    </div>
                </div>

                <div class="form-group col-sm-1">
                    <div class="form-group">
                        <label for="nota" class="required"><b>Nota: </b></label>
                        <input type="text" class="form-control" id="nota" name="nota"  maxlength="5" value="{{ old('nota') }}" placeholder="Ex.:10 ou A+">
                    </div>
                </div>

                <div class="form-group col-sm-2">
                    <div class="form-group">
                        <label for="creditos" class="required"><b>Créditos obtidos: </b></label>
                        <input type="text" class="form-control" id="creditos" name="creditos" value="{{ old('creditos') }}" placeholder="Insira os créditos obtidos">
                    </div>
                </div>

                <div class="form-group col-sm-2">
                    <div class="form-group">
                        <label for="carga_horaria" class="required"><b>Carga Horária Semestral: </b></label>
                        <input type="text" class="form-control" id="carga_horaria" name="carga_horaria" value="{{ old('carga_horaria') }}" placeholder="Insira a carga horária semestral">
                    </div>
                </div>
            </div>
            <div class="row" id="lista_obrigatorias" @if(old('tipo') == 'Obrigatória') style="display: block;" @else style="display: none;" @endif>
                <div class="form-group col-sm">
                    <div class="form-group">
                    <label id="textocodigo" for="codigo" class="required"><b>Selecione o código equivalente na USP </b></label>
                    <br>
                    <select id="codigo" name="codigo">
                        @foreach($disciplinas as $disciplina)
                            <option id="codigo" name="codigo" value="{{ $disciplina['coddis'] }}"
                                @if(old('codigo') == $disciplina['coddis']) selected @endif>
                                {{ $disciplina['coddis'] }} - {{ $disciplina['nomdis'] }}
                            </option>
                        @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="form-group col-sm">
                    <div class="form-group">
                        <label for="file" class="required"><b>Ementa: </b></label> <br>
                        <input type="file" name="file">
                    </div>
                </div>
            </div>

            <div class="row">
            <b>Comentário adicional para a Disciplina (Opcional):</b>
                <textarea  class="form-control" rows="3" name="comentario"></textarea>
            </div>

            <div class="row">
                <div class="form-group col-sm-2">
                    <br>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success" >Adicionar</button>
                        <input class="form-control" type="hidden" name="pedido_id" value="{{ $pedido->id }}">
                    </div>
                </div>
            </div>
        </div>
</form>
