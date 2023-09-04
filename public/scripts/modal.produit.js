$(document).ready(() => {

    $(".produit-modal").click(async (event) => {

        event.preventDefault(); // Evite de reprendre le path par defaut

        const href = event.currentTarget.href; // Prendre la page actuel et garder les donnees
        console.log(href);

        const response = await axios.get(href);
        if (response.status === 200) {
            $("#produit-modal-contenu").html(response.data);
            const produitViewModal = new bootstrap.Modal(document.getElementById('produit-modal'), {});
            produitViewModal.show();
        }

    });

})