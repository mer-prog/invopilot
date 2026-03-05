# InvoPilot — マルチテナント請求書SaaS

> **何を:** 組織単位で顧客・請求書・定期請求・支払いを一元管理できるフルスタック請求書SaaS
> **誰に:** 小規模事業者・フリーランス、および採用担当・技術リードへのスキル証明として
> **技術:** PHP 8.5 · Laravel 12 · React 19 · TypeScript · Inertia.js 2 · Tailwind CSS 4 · PostgreSQL 17

- ソースコード: [github.com/mer-prog/invopilot](https://github.com/mer-prog/invopilot)

---

## このプロジェクトで証明できるスキル

| スキル | 実装内容 |
|--------|----------|
| フルスタック設計 | Laravel 12 + Inertia.js 2 + React 19 のモノリスSPA。サーバーサイドルーティングでクライアントサイドレンダリングを実現 |
| マルチテナント設計 | `organization_id` による全クエリスコープ、ミドルウェアでのセッション管理、Policy による組織境界の認可制御 |
| サービス層アーキテクチャ | Controller → Service → Model の責務分離。InvoiceService（168行）で請求書ライフサイクル全体を管理 |
| イベント駆動設計 | 4つのドメインイベント（InvoiceCreated/Sent/PaymentRecorded/Overdue）と3つのリスナーによる副作用の分離 |
| REST API 設計 | Sanctum トークン認証、Eloquent API Resource によるレスポンス整形、組織スコープ付きの完全CRUD |
| 国際化（i18n） | 日英2言語の完全対応。`useTrans()` フックで翻訳辞書をフロントエンドに共有、ランタイム切り替え |
| テスト駆動開発 | Pest 4 で126テスト・447アサーション。Controller/Service/Policy/Event/API/PDF/メール通知を網羅 |

---

## 技術スタック

| カテゴリ | 技術 | 用途 |
|----------|------|------|
| 言語 | PHP 8.5 | バックエンド |
| フレームワーク | Laravel 12 | ルーティング、ORM、認証、キュー、イベント |
| フロントエンド | React 19 + TypeScript 5.7 | UI コンポーネント |
| ブリッジ | Inertia.js 2 | サーバーサイドルーティング + クライアントサイドSPA |
| スタイリング | Tailwind CSS 4 + shadcn/ui | ユーティリティファーストCSS + Radix UIベースコンポーネント |
| データベース | PostgreSQL 17 (Neon) | 本番環境。ローカル開発は SQLite |
| 認証 | Laravel Sanctum 4 + Fortify 1 | セッション認証、APIトークン、2FA (TOTP) |
| PDF生成 | barryvdh/laravel-dompdf 3 | 請求書PDF (A4) のダウンロード・メール添付 |
| チャート | recharts 2 | ダッシュボード月次売上グラフ (AreaChart) |
| ドラッグ&ドロップ | dnd-kit | 請求書明細行の並び替え |
| ルート生成 | Laravel Wayfinder | バックエンドルートの TypeScript 関数自動生成 |
| テスト | Pest 4 | PHP テストフレームワーク |
| リンター | Laravel Pint (PSR-12) + ESLint 9 + Prettier 3 | コード品質 |
| CI/CD | GitHub Actions | テスト・リント自動実行 |
| デプロイ | Docker + Render.com | コンテナ化デプロイ（シンガポールリージョン） |
| ビルド | Vite 7 | フロントエンドバンドル + HMR |

---

## アーキテクチャ概要

```
┌─────────────────────────────────────────────────────────────┐
│                      ブラウザ (React 19)                     │
│   ┌──────────┐  ┌──────────┐  ┌───────────┐  ┌──────────┐  │
│   │ダッシュ   │  │顧客管理  │  │請求書管理  │  │設定      │  │
│   │ボード    │  │CRUD      │  │CRUD+PDF   │  │プロフィール│  │
│   └──────────┘  └──────────┘  └───────────┘  └──────────┘  │
└─────────────────────┬───────────────────────────────────────┘
                      │ Inertia.js 2 (XHR)
┌─────────────────────▼───────────────────────────────────────┐
│                    Laravel 12 バックエンド                    │
│                                                              │
│  ┌──────────────┐    ┌──────────────┐    ┌───────────────┐  │
│  │  Middleware   │───▶│ Controllers  │───▶│   Services    │  │
│  │ ・認証       │    │ ・Web (6)    │    │ ・Invoice     │  │
│  │ ・組織コンテキスト│ │ ・API (2)    │    │ ・Recurring   │  │
│  │ ・ロケール    │    │ ・Settings(5)│    │ ・Dashboard   │  │
│  │ ・Inertia    │    └──────┬───────┘    │ ・PDF         │  │
│  └──────────────┘           │            └───────┬───────┘  │
│                             │                    │           │
│  ┌──────────────┐    ┌──────▼───────┐    ┌───────▼───────┐  │
│  │   Policies   │    │   Models     │    │   Events      │  │
│  │ ・Client     │    │   (8個)      │◄──▶│ ・Created     │  │
│  │ ・Invoice    │    │              │    │ ・Sent        │  │
│  │ ・Recurring  │    └──────┬───────┘    │ ・Paid        │  │
│  └──────────────┘           │            │ ・Overdue     │  │
│                             │            └───────┬───────┘  │
│                      ┌──────▼───────┐    ┌───────▼───────┐  │
│                      │ PostgreSQL   │    │  Listeners    │  │
│                      │  10テーブル   │    │ ・ActivityLog │  │
│                      └──────────────┘    │ ・StatusUpdate│  │
│                                          │ ・Receipt     │  │
│  ┌──────────────┐    ┌──────────────┐    └───────────────┘  │
│  │  Sanctum API │    │  Queue/Jobs  │                       │
│  │  Bearer認証  │    │ ・メール送信  │                       │
│  │  CRUD x 2    │    │ ・PDF生成    │                       │
│  └──────────────┘    └──────────────┘                       │
└──────────────────────────────────────────────────────────────┘
```

---

## 主要機能

### ダッシュボード
- 今月/先月の売上、成長率、未収金額、延滞件数、顧客数をカード表示
- recharts の AreaChart で過去12ヶ月の月次売上推移をグラフ表示
- 直近のアクティビティフィード（請求書作成・送信・支払い記録・延滞）
- 延滞請求書がある場合のアラート表示

### 顧客管理
- 顧客の完全CRUD（名前、メール、電話、会社名、住所、税ID、メモ）
- 顧客一覧に請求書件数・合計金額をサマリ表示（`withCount` / `withSum`）
- 顧客詳細ページに直近20件の請求書履歴
- 全クエリは `forOrganization()` スコープで組織境界を保証

### 請求書管理
- 請求書のCRUD + 送信・支払い記録・複製・PDF・キャンセル
- dnd-kit による明細行のドラッグ&ドロップ並び替え
- リアルタイムプレビューパネル（入力内容を即座に反映）
- ステータスフィルター（下書き/送信済み/支払済み/延滞/取消）+ 日付範囲フィルター
- 小計・税額・割引・合計の自動計算（Service 層で実行）
- `InvoiceStatus` Enum による型安全なステータス管理

### 支払い記録
- 部分支払い・全額支払いに対応
- 支払い方法: 銀行振込、クレジットカード、現金、小切手、その他（`PaymentMethod` Enum）
- 合計支払額が請求額に達すると自動的にステータスを「支払済み」に更新（`UpdateInvoiceStatus` リスナー）
- 支払い記録時に顧客へ領収書メール通知（`SendPaymentReceipt` リスナー）

### PDF生成
- Blade テンプレートで A4 請求書を描画し、dompdf で PDF 変換
- ブラウザダウンロードおよびメール添付に対応
- キュー対応（`GenerateInvoicePdf` ジョブ）

### 定期請求
- 頻度設定: 毎週/隔週/毎月/四半期/毎年（`Frequency` Enum）
- 次回発行日・終了日の管理
- 有効/無効のトグル切り替え
- スケジュールコマンド `invoices:process-recurring` が毎朝8時に自動生成
- `invoices:check-overdue` が毎朝9時に延滞チェック

### メール通知
- 請求書送信通知（PDF添付）
- 支払い領収書通知
- 延滞リマインダー通知
- 全通知はキュー対応（`ShouldQueue` 実装）

### 組織設定
- 組織情報（名前、住所、電話、税ID、ロゴURL）
- 請求書デフォルト設定（通貨、プレフィックス、支払期限日数、デフォルト備考）
- 対応通貨: USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, KRW, TWD（`Currency` Enum）

### 認証・セキュリティ
- Laravel Fortify によるログイン/登録/パスワードリセット/メール認証
- TOTP ベースの二要素認証（QRコード + 手動セットアップキー + リカバリーコード）
- セッション認証（Web）+ Sanctum トークン認証（API）
- Policy による組織スコープの認可制御

### REST API
- Sanctum Bearer トークン認証
- 顧客・請求書の完全CRUD
- Eloquent API Resource によるレスポンス整形
- 組織スコープによるデータ分離

### 国際化
- 日本語（デフォルト）/ 英語の完全対応
- 10個の翻訳ファイル（common, auth, invoices, clients, recurring_invoices, dashboard, settings, payments, activity, navigation）
- `useTrans()` フックでフロントエンド全体に翻訳を提供
- ヘッダーのトグルボタンでランタイム切り替え

### ダークモード
- システム / ライト / ダーク の3モード
- Cookie ベースで設定を永続化

---

## データベース設計

```
┌──────────┐       ┌───────────────────┐       ┌──────────┐
│  users   │◄─────▶│ organization_user │◄─────▶│organizat-│
│          │  N:N  │   (role)          │  N:N  │  ions     │
└────┬─────┘       └───────────────────┘       └────┬─────┘
     │                                              │
     │ 1:N                                    1:N   │ 1:N
     ▼                                         ┌────▼─────┐
┌──────────┐                                   │ clients  │
│ activity │                                   └────┬─────┘
│ _logs    │                                        │
└──────────┘                                   1:N  │ 1:N
                                                ┌───▼────┐
                   ┌────────────┐               │invoices│
                   │ recurring  │               └───┬────┘
                   │ _invoices  │                   │
                   └────────────┘              1:N  │ 1:N
                                            ┌───────┴──────┐
                                            ▼              ▼
                                     ┌──────────┐  ┌──────────┐
                                     │ invoice  │  │ payments │
                                     │ _items   │  └──────────┘
                                     └──────────┘
```

| テーブル | 主要カラム | 説明 |
|----------|-----------|------|
| users | name, email, locale, timezone, 2FA関連 | ユーザー。Fortify 連携 |
| organizations | name, slug, default_currency, invoice_prefix, invoice_next_number | 組織。テナント単位 |
| organization_user | organization_id, user_id, role | 多対多ピボット。Owner/Admin/Member |
| clients | organization_id, name, email, company, address, tax_id | 取引先 |
| invoices | organization_id, client_id, invoice_number, status, 金額4列, 日付3列 | 請求書。金額は全て `decimal(12,2)` |
| invoice_items | invoice_id, description, quantity, unit_price, tax_rate, amount | 明細行。sort_order で並び順管理 |
| payments | invoice_id, amount, method, reference, paid_at | 支払い記録 |
| recurring_invoices | organization_id, client_id, frequency, next_issue_date, items(JSON) | 定期請求テンプレート |
| activity_logs | organization_id, user_id, action, subject(polymorphic), metadata(JSON) | 操作履歴 |
| personal_access_tokens | tokenable(polymorphic), name, token, abilities | Sanctum API トークン |

---

## API エンドポイント

全エンドポイントは Sanctum Bearer トークン認証が必要。

```
Authorization: Bearer <token>
```

| メソッド | エンドポイント | 説明 | レスポンス |
|---------|---------------|------|-----------|
| GET | `/api/clients` | 顧客一覧 | ClientResource コレクション |
| POST | `/api/clients` | 顧客作成 | ClientResource |
| GET | `/api/clients/{id}` | 顧客詳細 | ClientResource |
| PUT | `/api/clients/{id}` | 顧客更新 | ClientResource |
| DELETE | `/api/clients/{id}` | 顧客削除 | 204 |
| GET | `/api/invoices` | 請求書一覧 | InvoiceResource コレクション |
| POST | `/api/invoices` | 請求書作成 | InvoiceResource |
| GET | `/api/invoices/{id}` | 請求書詳細 | InvoiceResource（items, client 含む） |
| PUT | `/api/invoices/{id}` | 請求書更新 | InvoiceResource |
| DELETE | `/api/invoices/{id}` | 請求書削除 | 204 |

---

## プロジェクト構成

```
invopilot/
├── app/
│   ├── Console/Commands/          # Artisan コマンド (2)
│   │   ├── CheckOverdueInvoices.php
│   │   └── ProcessRecurringInvoices.php
│   ├── Enums/                     # 型安全な Enum (5)
│   │   ├── InvoiceStatus.php      # Draft/Sent/Paid/Overdue/Cancelled
│   │   ├── PaymentMethod.php      # BankTransfer/CreditCard/Cash/Check/Other
│   │   ├── Frequency.php          # Weekly/Biweekly/Monthly/Quarterly/Yearly
│   │   ├── Currency.php           # USD/EUR/GBP/JPY 他10通貨
│   │   └── OrganizationRole.php   # Owner/Admin/Member
│   ├── Events/                    # ドメインイベント (4)
│   ├── Http/
│   │   ├── Controllers/           # コントローラー (13)
│   │   │   ├── DashboardController.php
│   │   │   ├── ClientController.php
│   │   │   ├── InvoiceController.php      # 182行: CRUD + send/pay/duplicate/pdf
│   │   │   ├── RecurringInvoiceController.php
│   │   │   ├── LocaleController.php
│   │   │   ├── Api/               # API コントローラー (2)
│   │   │   └── Settings/          # 設定コントローラー (5)
│   │   ├── Middleware/            # ミドルウェア (4)
│   │   └── Requests/              # FormRequest (14)
│   ├── Jobs/                      # キュージョブ (2)
│   ├── Listeners/                 # イベントリスナー (3)
│   ├── Models/                    # Eloquent モデル (8)
│   ├── Notifications/             # メール通知 (3)
│   ├── Policies/                  # 認可ポリシー (3)
│   └── Services/                  # ビジネスロジック (4)
│       ├── InvoiceService.php     # 168行: 請求書ライフサイクル管理
│       ├── RecurringInvoiceService.php
│       ├── DashboardService.php   # 119行: 統計・グラフデータ集計
│       └── PdfService.php
├── resources/js/
│   ├── pages/                     # React ページ (24)
│   │   ├── dashboard.tsx          # 290行: 統計カード + グラフ + フィード
│   │   ├── clients/               # 一覧/作成/編集/詳細 (4)
│   │   ├── invoices/              # 一覧/作成/編集/詳細 (4)
│   │   │   └── show.tsx           # 559行: 詳細+支払い+アクション
│   │   ├── recurring-invoices/    # 一覧/作成/編集 (3)
│   │   ├── settings/              # 6ページ (profile/password/appearance/org/2fa/tokens)
│   │   └── auth/                  # 7ページ (login/register/reset/verify/2fa)
│   ├── components/                # React コンポーネント (60+)
│   │   ├── invoices/
│   │   │   ├── invoice-form.tsx   # 435行: dnd-kit 対応フォーム
│   │   │   └── invoice-preview.tsx # 214行: リアルタイムプレビュー
│   │   └── ui/                    # shadcn/ui コンポーネント群
│   ├── hooks/                     # カスタムフック (5)
│   ├── layouts/                   # レイアウト (4)
│   └── types/                     # TypeScript 型定義
├── lang/
│   ├── en/                        # 英語翻訳 (10ファイル)
│   └── ja/                        # 日本語翻訳 (10ファイル)
├── database/
│   ├── migrations/                # マイグレーション (15)
│   ├── factories/                 # ファクトリー (7)
│   └── seeders/                   # シーダー (185行のデモデータ)
├── tests/
│   └── Feature/                   # テスト (28ファイル, 2,064行)
├── Dockerfile                     # マルチステージビルド
├── render.yaml                    # Render.com デプロイ設定
└── .github/workflows/ci.yml      # CI パイプライン
```

**コード規模:** PHP 3,504行 / TypeScript 23,710行 / テスト 2,064行

---

## 画面仕様

| 画面 | パス | 機能概要 |
|------|------|----------|
| ログイン | `/login` | メール/パスワード認証、リメンバーミー、2FAチャレンジ連携 |
| ユーザー登録 | `/register` | 名前/メール/パスワード入力、自動で組織作成 |
| パスワードリセット | `/forgot-password` → `/reset-password` | メール送信 → トークン付きリセット |
| メール認証 | `/verify-email` | 認証メール再送 |
| 二要素認証 | `/two-factor-challenge` | OTP 6桁入力またはリカバリーコード |
| ダッシュボード | `/dashboard` | 統計カード4枚 + 月次グラフ + アクティビティ |
| 顧客一覧 | `/clients` | ページネーション + 請求書サマリ |
| 顧客作成 | `/clients/create` | フォーム入力 |
| 顧客詳細 | `/clients/{id}` | 情報表示 + 請求書履歴 |
| 顧客編集 | `/clients/{id}/edit` | フォーム編集 |
| 請求書一覧 | `/invoices` | ステータスフィルター + 日付フィルター + ソート |
| 請求書作成 | `/invoices/create` | 明細行ドラッグ&ドロップ + プレビュー |
| 請求書詳細 | `/invoices/{id}` | 情報 + 支払い履歴 + 送信/支払い/複製/PDF/削除 |
| 請求書編集 | `/invoices/{id}/edit` | 作成画面と同一UI |
| 定期請求一覧 | `/recurring-invoices` | 有効/無効トグル付き一覧 |
| 定期請求作成 | `/recurring-invoices/create` | 頻度選択 + 明細設定 |
| 定期請求編集 | `/recurring-invoices/{id}/edit` | テンプレート編集 |
| プロフィール設定 | `/settings/profile` | 名前/メール変更 + アカウント削除 |
| パスワード設定 | `/settings/password` | 現パスワード確認 + 新パスワード |
| 外観設定 | `/settings/appearance` | ライト/ダーク/システムテーマ選択 |
| 組織設定 | `/settings/organization` | 組織情報 + 請求書デフォルト |
| 二要素認証設定 | `/settings/two-factor` | 2FA 有効化/無効化 + リカバリーコード |
| APIトークン | `/settings/api-tokens` | トークン作成/一覧/失効 |

---

## テスト構成

| カテゴリ | テスト内容 | ファイル数 |
|----------|-----------|-----------|
| 認証 | ログイン、登録、パスワードリセット、メール認証、2FA | 6 |
| 設定 | プロフィール更新、パスワード変更、2FA設定 | 3 |
| コントローラー | ダッシュボード、顧客CRUD、請求書CRUD、定期請求CRUD | 4 |
| API | 顧客API、請求書API、トークン管理 | 3 |
| 機能 | PDF生成、支払い記録、メール通知、延滞チェック、定期請求処理 | 5 |
| イベント | イベント発火・リスナー実行の検証 | 1 |
| 認可 | Policy テスト（組織スコープ外アクセス拒否） | 1 |
| 合計 | **126テスト / 447アサーション** | **28** |

テスト実行:
```bash
php artisan test --compact              # 全テスト
php artisan test --filter=InvoiceTest   # 個別テスト
```

テスト環境: SQLite インメモリ、`RefreshDatabase` トレイトで毎テスト初期化

---

## CI/CD パイプライン

GitHub Actions で2ジョブを並列実行:

| ジョブ | 実行内容 |
|--------|----------|
| tests | PHP 8.5 + Node 22 セットアップ → composer install → npm ci → npm run build → `php artisan test` |
| lint | `vendor/bin/pint --test` (PHP) + `prettier --check` (TS) + `npm run lint` (ESLint) |

---

## シードデータ

`php artisan migrate --seed` で以下のデモデータを生成:

| データ | 内容 |
|--------|------|
| テストユーザー | `test@example.com`（日本語ロケール、東京タイムゾーン） |
| 組織 | 3組織（Acme Corp USD / Sakura Tech JPY / Global Trading EUR） |
| 顧客 | 組織あたり 5〜10件 |
| 請求書 | 組織あたり 20〜50件（ステータス分布: 下書き2/送信3/支払4/延滞2/取消1） |
| 明細行 | 請求書あたり 1〜5行 |
| 支払い | 支払済み請求書に紐づく支払い記録 |
| 定期請求 | 組織あたり 2〜5件 |
| アクティビティログ | 請求書作成の操作履歴 |

---

## セキュリティ設計

| 対策 | 実装 |
|------|------|
| マルチテナント分離 | 全クエリに `organization_id` スコープ。ミドルウェアでセッションに組織IDをセット |
| 認可制御 | 3つの Policy で組織境界を検証。`AuthorizesRequests` トレイトで Controller から呼び出し |
| ソートカラム検証 | ユーザー入力のソートカラムをホワイトリストで検証（SQLインジェクション防止） |
| CSRF保護 | Laravel デフォルトの `VerifyCsrfToken` ミドルウェア |
| API認証 | Sanctum トークン認証。API コントローラーで組織所有権を検証 |
| パスワード | bcrypt ハッシュ（本番12ラウンド、テスト4ラウンド） |
| 二要素認証 | TOTP + リカバリーコード（Fortify） |
| XSS防止 | Blade の `{{ }}` 自動エスケープ、React の JSX 自動エスケープ |
| 入力検証 | 全リクエストに専用の FormRequest クラス（14個） |

---

## セットアップ

### 前提条件
- PHP 8.5+（pdo_pgsql, intl, gd, zip, bcmath 拡張）
- Node.js 22+
- Composer 2
- PostgreSQL 17（本番）または SQLite（ローカル開発）

### 手順

```bash
git clone https://github.com/mer-prog/invopilot.git
cd invopilot

cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed

npm install
npm run dev

# 別ターミナルで
php artisan serve
```

`http://localhost:8000` にアクセス。テストユーザー: `test@example.com`

### 環境変数

| 変数 | 説明 | 必須 |
|------|------|------|
| APP_KEY | アプリケーション暗号化キー | はい（`key:generate`で自動生成） |
| DB_CONNECTION | データベースドライバ（`sqlite` / `pgsql`） | はい |
| DB_HOST | PostgreSQL ホスト | 本番のみ |
| DB_PORT | PostgreSQL ポート（デフォルト: 5432） | 本番のみ |
| DB_DATABASE | データベース名 | 本番のみ |
| DB_USERNAME | データベースユーザー | 本番のみ |
| DB_PASSWORD | データベースパスワード | 本番のみ |
| MAIL_MAILER | メールドライバ（`log` / `smtp`） | いいえ（デフォルト: log） |
| APP_LOCALE | デフォルトロケール（`ja` / `en`） | いいえ（デフォルト: ja） |

---

## 設計判断の根拠

| 判断 | 根拠 |
|------|------|
| Inertia.js モノリス（SPA分離ではなく） | API 設計の二重管理を回避。サーバーサイドルーティングの型安全性を Wayfinder で確保 |
| Service 層の導入 | Controller の肥大化を防止。InvoiceService に請求書ライフサイクル（作成・計算・送信・支払い・複製）を集約 |
| イベント駆動の副作用管理 | 支払い記録 → ステータス更新/領収書送信/アクティビティログを Listener で分離。テスタビリティ向上 |
| Enum による型安全性 | ステータス・通貨・頻度・支払方法・ロールを PHP Enum で型制約。不正値を型レベルで排除 |
| `organization_id` スコープ | データ漏洩防止の最も確実な方法。Middleware + Policy + Model Scope の3層で保証 |
| SQLite（ローカル）+ PostgreSQL（本番） | ローカル開発の簡便性と本番のスケーラビリティを両立。DashboardService で DB ドライバ判定して互換性を維持 |
| `useTrans()` フック | PHP 翻訳ファイルを Inertia の共有データとしてフロントエンドに渡し、単一ソースの翻訳管理を実現 |
| shadcn/ui + Tailwind CSS 4 | コピー&ペーストでカスタマイズ可能なUIコンポーネント。ベンダーロックインなし |
| Pest 4 | PHPUnit より簡潔な記法。`it()` + `expect()` でテストの可読性向上 |
| dompdf（wkhtmltopdf ではなく） | Composer パッケージとして完結。外部バイナリ依存なしでコンテナデプロイが容易 |

---

## 運用コスト

| サービス | プラン | 月額 |
|----------|--------|------|
| Render.com | Free (Docker) | $0 |
| Neon PostgreSQL | Free Tier | $0 |
| GitHub Actions | Free (パブリックリポジトリ) | $0 |
| **合計** | | **$0** |

---

## 作者

[@mer-prog](https://github.com/mer-prog)
