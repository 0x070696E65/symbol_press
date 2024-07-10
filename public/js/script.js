jQuery(document).ready(function ($) {
  $('form[id^="symbol-press-form-"]').on('submit', function (e) {
    var formId = $(this).attr('id')
    var formIdSuffix = formId.split('-').pop()

    e.preventDefault()

    $('#symbol-press-result-' + formIdSuffix).html('<div class="spinner"></div>')

    var formData = {
      action: 'send_transaction',
      nonce: symbol_press.nonce,
      transaction_type: $('#' + 'transaction_type-' + formIdSuffix).val(),
    }

    // 選択されているラジオボタンの値を取得
    $(this)
      .find('input[type="radio"]:checked')
      .not($(this).find('form input, form textarea, form select')) // ネストされたフォームの入力を除外
      .each(function () {
        formData[this.name] = $(this).val()
      })
    // 外部フォームのデータを収集
    $(this)
      .find('input, textarea, select')
      .not($(this).find('form input, form textarea, form select')) // ネストされたフォームの入力を除外
      .each(function () {
        formData[this.name] = $(this).val()
      })

    // 内部フォームのデータを収集
    formData['transactions'] = []
    $(this)
      .find('form')
      .each(function () {
        var innerFormData = {}
        $(this)
          .find('input, textarea, select')
          .each(function () {
            innerFormData[this.name] = $(this).val()
          })
        $(this)
          .find('input[type="radio"]:checked')
          .each(function () {
            innerFormData[this.name] = $(this).val()
          })
        formData['transactions'].push(innerFormData)
      })

    $.ajax({
      url: symbol_press.ajax_url,
      type: 'post',
      data: formData,
      success: function (response) {
        if (response.success) {
          $('#symbol-press-result-' + formIdSuffix).html(response.data.message)
          $('#explorer-link-' + formIdSuffix).html(response.data.explorer_link)
        } else {
          $('#symbol-press-result-' + formIdSuffix).html('<p>Error: ' + response.data + '</p>')
        }
      },
      error: function (xhr, status, error) {
        $('#symbol-press-result-' + formIdSuffix).html('<p>An error occurred: ' + error + '</p>')
      },
      complete: function () {
        $('#symbol-press-result-' + formIdSuffix + ' .spinner').remove()
      },
    })
  })
})
