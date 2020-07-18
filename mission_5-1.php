<?php
    //Mysqlに接続
    $dsn = 'データベース名';
    $user = 'ユーザー名';
    $password = 'パスワード';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

    // テーブルを作成
    // $sql = "CREATE TABLE IF NOT EXISTS board"
	// ." ("
	// . "id INT AUTO_INCREMENT PRIMARY KEY,"  
	// . "name char(32) not null," //記入がない場合実行されない
	// . "comment text not null," //記入がない場合は実行されない 
	// . "created timestamp not null default current_timestamp,"   //記入がない場合は実行されない
	// . "pass char(30) not null"  //記入がない場合は実行されない
	// .");";
	// $stmt = $pdo -> query($sql);
	
    // テーブルの詳細を表示
    // $sql ='SHOW CREATE TABLE board';
	// $result = $pdo -> query($sql);
	// foreach ($result as $row){
	// 	echo $row[1];
	// }
	// echo "<hr>";
	
    // テーブル削除
    // $sql = 'DROP TABLE board';
    // $stmt = $pdo->query($sql);

    
    if (!empty($_POST['name']) && !empty($_POST['comment']) && !empty($_POST['pass'])) {
        $name = $_POST['name'];         //ユーザー名
        $comment = $_POST['comment'];   //コメント
        $created = date("Y/m/d H:i:s"); //投稿日時
        $pass = $_POST['pass'];         //パスワード
        //新規投稿か編集かの判断
        if (empty($_POST['edit_num'])) {    //Hiddenされたフォームにidが入っていないとき
            //新規投稿処理
            //INSERT文に変数を格納し値を挿入
            $sql = $pdo -> prepare("INSERT INTO board (name, comment, created, pass) VALUES (:name, :comment, :created, :pass)"); 
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':created', $created, PDO::PARAM_STR);
            $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
            $sql -> execute();
        } else {  //Hiddenされたフォームにidが入っているとき
            $edit_num = $_POST['edit_num'];
            //編集処理
            $sql = 'SELECT * FROM board WHERE id=:id ';
            $stmt = $pdo -> prepare($sql);                          // ←差し替えるパラメータを含めて記述したSQLを準備
            $stmt -> bindParam(':id', $edit_num, PDO::PARAM_INT);   // その差し替えるパラメータの値を指定
            $stmt -> execute();                                     //sql実行
            $results = $stmt -> fetchAll(); 
            foreach ($results as $row) {
                //$rowの中にはテーブルのカラム名が入る
                if ($row['id'] == $edit_num && $row['pass'] == $pass) {
                    $sql = 'UPDATE board SET name=:name, comment=:comment, created=:created WHERE id=:id';
                    $stmt = $pdo -> prepare($sql);
                    $stmt -> bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt -> bindParam(':created', $created, PDO::PARAM_STR);
                    $stmt -> bindParam(':id', $edit_num, PDO::PARAM_INT);
                    $stmt -> execute();
                }
            }
        }
    }

    //削除処理
    if (!empty($_POST['delete_id']) && !empty($_POST['delete_pass'])) {
        $delete_id = $_POST['delete_id'];     //削除番号
        $delete_pass = $_POST['delete_pass']; //パスワード
        //削除するidの配列を取得する処理
        $sql = 'SELECT * FROM board WHERE id=:id ';
        $stmt = $pdo -> prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備
        $stmt -> bindParam(':id', $delete_id, PDO::PARAM_INT); // その差し替えるパラメータの値を指定
        $stmt -> execute();                                    //sql実行
        //取得した配列のidカラムとpasswordカラムがフォームと合致しているかを確認
        $results = $stmt -> fetchAll(); 
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            if ($row['id'] == $delete_id && $row['pass'] == $delete_pass) {
                $sql = 'delete from board where id=:id';
                $stmt = $pdo -> prepare($sql);
                $stmt -> bindParam(':id', $delete_id, PDO::PARAM_INT);
                $stmt -> execute();
            }
        }
     }

     //編集番号選択
     if (!empty($_POST['edit_id']) && !empty($_POST['edit_pass'])) {
        $edit_id = $_POST['edit_id'];
        $edit_pass = $_POST['edit_pass'];
        //編集するidの配列を取得する処理
        $sql = 'SELECT * FROM board WHERE id=:id';
        $stmt = $pdo -> prepare($sql);
        $stmt -> bindParam(':id', $edit_id, PDO::PARAM_INT);
        // $stmt -> bindParam(':pass', $edit_pass, PDO::PARAM_STR);
        $stmt -> execute();
        //取得した配列のidカラムとpasswordカラムがフォームと合致しているかを確認
        $results = $stmt -> fetchAll(); 
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る
            if ($row['id'] == $edit_id && $row['pass'] == $edit_pass) {
                //編集するカラムをHTMLのフォームに入れる
                $edit_num = $row['id'];
                $edit_name = $row['name'];
                $edit_comment = $row['comment'];
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta chaset="utf-8">
        <title>簡易掲示板</title>
    </head>
    <body>
        <!--名前・コメント・編集番号・パスワード-->
        <form action="" method="post">
            <input type="text" name="name" placeholder="名前" value="<?php if(isset($edit_name)) {echo $edit_name;} ?>"><br> 
            <input type="text" name="comment" placeholder="コメント" value="<?php if(isset($edit_comment)) {echo $edit_comment;} ?>"><br>
            <input type="hidden" name="edit_num" value="<?php if(isset($edit_num)) {echo $edit_num;} ?>">
            <input type="password" name="pass" placeholder="パスワード"><br>
            <input type="submit" value="送信">
        </form>
    
        <!--削除-->
        <form action="" method="post">
            <input type="text" placeholder="削除番号" name="delete_id"><br>
            <input type="password" placeholder="パスワード" name="delete_pass"><br>
            <input type="submit" value="削除">
        </form>
    
        <!--編集-->
        <form action="" method="post">
            <input type="text" placeholder="編集番号" name="edit_id"><br>
            <input type="password" placeholder="パスワード" name="edit_pass"><br>
            <input type="submit" value="編集">
        </form>
        <?php
            $sql = 'SELECT * FROM board';
            $stmt = $pdo -> query($sql);
            $results = $stmt -> fetchAll();
            foreach ($results as $row){
                //$rowの中にはテーブルのカラム名が入る
                echo $row['id'].','.$row['name'].','.$row['comment'].','.$row['created'].'<br>';
                echo "<hr>";
            }
        ?>
    </body>
</html>