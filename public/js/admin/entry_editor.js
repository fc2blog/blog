// メディア追加関数
var addMedia = {
  target_id: null,
  elrte: true,    // elRTEの使用,不使用
  init: function(){
    if (this.target_id!=null) {
      return ;
    }
    // 検索ボタン
    $('#sys-add-media-search-button').on('click', function(){
      var keyword = $('#sys-add-media-search-keyword').val();
      $('#sys-add-media-load').load(addMedia.load({keyword: keyword}));
    });
    // Enterキーで検索
    $('#sys-add-media-search-keyword').keypress(function(e){
      if ((e.which && e.which===13) || (e.keyCode && e.keyCode===13)) {
        $('#sys-add-media-search-button').click();
      }
    });
    // リサイズ時に幅を変更
    $(window).on('resize', function(){
      $('#sys-add-media-dialog').dialog('option', {width: $(window).width() - 100});
    });
  },
  load: function(params){
    $('#sys-add-media-load').fadeOut('fast', function(){
      $('#sys-add-media-load').load(common.fwURL('Entries', 'ajax_media_load', params), function(){
        $('#sys-add-media-load').fadeIn('fast');
        $('#sys-add-media-load').find('input[type=checkbox]').on('click', function(){
          if ($(this).prop('checked')) {
            $(this).closest('li').addClass('selected');
          } else {
            $(this).closest('li').removeClass('selected');
          }
        })
      });
    });
  },
  open: function(key, config){
    addMedia.init();    // 初期化処理
    addMedia.target_id = 'sys-entry-' + key;
    addMedia.elrte = config.elrte;

    var buttons = {};
    buttons[config.Add] = function(e){
      addMedia.add();
    };
    var option = {
      modal: true,
      dialogClass: 'no-title',
      resizable: false,
      draggable: false,
      width: $(window).width() - 100,
      position: ['center', 50],
      buttons: buttons
    };
    // スマフォ用のパラメータ調整
    if (common.isSP()) {
      option['width'] = $(window).width() - 20;
      option['position'] = ['center', 10];
      option['buttons'] = null;
    }
    $('#sys-add-media-dialog').dialog(option);
    $('#sys-add-media-load').html('<p>Now loading...</p>');
    $('#sys-add-media-load').load(addMedia.load({}));
  },
  add: function(){
    var textarea_id = addMedia.target_id;
    var insert_tag = '';
    $('.sys-form-add-media input[type=checkbox]:checked').parent().prev().find('img').each(function(a){
      insert_tag += '<img src="' + $(this).attr('src') + '" />';
    });

    if (!addMedia.elrte || $('#' + textarea_id).elrte()[0].style.display!='none') {
      // textarea時
      addMedia.insertText(textarea_id, insert_tag);
      $('#' + textarea_id).focus();
      $('#sys-add-media-dialog').dialog('close');
      return ;
    }

    // elRTE時
    var rte = $('#' + textarea_id).elrte()[0].elrte;
    rte.browser.msie && rte.selection.restoreIERange();
    rte.history.add();
    rte.selection.selectContents().deleteContents().insertText(insert_tag);
    rte.ui.update();
    rte.window.focus();
    $('#sys-add-media-dialog').dialog('close');
  },
  // 位置情報取得
  getSelection: function(dom) {
    var pos = {};
    if (/*@cc_on!@*/false) {
      dom.focus();
      var range = document.selection.createRange();
      var clone = range.duplicate();

      clone.moveToElementText(dom);
      clone.setEndPoint('EndToEnd', range);

      pos.start = clone.text.length - range.text.length;
      pos.end = clone.text.length - range.text.length + range.text.length;
    } else if (window.getSelection()) {
      pos.start = dom.selectionStart;
      pos.end = dom.selectionEnd;
    }
    return pos;
  },
  // Textを挿入
  insertText: function(id, tag) {
    var closeTag = arguments.length == 3 ? arguments[2] : null;
    var target = document.getElementById(id);
    var pos = addMedia.getSelection(target);

    var value = target.value;
    var range = value.slice(pos.start, pos.end);
    var beforeNode = value.slice(0, pos.start);
    var afterNode = value.slice(pos.end);

    if (closeTag==null) {
      target.value = beforeNode + tag + afterNode;
    } else if (range || pos.start != pos.end) {
      target.value = beforeNode + tag + range + closeTag + afterNode;
    } else if (pos.start == pos.end) {
      target.value = beforeNode + tag + closeTag + afterNode;
    }
  }
};

// 画面外クリック時にダイアログを閉じる処理
$(document).on('click', '.ui-widget-overlay', function(){
  $(this).prev().find('.ui-dialog-content').dialog('close');
});

