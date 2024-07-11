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
      .not($(this).find('form input, form textarea, form select,.array_field input')) // ネストされたフォームの入力を除外
      .each(function () {
        formData[this.name] = $(this).val()
      })
    // 外部フォームのデータを収集
    $(this)
      .find('input, textarea, select')
      .not($(this).find('form input, form textarea, form select,.array_field input')) // ネストされたフォームの入力を除外
      .each(function () {
        formData[this.name] = $(this).val()
      })

    var arrayValues = $(this)
      .find('.array_field input')
      .not($(this).find('form input, form textarea, form select')) // ネストされたフォームの入力を除外
      .map(function () {
        return {
          name: $(this).attr('name'),
          value: $(this).val(),
        }
      })
      .get()

    var { key, result } = arrayToObject(arrayValues)
    formData[key] = result

    // 内部フォームのデータを収集
    formData['transactions'] = []
    $(this)
      .find('form')
      .each(function () {
        var innerFormData = {}
        $(this)
          .find('input, textarea, select')
          .not($(this).find('.array_field input'))
          .each(function () {
            innerFormData[this.name] = $(this).val()
          })
        $(this)
          .find('input[type="radio"]:checked')
          .each(function () {
            innerFormData[this.name] = $(this).val()
          })
        var arrayValues = $(this)
          .find('.array_field input')
          .not($(this).find('.array_field input'))
          .map(function () {
            return {
              name: $(this).attr('name'),
              value: $(this).val(),
            }
          })
          .get()
        var { key, result } = arrayToObject(arrayValues)
        innerFormData[key] = result
        formData['transactions'].push(innerFormData)
      })

    console.log(formData)

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

const arrayToObject = (array) => {
  // 結果を格納するオブジェクト
  var result = {}

  // 各入力値を処理して、結果オブジェクトに追加
  array.forEach(function (input) {
    // name 属性を - で分割
    var parts = input.name.split('-')
    var key = parts[0]
    var subKey = parts[1]
    var suffix = parts[2]

    // key がまだ存在しない場合は初期化
    if (!result[key]) {
      result[key] = []
    }

    // 同じ suffix を持つオブジェクトを見つけるか新しく作成
    var obj = result[key].find((o) => o._suffix === suffix)
    if (!obj) {
      obj = { _suffix: suffix }
      result[key].push(obj)
    }

    // subKey と値をオブジェクトに追加
    obj[subKey] = input.value
  })

  // _suffix プロパティを削除
  for (var key in result) {
    result[key].forEach(function (obj) {
      delete obj._suffix
    })
  }
  return { key, result: result[Object.keys(result)[0]] }
}
