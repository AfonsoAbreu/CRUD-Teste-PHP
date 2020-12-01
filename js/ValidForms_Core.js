export class Form {//mantém um inventário sobre quais campos foram preenchidos e quais não foram
  constructor(toDisableElement/*HTMLElement*/, form/*HTMLElement*/) {
    this.element = toDisableElement;
    this._inputs = {};
    let self = this;
    self.CheckInterval = setInterval(() => self.CheckSubmitState(), 100);
    form.addEventListener("submit", () => {
      let temp = self.Validate();
      if (!temp) {
        event.preventDefault();
      }
    });
    setTimeout(() => {
      let elements = Array.from(document.querySelectorAll("input"));
      elements = elements.filter(e => {
        let attr = e.getAttribute("formID");
        if (attr !== "" && attr !== null) {
          let attributes = [];
          for (let i in this._inputs) {
            attributes.push(this._inputs[i].element.getAttribute("formID"));
          }
          return (attributes.includes(attr));
        }
        return false;
      });//pega todos os elementos que tem referência na biblioteca (no estado atual)
      elements.forEach(e => e.addEventListener("keypress", (f) => {
        let key = f.keycode || f.which;
        if (key === 13) {
          let temp = self.Validate();
          if (!temp) {
            event.preventDefault();
          }
        }
      }));
    }, 0);
  }
  CreateInput(strType, strName) {//cria uma input vazia
    if (typeof this._inputs === "object") {
      if (Object.keys(this._inputs).includes("strName")) {
        return;
      }
    }
    switch (strType) {
      case "std":
        this._inputs[strName] = new Input();
        break;
      case "filter":
        this._inputs[strName] = new InputFilter();
        break;
      case "int":
        this._inputs[strName] = new InputInt();
        break;
      case "float":
        this._inputs[strName] = new InputFloat();
        break;
      case "regex":
        this._inputs[strName] = new InputRegex();
        break;
      case "fullName":
        this._inputs[strName] = new InputFullName();
        break;
      case "email":
        this._inputs[strName] = new InputEmail();
        break;
    }
    return this._inputs[strName];
  }
  AddInput(InputReference, inputName) {//adiciona uma input já criada
    if (!this._inputs[inputName]) {
      if (InputReference.element) {
        this._inputs[inputName] = InputReference;
      }
    }
  }
  GetInput(strName) {//retorna uma referência do objeto caso ele exista
    let inputs = Object.keys(this._inputs);
    if (inputs.includes(strName)) {
      return this._inputs[strName];
    } else {
      return undefined;
    }
  }
  GetAllInputs() {
    return this._inputs;
  }
  DeleteInput(strName) {//remove a referência do objeto se ela existe
    let inputs = Object.keys(this._inputs);
    if (inputs.includes(strName)) {
      this._inputs[strName] = undefined;
      return true;
    } else {
      return false;
    }
  }
  CheckSubmitState() {//checagem passiva: checa somente o atributo estado
    let estado = true;
    for (let i in this._inputs) {
      estado = estado && !!this._inputs[i].estado;
    }
    if (!estado) this.element.disabled = true;
    else this.element.disabled = false;
  }
  Validate() {
    //checagem ativa: executa o método Validate() de todas as inputs filhas, que modifica o atributo estado e retorna a validez do campo
    let estado = true;
    for (let i in this._inputs) {
      estado = !!this._inputs[i].Validate() && estado;
    }
    return estado;
  }
}

export class Input {//cuida da validação de dados de uma única input, núcleo base que serve para inputs que somente não podem ficar vazias
  constructor(elemento/*HTMLElement*/, conjuntoErro/*[...{element: HTMLElement, class: string}]*/, msgErrorFill/*string*/, errorAnchor/*HTMLElement*/, errorMsgStyle/*string*/) {
    this.estado = true;//true: input válida, false: input inválida
    this.element = null;//a input em si
    this.errorElements = null;//elementos que vão ser modificados quando houver um erro
    this.msgErrorFill = null;//mensagem de erro caso o campo nao seja preenchido
    this.bufferError;//ponteiro para o HTMLElement da mensagem de erro
    this.errorAnchor = null;//ponteiro para o elemento que irá receber uma mensagem de erro depois dele
    this.errorMsgStyle = null;//estilo que será aplicado nas mensagens de erro
    if (elemento) this.Element = elemento;
    if (conjuntoErro) this.ErrorElements = conjuntoErro;
    if (msgErrorFill) this.MsgErrorFill = msgErrorFill;
    if (errorAnchor) this.ErrorAnchor = errorAnchor;
    if (errorMsgStyle) this.ErrorMsgStyle = elemento;
  }
  Validate(callback = null/*function(short-hand-value, this)*/) {
    if (!this.element) return;
    let value = this.element.value;//pega o valor atual
    let rtrn = false;//bool de valor de retorno
    if (value !== "") {//se preenchido...
      if (this.bufferError) {//...e com erro, remove o erro
        this.RemoveError();
      }
      this.On();
      rtrn = true;
    } else {//se vazio, adiciona mensagem de erro
      this.AddError(this.msgErrorFill);
    }
    if (callback) {
      callback(value, this);
    }
    return rtrn;
  }
  AddError(msg) {//adiciona classes CSS de erro nos elementos, e coloca uma mensagem depois do elemento especificado
    this.errorElements.forEach(e => {
      e.element.classList.add(e.class);//adiciona as classes de erro para os elementos desejados
    });
    let erroMsg = document.createTextNode(msg);
    let erroElement = document.createElement("p");
    erroElement.appendChild(erroMsg);
    erroElement.classList.add(this.errorMsgStyle);//cria uma mensagem de erro estilizada
    let nextElement = this.errorAnchor.nextElementSibling;
    if (nextElement && nextElement.classList.contains(this.errorMsgStyle)) {//se já houver uma mensagem, a remove
      nextElement.remove();
    }
    this.errorAnchor.after(erroElement);//adiciona a mensagem
    this.bufferError = erroElement;//armazena ponteiro da mensagem
    this.Off();//"desliga" a si mesmo
  }
  RemoveError() {
    this.errorElements.forEach(e => {
      e.element.classList.remove(e.class);//remove todos os estilos de erro dos elementos
    });
    this.bufferError.remove();//remove elemento de erro que estava no buffer
    this.bufferError = null;
    this.On();//"liga" a si mesmo
  }
  Off() {
    this.estado = false;
  }
  On() {
    this.estado = true;
  }

  set Element(e/*HTMLElement*/) {
    if (typeof e === "object") {
      this.element = e;
      e.addEventListener("blur", () => this.Validate());
      e.setAttribute("formID", Math.random().toString());
    }
    if (!this.errorAnchor) this.errorAnchor = e;
  }
  get Element() {
    return this.element;
  }

  set ErrorElements(e/*...{element: HTMLElement, class: string}*/) {
    if (e instanceof Array) this.errorElements = e;
    else if (typeof e === "object") this.errorElements = [e];
  }
  get ErrorElements() {
    return this.errorElements;
  }

  set MsgErrorFill(e/*string*/) {
    if (typeof e === "string") this.msgErrorFill = e;
  }
  get MsgErrorFill() {
    return this.msgErrorFill;
  }

  get BufferError() {
    return this.bufferError;
  }

  set ErrorAnchor(e/*HTMLElement*/) {
    if (typeof e === "object") this.errorAnchor = e;
  }
  get ErrorAnchor() {
    return this.errorAnchor;
  }

  set ErrorMsgStyle(e/*string*/) {
    if (typeof e === "string") this.errorMsgStyle = e;
  }
  get ErrorMsgStyle() {
    return this.errorMsgStyle;
  }
}

export class InputFilter extends Input {//Classe que herda o comportamento e atributos de Input, mas adiciona filtragem de teclas, serve para inputs que podem ou não ser vazias, mas a entrada de teclas DEVE obedecer a lista fornecida
  constructor(elemento/*HTMLElement*/, conjuntoErro/*[...{element: HTMLElement, class: string}]*/, errorAnchor/*HTMLElement*/, errorMsgStyle/*string*/, msgErrorFill = null/*string || null*/, keyExceptions /*[...{key: int, callback: function}]*/, keys/*[...int]*/) {
    super(elemento, conjuntoErro, msgErrorFill, errorAnchor, errorMsgStyle);//chama o construtor de Input para colocar os atributos padrões
    let allowNull = !msgErrorFill;
    this.allowNull = allowNull;//caso não tenha mensagem, torna o campo opcional
    this.msgErrorFill = null;
    this.keyExceptions = null;
    this.keys = null;
    if (msgErrorFill) this.MsgErrorFill = msgErrorFill;
    if (keyExceptions) this.KeyExceptions = keyExceptions;
    if (keys) this.Keys = keys;
  }
  ValidateKey() {//vai checar a tecla associada a um evento, compara com o array keyExceptions
    let e = event || window.event;
    let key = e.keyCode || e.which;//pega o codigo da tecla
    // alert(key);
    if (this.keyExceptions) {//se existem exceções...
      let i = 0;
      for (let elemento of this.keyExceptions) {//...percorre o array de exceções...
        if (elemento.key === key) {//...e se o atributo ".key" do elemento atual for igual a tecla pressionada...
          elemento.callback();//...chama a callback do elemento...
          if (i === (this.keyExceptions.length - 1) && e.preventDefault) {//...e se for a ultima iteração...
            e.preventDefault();//...cancela o evento padrão
            return;
          };
        }
        i++;
      }
    }
    if (this.keys) {
      for (let e of this.keys) {//...senão, percorre as teclas da lista fornecida...
        if (e === key) return;//... e caso uma dessas seja a tecla atual, retorna...
      }
    } else return;
    if (e.preventDefault) e.preventDefault();//...senão, cancela o evento atual
  }
  Validate(callback = null/*function(short-hand-value, this)*/) {
    if (!this.allowNull) {//se o campo for obrigatório...
      return super.Validate(callback);//...chama a função original com a callback
    } else {//se o campo for opcional...
      let icFill = (this.element.value !== "");
      if (icFill) {//...e está preenchido...
        this.On();//..."liga" a si mesmo
      }
      if (callback) callback(this.element.value, this);
      return icFill;//...retorna se o campo está vazio ou não
    }
  }

  set Element(e/*HTMLElement*/) {
    if (typeof e === "object") {
      this.element = e;
      e.addEventListener("blur", () => this.Validate());
      e.setAttribute("formID", Math.random().toString());
    }
    if (!this.errorAnchor) this.errorAnchor = e;
    if (this.keys || this.keyExceptions) {
      e.addEventListener("keydown", () => this.ValidateKey());
    }
  }

  set AllowNull(e/*bool*/) {
    if (typeof e === "boolean") this.allowNull = e;
    if (e) this.msgErrorFill = null;
  }
  get AllowNull() {
    return this.allowNull;
  }

  set MsgErrorFill(e/*string*/) {
    if (typeof e === "string" && !this.allowNull) this.msgErrorFill = e;
  }
  get MsgErrorFill() {
    return this.msgErrorFill;
  }

  set KeyExceptions(e/*...{key: int, callback: function}*/) {
    if (e instanceof Array && typeof e[0] === "object") this.keyExceptions = e;
    else if (typeof e === "object") this.keyExceptions = [e];
  }
  get KeyExceptions() {
    return this.keyExceptions;
  }

  set Keys(e) {
    if (e instanceof Array && typeof e[0] === "number") this.keys = e;
    else if (typeof e === "object") this.keys = [e];
    if (this.element) {
      this.element.addEventListener("keydown", () => this.ValidateKey);
    }
  }
  get Keys() {
    return this.keys;
  }
}

export class InputInt extends InputFilter {//um superset de InputFilter, adiciona uma validação de intervalo e número (exclusivo), fornece uma especificação de teclas pronta (opcional) e tem gerenciamento de sinal (positivo ou negativo) (opcional)
  constructor(elemento/*HTMLElement*/, conjuntoErro/*[...{element: HTMLElement, class: string}]*/, errorAnchor/*HTMLElement*/, errorMsgStyle/*string*/, allowSigned = false/*bool*/, msgError = "Insira o valor corretamente"/*string*/, msgErrorFill = null/*string || null*/, vlMin = null/*Number*/, vlMax = null/*Number*/, keyExceptions /*[...{key: int, callback: function}]*/, keys = [8, 9, 13, 37, 39, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 109, 116, 173]/*[...int]*/) {
    super(elemento, conjuntoErro, errorAnchor, errorMsgStyle, msgErrorFill, keyExceptions, keys);//chama o construtor de InputFilter para colocar os atributos padrões
    this.maxValue = null;
    this.minValue = null;
    this.msgError = null;//mensagem de erro caso o valor esteja incorreto
    this.allowSigned = null;//verifica se o campo pode ser negativo ou não
    if (vlMax) this.MaxValue = vlMax;
    if (vlMin) this.MinValue = vlMin;
    if (msgError) this.MsgError = msgError;
    if (allowSigned) this.AllowSigned = allowSigned;
  }
  Validate(callback = null/*Function(int, validationIndicator, element)*/, callbackNull = null/*callback para ValidateFill*/, value = null/*string*/) {//um superset de Validate() com intervalos para números e validação para Number
    if (!super.Validate(callbackNull)) {//chama Validate() de InputFilter e verifica se está vazio...
      return false;//...se sim, retorna
    }
    value = (value) ? value : this.element.value;//pega o valor da input
    let num = Number(value);
    let correct;//define bool para a checagem de intervalo
    if (!isNaN(num)) {//se for um Number...
      if ((!isNaN(this.minValue) && this.minValue !== null) && (!isNaN(this.maxValue) && this.maxValue !== null)) {//...se o intervalo tiver valor minimo e maximo...
        correct = (num > this.minValue && num < this.maxValue);//...verifica num...
      } else if ((!isNaN(this.minValue) && this.minValue !== null) && (!!isNaN(this.maxValue) || this.maxValue === null)) {//...se o intervalo tiver somente um valor minimo...
        correct = (num > this.minValue);//...verifica num...
      } else if ((!isNaN(this.maxValue) && this.maxValue !== null) && (!!isNaN(this.minValue) || this.minValue === null)) {//...se o intervalo tiver somente um valor maximo...
        correct = (num < this.maxValue);//...verifica num...
      } else {//...se não houver intervalo...
        correct = true;//..."ignora" a checagem...
      }
      if (!correct) {//se não estiver dentro do intervalo...
        this.AddError(this.msgError);//...adiciona um erro
      } else if (correct && this.bufferError) {//se estiver dentro do intervalo e tiver erro...
        this.RemoveError();//...remove o erro
      }
    } else {//se não for Number...
      this.AddError(this.msgError);//...adiciona erro
      correct = false;
    }
    if (callback) {//se houver uma callback...
      callback(value, rtrn, this);//...chama ela
    }
    return correct;
  }
  ValidateKey() {//um superset do método ValidateKey de InputFilter, vai gerenciar a colocação do símbolo "-"
    let e = event || window.event;
    let key = e.keyCode || e.which;//pega o codigo da tecla
    if (this.allowSigned) {//se números negativos são permitidos...
      let minus = (this.element.value).includes("-");//...pega o valor da input e checa se existe o simbolo de menos...
      if (minus) {//...se tiver menos...
        if (key === 109 || key === 173) {//...sendo que a tecla pressionada é ela...
          if (e.preventDefault()) e.preventDefault();//...cancela o evento atual...
          return;//...e termina a função
        }
      }
    } else if (key === 109 || key === 173) {//se numeros negativos não forem permitidos e uma tecla "-" foi pressionada...
      if (e.preventDefault()) e.preventDefault();//...cancela o evento atual...
      return;//...e termina a função
    }
    super.ValidateKey();//chama o método ValidateKey() de InputFilter para outras validações
  }

  set MaxValue(e/*int*/) {
    if (typeof e === "number") this.maxValue = e;
  }
  get MaxValue() {
    return this.maxValue;
  }

  set MinValue(e/*int*/) {
    if (typeof e === "number") this.minValue = e;
  }
  get MinValue() {
    return this.minValue;
  }

  set MsgError(e/*string*/) {
    if (typeof e === "string") this.msgError = e;
  }
  get MsgError() {
    return this.msgError;
  }

  set AllowSigned(e/*bool*/) {
    if (typeof e === "boolean") this.allowSigned = e;
  }
  get AllowSigned() {
    return this.allowSigned;
  }
}

export class InputFloat extends InputInt {//um superset de InputInt que adiciona um gerenciamento de vírgulas e uma lista de teclas pré-definida (opcional)
  constructor(elemento/*HTMLElement*/, conjuntoErro/*[...{element: HTMLElement, class: string}]*/, errorAnchor/*HTMLElement*/, errorMsgStyle/*string*/, allowSigned = false/*bool*/, msgError = "Insira o valor corretamente"/*string*/, msgErrorFill = null/*string || null*/, vlMin = null/*Number*/, vlMax = null/*Number*/, keyExceptions /*[...{key: int, callback: function}]*/, keys = [8, 9, 13, 37, 39, 46, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 108, 109, 110, 116, 173, 188, 190]/*[...int]*/) {
    super(elemento, conjuntoErro, errorAnchor, errorMsgStyle, allowSigned, msgError, msgErrorFill, vlMin, vlMax, keyExceptions, keys);//chama o construtor de InputInt para colocar os atributos padrões
  }
  Validate(callback = null, callbackNull = null/*callback para ValidateFill*/, value = null/*string*/) {
    let num = (value) ? value : this.element.value;//aceita um valor forçado
    return super.Validate(callback, callbackNull, this._virgula(num));//chama o método Validate() de InputInt, fornece um valor tratado para vírgulas
  }
  ValidateKey() {//um superset do método ValidateKey de InputInt, vai gerenciar a colocação de virgulas
    let virgula = (this.element.value).includes(",") || (this.element.value).includes(".");//...pega o valor da input e checa se existe o simbolo de virgula ou ponto...
    if (virgula) {//...se tiver virgula ou ponto...
      let e = event || window.event;
      let key = e.keyCode || e.which;//pega o codigo da tecla
      if (key === 188 || key === 190 || key === 108 || key === 110) {//...sendo que a tecla pressionada é ela...
        if (e.preventDefault()) e.preventDefault();//...cancela o evento atual...
        return;//...e termina a função
      }
    }
    super.ValidateKey();//chama o método ValidateKey() de InputInt para outras validações
  }
  _virgula(str) {//se tiver uma virgula e números depois dela, troca ela por um ponto
    let i = str.indexOf(",");
    str = (i !== -1 && str.length > i+1) ? str.substring(0, i) + "." + str.substring(i + 1) : str;
    return str;
  }
}

export class InputRegex extends InputFilter {//testa se a entrada corresponde com a regex
  constructor(elemento/*HTMLElement*/, conjuntoErro/*[...{element: HTMLElement, class: string}]*/, errorAnchor/*HTMLElement*/, errorMsgStyle/*string*/, msgErrorFill = null/*string || null*/, msgError = "Insira o valor corretamente"/*string*/, regex/*RegExp*/, keyExceptions /*[...{key: int, callback: function}]*/, keys/*[...int]*/) {
    super(elemento, conjuntoErro, errorAnchor, errorMsgStyle, msgErrorFill, keyExceptions, keys);
    this.msgError = null;
    this.regex = null;
    if (msgError) this.MsgError = msgError;
    if (regex) this.Regex = regex;
  }
  Validate(callbackNome = null, callback = null/*function(short-hand-value, this)*/) {
    if (!this.regex.test(this.element.value)) {//se não for um nome válido
      this.AddError(this.msgError);
    } else {
      return super.Validate(callback);
    }
    if (callbackNome) callbackNome(this.element.value, this);
    return false;
  }

  set MsgError(e/*string*/) {
    if (typeof e === "string") this.msgError = e;
  }
  get MsgError() {
    return this.msgError;
  }

  set Regex(e/*RegExp*/) {
    if (typeof e === "object") this.regex = e;
  }
  get Regex() {
    return this.regex;
  }
}

export class InputFullName extends InputRegex {//superset pronto de InputRegex que aceita nomes completos
  constructor(elemento/*HTMLElement*/, conjuntoErro/*[...{element: HTMLElement, class: string}]*/, errorAnchor/*HTMLElement*/, errorMsgStyle/*string*/, msgErrorFill = null/*string || null*/, msgError = "Insira o valor corretamente"/*string*/, keyExceptions /*[...{key: int, callback: function}]*/, keys = [8, 9, 13, 27, 32, 37, 39, 46, 59, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 78, 79, 80, 81, 82, 83, 84, 85, 86, 87, 88, 89, 90, 116, 176, 192]/*[...int]*/) {
    super(elemento, conjuntoErro, errorAnchor, errorMsgStyle, msgErrorFill, msgError, /^[A-ZÀ-Ÿ][A-zÀ-ÿ']+\s([A-zÀ-ÿ']\s?)*[A-ZÀ-Ÿ][A-zÀ-ÿ']+\s*$/, keyExceptions, keys);
  }
}

export class InputEmail extends InputRegex {
  constructor(elemento/*HTMLElement*/, conjuntoErro/*[...{element: HTMLElement, class: string}]*/, errorAnchor/*HTMLElement*/, errorMsgStyle/*string*/, formId/*string*/, callbackOn/*function(string)*/, callbackOff/*function(string)*/, msgErrorFill = null/*string || null*/, msgError = "Insira o valor corretamente"/*string*/, keyExceptions /*[...{key: int, callback: function}]*/, keys/*[...int]*/) {
    super(elemento, conjuntoErro, errorAnchor, errorMsgStyle, formId, callbackOn, callbackOff, msgErrorFill, msgError, /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))\s*$/, keyExceptions, keys);//obrigado https://emailregex.com/
  }
}
//oh? você está se aproximando de mim? Ao invés de fugir você está indo diretamente a mim?
//Eu não posso te encher de mensagem de erro sem chegar perto.
//OOOOOHHH! então aproxime-se o quanto quiser.