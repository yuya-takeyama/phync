Phync
=====

PHP による `rsync` ラッパ

Features
--------

1. `rsync` のラッパとして動作し、複雑な `rsync` コマンドを簡単に生成・実行できる。
2. プロジェクトルートから全体のシンクができる。
3. プロジェクトツリー内の一部のファイル・ディレクトリだけを選択してシンクできる。
4. デフォルト動作は `rsync --dry-run` となっており、確認が安全に行える。

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

引数としてファイルを指定すると、階層を保持して対象サーバに `rsync` します。

デフォルトでは `--dry-run` オプションの指定によるドライランとなります。

```
$ /path/to/phync file-to-sync
Generated commands:
rsync -avC --dry-run --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'foo.example.com:/home/yuya/dev/php/phync/file-to-sync'
rsync -avC --dry-run --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'bar.example.com:/home/yuya/dev/php/phync/file-to-sync'
rsync -avC --dry-run --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'baz.example.com:/home/yuya/dev/php/phync/file-to-sync'
```

`--execute` オプションを指定することで、実際の同期を実行します。

```
$ /path/to/phync --execute file-to-sync
Generated commands:
rsync -avC --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'foo.example.com:/home/yuya/dev/php/phync/file-to-sync'
rsync -avC --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'bar.example.com:/home/yuya/dev/php/phync/file-to-sync'
rsync -avC --delete '--exclude-from=exclude.lst' '--rsync-path=/usr/bin/rsync' '--rsh=/usr/bin/ssh' '/home/yuya/dev/php/phync/file-to-sync' 'baz.example.com:/home/yuya/dev/php/phync/file-to-sync'
```

Configuration
-------------

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
