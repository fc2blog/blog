$(function(){
  // ページ件数 or ページ数を変更した際に自動でサブミット
  $('select[name=limit]').on('change', function(){
    $('input[name=limit]').val($(this).val());
    $('#sys-search-form').submit();
  });
  // ページ数初期化有無フラグ
  var isPageChange = false
  $('select[name=page]').on('change', function(){
    $('input[name=page]').val($(this).val());
    isPageChange = true;
    $('#sys-search-form').submit();
  });
  // ページ数変更以外からのsubmitはページ数を初期化する
  $('#sys-search-form').submit(function(){
    if (isPageChange==false) {
      $('input[name=page]').val(0);
    }
  });
});
// 順序変更用関数
function orderChange(order){
  var now = $('input[name=order]').val();
  // 現在の順序と同じ場合はdescとascを逆順に変更する
  if (now==order) {
    var matches = order.match('^(.*?)([^_]+)$');
    if (matches && matches.length==3){
      order = matches[1] + (matches[2]=='desc' ? 'asc' : 'desc')
    }
  }
  $('input[name=order]').val(order);
  $('#sys-search-form').submit();
}
