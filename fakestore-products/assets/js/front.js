(function ($) {
  $(document).on('click', '.js-fakestore-random', function (e) {
    const $btn = $(this)
    const $box = $btn.closest('.fakestore-products-random')
    const $result = $box.find('.js-fakestore-result')

    $btn.prop('disabled', true)
    $result.html('<p>Loading…</p>')

    $.ajax({
      url: FakestoreProducts.ajaxUrl,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'fakestore_random_product',
        nonce: FakestoreProducts.nonce,
      },
      success: function (res) {
        if (!res || !res.success) {
          $result.html('<p>Request failed.</p>')

          return
        }
        $result.html(res.data.html || '')
      },
      error: function () {
        $result.html('<p>Network error.</p>')
      },
      complete: function () {
        $btn.prop('disabled', false)
      }
    })
  })
})(jQuery)