jQuery(document).ready(function ($) {
  // フォームにイベントリスナーを設定する関数
  function bindFormEvents(form) {
    // addressの入力内容が変更されたときのイベント
    form.find('input[name="address"]').on('input', function () {
      var transactionType = form.find('input[name="transaction_type"]').val()
      var address = $(this).val()

      // transactionTypeがmosaic_definitionで、addressが64文字の場合のみ処理を実行
      if (transactionType === 'mosaic_definition_transaction' && address.length === 39) {
        // Ajaxリクエストを送信
        $.ajax({
          type: 'POST',
          url: generate_mosaic_id.ajax_url,
          data: {
            action: 'generate_mosaic_id', // Ajax処理のハンドラー
            nonce: generate_mosaic_id.nonce,
            address: address, // addressを送信
          },
          success: function (response) {
            var mosaicId = response.data.mosaic_id
            form.find('input[id="mosaic_id-' + form.attr('id').replace('symbol-press-form-', '') + '"]').val(mosaicId)
            var mosaicNonce = response.data.nonce
            form
              .find('input[id="mosaic_nonce-' + form.attr('id').replace('symbol-press-form-', '') + '"]')
              .val(mosaicNonce)
          },
          error: function (error) {
            // エラー時の処理
            console.error('Ajax request error:', error)
          },
        })
      }
    })
  }

  // 既存の全てのフォームにイベントリスナーを設定
  $('form').each(function () {
    bindFormEvents($(this))
  })

  // 動的に追加されるフォームに対してもイベントリスナーを設定
  var observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      mutation.addedNodes.forEach(function (node) {
        if ($(node).is('form')) {
          bindFormEvents($(node))
        } else {
          $(node)
            .find('form')
            .each(function () {
              bindFormEvents($(this))
            })
        }
      })
    })
  })

  // DOMツリーの変更を監視
  observer.observe(document.body, {
    childList: true,
    subtree: true,
  })
})
