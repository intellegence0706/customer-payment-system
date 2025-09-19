# バックアップシステム - Backup System

このドキュメントでは、Laravel顧客支払いシステムのバックアップと復元の方法について説明します。

## 📁 バックアップファイル

システムには以下の3つのバックアップスクリプトが含まれています：

1. **`backup.sh`** - 手動バックアップ用（完全バックアップ）
2. **`restore.sh`** - システム復元用
3. **`auto_backup.sh`** - 自動バックアップ用（cron用）

## 🚀 手動バックアップの実行

### 1. スクリプトに実行権限を付与

```bash
chmod +x backup.sh
chmod +x restore.sh
chmod +x auto_backup.sh
```

### 2. バックアップの実行

```bash
cd /home/ksb/dev/customer-payment-system
./backup.sh
```

### 3. バックアップの内容

バックアップには以下が含まれます：

- **データベース**: 完全なMySQLダンプ
- **アプリケーションコード**: Laravelアプリケーションのソースコード
- **アップロードファイル**: ユーザーがアップロードしたファイル
- **環境設定**: .envファイル（機密情報は除去）
- **バックアップ情報**: バックアップの詳細情報

### 4. バックアップの保存場所

バックアップは `/home/ksb/backups/` ディレクトリに保存されます。

ファイル名形式: `customer-payment-system_backup_YYYYMMDD_HHMMSS.tar.gz`

## 🔄 システムの復元

### 1. 復元の実行

```bash
cd /home/ksb/dev/customer-payment-system
./restore.sh /home/ksb/backups/customer-payment-system_backup_YYYYMMDD_HHMMSS.tar.gz
```

### 2. 復元の手順

1. バックアップアーカイブの展開
2. データベースの復元
3. アプリケーションコードの復元（確認後）
4. アップロードファイルの復元
5. Laravelキャッシュのクリア
6. データベースマイグレーションの実行

### 3. 復元時の注意事項

- 復元前に現在のシステムのバックアップが自動的に作成されます
- 復元は現在のシステムを上書きします
- データベースの復元は自動的に実行されます
- コードの復元は確認後に実行されます

## ⏰ 自動バックアップの設定

### 1. cronジョブの設定

```bash
# cronエディタを開く
crontab -e

# 毎日午前2時にバックアップを実行
0 2 * * * /home/ksb/dev/customer-payment-system/auto_backup.sh

# 毎週日曜日の午前3時にバックアップを実行
0 3 * * 0 /home/ksb/dev/customer-payment-system/auto_backup.sh

# 毎月1日の午前4時にバックアップを実行
0 4 1 * * /home/ksb/dev/customer-payment-system/auto_backup.sh
```

### 2. 自動バックアップの特徴

- ログファイル: `/home/ksb/backups/backup.log`
- 最新20個のバックアップを保持
- エラー処理とログ記録
- ディスク容量の監視

## 📊 バックアップの管理

### 1. バックアップの一覧表示

```bash
ls -la /home/ksb/backups/
```

### 2. バックアップの詳細確認

```bash
# バックアップの内容を確認（展開せずに）
tar -tzf /home/ksb/backups/customer-payment-system_backup_YYYYMMDD_HHMMSS.tar.gz

# 特定のファイルを確認
tar -xzf /home/ksb/backups/customer-payment-system_backup_YYYYMMDD_HHMMSS.tar.gz --wildcards '*/backup_info.txt' -O
```

### 3. 古いバックアップの削除

```bash
# 30日以上古いバックアップを削除
find /home/ksb/backups/ -name "*.tar.gz" -mtime +30 -delete

# 特定の日付より古いバックアップを削除
find /home/ksb/backups/ -name "*.tar.gz" -not -newermt "2024-01-01" -delete
```

## 🔒 セキュリティと権限

### 1. ファイル権限

- バックアップファイル: 600 (所有者のみ読み書き)
- バックアップディレクトリ: 700 (所有者のみアクセス)

### 2. 機密情報の保護

- データベースパスワードはバックアップに含まれません
- .envファイルの機密情報は除去されます
- バックアップログには機密情報は記録されません

## 🚨 トラブルシューティング

### 1. よくある問題

**バックアップが失敗する場合:**
```bash
# ログファイルを確認
tail -f /home/ksb/backups/backup.log

# ディスク容量を確認
df -h /home/ksb/backups/

# 権限を確認
ls -la /home/ksb/backups/
```

**復元が失敗する場合:**
```bash
# バックアップファイルの整合性を確認
tar -tzf /home/ksb/backups/customer-payment-system_backup_YYYYMMDD_HHMMSS.tar.gz

# データベース接続を確認
mysql -u[username] -p -h[host] [database] -e "SELECT 1;"
```

### 2. ログファイルの確認

```bash
# 最新のログを表示
tail -50 /home/ksb/backups/backup.log

# エラーログを検索
grep -i error /home/ksb/backups/backup.log

# 特定の日付のログを表示
grep "2024-01-15" /home/ksb/backups/backup.log
```

## 📋 バックアップチェックリスト

### 定期チェック項目

- [ ] バックアップファイルが正常に作成されているか
- [ ] バックアップサイズが適切か
- [ ] ログファイルにエラーがないか
- [ ] ディスク容量が十分か
- [ ] 古いバックアップが適切に削除されているか

### 復元テスト

- [ ] 月1回は復元テストを実行
- [ ] テスト環境で復元を確認
- [ ] 復元後のアプリケーション動作を確認

## 🔧 カスタマイズ

### 1. バックアップ設定の変更

各スクリプトの先頭にある設定変数を編集してください：

```bash
PROJECT_NAME="customer-payment-system"
BACKUP_DIR="/home/ksb/backups"
```

### 2. 除外ファイルの追加

`backup.sh`の`rsync`コマンドの`--exclude`オプションに追加してください。

### 3. バックアップ保持数の変更

`backup.sh`の最後にある`tail -n +11`の数字を変更してください。

## 📞 サポート

バックアップシステムに関する問題が発生した場合は、以下を確認してください：

1. ログファイルの内容
2. システムのディスク容量
3. データベース接続設定
4. ファイル権限設定

---

**重要**: 本番環境で復元を実行する前に、必ずテスト環境でテストしてください。
