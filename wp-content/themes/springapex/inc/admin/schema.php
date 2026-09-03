<?php
/**
 * Admin screen definitions.
 *
 * Every screen is plain data: which content paths it edits, how they are
 * grouped, and what a non-technical editor needs to be told about each field.
 * The renderer in render.php turns this into the actual form.
 *
 * Field shape:
 *   path  — dot path into springapex_content(), also the form field name
 *   label — Chinese label shown to the editor
 *   type  — text | textarea | url | email | tel | image | youtube | lines | repeater
 *   help  — one line of guidance (what it is, where it shows, any limit)
 *   fields — sub-fields, for repeater rows
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_admin_screens(): array
{
    static $screens = null;
    if ($screens !== null) {
        return $screens;
    }

    $screens = [
        'brand' => [
            'label' => '品牌与联系方式',
            'title' => '品牌与联系方式',
            'intro' => '这里改的内容会出现在网站的每一个页面：顶部 Logo 旁的名称、页脚、以及联系页的联系方式区块。',
            'preview' => '/',
            'sections' => [
                [
                    'title' => '公司名称',
                    'desc' => '客户在页头、页脚和邮件里看到的名字。',
                    'fields' => [
                        ['path' => 'brand.logo', 'label' => '站点 LOGO', 'type' => 'image', 'help' => '透明背景的横版 PNG/SVG，会替换页头和页脚的 Logo。选好后请在定制器「站点身份」里同步设置站点图标（浏览器标签页小图标）。'],
                        ['path' => 'brand.name', 'label' => '品牌名', 'type' => 'text', 'help' => '显示在页头 Logo 旁，例如 NorenSpring。'],
                        ['path' => 'brand.tagline', 'label' => '品牌标语', 'type' => 'text', 'help' => '品牌名下方的一行小字，全大写效果由前台自动处理。'],
                        ['path' => 'brand.company', 'label' => '公司全称', 'type' => 'text', 'help' => '用于页脚版权行和询盘邮件签名，请填写营业执照上的英文全称。'],
                    ],
                ],
                [
                    'title' => '联系方式',
                    'desc' => '页脚、联系页、以及右侧悬浮的快捷咨询按钮都读取这里。',
                    'fields' => [
                        ['path' => 'brand.email', 'label' => '业务邮箱', 'type' => 'email', 'help' => '客户询盘会发送到这个邮箱，改错会收不到询盘。'],
                        ['path' => 'brand.phone', 'label' => '电话', 'type' => 'tel', 'help' => '含国家区号，例如 +86 187 9642 2510。'],
                        ['path' => 'brand.whatsapp', 'label' => 'WhatsApp 号码', 'type' => 'tel', 'help' => '悬浮按钮会用这个号码打开 WhatsApp 对话；留空则隐藏该按钮。'],
                        ['path' => 'brand.address', 'label' => '公司地址', 'type' => 'textarea', 'help' => '英文地址，显示在页脚和联系页地图旁。'],
                        ['path' => 'brand.hours', 'label' => '工作时间', 'type' => 'text', 'help' => '例如 Monday – Friday, China Standard Time。'],
                    ],
                ],
                [
                    'title' => '社交媒体',
                    'desc' => '填完整网址（以 https:// 开头）。留空的平台不会在页脚显示图标。',
                    'fields' => [
                        ['path' => 'brand.linkedin', 'label' => 'LinkedIn', 'type' => 'url', 'help' => '公司主页链接。'],
                        ['path' => 'brand.facebook', 'label' => 'Facebook', 'type' => 'url', 'help' => '公司主页链接。'],
                        ['path' => 'brand.x', 'label' => 'X（原 Twitter）', 'type' => 'url', 'help' => '公司主页链接。'],
                        ['path' => 'brand.instagram', 'label' => 'Instagram', 'type' => 'url', 'help' => '公司主页链接。'],
                        ['path' => 'brand.tiktok', 'label' => 'TikTok', 'type' => 'url', 'help' => '公司主页链接。'],
                    ],
                ],
            ],
        ],

        // 顶部导航不在这里改：springapex_navigation_items() 用的是 WordPress
        // 原生菜单（外观 → 菜单，primary/footer 两个位置）。内容数组里的 nav
        // 只是无数据库静态预览时的兜底，做成后台页面会让运营改了却不生效。

        'home' => [
            'label' => '首页',
            'title' => '首页',
            'intro' => '首页从上到下的各个板块。产品卡片、新闻列表等内容来自「产品」「新闻」里的条目，不在这里改。',
            'preview' => '/',
            'sections' => [
                [
                    'title' => '首屏大图',
                    'desc' => '客户打开网站第一眼看到的区域。标题建议不超过三行。',
                    'fields' => [
                        ['path' => 'home.hero.title', 'label' => '主标题', 'type' => 'textarea', 'help' => '换行的位置就是前台断行的位置。'],
                        ['path' => 'home.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => '一到两句话说明公司能做什么。'],
                        ['path' => 'home.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => '建议横版、主体居中、背景干净，宽度不小于 1600px。'],
                        ['path' => 'home.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => '留空则手机上沿用电脑端配图。'],
                        ['path' => 'home.hero.video_cta.label', 'label' => '视频按钮文字', 'type' => 'text', 'help' => '例如 Play a Video。'],
                        ['path' => 'home.hero.video_cta.youtube_id', 'label' => 'YouTube 视频 ID', 'type' => 'youtube', 'help' => '只填 ID，不是整条网址。'],
                        ['path' => 'home.hero.quote_cta.label', 'label' => '询价按钮文字', 'type' => 'text', 'help' => '例如 Request a Quote。'],
                        ['path' => 'home.hero.quote_cta.href', 'label' => '询价按钮链接', 'type' => 'route', 'help' => '从下拉里选一个目的地，默认指向联系页的询价表单。'],
                        ['path' => 'home.video_dialog_title', 'label' => '视频弹窗标题', 'type' => 'text', 'help' => '点击视频按钮后弹窗顶部显示。'],
                    ],
                ],
                [
                    'title' => '推荐产品板块',
                    'desc' => '产品卡片来自产品后台，这里维护板块标题和入口。',
                    'fields' => [
                        ['path' => 'home.sections.products.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.products.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.products.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'home.sections.products.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.products.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                    ],
                ],
                [
                    'title' => '四大优势',
                    'desc' => '四张带图标的卡片。建议保持四条，多于四条排版会变形。',
                    'fields' => [
                        ['path' => 'home.sections.why.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.why.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.why.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'home.sections.why.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.why.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                        [
                            'path' => 'home.pillars',
                            'type' => 'repeater',
                            'label' => '优势',
                            'item_label' => '优势',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => '尽量控制在 4 个英文单词内。'],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => '一句话，约 10–15 个英文单词。'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '应用领域卡片',
                    'desc' => '首页自动显示 Industry Solutions 中排在最前的四个行业。',
                    'signpost' => [
                        'path' => 'home-industry-solutions',
                        'title' => '首页行业卡片请到「Industry Solutions」编辑',
                        'text' => '首页按行业方案后台排序显示前四项，名称、配图和链接都与 Solutions 页保持一致。',
                        'button' => '前往 Industry Solutions',
                        'url' => 'edit.php?post_type=spring_solution',
                    ],
                    'fields' => [
                        ['path' => 'home.sections.industries.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.industries.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.industries.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'home.sections.industries.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.industries.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                    ],
                ],
                [
                    'title' => '生产流程',
                    'desc' => '一条横向的流程带，从左到右显示。',
                    'fields' => [
                        ['path' => 'home.sections.process.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.process.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'home.sections.process.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'home.sections.process.note', 'label' => '底部总结', 'type' => 'text', 'help' => ''],
                        [
                            'path' => 'home.process',
                            'type' => 'repeater',
                            'label' => '流程步骤',
                            'item_label' => '步骤',
                            'title_key' => 'label',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'label', 'label' => '步骤名称', 'type' => 'text', 'help' => '一到两个英文单词。'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '常见问题',
                    'desc' => '这是全站公共问答，同时出现在首页和所有产品详情页。',
                    'signpost' => [
                        'path' => 'site-common-faq',
                        'title' => '常见问答请到「网站通用 → 常见问题」编辑',
                        'text' => '在总编辑入口修改一次，首页和所有产品详情页会同步更新。',
                        'button' => '前往常见问题',
                        'url' => 'admin.php?page=' . SPRINGAPEX_ADMIN_SLUG . '-faq',
                    ],
                    'fields' => [],
                ],
            ],
        ],

        'faq' => [
            'label' => '常见问题',
            'title' => '全站常见问题',
            'intro' => '全站公用的问答内容，目前显示在首页和所有产品详情页。',
            'preview' => '/',
            'sections' => [
                [
                    'title' => '问答内容',
                    'desc' => '修改后会同时影响首页和产品详情页。',
                    'fields' => [
                        [
                            'path' => 'home_faq',
                            'type' => 'repeater',
                            'label' => '问答',
                            'item_label' => '问答',
                            'title_key' => 'question',
                            'fields' => [
                                ['path' => 'question', 'label' => '问题', 'type' => 'text', 'help' => '客户视角的一句问题。'],
                                ['path' => 'answer', 'label' => '回答', 'type' => 'textarea', 'help' => '两到三句话说清楚。'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'products' => [
            'label' => '产品页',
            'title' => '产品页',
            'intro' => '这里编辑产品列表页和所有产品详情页共用的内容。产品卡片及每个产品自己的详细内容，请到左侧菜单「Spring Products」编辑。',
            'preview' => '/products/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'products.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'products.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => '横版，宽度不小于 1600px。'],
                        ['path' => 'products.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => '留空则手机上沿用电脑端配图。'],
                        ['path' => 'products.hero.primary_cta.label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.hero.primary_cta.href', 'label' => '按钮链接', 'type' => 'route', 'help' => '从下拉里选一个目的地。'],
                        ['path' => 'products.hero.drawing_cta.label', 'label' => '发图按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.hero.drawing_cta.href', 'label' => '发图按钮链接', 'type' => 'route', 'help' => ''],
                    ],
                ],
                [
                    'title' => '开始方式',
                    'desc' => '页面头部下方的三张入口卡片，最多三项。',
                    'fields' => [
                        ['path' => 'products.entry.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.entry.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.entry.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'products.entry.items', 'type' => 'repeater', 'label' => '入口卡片', 'item_label' => '卡片', 'title_key' => 'title', 'max_items' => 3, 'fields' => [
                            ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                            ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                            ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                            ['path' => 'href', 'label' => '链接', 'type' => 'route', 'help' => ''],
                        ]],
                    ],
                ],
                [
                    'title' => '产品分类',
                    'desc' => '这里只说明编辑位置，不再提供重复的产品表单。',
                    'signpost' => [
                        'path' => 'products-categories',
                        'title' => '产品卡片请到「Spring Products」编辑',
                        'text' => '产品名称、网址标识、卡片介绍、产品图和首页推荐，都由对应的产品条目统一管理。',
                        'button' => '前往 Spring Products',
                        'url' => 'edit.php?post_type=spring_product',
                    ],
                    'fields' => [],
                ],
                [
                    'title' => '产品大菜单',
                    'desc' => '页头 Products 展开后左侧的品牌展示图，与单个产品的主图独立。',
                    'fields' => [
                        ['path' => 'products.mega_menu.feature_image', 'label' => '左侧展示图', 'type' => 'image', 'help' => '建议使用深色、横版、主体居中的弹簧图。这张图不会随产品主图变化。'],
                    ],
                ],
                [
                    'title' => '产品详情页 · 质量检测图片',
                    'desc' => '所有产品详情页的“Quality & Testing”区域共用这一组图片，修改一次会同步到全部产品。',
                    'fields' => [
                        ['path' => 'products.detail_media.quality.load_test.image', 'label' => '载荷测试大图', 'type' => 'image', 'required' => true, 'help' => '区域左侧的大图，固定版式必需。'],
                        ['path' => 'products.detail_media.quality.load_test.alt', 'label' => '载荷测试图说明', 'type' => 'text', 'help' => '请如实描述替换后的图片内容，供搜索引擎和读屏软件使用。'],
                        ['path' => 'products.detail_media.quality.dimensional_inspection.image', 'label' => '尺寸检测图', 'type' => 'image', 'required' => true, 'help' => '区域右上图片，固定版式必需。'],
                        ['path' => 'products.detail_media.quality.dimensional_inspection.alt', 'label' => '尺寸检测图说明', 'type' => 'text', 'help' => '请如实描述替换后的图片内容，供搜索引擎和读屏软件使用。'],
                        ['path' => 'products.detail_media.quality.material_analysis.image', 'label' => '材料分析图', 'type' => 'image', 'required' => true, 'help' => '区域右下图片，固定版式必需。'],
                        ['path' => 'products.detail_media.quality.material_analysis.alt', 'label' => '材料分析图说明', 'type' => 'text', 'help' => '请如实描述替换后的图片内容，供搜索引擎和读屏软件使用。'],
                    ],
                ],
                [
                    'title' => '产品详情页 · 包装交付图片',
                    'desc' => '所有产品详情页的“Packing & Delivery”区域共用这一组图片，修改一次会同步到全部产品。',
                    'fields' => [
                        ['path' => 'products.detail_media.delivery.protected_packaging', 'label' => '防护包装', 'type' => 'image', 'required' => true, 'help' => '固定版式必需。'],
                        ['path' => 'products.detail_media.delivery.custom_crates', 'label' => '定制木箱', 'type' => 'image', 'required' => true, 'help' => '固定版式必需。'],
                        ['path' => 'products.detail_media.delivery.palletized_labelled', 'label' => '托盘与标签', 'type' => 'image', 'required' => true, 'help' => '固定版式必需。'],
                        ['path' => 'products.detail_media.delivery.global_delivery', 'label' => '国际交付', 'type' => 'image', 'required' => true, 'help' => '固定版式必需。'],
                    ],
                ],
                [
                    'title' => '产品选型（按受力方向）',
                    'desc' => '产品列表页中部的选型引导板块，帮助客户按受力方向找到对应的弹簧类别。',
                    'fields' => [
                        ['path' => 'products.range.eyebrow', 'label' => '产品系列小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.range.title', 'label' => '产品系列标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.range.text', 'label' => '产品系列说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'product_selection.eyebrow', 'label' => '选型小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'product_selection.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'product_selection.text', 'label' => '说明', 'type' => 'textarea', 'help' => '一句话。'],
                        [
                            'path' => 'product_selection.items',
                            'type' => 'repeater',
                            'label' => '选型卡片',
                            'item_label' => '卡片',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '产品详情页 · 质量佐证',
                    'desc' => '每个产品详情页底部「QUALITY & DOCUMENTS」左侧的质检佐证卡片，所有产品共用这一组。',
                    'fields' => [
                        [
                            'path' => 'quality_evidence',
                            'type' => 'repeater',
                            'label' => '佐证卡片',
                            'item_label' => '卡片',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'solutions' => [
            'label' => '行业方案页',
            'title' => '行业方案页',
            'intro' => '这里只编辑行业方案列表页和案例页的页面头部。行业卡片及每个行业的详细内容，请到左侧菜单「Industry Solutions」编辑。',
            'preview' => '/solutions/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'solutions.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'solutions.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'solutions.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'solutions.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => ''],
                    ],
                ],
                [
                    'title' => '行业卡片',
                    'desc' => '这里只说明编辑位置，不再提供重复的卡片表单。',
                    'signpost' => [
                        'path' => 'solutions-industry-cards',
                        'title' => '行业卡片请到「Industry Solutions」编辑',
                        'text' => '行业名称、网址标识、卡片标语和配图，都由对应的行业方案条目统一管理。',
                        'button' => '前往 Industry Solutions',
                        'url' => 'edit.php?post_type=spring_solution',
                    ],
                    'fields' => [],
                ],
                [
                    'title' => '底部咨询区',
                    'desc' => '行业列表下方的咨询行动区。',
                    'fields' => [
                        ['path' => 'solutions.cta.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'solutions.cta.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'solutions.cta.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'solutions.cta.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                        ['path' => 'solutions.cta.image', 'label' => '配图', 'type' => 'image', 'help' => '横版弹簧产品图。'],
                        ['path' => 'solutions.cta.image_alt', 'label' => '图片说明', 'type' => 'text', 'help' => ''],
                    ],
                ],
                [
                    'title' => '案例页头部',
                    'desc' => '案例列表页（Case Studies）的头部。案例条目请到左侧菜单「Case Studies」里编辑。',
                    'fields' => [
                        ['path' => 'case_studies.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'case_studies.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'case_studies.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'case_studies.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => ''],
                    ],
                ],
            ],
        ],

        'capabilities' => [
            'label' => '定制能力页',
            'title' => '定制能力页',
            'intro' => '「Custom Springs」页面。这是客户上传图纸前看到的最后一页，内容要能回答"你们能不能做我的件"。',
            'preview' => '/capabilities/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'capabilities.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'capabilities.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'capabilities.hero.cta.label', 'label' => '按钮文字', 'type' => 'text', 'help' => '例如 Upload Your Drawing。'],
                        ['path' => 'capabilities.hero.cta.href', 'label' => '按钮链接', 'type' => 'route', 'help' => '从下拉里选一个目的地。'],
                        ['path' => 'capabilities.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'capabilities.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => ''],
                    ],
                ],
                [
                    'title' => '开篇说明',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'capabilities.intro.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'capabilities.intro.text', 'label' => '正文', 'type' => 'textarea', 'help' => '两到三句话。'],
                    ],
                ],
                [
                    'title' => '能力项',
                    'desc' => '四项能力卡片。',
                    'fields' => [
                        [
                            'path' => 'capabilities.items',
                            'type' => 'repeater',
                            'label' => '能力',
                            'item_label' => '能力',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '需要客户提供的资料',
                    'desc' => '告诉客户询价时要准备什么，能明显减少来回沟通。',
                    'fields' => [
                        ['path' => 'capabilities.project_brief.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => '标题上方的一行小字，全大写。'],
                        ['path' => 'capabilities.project_brief.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'capabilities.project_brief.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'capabilities.project_brief.image', 'label' => '配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'capabilities.project_brief.image_alt', 'label' => '图片说明', 'type' => 'text', 'help' => ''],
                        ['path' => 'capabilities.project_brief.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'capabilities.project_brief.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                        [
                            'path' => 'capabilities.project_brief.items',
                            'type' => 'repeater',
                            'label' => '资料项',
                            'item_label' => '资料项',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '生产流程',
                    'desc' => '定制能力页中部从左到右的生产流程步骤带（目前只有这个页面显示它）。',
                    'fields' => [
                        [
                            'path' => 'manufacturing_process',
                            'type' => 'repeater',
                            'label' => '流程步骤',
                            'item_label' => '步骤',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'step', 'label' => '序号', 'type' => 'text', 'help' => '如 01、02，顺序显示。'],
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                                ['path' => 'image', 'label' => '配图', 'type' => 'image', 'help' => '横版实拍。'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '质量佐证图',
                    'desc' => '定制能力页尾部的尺寸参考图，配合「发图纸或填尺寸」的说明展示。',
                    'fields' => [
                        ['path' => 'capabilities.verification.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'capabilities.verification.image', 'label' => '参考图', 'type' => 'image', 'help' => '弹簧尺寸标注示意图。'],
                        ['path' => 'capabilities.verification.image_alt', 'label' => '图片说明', 'type' => 'text', 'help' => '给搜索引擎和读屏软件看的描述，请如实填写图里是什么。'],
                    ],
                ],
            ],
        ],

        'videos' => [
            'label' => '制造视频',
            'title' => '制造视频页',
            'intro' => '视频页的头部、置顶视频和视频分类卡片。',
            'preview' => '/manufacturing-videos/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'manufacturing_videos.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => '全大写的一行小字。'],
                        ['path' => 'manufacturing_videos.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'manufacturing_videos.intro', 'label' => '介绍', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'manufacturing_videos.hero_image', 'label' => '电脑端配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'manufacturing_videos.hero_mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => '留空则手机上沿用电脑端配图。'],
                    ],
                ],
                [
                    'title' => '置顶视频',
                    'desc' => '页面最上方的大幅视频。',
                    'fields' => [
                        ['path' => 'manufacturing_videos.featured.category', 'label' => '分类标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'manufacturing_videos.featured.title', 'label' => '视频标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'manufacturing_videos.featured.youtube_id', 'label' => 'YouTube 视频 ID', 'type' => 'youtube', 'help' => '只填 ID。'],
                        ['path' => 'manufacturing_videos.featured.duration', 'label' => '时长', 'type' => 'text', 'help' => '格式 分:秒，例如 09:57。'],
                        ['path' => 'manufacturing_videos.featured.image', 'label' => '封面图', 'type' => 'image', 'help' => '横版 16:9。'],
                    ],
                ],
                [
                    'title' => '视频分类',
                    'desc' => '',
                    'fields' => [
                        [
                            'path' => 'manufacturing_videos.categories',
                            'type' => 'repeater',
                            'label' => '视频分类',
                            'item_label' => '分类',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                                ['path' => 'duration', 'label' => '时长', 'type' => 'text', 'help' => '格式 分:秒。'],
                                ['path' => 'image', 'label' => '封面图', 'type' => 'image', 'help' => '横版 16:9。'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'about' => [
            'label' => '关于我们页',
            'title' => '关于我们页',
            'intro' => '公司介绍页。页面头图、公司视频、为什么选择我们和团队成员都在这里维护。',
            'preview' => '/about/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'about.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'about.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => '建议用厂区实景。'],
                        ['path' => 'about.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => ''],
                    ],
                ],
                [
                    'title' => '公司视频',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'about.company_video.title', 'label' => '视频标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.company_video.youtube_id', 'label' => 'YouTube 视频 ID', 'type' => 'youtube', 'help' => '只填 ID。'],
                    ],
                ],
                [
                    'title' => '品牌字景',
                    'desc' => '公司视频下方 NorenSpring 大字中的背景图片。',
                    'fields' => [
                        ['path' => 'about.brand_window.image', 'label' => '背景图片', 'type' => 'image', 'required' => true, 'help' => '横版厂区或制造场景。'],
                        ['path' => 'about.brand_window.aria_label', 'label' => '区块说明', 'type' => 'text', 'help' => '给读屏软件使用。'],
                    ],
                ],
                [
                    'title' => '公司简介',
                    'desc' => 'ABOUT NORENSPRING 区块：小标签、标题、正文段落，以及下面那排关键数据。',
                    'signpost' => [
                        'path' => 'about-company-profile',
                        'title' => '关于页开头的公司简介请到「公司实力与资质」编辑',
                        'text' => '小标签、标题、正文段落和关键数据都在那一页的「公司简介」「关键事实」两节；首页用的短版本也在同一处，改长版本不会影响首页。',
                        'button' => '前往公司实力与资质',
                        'url' => 'admin.php?page=springapex-content-company',
                    ],
                    'fields' => [],
                ],
                [
                    'title' => '为什么选择我们',
                    'desc' => '从需求评审到量产的五个步骤。',
                    'fields' => [
                        ['path' => 'about.why_choose.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.why_choose.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        [
                            'path' => 'about.why_choose.media',
                            'type' => 'repeater',
                            'label' => '左侧大图',
                            'item_label' => '大图',
                            'title_key' => 'label',
                            'max_items' => 2,
                            'fields' => [
                                ['path' => 'label', 'label' => '图片标签', 'type' => 'text', 'help' => '显示在图片左下角。'],
                                ['path' => 'image', 'label' => '图片', 'type' => 'image', 'required' => true, 'help' => '横版实拍图。需要取消展示时请删除整条大图。'],
                                ['path' => 'alt', 'label' => '图片说明', 'type' => 'text', 'help' => '请如实描述图片内容，供搜索引擎和读屏软件使用。'],
                            ],
                        ],
                        [
                            'path' => 'about.why_choose.items',
                            'type' => 'repeater',
                            'label' => '步骤',
                            'item_label' => '步骤',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'title', 'label' => '步骤名称', 'type' => 'text', 'help' => ''],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                                ['path' => 'icon_image', 'label' => '步骤配图', 'type' => 'image', 'help' => '方形小图。'],
                            ],
                        ],
                        ['path' => 'about.why_choose.outcomes_title', 'label' => '结果小标题', 'type' => 'text', 'help' => ''],
                        [
                            'path' => 'about.why_choose.outcomes',
                            'type' => 'repeater',
                            'label' => '结果',
                            'item_label' => '结果',
                            'title_key' => 'text',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'text', 'label' => '文字', 'type' => 'text', 'help' => '两到三个英文单词。'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '体系证书',
                    'desc' => '关于页中部的证书墙。',
                    'signpost' => [
                        'path' => 'about-quality-certificates',
                        'title' => '关于页的体系证书请到「公司实力与资质」编辑',
                        'text' => '证书图片、名称和适用范围在那一页的「体系证书」一节维护。',
                        'button' => '前往公司实力与资质',
                        'url' => 'admin.php?page=springapex-content-company',
                    ],
                    'fields' => [],
                ],
                [
                    'title' => '团队',
                    'desc' => '创始人单独展示，其余成员按分组显示。照片请用同一尺寸的方形头像。',
                    'fields' => [
                        ['path' => 'about.team.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.team.statement_lead', 'label' => '主张第一行', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.team.statement_signature', 'label' => '主张第二行', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.team.founder.name', 'label' => '创始人姓名', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.team.founder.role', 'label' => '创始人职务', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.team.founder.image', 'label' => '创始人照片', 'type' => 'image', 'help' => '方形头像。'],
                        [
                            'path' => 'about.team.groups',
                            'type' => 'repeater',
                            'label' => '团队分组',
                            'item_label' => '分组',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'title', 'label' => '分组名称', 'type' => 'text', 'help' => '例如 Engineering。'],
                                [
                                    'path' => 'members',
                                    'type' => 'repeater',
                                    'label' => '成员',
                                    'item_label' => '成员',
                                    'title_key' => 'name',
                                    'fields' => [
                                        ['path' => 'name', 'label' => '姓名', 'type' => 'text', 'help' => ''],
                                        ['path' => 'role', 'label' => '职务', 'type' => 'text', 'help' => ''],
                                        ['path' => 'image', 'label' => '照片', 'type' => 'image', 'help' => '方形头像。'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '公司发展历程',
                    'desc' => '关于页底部的发展历程时间轴。',
                    'signpost' => [
                        'path' => 'about-timeline',
                        'title' => '关于页底部的发展历程请到「公司实力与资质」编辑',
                        'text' => '小标签、标题和各个节点的年份、说明、配图在那一页的「公司发展历程」一节维护。',
                        'button' => '前往公司实力与资质',
                        'url' => 'admin.php?page=springapex-content-company',
                    ],
                    'fields' => [],
                ],
                [
                    'title' => '全球支持',
                    'desc' => 'About 页的 GLOBAL SUPPORT 区块。',
                    'fields' => [
                        ['path' => 'about.global_support.wordmark', 'label' => '背景大字', 'type' => 'text', 'help' => '例如 GLOBAL。'],
                        ['path' => 'about.global_support.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.global_support.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.global_support.text', 'label' => '正文', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'about.global_support.image', 'label' => '地图图片', 'type' => 'image', 'required' => true, 'help' => ''],
                        ['path' => 'about.global_support.image_alt', 'label' => '图片说明', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.global_support.location', 'label' => '所在地', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.global_support.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.global_support.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                    ],
                ],
                [
                    'title' => '官方渠道',
                    'desc' => 'About 页的 OFFICIAL CHANNELS 区块；主页链接在「品牌与联系方式 → 社交媒体」维护。',
                    'fields' => [
                        ['path' => 'about.official_channels.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.official_channels.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.official_channels.text', 'label' => '正文', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'about.official_channels.rail_label', 'label' => '侧边大字', 'type' => 'text', 'help' => '例如 FOLLOW。'],
                        ['path' => 'about.official_channels.facebook_text', 'label' => 'Facebook 说明', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.official_channels.instagram_text', 'label' => 'Instagram 说明', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.official_channels.youtube_text', 'label' => 'YouTube 说明', 'type' => 'text', 'help' => ''],
                    ],
                ],
            ],
        ],

        'company' => [
            'label' => '公司实力与资质',
            'title' => '公司实力与资质',
            'intro' => '公司简介长文、关键事实、发展历程和体系证书。证书会同时出现在首页和关于页，过期前记得更新有效期。',
            'preview' => '/about/',
            'sections' => [
                [
                    'title' => '公司简介',
                    'desc' => '首页用短版本，关于页用长版本。',
                    'fields' => [
                        ['path' => 'company.profile.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'company.profile.home_title', 'label' => '首页标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'company.profile.home_text', 'label' => '首页正文', 'type' => 'textarea', 'help' => '两到三句话。'],
                        ['path' => 'company.profile.home_support', 'label' => '首页补充', 'type' => 'textarea', 'help' => '一句话。'],
                        ['path' => 'company.profile.title', 'label' => '关于页标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'company.profile.paragraphs', 'label' => '关于页正文', 'type' => 'lines', 'help' => '一行一个自然段，中间不要留空行。'],
                        ['path' => 'company.profile.image', 'label' => '配图', 'type' => 'image', 'help' => '厂区实景。'],
                        ['path' => 'company.profile.image_alt', 'label' => '图片说明', 'type' => 'text', 'help' => '给搜索引擎和读屏软件看的描述，请如实填写图里是什么。'],
                    ],
                ],
                [
                    'title' => '关键事实',
                    'desc' => '',
                    'fields' => [
                        [
                            'path' => 'company.facts',
                            'type' => 'repeater',
                            'label' => '事实',
                            'item_label' => '事实',
                            'title_key' => 'label',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'value', 'label' => '数值', 'type' => 'text', 'help' => ''],
                                ['path' => 'label', 'label' => '名称', 'type' => 'text', 'help' => ''],
                                ['path' => 'detail', 'label' => '补充说明', 'type' => 'textarea', 'help' => ''],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '公司发展历程',
                    'desc' => '对应关于我们页底部的发展历程图片和文字，最多显示 3 个节点，可删除、补回或排序。',
                    'fields' => [
                        ['path' => 'company.timeline_header.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'company.timeline_header.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        [
                            'path' => 'company.timeline',
                            'type' => 'repeater',
                            'label' => '发展节点',
                            'item_label' => '节点',
                            'title_key' => 'title',
                            'max_items' => 3,
                            'fields' => [
                                ['path' => 'year', 'label' => '年份或阶段', 'type' => 'text', 'help' => '例如 2001、Growth 或 Today。'],
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                                ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                                ['path' => 'image', 'label' => '图片', 'type' => 'image', 'required' => true, 'help' => '建议所有节点使用相同尺寸的横版图片。需要取消节点时请删除整条节点。'],
                                ['path' => 'alt', 'label' => '图片说明', 'type' => 'text', 'help' => '请如实描述图片内容，供搜索引擎和读屏软件使用。'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '体系证书',
                    'desc' => '显示在关于我们页中部。证书图片会放大查看，请上传清晰的扫描件。',
                    'fields' => [
                        ['path' => 'company.quality.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'company.quality.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'company.quality.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        [
                            'path' => 'company.quality.certificates',
                            'type' => 'repeater',
                            'label' => '证书',
                            'item_label' => '证书',
                            'title_key' => 'name',
                            'fields' => [
                                ['path' => 'name', 'label' => '证书名称', 'type' => 'text', 'help' => '例如 IATF 16949。'],
                                ['path' => 'scope', 'label' => '适用范围', 'type' => 'text', 'help' => ''],
                                ['path' => 'valid_until', 'label' => '有效期', 'type' => 'text', 'help' => '例如 Valid until February 4, 2028。过期的证书请及时更新或下架。'],
                                ['path' => 'image', 'label' => '徽标图', 'type' => 'image', 'help' => '列表上显示的小图。'],
                                ['path' => 'document', 'label' => '证书扫描件', 'type' => 'image', 'base' => 'assets/documents', 'help' => '点击放大后显示的完整证书，通常是整页扫描图。'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'news' => [
            'label' => '新闻页',
            'title' => '新闻页',
            'intro' => '只管新闻列表页的头部。新闻文章请到左侧菜单「News」里逐篇编辑，分类在「News」→「新闻类型」里维护。',
            'preview' => '/news/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'news.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'news.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'news.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'news.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => ''],
                    ],
                ],
                [
                    'title' => '关注官方渠道',
                    'desc' => '新闻列表底部跳转到 About 官方渠道的区块。',
                    'fields' => [
                        ['path' => 'news.follow.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'news.follow.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'news.follow.text', 'label' => '正文', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'news.follow.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'news.follow.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                    ],
                ],
            ],
        ],

        'sustainability' => [
            'label' => '可持续发展页',
            'title' => '可持续发展页',
            'intro' => '维护 Sustainability 页的头部文案和电脑端、手机端配图。',
            'preview' => '/sustainability/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'sustainability.hero.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => '全大写的一行小字。'],
                        ['path' => 'sustainability.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'sustainability.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'sustainability.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => '留空则手机上沿用电脑端配图。'],
                    ],
                ],
                [
                    'title' => '材料生命周期',
                    'desc' => '页面主体的四个生命周期阶段。',
                    'fields' => [
                        ['path' => 'sustainability.lifecycle.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.lifecycle.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.lifecycle.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'sustainability.lifecycle.status', 'label' => '状态文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.lifecycle.items', 'type' => 'repeater', 'label' => '生命周期阶段', 'item_label' => '阶段', 'title_key' => 'title', 'max_items' => 4, 'fields' => [
                            ['path' => 'number', 'label' => '序号', 'type' => 'text', 'help' => ''],
                            ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                            ['path' => 'text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                            ['path' => 'points', 'label' => '要点', 'type' => 'lines', 'help' => '一行一个。'],
                            ['path' => 'image', 'label' => '图片', 'type' => 'image', 'required' => true, 'help' => ''],
                            ['path' => 'alt', 'label' => '图片说明', 'type' => 'text', 'help' => ''],
                        ]],
                    ],
                ],
                [
                    'title' => '管理体系',
                    'desc' => '证书来自「公司实力与资质」，这里维护区块文案和入口。',
                    'fields' => [
                        ['path' => 'sustainability.management.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.management.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.management.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'sustainability.management.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.management.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                        ['path' => 'sustainability.proof_record.title', 'label' => '记录卡片标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.proof_record.text', 'label' => '记录卡片说明', 'type' => 'textarea', 'help' => ''],
                    ],
                ],
                [
                    'title' => '安全生产',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'sustainability.safety.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.safety.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.safety.text', 'label' => '正文', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'sustainability.safety.image', 'label' => '图片', 'type' => 'image', 'required' => true, 'help' => ''],
                        ['path' => 'sustainability.safety.image_alt', 'label' => '图片说明', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.safety.points', 'label' => '要点', 'type' => 'lines', 'help' => '一行一个。'],
                    ],
                ],
                [
                    'title' => '透明进展',
                    'desc' => '页面底部行动区。',
                    'fields' => [
                        ['path' => 'sustainability.progress.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.progress.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.progress.text', 'label' => '正文', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'sustainability.progress.primary_label', 'label' => '主按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.progress.primary_href', 'label' => '主按钮链接', 'type' => 'route', 'help' => ''],
                        ['path' => 'sustainability.progress.secondary_label', 'label' => '次按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'sustainability.progress.secondary_href', 'label' => '次按钮链接', 'type' => 'route', 'help' => ''],
                    ],
                ],
            ],
        ],

        'contact' => [
            'label' => '联系页',
            'title' => '联系页',
            'intro' => '联系页的标题、厂区图、总部信息、全球网络和询盘选项。收到的询盘在左侧菜单「Inquiries」查看。',
            'preview' => '/contact/',
            'sections' => [
                [
                    'title' => '询盘类型',
                    'desc' => '客户在表单里选择的下拉选项，会原样出现在询盘通知邮件里。',
                    'fields' => [
                        ['path' => 'contact.inquiry_types', 'label' => '选项', 'type' => 'lines', 'help' => '一行一个，把最常见的放在最前面。'],
                    ],
                ],
                [
                    'title' => '询盘表单文案',
                    'desc' => '联系页右侧表单的标题、说明和按钮。',
                    'fields' => [
                        ['path' => 'contact.form.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'contact.form.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'contact.form.submit_label', 'label' => '提交按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'contact.form.direct_label', 'label' => '直接联系提示', 'type' => 'text', 'help' => ''],
                    ],
                ],
                [
                    'title' => '全球联系网络',
                    'desc' => '联系页下方的全球网络板块。地图现在是可缩放拖动的真实地图，标记点按下方填写的经纬度定位。',
                    'fields' => [
                        ['path' => 'contact_network.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => '全大写的一行小字。'],
                        ['path' => 'contact_network.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'contact_network.facility_image', 'label' => '厂区图', 'type' => 'image', 'help' => '标题旁的厂区实景。'],
                        ['path' => 'contact_network.map_image', 'label' => '地图占位图（无脚本兜底）', 'type' => 'image', 'help' => '仅当访客浏览器禁用 JavaScript 时，用这张静态图代替可交互地图。'],
                        ['path' => 'contact_network.headquarters.title', 'label' => '总部简介标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'contact_network.headquarters.location', 'label' => '总部所在地', 'type' => 'text', 'help' => '留空则不显示这行。'],
                        ['path' => 'contact_network.headquarters.text', 'label' => '总部简介正文', 'type' => 'textarea', 'help' => ''],
                        [
                            'path' => 'contact_network.facts',
                            'type' => 'repeater',
                            'label' => '关键数据',
                            'item_label' => '数据',
                            'title_key' => 'label',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'value', 'label' => '数值', 'type' => 'text', 'help' => ''],
                                ['path' => 'label', 'label' => '名称', 'type' => 'text', 'help' => ''],
                            ],
                        ],
                        [
                            'path' => 'contact_network.markers',
                            'type' => 'repeater',
                            'label' => '地图标记点',
                            'item_label' => '标记点',
                            'title_key' => 'label',
                            'fields' => [
                                ['path' => 'label', 'label' => '标签文字', 'type' => 'text', 'help' => '地图上点旁边常显的文字，例如 Europe · 7 partners。'],
                                ['path' => 'lat', 'label' => '纬度 Lat', 'type' => 'text', 'help' => '十进制，例如 34.1560；南纬为负数。在 Google/高德地图右键“这是哪里”可取。'],
                                ['path' => 'lng', 'label' => '经度 Lng', 'type' => 'text', 'help' => '十进制，例如 117.1060；西经为负数。留空或非数字则该点不显示。'],
                                ['path' => 'address', 'label' => '弹窗地址', 'type' => 'text', 'help' => '点击标记后弹窗里显示的一行地址，可留空。'],
                                ['path' => 'label_side', 'label' => '标签方向', 'type' => 'text', 'help' => 'left / right / top / bottom，控制标签在点的哪一侧，留空为 right。'],
                                ['path' => 'headquarters', 'label' => '是否总部', 'type' => 'text', 'help' => '总部点填 1（显示更大更深的点），其他留空。'],
                            ],
                        ],
                        [
                            'path' => 'contact_network.regions',
                            'type' => 'repeater',
                            'label' => '区域',
                            'item_label' => '区域',
                            'title_key' => 'label',
                            'fields' => [
                                ['path' => 'label', 'label' => '区域名称', 'type' => 'text', 'help' => '标签页上显示，例如 Europe。'],
                                ['path' => 'slug', 'label' => '网址标识', 'type' => 'text', 'help' => '小写英文加连字符，用于标签切换。'],
                                [
                                    'path' => 'locations',
                                    'type' => 'repeater',
                                    'label' => '联系点',
                                    'item_label' => '联系点',
                                    'title_key' => 'name',
                                    'fields' => [
                                        ['path' => 'name', 'label' => '国家/地区', 'type' => 'text', 'help' => ''],
                                        ['path' => 'detail', 'label' => '补充说明', 'type' => 'text', 'help' => '名称下方的小字，可留空。'],
                                        ['path' => 'company', 'label' => '公司名称', 'type' => 'text', 'help' => ''],
                                        ['path' => 'phone', 'label' => '电话', 'type' => 'tel', 'help' => '含国家区号。'],
                                        ['path' => 'email', 'label' => '邮箱', 'type' => 'email', 'help' => ''],
                                        ['path' => 'address', 'label' => '地址', 'type' => 'textarea', 'help' => ''],
                                        ['path' => 'website', 'label' => '网站', 'type' => 'url', 'help' => '完整网址，可留空。'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'resources' => [
            'label' => '资源下载',
            'title' => '资源下载',
            'intro' => '维护资源下载页头部、下载手册、行业资料提示，以及联系页和搜索使用的资源指引。',
            'preview' => '/resources/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'resources.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'resources.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'resources.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'resources.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => '留空则手机上沿用电脑端配图。'],
                    ],
                ],
                [
                    'title' => '下载资料库',
                    'desc' => '实际显示在 Download Center 的资料卡片。PDF 文件名对应主题 assets/documents 目录。',
                    'fields' => [
                        ['path' => 'resources.library.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'resources.library.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'resources.library.text', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'resources.downloads', 'type' => 'repeater', 'label' => '下载手册', 'item_label' => '手册', 'title_key' => 'title', 'fields' => [
                            ['path' => 'id', 'label' => '锚点标识', 'type' => 'text', 'help' => '小写英文加连字符。'],
                            ['path' => 'category', 'label' => '分类', 'type' => 'text', 'help' => ''],
                            ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                            ['path' => 'description', 'label' => '说明', 'type' => 'textarea', 'help' => ''],
                            ['path' => 'cover', 'label' => '封面图', 'type' => 'image', 'required' => true, 'help' => ''],
                            ['path' => 'document', 'label' => 'PDF 文件名', 'type' => 'text', 'help' => '例如 norenspring-company-profile.pdf。'],
                            ['path' => 'pages', 'label' => '页数', 'type' => 'text', 'help' => ''],
                            ['path' => 'size', 'label' => '文件大小', 'type' => 'text', 'help' => ''],
                        ]],
                    ],
                ],
                [
                    'title' => '行业资料提示',
                    'desc' => '尚未上传行业手册时显示的占位列表和咨询入口。',
                    'fields' => [
                        ['path' => 'resources.industry.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'resources.industry.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'resources.industry.status_text', 'label' => '状态文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'resources.industry.action_label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'resources.industry.action_href', 'label' => '按钮链接', 'type' => 'route', 'help' => ''],
                        ['path' => 'resources.industry.items', 'label' => '行业名称', 'type' => 'lines', 'help' => '一行一个。'],
                    ],
                ],
                [
                    'title' => '资源指引条目',
                    'desc' => '联系页「图纸准备指引」取这里的前三条；搜索结果也会用到。',
                    'fields' => [
                        [
                            'path' => 'resources.items',
                            'type' => 'repeater',
                            'label' => '指引',
                            'item_label' => '指引',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'type', 'label' => '类型标签', 'type' => 'text', 'help' => '例如 Quality Guide。'],
                                ['path' => 'title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                                ['path' => 'summary', 'label' => '摘要', 'type' => 'textarea', 'help' => '一到两句话。'],
                                ['path' => 'points', 'label' => '要点', 'type' => 'lines', 'help' => '一行一个要点。'],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    return $screens;
}

/**
 * Where a plain filename lives for an image field. Most sit under
 * assets/images/; fields pointing elsewhere (certificate scans) declare their
 * folder with 'base'. Used for both the admin preview and the save-time
 * existence check, so the two can never disagree.
 */
function springapex_admin_image_base(array $field): string
{
    $base = trim((string) ($field['base'] ?? ''), '/');
    return $base === '' ? 'assets/images/' : $base . '/';
}

/**
 * name => preview URL for every icon springapex_icon() can render.
 */
function springapex_admin_icon_choices(): array
{
    static $choices = null;
    if ($choices !== null) {
        return $choices;
    }

    $choices = [];
    foreach (springapex_icon_map() as $name => $file) {
        $choices[$name] = springapex_asset('assets/icons/iconoir/' . $file);
    }
    ksort($choices);
    return $choices;
}

/**
 * Recommended upload size for every image field, so whoever makes the artwork
 * has an exact target. Keyed by the content path with repeater row indexes
 * normalised to '#'. Hero/content images are shown at their own aspect ratio
 * (the theme sets no crop on them), so these values match the ratio of the
 * current placeholder in each slot — keeping the layout identical. Add a row
 * when a new image field appears; springapex_admin_image_dimension() returns ''
 * for anything missing.
 */
function springapex_admin_image_dimensions_map(): array
{
    return [
        // 品牌
        'brand.logo' => '916 × 529 px 或同比例横版（透明背景 PNG/SVG）',

        // 页面头图 · 电脑端（各页宽幅不一，按现有图比例）
        'home.hero.image' => '1920 × 1080 px（16:9，横版）',
        'products.hero.image' => '2400 × 1050 px（16:7，横版宽幅）',
        'solutions.hero.image' => '2400 × 1050 px（16:7，横版宽幅）',
        'case_studies.hero.image' => '2400 × 1050 px（16:7，横版宽幅）',
        'capabilities.hero.image' => '2400 × 1050 px（16:7，横版宽幅）',
        'about.hero.image' => '2400 × 1050 px（16:7，横版宽幅）',
        'news.hero.image' => '2400 × 1000 px（2.4:1，横版宽幅）',
        'manufacturing_videos.hero_image' => '2400 × 810 px（约 3:1，横版超宽）',
        'resources.hero.image' => '2400 × 1050 px（16:7，横版宽幅）',
        'sustainability.hero.image' => '2023 × 777 px（约 2.6:1，横版宽幅）',

        // 页面头图 · 手机端（现有图均为 4:3 横版）
        'home.hero.mobile_image' => '1200 × 900 px（4:3，横版）',
        'products.hero.mobile_image' => '1200 × 900 px（4:3，横版）',
        'solutions.hero.mobile_image' => '1200 × 900 px（4:3，横版）',
        'case_studies.hero.mobile_image' => '1200 × 900 px（4:3，横版）',
        'capabilities.hero.mobile_image' => '1200 × 900 px（4:3，横版）',
        'about.hero.mobile_image' => '1200 × 900 px（4:3，横版）',
        'news.hero.mobile_image' => '1200 × 900 px（4:3，横版）',
        'resources.hero.mobile_image' => '1200 × 900 px（4:3，横版）',
        'manufacturing_videos.hero_mobile_image' => '1200 × 900 px（4:3，横版）',
        'sustainability.hero.mobile_image' => '1200 × 900 px（4:3，横版）',

        // 卡片 / 内容配图
        'products.mega_menu.feature_image' => '1200 × 700 px（约 12:7，横版）',
        'solutions.cta.image' => '1600 × 560 px（约 20:7，横版）',
        'products.detail_media.quality.load_test.image' => '1600 × 1067 px（3:2，横版）',
        'products.detail_media.quality.dimensional_inspection.image' => '1200 × 800 px（3:2，横版）',
        'products.detail_media.quality.material_analysis.image' => '1200 × 800 px（3:2，横版）',
        'products.detail_media.delivery.protected_packaging' => '1200 × 1500 px（4:5，竖版）',
        'products.detail_media.delivery.custom_crates' => '1200 × 800 px（3:2，横版）',
        'products.detail_media.delivery.palletized_labelled' => '1200 × 800 px（3:2，横版）',
        'products.detail_media.delivery.global_delivery' => '1200 × 800 px（3:2，横版）',
        'capabilities.project_brief.image' => '1600 × 900 px（16:9，横版）',
        'about.global_support.image' => '1672 × 941 px（16:9，横版地图）',
        'about.brand_window.image' => '1760 × 660 px（约 8:3，横版）',
        'sustainability.lifecycle.items.#.image' => '1536 × 1024 px（3:2，横版）',
        'sustainability.safety.image' => '1536 × 1024 px（3:2，横版）',
        'resources.downloads.#.cover' => '768 × 960 px（4:5，竖版封面）',
        'manufacturing_process.#.image' => '1600 × 1200 px（4:3，横版实拍）',
        'capabilities.verification.image' => '1680 × 700 px（2.4:1，横版示意图）',
        'company.profile.image' => '1920 × 700 px（约 2.75:1，横版宽幅）',
        'contact_network.facility_image' => '1920 × 700 px（约 2.75:1，横版宽幅）',

        // 视频封面：16:9
        'manufacturing_videos.featured.image' => '1920 × 1080 px（16:9，横版）',
        'manufacturing_videos.categories.#.image' => '1920 × 1080 px（16:9，横版）',

        // 头像 / 小图
        'about.team.founder.image' => '900 × 1200 px（3:4，竖版）',
        'about.team.groups.#.members.#.image' => '1200 × 900 px（4:3，横版）',
        'about.why_choose.media.#.image' => '1600 × 1000 px（约 8:5，横版）',
        'about.why_choose.items.#.icon_image' => '600 × 600 px（1:1，方形小图）',
        'company.timeline.#.image' => '1500 × 1000 px（3:2，横版）',
        'company.quality.certificates.#.image' => '600 × 800 px（3:4，竖版徽标）',

        // 特殊
        'contact_network.map_image' => '1800 × 822 px（约 2.19:1，横版世界地图底图）',
        'company.quality.certificates.#.document' => '1240 × 1754 px（A4 竖版整页扫描）',
    ];
}

/**
 * Recommended size string for one image field's content path (row indexes are
 * normalised to '#' before lookup). Empty when the field is not in the map.
 */
function springapex_admin_image_dimension(string $name_path): string
{
    $segments = explode('.', $name_path);
    foreach ($segments as $i => $segment) {
        if ($segment === '__i__' || $segment === '__row' || ctype_digit($segment)) {
            $segments[$i] = '#';
        }
    }
    $normalised = implode('.', $segments);
    return (string) (springapex_admin_image_dimensions_map()[$normalised] ?? '');
}

/**
 * Allowed destinations for `route` fields, grouped for the dropdown. Button
 * links point at real site locations, so operators pick one instead of typing a
 * path like /contact/?intent=quote by hand and getting it subtly wrong. Add a
 * new destination here when the site gains one.
 */
function springapex_admin_route_options(): array
{
    return [
        '主要页面' => [
            '/' => '首页',
            '/products/' => '产品',
            '/solutions/' => '行业方案',
            '/capabilities/' => '能力与工艺',
            '/manufacturing-videos/' => '制造视频',
            '/about/' => '关于我们',
            '/sustainability/' => '可持续发展',
            '/resources/' => '资源下载中心',
            '/news/' => '新闻',
            '/case-studies/' => '案例',
            '/contact/' => '联系我们',
        ],
        '联系页（带预选意图）' => [
            '/contact/?intent=quote' => '联系页 · 打开询价表单',
            '/contact/?intent=drawing' => '联系页 · 打开上传图纸',
            '/contact/?intent=solution' => '联系页 · 描述应用需求',
            '/contact/?intent=engineer' => '联系页 · 联系工程师',
            '/contact/?intent=catalog' => '联系页 · 索取资料',
            '/contact/?intent=sustainability' => '联系页 · 可持续发展资料',
        ],
        '页面内锚点' => [
            '/products/#product-families' => '产品页 · 跳到产品分类区块',
            '/about/#official-channels' => '关于页 · 官方渠道',
            '/about/#quality-certificates' => '关于页 · 体系证书',
            '/contact/#contact-network' => '联系页 · 全球联系网络',
        ],
    ];
}

/**
 * Flat list of every allowed route value, for the save-time whitelist.
 */
function springapex_admin_route_values(): array
{
    $values = [];
    foreach (springapex_admin_route_options() as $options) {
        foreach ($options as $value => $label) {
            $values[] = (string) $value;
        }
    }
    return $values;
}
