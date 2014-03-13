var syntax = {};
syntax.check = function(selector){
  var html = $(selector).val();
  var matches = html.match(/<!--(\/{0,1}[a-zA-Z0-9_]+)-->/g);
  if (!matches.length) {
    return ;
  }
  var html_index = 0;
  var syntaxes = [];
  for (var i=0;i < matches.length;i++) {
    html_index = html.indexOf(matches[i], html_index);
    // 構文判定
    if (!this.in_array(matches[i].match(/<!--\/{0,1}(.*?)-->/)[1])) {
      continue ;
    }
    // 開始タグと終了タグの判定チェック
    var s = matches[i].match(/<!--(.*?)-->/)[1];
    if (s[0]=='/') {
      // 閉じタグ判定
      if (s!='/'+syntaxes[syntaxes.length-1]) {
        // 警告用HTML作成
        var now_number = this.get_row_number(html, html_index);
        var start_index = Math.max(0, html.lastIndexOf("\n", html_index));
        var end_index = html.indexOf("\n", html_index);
        end_index = end_index == -1 ? html.length : end_index;
        var line_html = html.substring(start_index+1, end_index);
        var left_html = line_html.substring(0, html_index-start_index-1);
        var middle_html = '<!--' + s + '-->';
        var right_html = line_html.substr(left_html.length+middle_html.length);
        // 前後2行分取得
        start_index = Math.max(0, html.lastIndexOf("\n", start_index-1));
        start_index = Math.max(0, html.lastIndexOf("\n", start_index-1)+1);
        end_index = html.indexOf("\n", end_index+1);
        end_index = end_index == -1 ? html.length : end_index;
        end_index = html.indexOf("\n", end_index+1);
        end_index = end_index == -1 ? html.length : end_index;
        // 行列の番号を取得
        var number = this.get_row_number(html, start_index);
        // 警告用メッセージ作成
        var warning = html.substring(start_index, end_index);
        var warnings = warning.split("\n");
        var warning_html = '<p class="error">「' + s + '」の開始タグがありません</p><table>';
        if (syntaxes.length) {
          warning_html = '<p class="error">「'+ syntaxes[syntaxes.length-1] +'」が閉じられる前に「' + s + '」が閉じています</p><table>';
        }
        for (var w=0;w<warnings.length;w++) {
          if (number==now_number) {
            warning_html += '<tr><th>'+(number++)+'</th><td>'+this.h(left_html)+'<span style="color: red;">'+this.h(middle_html)+'</span>'+this.h(right_html)+'</td></tr>';
          } else {
            warning_html += '<tr><th>'+(number++)+'</th><td style="background-color: #ddd;">'+this.h(warnings[w])+'</td></tr>';
          }
        }
        warning_html += '</table>';
        syntax.error_display(selector, warning_html);
        return ;
      }
      syntaxes.pop();
    } else {
      syntaxes.push(s);
    }
  }
  if (syntaxes.length) {
    syntax.error_display(selector, '<p class="error">'+syntaxes[syntaxes.length-1] + 'が閉じられていません<p>');
    return ;
  }
  syntax.error_display(selector, null);
};
syntax.error_display = function(selector, html){
  if ($(selector).next('div.syntax-error').length) {
    $(selector).next('div.syntax-error').remove();
  }
  if (html==null) {
    // エラー文非表示
    return ;
  }
  $(selector).after('<div class="syntax-error">'+html+'</div>');
}
syntax.get_row_number = function(html, number_index){
  var number = 1;
  while((number_index=html.lastIndexOf("\n", number_index-1))!=-1){
    number++;
  }
  return number;
};
syntax.in_array = function(val){
  for (var i=0;i<template_syntaxes.length;i++) if (template_syntaxes[i]==val) return true;
  return false;
};
syntax.h = function(text){
  text = text.replace(/&/g,"&amp;");
  text = text.replace(/"/g,"&quot;");
  text = text.replace(/'/g,"&#039;");
  text = text.replace(/</g,"&lt;");
  text = text.replace(/>/g,"&gt;");
  text = text.replace(/ /g,"&nbsp;");
  return text;
};

