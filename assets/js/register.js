// Variables booléennes
let pseudo = false;
let email = false;
let rgpd = false;
let pass = false;

//on charge les éléments du formulaire
document.querySelector('#registration_form_nickname').addEventListener("input", checkPseudo);
document.querySelector('#registration_form_email').addEventListener("input", checkPseudo);
document.querySelector('#registration_form_agreeTerms').addEventListener("input", checkRgpd);
document.querySelector('#registration_form_plainPassword').addEventListener("input", checkPass);

function checkPseudo(){
    pseudo= this.value.length > 2;
    console.log(pseudo);
    checkAll()
    
}
function checkEmail(){
    let regex = new RegExp("\\S+@\\S+\\.\\S+"); // les + veulent dire qu'on peut avoir plusieurs caractères
    email = regex.test(this.value);
    console.log(email);
    checkAll()
}

function checkRgpd(){
    rgpd = this.checked;// devient true qd c'est check
    console.log(rgpd);
    checkAll()
}
function checkAll(){
    document.querySelector('#submit-button').setAttribute("disabled", "disabled");
   if( email && pseudo && rgpd){
    document.querySelector('#submit-button').removeAttribute("disabled", "disabled");
   }
} //il faut que je l'appelle à la fin de mes fonctions


// POur la vérification du mdp: CA passe par un calcul mathématique
// on passe par la table ASCII
const PasswordStrength = {
    STRENGTH_VERY_WEAK: 'Très faible',
    STRENGTH_WEAK: 'Faible',
    STRENGTH_MEDIUM: 'Moyen',
    STRENGTH_STRONG: 'Fort',
    STRENGTH_VERY_STRONG: 'Très fort',
}

function checkPass(){
    // on récupère le mdp tapé
    let mdp = this.value;

    // on récupère l'élément d'affichage de l'entropie dans le span:
    let entropyElement= document.getElementById("entropy");

    // on évamlue la force du mdp:, à l'aide de la function en lui passant la valeur de mdp 
    let entropy = evaluatePasswordStrength(mdp);

    entropyElement.classList.remove("text-red", "text-orange", "text-green");

    // on attribue la couleur en fonrion de l'entropy:
    switch(entropy){
        case 'Très faible':
            entropyElement.classList.add('text-red');
            pass = false;
            break;
        case'Faible':
            entropyElement.classList.add('text-red');
            pass = false;
            break;
        case  'Moyen':
            entropyElement.classList.add('text-orange');
            pass = false;
            break;
        case 'Fort':
            entropyElement.classList.add('text-green');
            pass = true;
            break;
        case 'Très fort':
            entropyElement.classList.add('text-green');
            pass = true;
            break;
        default: 
            entropyElement.classList.add('text-red');
            pass = false;
    }
    // le span prend la valeur d'entropy
    entropyElement.textContent = entropy;
}

function evaluatePasswordStrength(password){
    // on calcul la longueur du mdp:
    let length = password.length;

    // si il n'y a pas de length(pas de mdp)tu me renvoie Très faible
    if(!length){
        return PasswordStrength.STRENGTH_VERY_WEAK;
    }

    // on crée un objet qui contiendra les caractères et leur nombre
    let passwordChars = {};

    for(let index=0; index < password.length; index++){
        let charCode = password.charCodeAt(index); // récupère le code de la table ASCII POUR CHAQUE CARACT7RE
        console.log(charCode);
        passwordChars[charCode] = (passwordChars[charCode] || 0) + 1; // pour construire un tableau en fonction de chaque caractères, pour dire cb de fois j'ai un caractère

        // compte le nbre de caractères différent dans le mdp:
        let chars = Object.keys(passwordChars); // pour prendre unqiuement les clés des objets du tableau créer précèdement
        console.log(chars);

        // on initialise les var de types de caractères7
        let control = 0, digit = 0, upper = 0,lower=0, symbol=0, other=0;

        for(let [chr, count] of Object.entries(passwordChars)){
            chr = Number(chr); //je convertis en nbre
            if(chr<32 || chr===127){
                // caractère de control
                control = 33;
            } else if(chr>=48 || chr<=57){
                //chiffres
                digit = 10;
            } else if(chr>=65|| chr<=90){
                upper=26;
            } else if(chr>=97|| chr<=122){
                lower=26;
            } else if(chr>=128 |){
                other=128;
            } else {
                symbol = 33;
            }
        }
        // on calcul le pool de caractères:
        let pool = symbol + digit + upper + lower + control + other;

        //formule de l'entropie(formulle officielle):
        let entropy = chars * Math.log2(pool) + (length - chars) * Math.log2(chars);

        console.log(entropy);

        if(entropy >= 120){
            return PasswordStrength.STRENGTH_VERY_STRONG;
        } else if(entropy >= 100){
            return PasswordStrength.STRENGTH_STRONG;
        } else if(entropy >= 80){
            return PasswordStrength.STRENGTH_MEDIUM;
        }else if(entropy >= 60){
            return PasswordStrength.STRENGTH_WEAK;
        }else {
            return PasswordStrength.STRENGTH_VERY_WEAK;
        }
        
        
        
    }
}