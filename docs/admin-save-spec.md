# 后台内容保存层 — 开发需求（交给后端）

前端（管理界面）已完成，见 `inc/admin/`、`assets/css/admin.css`、`assets/js/admin.js`。
现在缺的只有**保存与读取**这一层。界面上的「保存修改」按钮目前是 `disabled`，接好之后去掉即可。

## 1. 现状

- 全站文案数据来自 `springapex_content()`（`inc/content.php` + `inc/content-enhancements.php`），目前是硬编码数组。
- 该函数末尾已经有钩子：`$data = apply_filters('springapex_content', $data);`
  **这就是接入点** —— 不要改 `inc/content.php` 的数组本身，用这个 filter 把用户改过的值覆盖上去。
- 页面模板一律通过 `springapex_get('a.b.c', $default)` 读取，不需要改动。

## 2. 表单提交格式

每个后台页面是一个普通 POST 表单：

- `action=springapex_save_content`（提交到 `admin-post.php`）
- `screen=<屏幕键>`，取值见 `springapex_admin_screens()` 的键：
  `brand nav home products solutions capabilities videos about company news contact`
- nonce：`wp_nonce_field('springapex_save_content_' . $screen)`
- 字段名就是内容路径，例如：
  - `springapex_content[home][hero][title]`
  - `springapex_content[nav][1][children][0][label]`
  - `springapex_content[company][quality][certificates][2][name]`

也就是说 `$_POST['springapex_content']` 本身已经是一棵和内容数组同构的子树，**不需要逐字段映射**。

## 3. 需要实现的内容

### 3.1 保存

```php
add_action('admin_post_springapex_save_content', 'springapex_admin_handle_save');
```

处理流程：

1. `current_user_can('edit_theme_options')`，否则 `wp_die`。
2. 校验 `screen` 在白名单内；校验 nonce `springapex_save_content_{screen}`。
3. 取 `$_POST['springapex_content']`（**不要**用 `$_REQUEST`，不要 `stripslashes` 之外的魔改；WP 会自动加斜杠，需要 `wp_unslash()`）。
4. 按 schema 递归清洗（见 3.3），得到干净子树。
5. 与已存的 option 合并后写回：
   - option 名：`springapex_content_overrides`
   - 合并规则：**列表整体替换，关联数组递归合并**。
     重排、删除依赖「整体替换」，如果对列表做递归合并，删掉最后一项会删不掉。
   - 只合并本次 screen 涉及的顶层键，避免一个页面把别的页面覆盖掉。
6. `wp_safe_redirect(add_query_arg('sa-saved', '1', wp_get_referer()))` + `exit`。

### 3.2 读取

```php
add_filter('springapex_content', function (array $data): array {
    $overrides = get_option('springapex_content_overrides', []);
    return is_array($overrides) ? springapex_content_merge($data, $overrides) : $data;
});
```

`springapex_content_merge()` 的规则要和保存时一致（列表替换、关联数组递归合并）。
注意这个 filter 在前台每个请求都会跑，`get_option` 已经走 WP 自己的缓存，不用再加一层。

### 3.3 清洗规则（按 schema 里的 `type`）

字段类型在 `springapex_admin_screens()` 里，用 `path` 定位：

| type | 清洗 |
|---|---|
| `text` / `icon` | `sanitize_text_field()`；`icon` 另外校验值在 `springapex_icon_map()` 的键里，不在就丢弃该字段 |
| `textarea` | `sanitize_textarea_field()`（要保留换行，首页主标题靠换行断行） |
| `url` | `esc_url_raw()`，只允许 `http` / `https` |
| `email` | `sanitize_email()`，非法就丢弃并回报错误 |
| `tel` | 只保留数字、空格、`+`、`-`、`()` |
| `youtube` | 只允许 `[A-Za-z0-9_-]{11}`；用户如果粘了整条网址，**自动提取 ID**（v= 参数或 youtu.be 路径），这是很常见的误操作 |
| `lines` | 按 `\R` 切行，逐行 `sanitize_text_field()`，去掉空行，存为索引数组 |
| `image` | 见下 |
| `repeater` | 递归；重新 `array_values()` 保证下标连续；行内字段按各自 type 清洗；**丢弃 schema 里没有声明的键** |

**未在 schema 中声明的字段一律丢弃**，不要原样写入 option。

### 3.4 图片字段

图片的值有三种历史形态，`springapex_image()` 三种都支持：
附件 ID（int）、`assets/images/` 下的相对文件名（string）、`['id' => int, 'file' => string]`。

- 后台选图走媒体库，提交上来的是**纯数字的附件 ID 字符串** → 存成 `(int)`。
- 用户没动过的字段，提交上来的是**原来的相对文件名** → 原样保留（要校验：不含 `..`、不以 `/` 开头、后缀在 `jpg jpeg png webp avif svg` 内）。
- 两种以外的值一律丢弃，回退到原内容。

### 3.5 反馈与容错

- 保存成功：`admin_notices` 显示「已保存，去前台刷新看看效果」，并附前台页面链接（`$screen['preview']`）。
- 有字段被丢弃：显示黄色提示，**列出具体是哪个字段、为什么**（例如「业务邮箱格式不对，没有保存，其他修改已保存」）。使用者不是技术人员，只说"保存失败"没有用。
- 「恢复默认」：每个屏幕提供一个按钮，删掉该屏幕在 option 里对应的顶层键即可回到 `inc/content.php` 的原始内容。这是给使用者的安全网，优先级不低。

### 3.6 其他

- 保存后如果站点有对象缓存/页面缓存，需要清一次。
- option 建议 `autoload = yes`（每个前台请求都要用）。整棵内容树不大，但如果超过 ~200KB 要改成 `no` 并自己做一次进程内缓存。
- 目前 `springapex_content()` 用 `static $data` 缓存，filter 只会跑一次，没问题。

## 4. 前端这边留的接口

- 字段名规则：`springapex_content` + 路径逐级 `[key]`，见 `springapex_admin_field_name()`。
- schema 是纯数据，加字段只要改 `inc/admin/schema.php`，不需要动渲染层。
- 保存层接好之后，把 `inc/admin/render.php` 里保存按钮的 `disabled` 去掉，并删掉「界面预览版」那条 `sa-notice`。
