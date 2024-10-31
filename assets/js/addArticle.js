// pour afficher un apercu de l'image upload

document.getElementById("add_post_form_featuredImage").addEventListener("change", checkfile);

function checkfile(){
    let preview = document.querySelector(".preview");
    let image = previwew.querySelector("img");
    let file = this.files[0];
    console.log(this.files);
    const types = ["image/jpeg", "image/png", "image/webp"];
    let reader = new FileReader(); //pour lire des fichiers on va chagé cette classe et on va générer un événement qui s'appelle onloadend(j'ai fini de lire mon fichier) on va donner le result de cette class à image.src et on change le display de la div
    reader.onloadend = function(){
        console.log(reader);
        image.src = reader.result;
        preview.style.display= "block";
        
    }
    // On vérifie qu'un fichier existe: 
    if(file){
        // on vérifie que le fichier est bien une image accepté avec le type; si mon tableau contient le type de fichier inclus dedans, dans ce cas la je charge mon fichier 
        if(types.includes(file.type)){
            reader.readAsDataURL(file);
        }
    }else {
        image.src = "";
        previwew.style.display = "none"
    }
    
}