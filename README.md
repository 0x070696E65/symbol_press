# インストール

```sh
git clone https://github.com/0x070696E65/symbol_press.git
```

`symbol_press`フォルダを以下に移動

```
/wordpress/wp-content/plugins/
```

`symbol_press`ディレクトリにて

```sh
composer install
```

を実行する。

WordPress 管理画面よりプラグインの有効化

# 初期設定

プラグインを有効化したらサイドバーより[Symbol Press]をクリックして初期設定を行います。

| 項目             | 詳細                                                                                                          |
| ---------------- | ------------------------------------------------------------------------------------------------------------- |
| Node             | 接続ノードです。SSL 接続しますので https://node_url.com:3001 のような形式です。最後のスラッシュなども不要です |
| Fee Multiplier   | 手数料乗数、Transaction サイズに対する手数料の係数です。デフォルト - 100                                      |
| Deadline Seconds | デッドライン、現在時刻から経過後秒数。デフォルト - 3600                                                       |

# 使用方法

Symbol の Transaction を WordPress のショートコードでページ等に配置できるようになります。

*現在は、アグリゲートボンデッド Transaction には対応していません。<br>
*アグリゲートコンプリートの場合でも署名者が起案者の場合のみ利用できます（複数署名が必要な場合は利用不可）

[transfer_tranasction]のようなショートコードで Transaction フォーム を設置できます。

それぞれ属性を設定することが可能で、これによりトランザクション内容を固定できます。

```js
[transfer_transaction sign_mode="SSS" recipient_address="TBRPLIEF2QL7KEYSTKQSP2YWEOAFCO5AGWWAWQQ" mosaic_id-0="72C0212E67A08BCE" mosaic_amount-0="1" message="hello, symbol!"]
```

例えば上記ケースだとラベルと送信ボタンのみが表示され、ボタンクリックで SSS が起動します。

以下にリファレンスを掲載します。

# ショートコードリファレンス

## 共通

| 属性名            | 詳細                                                                 |
| :---------------- | :------------------------------------------------------------------- |
| sign_mode         | 署名モード設定、これを設定しなければユーザーの選択式になる           |
| label             | ラベル、null にすれば空になる                                        |
| button_text       | 決定ボタンのテキスト                                                 |
| button_color      | 決定ボタンの背景色、16 進数 ex #000                                  |
| has_add_button    | アグリゲートコンプリートのみ、Transaction 追加ボタンを表示するか否か |
| signer_public_key | インナートランザクションのみ、署名者の公開鍵固定                     |

## TransferTransaction

| 属性名            | 詳細                                                  |
| :---------------- | :---------------------------------------------------- |
| recipient_address | 受信者                                                |
| message           | 固定メッセージ                                        |
| mosaic_id-0       | モザイク ID ※末尾の番号を増やすことで複数モザイク送信 |
| mosaic_amount-0   | モザイク量                                            |

## MosaicDefinitionTransaction

| 属性名         | 詳細                                               |
| :------------- | :------------------------------------------------- |
| address        | モザイク作成者アドレス                             |
| supply_mutable | 増減可能か                                         |
| transferable   | 転送可能か                                         |
| restrictable   | 制限可能か                                         |
| revokable      | リボーカルブルかどうか                             |
| mosaic_id      | モザイク ID 但しアドレスが設定されている場合は自動 |
| mosaic_nonce   | ナンス                                             |
| duration       | 期間                                               |
| divisibility   | 可分性                                             |

## MosaicSupplyChangeTransaction

| 属性名    | 詳細                 |
| :-------- | :------------------- |
| mosaic_id | モザイク ID          |
| action    | increase or decrease |
| delta     | 増減量               |

## MosaicSupplyRevocationTransaction

| 属性名         | 詳細         |
| :------------- | :----------- |
| source_address | 対象アドレス |
| mosaic_id      | モザイク ID  |
| amount         | 量           |

## AggregateCompleteTransaction

| 属性名         | 詳細                                           |
| :------------- | :--------------------------------------------- |
| has_add_button | インナートランザクション追加ボタンの表示非表示 |

## MultisigAccountModificationTransaction

| 属性名             | 詳細              |
| :----------------- | :---------------- |
| min_removal_delta  | 最小削除数        |
| min_approval_delta | 最小承認数        |
| address_additions  | 追加アドレス 配列 |
| address_deletions  | 削除アドレス 配列 |

## AccountMetadataTransaction

| 属性名              | 詳細           |
| :------------------ | :------------- |
| target_address      | 対象アドレス   |
| scoped_metadata_key | メタデータキー |
| value               | 値             |

## MosaicMetadataTransaction

| 属性名              | 詳細            |
| :------------------ | :-------------- |
| target_address      | 対象アドレス    |
| target_mosaic_id    | 対象モザイク ID |
| scoped_metadata_key | メタデータキー  |
| value               | 値              |

## NamespaceMetadataTransaction

| 属性名              | 詳細                  |
| :------------------ | :-------------------- |
| target_address      | 対象アドレス          |
| target_namespace_id | 対象ネームスペース ID |
| scoped_metadata_key | メタデータキー        |
| value               | 値                    |

## NamespaceRegistrationTransaction

| 属性名    | 詳細                                                |
| :-------- | :-------------------------------------------------- |
| name      | ネームスペース名                                    |
| duration  | 期間                                                |
| parent_id | ルートの場合は'blank'を与えるとフォーム非表示になる |

## AddressAliasTransaction

| 属性名       | 詳細              |
| :----------- | :---------------- |
| namespace_id | ネームスペース ID |
| address      | 対象アドレス      |
| alias_action | link or unlink    |

## MosaicAliasTransaction

| 属性名       | 詳細              |
| :----------- | :---------------- |
| namespace_id | ネームスペース ID |
| mosaic_id    | 対象モザイク ID   |
| alias_action | link or unlink    |

## HashLockTransaction

| 属性名        | 詳細           |
| :------------ | :------------- |
| mosaic_id     | モザイク ID    |
| mosaic_amount | モザイク量     |
| duration      | 期間           |
| hash          | ハッシュタイプ |

## MosaicAddressRestrictionTransaction

| 属性名                     | 詳細         |
| :------------------------- | :----------- |
| mosaic_id                  | モザイク ID  |
| restriction_key            | 制限キー     |
| previous_restriction_value | 旧制限値     |
| new_restriction_value      | 新制限値     |
| target_address             | 対象アドレス |

## MosaicGlobalRestrictionTransaction

| 属性名                     | 詳細         |
| :------------------------- | :----------- |
| mosaic_id                  | モザイク ID  |
| restriction_key            | 制限キー     |
| previous_restriction_value | 旧制限値     |
| new_restriction_value      | 新制限値     |
| target_address             | 対象アドレス |

## AccountKeyLinkTransaction

| 属性名            | 詳細           |
| :---------------- | :------------- |
| linked_public_key | リンク公開鍵   |
| link_action       | link or unlink |

## NodeKeyLinkTransaction

| 属性名            | 詳細           |
| :---------------- | :------------- |
| linked_public_key | リンク公開鍵   |
| link_action       | link or unlink |

## VotingKeyLinkTransaction

| 属性名            | 詳細           |
| :---------------- | :------------- |
| linked_public_key | リンク公開鍵   |
| link_action       | link or unlink |

## VrfKeyLinkTransaction

| 属性名            | 詳細           |
| :---------------- | :------------- |
| linked_public_key | リンク公開鍵   |
| link_action       | link or unlink |

※以下トランザクション属性は準備中、フォームは利用可能

## AccountAddressRestrictionTransaction

## AccountMosaicRestrictionTransaction

## AccountOperationRestrictionTransaction
