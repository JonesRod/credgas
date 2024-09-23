function toggleMenu() {
    $('#imenu').toggleClass('aberto');
}
function abrirNaDiv(link) {
    var div = document.getElementById('iconteudo');
    div.innerHTML = '<object type="text/html" data="' + link + '" style="width:100%; height:100%;">';
}