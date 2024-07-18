jQuery(document).ready(function ($) {
  $(document).on('click', '[id^="add-field-"]', function (e) {
    e.preventDefault()
    var buttonId = $(this).attr('id').split('-')

    var id = buttonId[buttonId.length - 3]
    var arrays = buttonId[buttonId.length - 2]
    var suffix = buttonId[buttonId.length - 1]

    var formData = {
      action: 'add_array_form',
      nonce: add_array_form.nonce,
      id,
      arrays,
    }

    $.ajax({
      url: add_array_form.ajax_url,
      type: 'post',
      data: formData,
      success: function (response) {
        if (response.success) {
          $('#' + response.data.id + '-' + suffix).append(response.data.content)
        }
      },
      error: function (xhr, status, error) {},
      complete: function () {},
    })
  })

  $(document).on('click', '[class^="remove-field-"]', function (e) {
    e.preventDefault()

    var buttonClass = $(this).attr('class') // クリックされたボタンのクラスを取得
    var buttonClassArray = buttonClass.split(' ')
    var suffix = buttonClassArray[0].replace('remove-field-', '') // クラスからsuffixを取得
    // suffixと一致するdivを選択し削除
    $('#field-' + suffix).remove()
  })
})
