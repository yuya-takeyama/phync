Phync
=====

PHP によるシンプルな `rsync` ラッパ

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
