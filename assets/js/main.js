function initSemanticUI() {
    // Initialisation Fomantic UI
    $('.ui.mini.modal').modal();
    $('.ui.dropdown').dropdown();
    $('.menu .item').tab();

    // Toggle formulaire de recherche

    $('.searchModal').css('display', 'none')
}

function toast() {
    console.log("ok")
    $.toast({
        class: 'success',
        message: 'Les étapes ont bien été enregistrées',
        showIcon: true,
        showProgress: 'bottom',
    })
}

// Initialisation au chargement de la page Turbo
document.addEventListener('turbo:load', initSemanticUI);