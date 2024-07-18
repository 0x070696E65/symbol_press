const TEST_NET_EXPLORER = 'https://testnet.symbol.fyi'
const MAIN_NET_EXPLORER = 'https://symbol.fyi'

jQuery(document).ready(function ($) {
  $('form[id^="symbol-press-form-"]').on('submit', function (e) {
    e.preventDefault()

    var formId = $(this).attr('id')
    var formIdSuffix = formId.split('-').pop()

    var signModeValue = 'SSS'
    if ($(`#sign_mode-aLice-${formIdSuffix}`).is(':checked')) signModeValue = 'aLice'
    if (signModeValue == 'SSS' && window.SSS == undefined) {
      alert('SSS is not installed')
      return
    }

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
      .not($(this).find('form input, form textarea, form select, .array_field input, input[type="radio"]')) // ネストされたフォームの入力を除外
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
            console.log(this.name)
            console.log($(this).val())
            innerFormData[this.name] = $(this).val()
          })
        var arrayValues = $(this)
          .find('.array_field input')
          .not($(this).find('form .array_field input'))
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
      success: async function (response) {
        if (response.success) {
          try {
            if (signModeValue == 'SSS') {
              window.SSS.setTransactionByPayload(response.data.payload)
              const signedTx = await window.SSS.requestSign()
              const explorerLink = `<a href='${
                signedTx.networkType == 152 ? TEST_NET_EXPLORER : MAIN_NET_EXPLORER
              }/transactions/${signedTx.hash}' target='_blank'>To Explorer</a>`

              const address = window.SSS.activeAddress
              const wsnode = response.data.node.replace(/^https/, 'wss') + '/ws'
              const socket = new WebSocket(wsnode)

              socket.onopen = async function (event) {
                console.log('Connection opened')
              }

              socket.onmessage = async function (event) {
                const wsResponse = JSON.parse(event.data)
                if ('uid' in wsResponse) {
                  //"subscribe": "confirmedAdded/{address}"
                  const blockBody = `{"uid":"${wsResponse.uid}", "subscribe":"block"}`
                  socket.send(blockBody)

                  const confirmedAddedBody = `{"uid":"${wsResponse.uid}", "subscribe":"confirmedAdded/${address}"}`
                  socket.send(confirmedAddedBody)

                  const statusBody = `{"uid":"${wsResponse.uid}", "subscribe":"status/${address}"}`
                  socket.send(statusBody)

                  console.log('uid:', wsResponse)

                  var announcedRes = await fetch(`${response.data.node}/transactions`, {
                    method: 'PUT',
                    headers: {
                      'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ payload: signedTx.payload }),
                  })
                  if (!announcedRes.ok) {
                    throw new Error('Network response was not ok ' + response.statusText)
                  } else {
                    $('#explorer-link-' + formIdSuffix).html(explorerLink)
                  }
                } else {
                  console.log(wsResponse)
                  if (wsResponse.topic == `status/${address}`) {
                    $('#symbol-press-result-' + formIdSuffix).html(wsResponse.data.code)
                    socket.close()
                    $('#symbol-press-result-' + formIdSuffix + ' .spinner').remove()
                  } else if (wsResponse.topic == `confirmedAdded/${address}`) {
                    $('#symbol-press-result-' + formIdSuffix).html('transaction confirmed!')
                    socket.close()
                    $('#symbol-press-result-' + formIdSuffix + ' .spinner').remove()
                  }
                }
              }

              socket.onerror = function (error) {
                console.error('WebSocket error: ', error)
              }

              socket.onclose = function (event) {
                if (event.wasClean) {
                  console.log(`Closed cleanly, code=${event.code} reason=${event.reason}`)
                } else {
                  console.error('Connection died')
                }
              }
            } else {
              const url = `alice://sign?data=${response.data.payload}&type=request_sign_transaction&node=${utf8ToHex(
                response.data.node
              )}&method=announce`
              jQuery('#qrcode-' + formIdSuffix).qrcode(url)
              const aliceButton = `<div class="wp-block-button" style="text-align: center;">
                <button  onclick="window.location.href = '${url}';" class="wp-block-button__link has-text-align-center wp-element-button" id="to_alice">Sign by aLice</button>
              </div>`
              $('#symbol-press-result-' + formIdSuffix).html(aliceButton)
              $('#symbol-press-result-' + formIdSuffix + ' .spinner').remove()
            }
          } catch (e) {
            $('#symbol-press-result-' + formIdSuffix).html('<p>Error: ' + e.message + '</p>')
          }
        } else {
          $('#symbol-press-result-' + formIdSuffix).html('<p>Error: ' + response.data + '</p>')
        }
      },
      error: function (xhr, status, error) {
        console.log(error)
        $('#symbol-press-result-' + formIdSuffix).html('<p>An error occurred: ' + error + '</p>')
      },
    })
  })
})

function waitThreeSeconds(milliSecond) {
  return new Promise((resolve) => {
    setTimeout(() => {
      resolve('Waited for 3 seconds')
    }, milliSecond)
  })
}

const utf8ToHex = (str) => {
  // UTF-8文字列をバイト配列に変換
  const utf8 = new TextEncoder().encode(str)

  // バイト配列を16進数文字列に変換
  let hex = ''
  for (let i = 0; i < utf8.length; i++) {
    let byte = utf8[i].toString(16) // バイトを16進数文字列に変換
    if (byte.length < 2) {
      byte = '0' + byte // 必要に応じて0埋め
    }
    hex += byte
  }
  return hex
}
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
