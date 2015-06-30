
var fields = new Array();
var fieldArray = function (url, icon, start, count, code, common, iconCommon){
    this.url = url || '';
    this.icon = icon || '';
    this.start = start || '';
    this.count = count || '';
    this.code = code || '';
    this.common = common? 'checked' : '';
    this.iconCommon = iconCommon? 'checked' : '';
}
function addFieldDOM(number){
    var rss_field = document.getElementById('rss-field');
    var field = document.createElement('div');
    field.id = 'field'+ number;
    field.style.margin = '0 0 20px 0';
    field.innerHTML = 'url<input name=save_field['+ number +'][url] type="text" size="60" value="'+ fields[number].url +'"/><br />' +
        'icon(画像URL)<input name=save_field['+ number +'][icon] type="text" size="60" value="'+ fields[number].icon +'"/><br />' +
        'start<input name=save_field['+ number +'][start] type="text" size="5" value="'+ fields[number].start +'"/><br />' +
        'count<input name=save_field['+ number +'][count] type="text" size="5" value="'+ fields[number].count +'"/><br />' +
        'code<br /><textarea style="width:80%; height:60px;" name=save_field['+ number +'][code]>'+ fields[number].code +'</textarea><br />' +
        '<label style="margin-right:20px;"><input type="checkbox" name=save_field['+ number +'][common] value="true" '+ fields[number].common +'>共通code</label>' +
        '<label><input type="checkbox" name=save_field['+ number +'][iconCommon] value="true" '+ fields[number].iconCommon +'>共通icon</label><br />' +
        '<input type="button" onclick="fieldUp(\''+ number +'\')" value="上へ" />' +
        '<input type="button" onclick="fieldDown(\''+ number +'\')" value="下へ" />' +
        '<input type="button" onclick="deleteField(\''+ number +'\')" value="削除" />';
    rss_field.appendChild(field);
}
function deleteFieldDOM (){
    var rss_field = document.getElementById('rss-field');
    rss_field.innerHTML = '';
}
function tmpField(){
    for(i = 0;i < fields.length; i++){
        fields[i].url = document.getElementsByName('save_field['+ i +'][url]')[0].value;
        fields[i].icon = document.getElementsByName('save_field['+ i +'][icon]')[0].value;
        fields[i].start = document.getElementsByName('save_field['+ i +'][start]')[0].value;
        fields[i].count = document.getElementsByName('save_field['+ i +'][count]')[0].value;
        fields[i].code = document.getElementsByName('save_field['+ i +'][code]')[0].value;
        fields[i].common = document.getElementsByName('save_field['+ i +'][common]')[0].checked ? 'checked' : '' ;
        fields[i].iconCommon = document.getElementsByName('save_field['+ i +'][iconCommon]')[0].checked ? 'checked' : '' ;
    }
}
function refreshFieldDOM(){
    deleteFieldDOM();
    for(i = 0;i < fields.length; i++){
        addFieldDOM(i);
    }
}
function addField(url, icon, start, count, code, common, iconCommon, addNumber) {
    addNumber = isNaN(addNumber)? false : addNumber;
    tmpField();
    if (addNumber === false) {
        fields.push(new fieldArray(url, icon, start, count, code, common, iconCommon));
    } else{
        fields.splice(parseInt(addNumber), 0, new fieldArray(url, icon, start, count, code, common, iconCommon));
    }
    refreshFieldDOM();
}
function deleteField (number){
    tmpField();
    fields.splice(parseInt(number),1);
    refreshFieldDOM();
}
function fieldUp(number){
    if (number != 0) {
        tmpField();
        var remove = fields.splice(parseInt(number), 1);
        fields.splice(parseInt(number) - 1, 0, remove[0]);
        refreshFieldDOM();
    }
}
function fieldDown(number){
    tmpField();
    var remove = fields.splice(parseInt(number),1);
    fields.splice(parseInt(number) + 1,0,remove[0]);
    refreshFieldDOM();
}
function changeLivedoorFormat(){
    tmpField();
    for(i = 0; i < fields.length; i++){
        document.getElementsByName('save_field['+ i +'][common]')[0].checked = 'check';
    }
    addField('layout', '上部分（一番上に設置）　※消さないでください ライブドア変更用タグです', '', '', '<div class="blogroll-channel"><ul class="blogroll-list-wrap">', '', '',0);
    addField('layout', '下部分（一番下に設置）　※消さないでください ライブドア変更用タグです', '', '', '</ul></div>', '', '', false);
    var Element = document.getElementById('commonCode');
    Element.innerHTML = '<li class="blogroll-list"><a title="<$title>" class="blogroll-link" href="<$link>" target="_blank"><$title></a></li>';
}
function changeLivedoorIconFormat(){
    tmpField();
    for(i = 0; i < fields.length; i++){
        document.getElementsByName('save_field['+ i +'][common]')[0].checked = 'check';
        document.getElementsByName('save_field['+ i +'][iconCommon]')[0].checked = 'check';
    }
    addField('layout', '上部分（一番上に設置）　※消さないでください ライブドア変更用タグです', '', '', '<div class="blogroll-channel"><ul class="blogroll-list-wrap">', '', '',0);
    addField('layout', '下部分（一番下に設置）　※消さないでください ライブドア変更用タグです', '', '', '</ul></div>', '', '', false);
    var Element = document.getElementById('commonCode');
    Element.innerHTML = '<li class="blogroll-list"><img class="blogroll-icon" src="<$icon>"><a title="<$title>" class="blogroll-link" href="<$link>" target="_blank"><$title></a></li>';
}