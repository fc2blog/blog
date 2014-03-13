blog
====

オープンソースのブログ  

#### 動作環境  
* PHP 5.2.17以上  
* MySQL 5.1以上  


#### インストール方法

1. git又はzipをダウンロードして解凍しサーバーに設置してください  
app  
public[公開ディレクトリ]  
  
2. 設定ファイルの名称を変更しDBやサーバーの情報を書き込んでください  
public/config.php.sample -> public/config.php  
　  
※ appディレクトリがpublicディレクトリと同階層に存在しない場合は  
config.php内の下記部分に関してパスを合わせる必要があります  
require(dirname(__FILE__) . '/../app/core/bootstrap.php');  
  
3. インストール画面にアクセスしてください  
ドメイン/admin/install.php  
  
4. 画面に従いインストールを完了してください  
  
5. インストール完了後はadmin/install.phpは不要なので削除してください
