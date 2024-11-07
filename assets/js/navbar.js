//par sécurité on peut mettre window.onload pour s'assurer que le dom est chargé:
window.onload = () => {
    const navButton = document.querySelector('#main-navbar .nav-button'); // on sélectionne la nav + le menu burger

    // si on cloque sur le btn on ouvre le menu:
    navButton.addEventListener('click', function(event){
        // on stroppe la propagation
        event.stopPropagation();

        // on ajoute la classe show ou on l'enleve à son prochain élément frère ou soeur cad <ul class="nav-menu show">:
        this.nextElementSibling.classList.toggle("show");
    });

    //si on clique n'importe ou on ferme le menu:
    document.addEventListener("click", closeNabbar);

    function closeNabbar(){
        navButton.nextElementSibling.classList.remove("show");
    }
}