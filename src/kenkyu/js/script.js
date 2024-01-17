
$(".txt1").bcralnit({
    width: '34px',
    background: '#e0ffff',
    color: '#cc52cc'
});


    ///////////////タブの切り替え機能/////////////////
window.onload = function() {
    //実行結果表示タブが最初に選択されているようにする
    var defaultTab = document.querySelector(".tablinks.active");
    var defaultTabContent = document.querySelector(".tabcontent.active");
    if (defaultTab && defaultTabContent) {
    defaultTabContent.style.display = "block";
}
}
    function openTab(evt, tabName) {
    //evt.preventDefault(); // フォーム送信を防止
    var i, tabcontent, tablinks;

    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
}

    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
}

    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
}

    //////////Tabが押されたときの処理////////////////////////////
function OnTabKey( e, obj ) {
    if (e.keyCode != 9) {
        return;
    }
    e.preventDefault();

    var cursorPosition = obj.selectionStart;
    var cursorLeft = obj.value.substr(0, cursorPosition);
    var cursorRight = obj.value.substr(cursorPosition, obj.value.length);

    obj.value = cursorLeft + "\t" + cursorRight;

    obj.selectionEnd = cursorPosition + 1;
}
document.getElementById( "program1" ).onkeydown = function( e ){ OnTabKey( e, this ); }
