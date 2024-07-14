jQuery(document).ready(function ($) {
  $('form[id^="load-content-form-"]').on('submit', function (e) {
    e.preventDefault()

    var form = $(this)
    var shortcode = form.find('select[name="shortcode-select"]').val()

    var formData = {
      action: 'load_content',
      nonce: form_draw.nonce,
      'inner-transaciton-id': $(this).find('input[name="inner-transaciton-id"]').val(),
      'is-inner': $(this).find('input[name="is-inner"]').val(),
      shortcode: shortcode,
    }

    console.log(formData)

    $.ajax({
      url: form_draw.ajax_url,
      type: 'POST',
      data: formData,
      success: function (response) {
        if (response.success) {
          $('#transactions-' + response.data.id).append(response.data.content)
        } else {
          console.error('Error loading content')
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX error:', error)
      },
    })
  })

  $(document).on('click', '[class^="remove-transaction-"]', function (e) {
    e.preventDefault()

    var buttonClass = $(this).attr('class') // クリックされたボタンのクラスを取得
    var suffix = buttonClass.replace('remove-transaction-', '') // クラスからsuffixを取得

    // suffixと一致するdivを選択し削除
    $('#symbol-transaction-' + suffix).remove()
  })
})
