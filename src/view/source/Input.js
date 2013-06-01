enyo.kind({
	name: "elang.input",
		components: [		
		{
			components:[]// Nous ins�rions ici des boutons afin de tester 
			// nos modifications, et ce avant de lier notre Input au reste 
			// de l'application. Ce n'est plus utile maintenant
		},
		
		{name:"result", components:[]}		// c'est ici que nous ins�rons les 
		//s�quences � la vol�e (inputs et textes)
	],
	
	published: {
		inputList: '',// Remplis depuis la liste des s�quences lors du clic
		gblID: '',// Permet de sauvegarder l'id lors du clic sur check et 		
		// help pour v�rifier les r�ponses
		inputCpt: 0,// Sert d'ID aux inputs. On l'a en global car on en a 		
		// besoin pour valider toute la sequence (une fois que tous est remplis).
		totalCheck: 0// Sert � compter les checks (on veut autant de check que 
		// d'input. Compar� � inputCpt
	},

	
	// Fonction appel�e par la Liste de S�quence(lors d'un clic sur une s�quence
	displaySequence: function(id)
	{
		this.reset();// On supprime l'ancienne s�quence
		for (x in this.inputList)
		{
			sequence = this.inputList[x];
			if (sequence.seq_id == id)// lors qu'on se positonne sur la bonne
			{
				for (y in sequence.content)
				{
					// On traite chaque contenu en fonction de son type
					seq_content = sequence.content[y];
					switch(seq_content.type)
					{
						// On utilise id-1 car les id commencent � 1
						// alors que les indexes des tableaux � 0
						case 'text' :
							this.addText(id-1, seq_content.content);
							break;
						case 'input' :
							this.addInput(id-1, '', y, this.inputCpt);
							this.inputCpt = this.inputCpt + 1;
							break;
					}
				}
			}
		}
	},
	
	// Fonction utilis�e pour ajouter du texte au r�sult
	addText: function(noSec, text) {
		this.$.result.createComponent({tag:"p", content: text });
		this.$.result.render();	// Le rendu est fait � chaque fois, on  aussi peut 
		// le faire une fois que tous est g�n�r�.
	},
	
	// Fonction utilis�e pour ajouter des inputs au r�sult
	addInput: function(noSec, content, ident, input_cpt) {
		this.$.result.createComponent(		
			// Le div est indispensable pour changer la couleur de l'input en fonction du check et help.
			// Nous avons g�n�r� les m�me id pour chacun des 3 �l�ments d'un input. Seul un premier caract�re
			// permet de les diff�rencier. Cette comodit� nous a bien aid�.
			{tag:'div', name :"divseq" ,classes:"input-append", id: 'd'+ noSec + '_' + ident, components: [
				{kind:"Input", id:noSec + '_' + ident + '_' + input_cpt ,  name:"Render", value:content},
				{tag:"button", classes:"btn btn-success", type:"button", ontap:"checkTapped", id:'c' + noSec + '_' + ident+ '_' + input_cpt, name:"Check", content:"Check"},
				{tag:"button", classes:"btn btn-info",    type:"button", ontap:"helpTapped",  id: 'h' + noSec + '_' + ident+ '_' + input_cpt, name:"Help",  content:"Help"},	
			]},
			{owner: this}// Indispensable pour que result (le parent) et les enfants cr��s se connaissent. Dans le cas contraire, impossible d'intercepter les onTap.
		);
		this.$.result.render();
	},

	// Fonction utilis�e avant chaque nouvelle s�quence pour supprimer le result
	reset: function() {
		this.$.result.destroy();// D�truit le result.
		// Nous avons essay� de d�truire les enfants,
		// mais impossible d'y parvenir. Seuls les 
		// textes se supprimaient, pas les input
		// ni les boutons. On recr�e donc un autre 
		// result vide une fois que tous est supprim�.
		
		// On remet � jour les globales
		this.setGblID('');
		this.setInputCpt(0);
		this.setTotalCheck(0);
		
		// On cr�e alors le nouveau result vide
		this.createComponent(
			{ name:"result", components:[]}
		);
		this.render();
	},		
		
	// Fonction appel�e lors d'un clic sur le bouton Check
	checkTapped: function(inSender, inEvent) {			
		var id = inSender.id.substr(1);//supprimer le c
		var tabID = id.split("_");
		var i = tabID[0];
		var j = tabID[1];
		var k = tabID[2];
		gblID = i + '_' + j;// On sauvegarde cet id pour v�rifier
		// On appelle enfin la requette AJAX en donnant les indentifiants
		// de l'input remplir ainsi que la valeur de ce dernier pour comparer
		this.verify(document.getElementById(id).getAttribute('value'), i, k);		
	},	
	
		
	// Fonction pour demander de v�rifier les infos de l'input
	verify: function(rep, seqId, inputId){
		// Request creation
		var request = new enyo.Ajax({
	    		url: document.URL,
				// url = "serveur.php";
	    		method: "POST", //"GET" or "POST"
	    		handleAs: "text", //"json", "text", or "xml"
	    	});	

		//tells Ajax what the callback function is
        request.response(enyo.bind(this, "getVerifyResponse")); 
		//makes the Ajax call with parameters		
		request.go({task: 'check', answer:rep, seq_id:seqId, input_id:inputId}); 
	},
	
	
	// Fonction pour v�rifier les infos de l'input
	getVerifyResponse: function(inRequest, inResponse){
		// If there is nothing in the response then return early.
		if (!inResponse) { 
	        alert('There is a problem, please try again later...');
	        return;
	    }
		var response = JSON.parse(inResponse);		
		// Broadcast the data to the children fields 
		var isOk = (response.check);
		if(isOk == 'true')
		{
			// On v�rifie si tous les check sont survenus ...
			this.totalCheck = this.totalCheck + 1;
			if (this.totalCheck == this.inputCpt)
			{
				// ... pour remonter une s�quence valide
				this.bubble('onValidSequence');
			}
			// On change le design en vert
			document.getElementById('d' + gblID).setAttribute('class', 'control-group success');
			//On bloque �galement lorsque le check est OK
			var child = document.getElementById('d' + gblID).firstChild;
			child.disabled = 'true';
			while(child.nextSibling != null)// Si on change l'ordre des Input et Boutons, osef ici
			{
				child = child.nextSibling;
				child.disabled = 'true';
			}		
		}
		else
			document.getElementById('d' + gblID).setAttribute('class', 'control-group error');
	},	
	
	
	// Fonction appel�e lors d'un clic sur le bouton Help
	helpTapped: function(inSender, inEvent) {		
		var id = inSender.id.substr(1);//supprimer le h
		var tabID = id.split("_");
		var i = tabID[0];
		var j = tabID[1];
		var k = tabID[2];
		gblID = i + '_' + j;// M�me chose
		// on appelle enfin la requette AJAX en ne donnant que 
		// les identifiants de l'input (on souhaite r�cup�rer
		// la valeur)
		this.help(i, k);
		// On fait remonter l'�v�nement � App.js pour Sequence.js.
		// A chaque clic sur help on r�cup�re pour logger son action
		this.bubble('onHelpTapped');
	},		
	
		
	// Function AJAX pour demander de r�cup�rer les infos de l'input
	help: function(seqId, inputId){
		// Request creation
		var request = new enyo.Ajax({
	    		url: document.URL,
				// url = "serveur.php";
	    		method: "POST", //"GET" or "POST"
	    		handleAs: "text", //"json", "text", or "xml"
	    	});	

		//tells Ajax what the callback function is
        request.response(enyo.bind(this, "getResponse")); 
		request.go({task: 'help', seq_id:seqId, input_id:inputId}); 
	},
	
	
	// Function AJAX pour demander de r�cup�rer les infos de l'input
	getResponse: function(inRequest, inResponse){
		// If there is nothing in the response then return early.
		if (!inResponse) { 
	        alert('There is a problem, please try again later...');
	        return;
	    }				
		var response = JSON.parse(inResponse);		
		// Broadcast the data to the children fields 
		var reponsehelp = (response.help);
		// On change le design en bleu
		document.getElementById('d' + gblID).setAttribute('class', 'control-group info');	
		var child = document.getElementById('d' + gblID).firstChild;		
		if (child.toString() == '[object HTMLInputElement]')
		{	
			// On affiche la r�ponse bloqu�e � l'�tudiant
			child.value=reponsehelp;
			child.disabled = 'true';
			while(child.nextSibling != null)// Pareil, osef
			{
				child = child.nextSibling;
				child.disabled = 'true';
			}
		}				
	}
	
	
});

