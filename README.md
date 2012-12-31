Phync
=====

PHP による `rsync` ラッパ

Features
--------

1. `rsync` のラッパとして動作し、複雑な `rsync` コマンドを簡単に生成・実行できる。
2. 複数のサーバにまとめてシンクできる。
3. プロジェクトルートから全体のシンクができる。
4. プロジェクトツリー内の一部のファイル・ディレクトリだけを選択してシンクできる。
5. デフォルト動作は `rsync --dry-run` となっており、確認が安全に行える。

Installation
------------

### phync 本体

GitHub から `git` コマンドで直接インストールできます。  
ワークツリーの `./bin` ディレクトリを `$PATH` に追加することで `phync` コマンドが使用できるようになります。

```
$ git clone https://github.com/yuya-takeyama/phync.git ~/.phync
```

GitHub の Subversion インターフェイスを使用すれば `svn` コマンドでもインストールできます。

```
$ svn co https://github.com/yuya-takeyama/phync/trunk ~/.phync
```

### 各プロジェクトへのインストール

各プロジェクトごとに設定ファイル、ログディレクトリを設置する必要があります。

`phync` コマンドの実行時に、カレントディレクトリ内の `.phync/config.php` を読み込みます。

また、ログディレクトリはデフォルトでは `.phync/log` となっており、予め作成しておく必要があります。

Usage
-----

### ドライラン

デフォルトの動作は `rsync --dry-run` によるドライランです。  
ドライランを行うと、実行時にどのような変更が行われるかを確認できます。  

引数を指定しなければ、プロジェクト全体が対象となります。

```
$ phync
# 以下のようなコマンドが生成される
# rsync -av --dry-run --delete '/project/' 'example.com:/project/'
```

引数を指定することで、一部のファイル・ディレクトリのみを対象とすることができます。  
また、同時に複数のファイル・ディレクトリを指定することが出来ます。

```
# ファイル
$ phync path/to/file
# 以下のようなコマンドが生成される
# rsync -av --dry-run --delete '/project/' 'example.com:/project/' --include '/path/to/file' --include '/path/to/' --include '/path/' --exclude '*'

# ディレクトリ
$ phync path/to/dir
# 以下のようなコマンドが生成される
# rsync -av --dry-run --delete '/project/' 'example.com:/project/' --include '/path/to/dir/' --include '/path/to/dir/*' --include '/path/to/dir/**/*' --exclude '*'

# 複数まとめて
$ phync path/to/file path/to/dir
# 以下のようなコマンドが生成される
# rsync -av --dry-run --delete '/project/' 'example.com:/project/' --include '/path/to/file' --include '/path/to/' --include '/path/' --include '/path/to/dir/' --include '/path/to/dir/*' --include '/path/to/dir/**/*' --exclude '*'
```

### シンクの実行

`phync` コマンドの実行時に `--execute` オプションを付加することで、シンクが実行されます。

このとき生成される `rsync` コマンドは、ドライラン時のものから `--dry-run` オプションのみが除かれたものです。

```
# ファイル
$ phync --execute path/to/file

# ディレクトリ
$ phync --execute path/to/dir

# 複数まとめて
$ phync --execute path/to/file path/to/dir
```

Configuration
-------------

カレントディレクトリ内の `.phync/config.php` が設定ファイルとなります。  
設定値は PHP の連想配列として記述できます。

### 設定ファイルの記述例

```php
<?php
return array(
    'destinations' => array(
        'foo.example.com',
        'bar.example.com',
        'baz.example.com',
    ),
    'exclude_from' => 'exclude.lst',
    'rsync_path'   => '/usr/bin/rsync',
    'rsh'          => '/usr/bin/ssh',
);
```

### 各設定値の詳細

項目名           |説明                                                                                            |必須|設定例                                               |
-----------------|------------------------------------------------------------------------------------------------|----|-----------------------------------------------------|
destinations     |シンク先サーバのホスト名を配列で指定する。                                                      |Yes |`array('server1.example.net', 'server2.example.net')`|
exclude\_from    |`rsync` コマンドの `--exclude-from` オプション。除外ファイルの一覧を記述したファイルを指定する。|No  |`dirname(__FILE__) . '/rsync_exclude'`               |
rsync\_path      |`rsync` コマンドの `--rsync-path` オプション。                                                  |No  |`'/usr/bin/rsync'`                                   |
rsh              |`rsync` コマンドの `--rsh` オプション。                                                         |No  |`'/usr/bin/rsync'`                                   |
default\_checksum|`true` にすると `rsync` コマンドに `--checksum` オプションが付加される。デフォルトは `false`。  |No  |`true`                                               |
log\_directory   |ログファイルを保存するディレクトリのパス。でフォルトは `.phync/log`                             |No  |`'/path/to/log-directory'`                           |

Author
------

Yuya Takeyama
