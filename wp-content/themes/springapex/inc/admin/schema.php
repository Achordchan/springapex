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
                        ['path' => 'brand.name', 'label' => '品牌名', 'type' => 'text', 'help' => '显示在页头 Logo 旁，例如 ApexSpring。'],
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
                        ['path' => 'home.hero.image', 'label' => '首屏配图', 'type' => 'image', 'help' => '建议横版、主体居中、背景干净，宽度不小于 1600px。'],
                        ['path' => 'home.hero.video_cta.label', 'label' => '视频按钮文字', 'type' => 'text', 'help' => '例如 Play a Video。'],
                        ['path' => 'home.hero.video_cta.youtube_id', 'label' => 'YouTube 视频 ID', 'type' => 'youtube', 'help' => '只填 ID，不是整条网址。'],
                        ['path' => 'home.hero.quote_cta.label', 'label' => '询价按钮文字', 'type' => 'text', 'help' => '例如 Request a Quote。'],
                        ['path' => 'home.hero.quote_cta.href', 'label' => '询价按钮链接', 'type' => 'text', 'help' => '默认指向联系页的询价表单。'],
                    ],
                ],
                [
                    'title' => '行业滚动条',
                    'desc' => '首屏下方滚动的行业名称，全部大写。',
                    'fields' => [
                        ['path' => 'home.industries', 'label' => '行业名称', 'type' => 'lines', 'help' => '一行一个，建议 5–8 个。'],
                    ],
                ],
                [
                    'title' => '四大优势',
                    'desc' => '四张带图标的卡片。建议保持四条，多于四条排版会变形。',
                    'fields' => [
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
                    'desc' => '带图片的行业卡片，点击后跳转到对应的行业方案页。',
                    'fields' => [
                        [
                            'path' => 'home.applications',
                            'type' => 'repeater',
                            'label' => '应用领域',
                            'item_label' => '领域',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'title', 'label' => '名称', 'type' => 'text', 'help' => ''],
                                ['path' => 'slug', 'label' => '对应行业方案', 'type' => 'text', 'help' => '要和「行业方案页」里的标识一致，否则点击会 404。'],
                                ['path' => 'image', 'label' => '配图', 'type' => 'image', 'help' => '竖版或方形，展示实际应用场景。'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '生产流程',
                    'desc' => '一条横向的流程带，从左到右显示。',
                    'fields' => [
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
            ],
        ],

        'products' => [
            'label' => '产品页',
            'title' => '产品页',
            'intro' => '产品列表页的头部和分类卡片。每个产品的详细参数请到左侧菜单「Spring Products」里逐个编辑。',
            'preview' => '/products/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'products.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'products.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => '横版，宽度不小于 1600px。'],
                        ['path' => 'products.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => '竖版；留空则手机上沿用电脑端配图。'],
                        ['path' => 'products.hero.primary_cta.label', 'label' => '按钮文字', 'type' => 'text', 'help' => ''],
                        ['path' => 'products.hero.primary_cta.href', 'label' => '按钮链接', 'type' => 'text', 'help' => ''],
                    ],
                ],
                [
                    'title' => '产品分类',
                    'desc' => '这些分类同时出现在产品页、页头的产品大菜单和首页。删除一项前，请先确认没有其他页面链接到它。',
                    'fields' => [
                        [
                            'path' => 'products.categories',
                            'type' => 'repeater',
                            'label' => '分类',
                            'item_label' => '产品分类',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'title', 'label' => '分类名称', 'type' => 'text', 'help' => ''],
                                ['path' => 'slug', 'label' => '网址标识', 'type' => 'text', 'help' => '小写英文加连字符，改了旧链接会失效。'],
                                ['path' => 'desc', 'label' => '一句话介绍', 'type' => 'textarea', 'help' => '显示在分类卡片上。'],
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'image', 'label' => '主图', 'type' => 'image', 'help' => '产品详情页顶部使用。'],
                                ['path' => 'category_image', 'label' => '列表图', 'type' => 'image', 'help' => '产品列表卡片使用；留空则用主图。'],
                                ['path' => 'featured_image', 'label' => '大菜单图', 'type' => 'image', 'help' => '页头产品大菜单使用；留空则用列表图。'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        'solutions' => [
            'label' => '行业方案页',
            'title' => '行业方案页',
            'intro' => '行业方案列表页。每个行业的详细内容请到左侧菜单「Industry Solutions」里编辑。',
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
                    'desc' => '列表上的行业卡片。',
                    'fields' => [
                        [
                            'path' => 'solutions.items',
                            'type' => 'repeater',
                            'label' => '行业',
                            'item_label' => '行业',
                            'title_key' => 'title',
                            'fields' => [
                                ['path' => 'title', 'label' => '行业名称', 'type' => 'text', 'help' => ''],
                                ['path' => 'slug', 'label' => '网址标识', 'type' => 'text', 'help' => '小写英文加连字符。'],
                                ['path' => 'tagline', 'label' => '标语', 'type' => 'text', 'help' => '卡片上的一句短语。'],
                                ['path' => 'image', 'label' => '配图', 'type' => 'image', 'help' => ''],
                            ],
                        ],
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
                        ['path' => 'capabilities.hero.cta.href', 'label' => '按钮链接', 'type' => 'text', 'help' => ''],
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
                        ['path' => 'manufacturing_videos.hero_image', 'label' => '头部配图', 'type' => 'image', 'help' => ''],
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
            'intro' => '公司介绍页。团队成员、数据统计和公司视频都在这里维护。',
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
                    'title' => '关键数据',
                    'desc' => '四个数字，建议保持四个。',
                    'fields' => [
                        [
                            'path' => 'about.stats',
                            'type' => 'repeater',
                            'label' => '数据',
                            'item_label' => '数据',
                            'title_key' => 'label',
                            'fields' => [
                                ['path' => 'icon', 'label' => '图标', 'type' => 'icon', 'help' => ''],
                                ['path' => 'value', 'label' => '数字', 'type' => 'text', 'help' => '例如 120+ 或 2,000+。'],
                                ['path' => 'label', 'label' => '说明', 'type' => 'text', 'help' => '例如 Employees。'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => '为什么选择我们',
                    'desc' => '从需求评审到量产的五个步骤。',
                    'fields' => [
                        ['path' => 'about.why_choose.eyebrow', 'label' => '小标签', 'type' => 'text', 'help' => ''],
                        ['path' => 'about.why_choose.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
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
            ],
        ],

        'company' => [
            'label' => '公司实力与资质',
            'title' => '公司实力与资质',
            'intro' => '公司简介长文、关键事实和体系证书。证书会同时出现在首页和关于页，过期前记得更新有效期。',
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
                    'title' => '体系证书',
                    'desc' => '证书图片会放大查看，请上传清晰的扫描件。',
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
            ],
        ],

        'contact' => [
            'label' => '联系页',
            'title' => '联系页',
            'intro' => '联系页的头部和询盘表单的选项。收到的询盘在左侧菜单「Inquiries」里查看。',
            'preview' => '/contact/',
            'sections' => [
                [
                    'title' => '页面头部',
                    'desc' => '',
                    'fields' => [
                        ['path' => 'contact.hero.title', 'label' => '标题', 'type' => 'text', 'help' => ''],
                        ['path' => 'contact.hero.subtitle', 'label' => '副标题', 'type' => 'textarea', 'help' => ''],
                        ['path' => 'contact.hero.image', 'label' => '电脑端配图', 'type' => 'image', 'help' => ''],
                        ['path' => 'contact.hero.mobile_image', 'label' => '手机端配图', 'type' => 'image', 'help' => ''],
                        [
                            'path' => 'contact.hero.points',
                            'type' => 'repeater',
                            'label' => '承诺条目',
                            'item_label' => '承诺',
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
                    'title' => '询盘类型',
                    'desc' => '客户在表单里选择的下拉选项，会原样出现在询盘通知邮件里。',
                    'fields' => [
                        ['path' => 'contact.inquiry_types', 'label' => '选项', 'type' => 'lines', 'help' => '一行一个，把最常见的放在最前面。'],
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
