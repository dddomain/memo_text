<?php

//日付を残すため、タイムゾーンを日本時間に設定
date_default_timezone_set('Asia/Tokyo');

//配列の初期化
$memos = [];

// 関数宣言

function validate($memo){
  //エラー内容の格納
  $errors = [];

  //タイトルのバリデーション
  if(!strlen($memo['title'])){
    $errors['title'] = " - タイトルを入力してください。- ";
  }
  elseif(strlen($memo['title']) > 21){
    $errors['title'] = " - タイトルは20文字以内で入力してください。- ";
  }

  //重要度のバリデーション(int は入力時点でキャストしているのでここでは不要)
  if(!$memo['level']){
    $errors['level'] = " - 重要度を入力してください - ";
  }
  elseif($memo['level'] < 1 || $memo['level'] > 5){
    $errors['level'] = " - 重要度は１〜５の半角整数で入力してください（全角不可）。- ";
  }
  //本文のバリデーション
  if(!strlen($memo['content'])){
    $errors['content'] = " - 本文を入力してください。- ";
  }
  if(strlen($memo['content']) > 100){
    $errors['content'] = " - 本文は100文字以内で入力してください。- ";
  }

  return $errors;
}

function createMemo(){

  //配列の初期化
  $memo = [];

  //メモを書く(各値を変数に格納)
  echo 'タイトル：';
  $memo['title'] = trim(fgets(STDIN));
  echo 'メモ：';
  $memo['content'] = trim(fgets(STDIN));
  echo '重要度：';
  $memo['level'] = (int)trim(fgets(STDIN)); //int で受け取る
  //メモした時間を変数に格納
  $memo['created_at'] = date('Y-m-d H:i:s');

  //バリデーション実行
  $validated = validate($memo);
  if(count($validated) > 0){
    echo PHP_EOL.' - 読書ログを登録できませんでした。- '.PHP_EOL.PHP_EOL;
    echo "エラー内容：".PHP_EOL;
    foreach($validated as $error){
      echo $error .PHP_EOL;
    }
    echo PHP_EOL;
    return;
    //ここでreturnすることでデータベースに繋がれる前に処理を離脱できる
  }
  echo PHP_EOL.' - メモを保存しました！ - '.PHP_EOL.PHP_EOL;

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

    // echo " - データベースに接続しました。- ".PHP_EOL;

    //データの追加処理
    try{
      //SQLの作成
      $sql = <<<EOT
      INSERT INTO memo (
        title,
        content,
        level,
        created_at
      )VALUES(
        "{$memo['title']}",
        "{$memo['content']}",
        "{$memo['level']}",
        "{$memo['created_at']}"
      );
    EOT;


    // SQLの実行
    $res = $dbh->query($sql);
    // echo " - データベースを追加しました。- ".PHP_EOL;

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
  // echo ' - データベースから切断しました。- '.PHP_EOL.PHP_EOL;
  //---------- データベース接続プロセス終了 ----------

  //取得した値を配列に格納
  return [
    'title' => $memo['title'],
    'content' => $memo['content'],
    'level' => $memo['level'],
    'created_at' => $memo['created_at']
  ];
}

function displayMemos($memos){
  //メモを表示する

  //メモがなかった場合
  // if(!count($memos)){
  //   echo PHP_EOL . ' - メモがありません。- ' . PHP_EOL.PHP_EOL;
  // }else{
    //メモを表示する
  //   echo '--------------------'.PHP_EOL;
  //   foreach($memos as $memo){
  //   echo '題名： '.$memo['title'] .PHP_EOL;
  //   echo '日時： '. $memo['created_at'] .PHP_EOL;
  //   echo '重要度： '. $memo['level'] .PHP_EOL;
  //   echo '内容： '. $memo['content'] .PHP_EOL;
  //   echo '--------------------'.PHP_EOL.PHP_EOL;
  //   }
  // }

  //---------- データベース接続プロセス開始 ----------
  try {
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

    // echo " - データベースに接続しました。- ".PHP_EOL;

    //データの取得・表示
    try {
      //SQL文の作成
      $sql = "SELECT title, level, content, created_at FROM memo";

      //SQLの実行（取得）
      $stmt = $dbh -> query($sql);
      // echo PHP_EOL." - データを取得しました。- ".PHP_EOL.PHP_EOL;

      //表示
      while($memo = $stmt -> fetch(PDO::FETCH_ASSOC)){
        echo "----------------------------------".PHP_EOL;
        echo 'タイトル： '.$memo['title'].PHP_EOL;
        echo '重要度  ： '.$memo['level'].PHP_EOL;
        echo '作成日時： '.$memo['created_at'].PHP_EOL;
        echo '内容    ： '.$memo['content'].PHP_EOL;
        echo "----------------------------------".PHP_EOL.PHP_EOL;
      }

    //取得・表示の例外処理
    } catch(PDOException $e) {
      echo " - データを取得できませんでした。- ".PHP_EOL;
    }

  } catch (PDOException $e){
    // 接続の例外処理
    echo 'データベースに接続できませんでした。'.PHP_EOL;
    exit($e -> getMessage());
  }
  //切断
  $dbh = null;
  // echo ' - データベースから切断しました。- '.PHP_EOL.PHP_EOL;
  //---------- データベース接続プロセス終了 ----------
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
    echo PHP_EOL . ' - 正しい番号を選択してください。- ' .PHP_EOL.PHP_EOL;
    }
}
