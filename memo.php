<?php

//日付を残すため、タイムゾーンを日本時間に設定
date_default_timezone_set('Asia/Tokyo');

//配列の初期化
$memos = [];

// 関数宣言

function createMemo(){
  //メモを書く(各値を変数に格納)
  echo 'タイトル：';
  $title = trim(fgets(STDIN));
  echo 'メモ：';
  $content = trim(fgets(STDIN));
  echo PHP_EOL.'メモを保存しました！'.PHP_EOL.PHP_EOL;
  //メモした時間を変数に格納
  $time = date('Y-m-d H:i:s');

  //各変数をデータベースに追加

  //---------- データベース接続プロセス開始 ----------
  try{
    // 接続処理
    $dbh = new PDO(
      'mysql:host=db;dbname=book_log;charset=utf8',
      'book_log',
      'pass',
      array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false
      )
    );

    echo " - データベースに接続しました。- ".PHP_EOL;

    //データの追加処理
    try{
      //SQLの作成
      $sql = <<<EOT
      INSERT INTO memo (
        title,
        content
      )VALUES(
        "{$title}",
        "{$content}"
      );
    EOT;

    // SQLの実行
    $res = $dbh->query($sql);
    echo " - データベースを追加しました。- ".PHP_EOL;

    //データ追加の例外処理
    }catch(PDOException $e){
      echo ' - データを追加できませんでした。- '.PHP_EOL;
      exit($e -> getMessage());
    }

  } catch (PDOException $e){
    // 接続の例外処理
    echo 'データベースに接続できませんでした。'.PHP_EOL;
    exit($e -> getMessage());
  }
  //切断
  $dbh = null;
  echo ' - データベースから切断しました。- '.PHP_EOL.PHP_EOL;
  //---------- データベース接続プロセス終了 ----------

  //取得した値を配列に格納
  return [
    'title' => $title,
    'content' => $content,
    'time' => $time,
  ];
}

function displayMemos($memos){
  //メモを表示する

  //メモがなかった場合
  if(!count($memos)){
    echo PHP_EOL . 'メモがありません' . PHP_EOL.PHP_EOL;
  }else{
    //メモを表示する
    echo '--------------------'.PHP_EOL;
    foreach($memos as $memo){
    echo '題名： '.$memo['title'] .PHP_EOL;
    echo '日時： '. $memo['time'] .PHP_EOL;
    echo '内容： '. $memo['content'] .PHP_EOL;
    echo '--------------------'.PHP_EOL.PHP_EOL;
    }
  }
}

//-------------------- 処理 --------------------

// ループ処理
while(true){
  //メニューの表示
  echo '1.メモを書く'.PHP_EOL;
  echo '2.メモを見る'.PHP_EOL;
  echo '9.アプリケーションを終了する'.PHP_EOL;
  echo '番号を選択してください(1,2,9)'.PHP_EOL;
  //番号を取得
  $num = trim(fgets(STDIN));
  
  //条件分岐
  if($num === '1'){
    //メモを書く
    $memos[] = createMemo();
  }
  elseif($num === '2'){
    //メモを表示する
    displayMemos($memos);
  }
  elseif($num === '9'){
    //アプリケーションを終了する
    break;
  }else{
    //選択番号が不適切な場合
    echo PHP_EOL . '正しい番号を選択してください' .PHP_EOL.PHP_EOL;
    }
}
