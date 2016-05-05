# yamareco-fbmessengerbot

Yamareco APIを使って、山の情報を検索するFacebook Messenger Bot
PHP on Heroku、HTTPSを処理できる普通にPHPが動作するサーバで動作

以下のFacebookページのBot
[山の情報を調べて返すBot](https://www.facebook.com/letsgoclimbing/)

## していること

* Facebook Messenger Platform
  * Welcome Messageの設定
  *　User Profileの取得（※未保存）
  * Webhook(Text, Payload) ※Imageは未使用
  * Send Messaege(Text, Generic Template, Button Template) ※Image, Receiptは未使用

* Yamareco API
  * poi情報の検索（地名、エリア指定、データ種別指定）※ジャンルは未使用
  * 近隣情報検索
  * エリア情報の取得
  * データ種別情報の取得

## できること

* 検索
　　* キーワード指定
　　* 緯度経度による近隣情報
　　* エリア指定
　　* データ種別指定

* 表示方法
　　* ヤマレコ表示
　　* Google Map表示
