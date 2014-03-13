/**
* 共通関数
*/

var common = {};
common.isURLRewrite = false;
common.baseDirectory = '/';
common.deviceType = 1;  // PC
common.deviceArgs = '';
common.isPC = function(){
  return common.deviceType == 1;
}
common.isSP = function(){
  return common.deviceType == 4;
}

// Form内でEnterを押してもSubmitさせない
common.formEnterNonSubmit = function(id){
  $('#' + id + ' input').keypress(function(e){
    if ((e.which && e.which===13) || (e.keyCode && e.keyCode===13)) {
      return false;
    }
    return true;
  });
};

// jsでのフレームワーク用URL生成
common.fwURL = function(controller, action, params){
  if (common.isURLRewrite) {
    var url = common.baseDirectory + controller + '/' + action;
    var args = [];
    for (var i in params) {
      args.push(i + "=" + common.rawurlencode(params[i]));
    }
    if (common.deviceArgs) {
      args.push(common.deviceArgs);
    }
    if (args.length) {
      return url + '?' + args.join('&');
    }
    return url;
  }
  var url = common.baseDirectory + "index.php?";
  url += "&mode=" + controller;
  url += "&process=" + action;
  var args = [];
  for (var i in params) {
    args.push(i + "=" + common.rawurlencode(params[i]));
  }
  if (common.deviceArgs) {
    args.push(common.deviceArgs);
  }
  if (args.length) {
    url += '&' + args.join('&');
  }
  return url;
};

// 同一テーブル内のチェックボックスを全てチェック
common.fullCheck = function(target){
  $(target).closest('table').find('input[type=checkbox]').prop('checked', $(target).prop('checked')).trigger('change');
}

// rawurlencode
common.rawurlencode = function(str){
  return encodeURIComponent(str)
    .replace(/!/g,  "%21")
    .replace(/'/g,  "%27")
    .replace(/\(/g, "%28")
    .replace(/\)/g, "%29")
    .replace(/\*/g, "%2A")
    .replace(/~/g,  "%7E");
}
