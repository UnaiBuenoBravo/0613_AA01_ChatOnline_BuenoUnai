function validaLoginName(){
    const contenedor = document.getElementById('campoNombre');
    const input = document.getElementById('inputUsername');
    const nombre = input.value.trim();
    const errorAnt = document.getElementById('errorUse');
    let parr = document.createElement("p");
    let error = "";

    if(errorAnt){
        input.classList.remove('errorInput');
        errorAnt.remove(); 

    }

    if(nombre.length === 0 || nombre === null || /^\s+$/.test(nombre)){
        error = "El nombre no puede estar vacio";

        parr.textContent = error;
        parr.id = 'errorUse';
        parr.classList.add('error');
        input.classList.add('errorInput')
        contenedor.appendChild(parr);
    }
    
    
return
}


function validaLoginCon(){
    const contenedor = document.getElementById('campoCon');
    const input = document.getElementById('inputCon');
    const con = input.value.trim();
    const errorAnt = document.getElementById('errorCon');
    let parr = document.createElement("p");
    let error = "";

    if(errorAnt){
        input.classList.remove('errorInput');
        errorAnt.remove(); 

    }

    if(con.length === 0 || con === null || /^\s+$/.test(con)){
        error = "El nombre no puede estar vacio";

        parr.textContent = error;
        parr.id = 'errorCon';
        parr.classList.add('error');
        input.classList.add('errorInput')
        contenedor.appendChild(parr);
    }
    
    
return
}


document.getElementById('inputUsername').onblur = validaLoginName;