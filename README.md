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
$ svn co https://github.com/yuya-takeyama/phync/master ~/.phync
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
# rsync -avC --dry-run --delete '/project/' 'example.com:/project/' 
```

引数を指定することで、一部のファイル・ディレクトリのみを対象とすることができます。  
また、同時に複数のファイル・ディレクトリを指定することが出来ます。

```
# ファイル
$ phync path/to/file
# 以下のようなコマンドが生成される
# rsync -avC --dry-run --delete '/project/' 'example.com:/project/' --include '/path/to/file' --include '/path/to/' --include '/path/' --exclude '*'

# ディレクトリ
$ phync path/to/dir
# 以下のようなコマンドが生成される
# rsync -avC --dry-run --delete '/project/' 'example.com:/project/' --include '/path/to/dir/' --include '/path/to/dir/*' --include '/path/to/dir/**/*' --exclude '*'

# 複数まとめて
$ phync path/to/file path/to/dir
# 以下のようなコマンドが生成される
# rsync -avC --dry-run --delete '/project/' 'example.com:/project/' --include '/path/to/file' --include '/path/to/' --include '/path/' --include '/path/to/dir/' --include '/path/to/dir/*' --include '/path/to/dir/**/*' --exclude '*'
```

`--execute` オプションを指定することで、実際の同期を実行します。

```
$ /path/to/phync --execute file-to-sync
Generated commands:
rsync -avC --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'foo.example.com:/home/yuya/dev/php/phync/file-to-sync'
rsync -avC --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'bar.example.com:/home/yuya/dev/php/phync/file-to-sync'
rsync -avC --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'baz.example.com:/home/yuya/dev/php/phync/file-to-sync'
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

`$HOME/.phync/config/config.php` に設定ファイルが必要です。

### Example

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

Author
------

Yuya Takeyama
