# 后台保存层复查与修复记录

复查对象：`inc/content-overrides.php`、`inc/admin/sanitize.php`、`inc/admin/save.php`，
以及 `inc/admin/render.php` / `assets/js/admin.js`。下面的问题都已修复并验证。
主题版本 2.9.24 → **2.9.25**。

---

## 1【阻断级】没有任何覆盖内容时，整站内容被清空 —— 已修

**现象**：`springapex_content_overrides` 这个 option 不存在时（全新安装，
或用户点了「恢复默认」之后），`springapex_content()` 返回**空数组**。
首页可见正文从 6864 字掉到 3153 字，导航、hero、联系方式、页脚全部消失；
后台每个字段渲染成 `value=""`，用户一按保存就把空字符串写进数据库。

**根因**：`get_option(..., [])` 在 option 缺失时返回 `[]`；
`springapex_content_is_list([])` 为 `true`（空数组遍历零次），于是
`springapex_content_merge()` 走「列表整体替换」分支，`array_values([])`
把整个内容数组换成了空。

**改动**（`inc/content-overrides.php`）：顶层空数组当成「没有覆盖」直接返回原数据。
`springapex_content_merge()` 内部对 `[]` 的语义保持不变——repeater 被删光时，
`[]` 确实应该覆盖成空列表。

**验证**：

```bash
wp option delete springapex_content_overrides
wp eval 'echo count(springapex_content());'   # 21（修复前是 0）
```

首页正文回到 6864 字，`Xuzhou APEX Spring`、`victoria@springapex.cn`、
`+86 187 9642 2510` 全部在位。

---

## 2【中】`wp_cache_flush()` 会清空整个对象缓存 —— 已修

每次保存文案都调 `wp_cache_flush()`，线上挂 Redis / Memcached 时会把全站所有人的
缓存打掉。`update_option()` 本身已经处理了 options 缓存，这里不需要额外 flush。
现在 `springapex_content_flush_caches()` 只保留 `springapex_content_cache_flushed`
这个 action，将来接页面缓存插件时在钩子里做精确清理。

---

## 3【中】repeater 字段被拒绝时，回退取值按下标错位 —— 已修

行是可以拖动排序、也可以删中间行的。原来回退取 `$current_rows[$index]`，
用户把第 5 项移到第 1 项、同时那行某个值填错，回退拿到的是**原来第 1 项**的值，
等于把别的行的内容串了过来，提示语还只说「没有保存」。

**改动**：每行渲染一个隐藏的来源标记 `__row`（`inc/admin/render.php`，
新增行为空），sanitizer 按这个标记而不是按当前下标去找原值
（`SPRINGAPEX_ADMIN_ROW_ORIGIN`，`inc/admin/sanitize.php`）。
标记只用于比对，不会被写进内容。

**验证**：把证书第 5 项（ISO 45001）移到第 1 位，同时把它的徽标图改成一个
不存在的文件名后保存。结果第 1 行回退成 `certificates/iso-45001.png`
（它自己的原值），而不是旧第 1 行的 `certificates/iatf-16949.png`；
警告为「证书第 1 项 → 徽标图指向的主题图片不存在，已保留原内容。」

---

## 4【低】图片路径只校验字符串形状 —— 已修，并顺带修了一处真实缺陷

原来只检查了 `..`、前导 `/`、`\`、`://`、控制字符和扩展名，不确认文件真的存在。
加检查时发现一个已经存在的错误：schema 里「证书扫描件」`document` 也标成了 `image` 类型，
但它在前台是从 **`assets/documents/`** 取的，不是 `assets/images/`。也就是说
后台这 5 个字段的缩略图本来就是坏的（只是之前没人点开看）。

**改动**：

- schema 的 image 字段支持 `'base'`，「证书扫描件」声明为 `assets/documents`；
  预览和保存校验共用 `springapex_admin_image_base()`，两边不会再对不上。
- 保存时校验文件确实存在于该目录，不存在就拒绝并保留原值。
- 新增 `springapex_file_url()`（`inc/helpers.php`），
  `parts/certification-gallery.php` 和 `templates/sustainability.php` 改用它——
  这样运营从媒体库上传新证书扫描件（存下来是附件 ID）时前台也能正确出链接，
  以前只支持主题自带文件名。

**验证**：公司实力页后台 11 张预览图全部加载成功，0 张坏图。

---

## 5【新发现】「导航菜单」页面其实不生效 —— 已删除该页面，改走 WP 原生菜单

`springapex_navigation_items()` 优先用「外观 → 菜单」里分配到 `primary` 位置的菜单，
内容数组里的 `nav` 只是**无数据库静态预览时的兜底**。当前站分配了 `ApexSpring Primary`，
所以那个后台页面改什么都不会显示——运营会以为改了，其实没改。

这不是当初的产品决策，是两条路径撞车：`inc/setup.php:22` 从初始提交就按 WP 标准
注册了 `primary`/`footer` 两个菜单位置，`inc/seed.php` 自动建菜单并分配；
而我做「网站内容」后台时按 schema 把内容数组每个顶层分组都铺成一页，`nav` 也顺手做了。

**决策：能用 WP 原生能力就不自己造**，减少二次开发带来的意外 bug。所以：

- 从 `inc/admin/schema.php` 删掉 `nav` 这个 screen（原位置留了注释说明去向）；
- 「内容总览」的「不在这里改的内容」里新增一块「顶部导航菜单」，直达 `nav-menus.php`，
  说明「拖动排序，拖到右边缩进就是二级菜单」；
- 一起删掉了为此临时加的冲突提示与 `.sa-notice--warning` 样式。

内容数组里的 `nav` 保留不动——静态预览还要靠它兜底。

顺带记录：`springapex_navigation_items()` 的 WP-menu 分支里有一层历史纠正逻辑
（`news`/`resources`/`catalog`/`view-our-catalog` 一律改回 News、`capabilities`
旧锚点改写成 `/capabilities/`、`solutions` 显示成 Industries）。这说明那个菜单以前
被手改乱过。现在导航既然只有一个入口，等菜单确认无误后可以考虑把这层兜底退役。

---

## 6【记录】`lines` 类型会静默丢弃空行

符合当前用法，暂不改。以后若有需要保留段落间隔的多行字段，这个类型不适用。

---

## 端到端验收结果

| 项目 | 结果 |
| --- | --- |
| 无覆盖时前台与后台取值 | 通过（21 个内容分组，字段有真实默认值） |
| 单字段保存 | 通过（tagline 落库，其余字段原值不变） |
| 非法邮箱拒绝 | 通过（原值 `victoria@springapex.cn` 保留） |
| repeater 排序 + 删除 | 通过（Contact 移到首位、删除 News，子菜单跟着行走） |
| repeater 拒绝回退 | 通过（回退到本行原值，见第 3 条） |
| 图片存在性校验与 base | 通过（11 张预览图全部正常） |
| 恢复默认 | 通过（只清掉本页的 root，其他页的覆盖保留） |

测试产生的覆盖数据已全部清除，`springapex_content_overrides` 现在不存在，
也就是最容易出问题的那个初始状态——现在它是好的。
