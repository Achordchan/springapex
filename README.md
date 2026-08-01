# SpringApex WordPress 主题

SpringApex 精密弹簧独立站的经典 WordPress 主题。运行栈为 PHP + WordPress + MySQL；前端由 PHP 模板、分层 CSS 和原生 JavaScript 组成，不依赖 GSAP、Three.js、Google Fonts 或前端构建工具。

## 运行要求

- WordPress 6.4 或更高版本
- PHP 8.0 或更高版本
- WordPress 支持的 MySQL / MariaDB
- 启用 JavaScript 的现代浏览器

主题声明位于 `style.css`，主题入口位于 `functions.php`。

## 目录结构

```text
style.css                         主题声明
functions.php                     加载主题模块
front-page.php                    首页入口
archive-spring_product.php        产品列表入口
single-spring_product.php         产品详情入口
archive-spring_solution.php       行业方案列表入口
page-about.php                    About 页面入口
page-capabilities.php             Capabilities 独立页面入口
page-contact.php                  Contact 页面入口
inc/
  setup.php                       主题支持、资源加载、首图预加载
  post-types.php                  产品、行业方案、询盘 CPT 与产品字段
  seed.php                        主题启用后的非破坏性初始化
  contact.php                     询盘保存、邮件、限流、文件上传
  customizer.php                  公司联系信息设置
  content.php                     默认内容与数据库内容适配
  helpers.php                     路由、图片、图标、导航辅助
templates/                        六个核心页面的业务模板
parts/                            Header、Footer、CTA 等公共片段
assets/css/                       foundation、components、pages、responsive
assets/js/main.js                 菜单、滚动揭示、数字与产品页交互
assets/images/                    主题静态图片
assets/icons/iconoir/             本地图标与许可证
preview/                          无 WordPress 时使用的只读 PHP 预览兼容层
```

## 本地 PHP 预览

本地预览只渲染公开页面，不加载 WordPress Core、数据库、后台钩子或真实表单处理。Contact 表单在预览模式会明确显示“未发送”，不会保存询盘、发送邮件或上传文件。

推荐在项目根目录启动前台预览：

```bash
./start.sh           # 默认使用 8877 端口
./start.sh 9000      # 指定端口
PORT=9000 ./start.sh # 使用环境变量指定端口
```

服务在当前终端以前台方式运行，按 `Ctrl+C` 或关闭终端即可停止；不会创建 PID 文件、后台日志或 `end.sh`。

也可以在项目根目录直接运行底层预览脚本：

```bash
cd /path/to/超拓弹簧
./start-preview.sh
```

默认端口是 `8877`。也可以指定端口；只有显式设置环境变量时才自动打开浏览器：

```bash
./start-preview.sh 9000
SPRINGAPEX_PREVIEW_OPEN=1 ./start-preview.sh 9000
```

六个效果图路由及 Resources 导航页：

| 页面 | 地址 |
| --- | --- |
| Home | `http://127.0.0.1:8877/preview/index.php` |
| Products | `http://127.0.0.1:8877/preview/index.php?sa_page=products` |
| Product | `http://127.0.0.1:8877/preview/index.php?sa_page=product&product=compression-springs` |
| Solutions | `http://127.0.0.1:8877/preview/index.php?sa_page=solutions` |
| About | `http://127.0.0.1:8877/preview/index.php?sa_page=about` |
| Contact | `http://127.0.0.1:8877/preview/index.php?sa_page=contact` |
| Resources | `http://127.0.0.1:8877/preview/index.php?sa_page=resources` |

也可以不使用脚本，直接从主题目录启动 PHP 内置服务器：

```bash
php -S 127.0.0.1:8877 -t .
```

## 安装到 WordPress

1. 将项目根目录中的主题运行文件（`style.css`、PHP 模板、`inc/`、`parts/`、`templates/`、`assets/`）复制到 `wp-content/themes/springapex`；不要复制 `node_modules/`、验收截图或证据目录。
2. 在 WordPress 后台启用 **SpringApex** 主题。
3. 主题首次启用会补充缺失的 Home、About、Contact、Resources 页面，产品、行业方案和主导航；已有同 slug 内容不会被覆盖。后续主题升级只迁移仍存在的对象，不会重建管理员已删除或改名的默认内容。
4. 在“设置 → 固定链接”保存一次，使 `/products/{slug}/`、产品归档和行业方案归档规则生效。
5. 在“外观 → 自定义 → SpringApex Company Details”配置对外邮箱、询盘接收邮箱、电话、地址、营业时间和 LinkedIn。
6. 在服务器上配置可用的 WordPress 邮件发送方式，并实际验证询盘保存、通知邮件和文件上传。
7. 按下方要求禁止 Web Server 和 CDN 对外访问 `springapex-private` 目录，用真实公网地址验证返回 403 或 404 后，再在 `wp-config.php` 启用私有上传门禁。

Products 与 Solutions 由自定义文章类型归档提供；产品详情使用标准 `single-spring_product.php` 模板。产品规格、材料、应用和目录链接可在产品编辑页的 Product Details 区域维护。

## 内容与询盘

- `spring_product` = 产品内容，支持标题、正文、摘要、特色图片、排序和产品规格字段。
- `spring_solution` = 行业方案内容，支持标题、正文、摘要、特色图片和排序。
- `spring_inquiry` = 后台私有询盘记录，不提供前台查询或 REST API；主题默认只给 Administrator 角色授予询盘查看、编辑、下载与删除权限。
- 联系表单同时支持 JavaScript AJAX 和无 JavaScript `admin-post.php` POST 路径；两者共用 nonce、蜜罐、提交时长、字段白名单、IP 限流和邮箱限流。
- 上传上限为 10 MB；仅允许 PDF、ZIP、DWG、DXF、STEP、IGES、JPG 和 PNG，服务端同时校验扩展名与文件签名。JPG/PNG 在真实 WordPress 中还会通过核心图片 MIME 检测。
- 生产环境需同步设置 PHP `upload_max_filesize=10M`、`post_max_size=12M`，Nginx `client_max_body_size 12m`，并确保 CDN 请求体上限不少于 12 MB；上线时验证 9.9 MB 文件成功、10.1 MB 文件被拒绝。
- 新图纸不创建媒体库 attachment，不保存公开 URL；文件使用随机名存入 `wp-content/uploads/springapex-private/{year}/{month}/`，数据库只保存私有根目录下的相对路径。
- 产品与方案在真实 WordPress 中以数据库为准：删除全部内容、清空字段或取消全部精选后，不会再被主题 seed 回退覆盖。seed fallback 仅用于无 WordPress 的本地预览。

### 私有图纸目录的服务器保护

主题会在私有目录根部创建 `.htaccess`、`web.config` 和防目录列表的 `index.php`。Apache 需允许该目录读取 `.htaccess`；Nginx / 宝塔 / 1Panel 不会执行 `.htaccess`，必须在站点 Nginx 配置中按实际 uploads URL 增加拒绝规则，例如：

```nginx
location ^~ /wp-content/uploads/springapex-private/ {
    return 404;
}
```

如果使用多站点、自定义 uploads 路径、CDN 或对象存储，需改为实际 URL，并排除该目录的同步和缓存。仅有上传后使用真实公网 URL 请求并确认 403/404，才能证明目录不可公开访问。旧版主题已经上传到媒体库的图纸不会自动迁移，上线前需单独审计。

目录和 CDN 规则验证完成后，在 `wp-config.php` 的“停止编辑”标记前加入：

```php
define('SPRINGAPEX_PRIVATE_UPLOADS_PROTECTED', true);
```

未显式定义为 `true` 时，普通询盘仍可保存和发信，但所有图纸上传会返回 503，后台也会显示配置警告。该常量只表示公开访问已被阻断，不代表附件内容可信；ZIP 与 CAD 文件仍可能包含恶意载荷。生产环境应在 `wp_mail()` 前接入 ClamAV 或托管恶意软件扫描，并在员工下载终端保留安全软件防护。

## 动效与性能

- 页面滚动揭示使用 `IntersectionObserver`，数字动画使用 `requestAnimationFrame`。
- `prefers-reduced-motion: reduce` 下关闭非必要动画。
- CSS 按基础、组件、页面、响应式四层加载，不依赖外部字体或动画 CDN。
- 主题静态图片使用 `<picture>` 输出 AVIF，并以响应式 WebP 作为 `<img>` 回退；两者提供 480、768、1200 和原始宽度候选，避免旧回退路径直接下载全尺寸 PNG。首屏预加载复用同一 AVIF `srcset`，WordPress 媒体库图片仍由核心输出响应式尺寸。
- 生产服务器应对带主题版本号的 CSS、JavaScript、图片和 SVG 设置长期 `Cache-Control`，并启用 Brotli 或 Gzip；上线后必须通过真实域名响应头确认，而不是只依据本地文件体积。
- 当前视觉不需要 Three.js，避免增加 3D 运行时和移动端 GPU 成本。

## 验证边界

本地 PHP 预览只能证明模板与静态资源可渲染。交付生产前仍需在真实 WordPress + MySQL 环境验证：主题启用与初始化、固定链接、CPT 后台编辑、Administrator/Editor/Author 权限矩阵、无 JavaScript POST 和 AJAX 询盘、邮件、全部允许/拒绝文件样本、私有目录公网 403/404、CDN 缓存与服务器日志。首页、About 和 Contact 主体内容仍由受控 PHP 数组提供，此次未扩展为后台全字段编辑。

---

## 2026-07-20 样式优化

针对"文字太小、页面太窄"问题完成全面优化：

- 容器宽度：994px → 1180px，宽屏模式 1280px
- 正文字号：15px → 16px
- 标题层级：主标题 52-72px，区块标题 28-36px
- 组件字号：按钮 14px，导航 13px，表单 14px
- 响应式断点同步更新，新增 1320px 大屏适配

详见 [OPTIMIZATION-SUMMARY.md](OPTIMIZATION-SUMMARY.md)
