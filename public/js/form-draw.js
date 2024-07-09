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

    $.ajax({
      url: form_draw.ajax_url,
      type: 'POST',
      data: formData,
      success: function (response) {
        if (response.success) {
          console.log(response.data)
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
})
