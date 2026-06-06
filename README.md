# zjmf-security-group

魔方财务 (ZJMF) 安全组显示插件 — 为 `zjmf_api` 代理类型产品的前台详情页注入安全组管理 Tab。
<img width="1897" height="926" alt="image" src="https://github.com/user-attachments/assets/dcecbf22-2f80-4146-9635-ee8f0493fde6" />


## 背景与问题

魔方财务系统中，**直连 dcimcloud 类型产品**的前台产品详情页原生支持"安全组"Tab（由 `DcimCloud` 模块提供完整的 CRUD 界面）。但 **`zjmf_api` 代理模式产品**在 app/home/controller/HostController.php#L1453-L1463 中被**故意过滤掉了**安全组 Tab：

```php
// HostController::hostHeader() 中的过滤逻辑
foreach ($upstream_data["module_client_area"] as $item) {
    if ($item["key"] != "security_groups") {   // 安全组被过滤
        $filter[] = $item;
    }
}
```

导致代理产品的用户无法在前台查看和管理安全组。

## 解决方案

本插件通过魔方财务的 **Hook 机制** (`template_after_servicedetailSuspended`)，在产品详情页动态注入安全组 Tab，**不修改任何核心代码**。

### 工作原理

```
产品详情页加载
    |
    v
Hook 触发: template_after_servicedetailSuspended
    |
    v
PHP 直接输出 <script>（零模板引擎依赖）
    |
    v
JS 动态注入:
    - <li> "安全组" Tab（插入到"财务"前面）
    - <div.tab-pane> 内容区域（带 loading 动画）
    |
    v
用户点击"安全组"Tab
    |
    v
AJAX: GET /provision/custom/content?id=X&key=security_groups
    |
    v
ProvisionController 检测 zjmf_api 模式
    -> zjmfCurl() 转发到上游
    -> 上游 DcimCloud::moduleClientAreaDetail() 处理
    -> 调用 DCIM Cloud API: /security_groups, /security_group_rule_protocols
    -> 渲染 security_groups.html 返回完整管理界面
    |
    v
填入 tab-pane，用户可正常操作安全组
```

所有 CRUD 操作（创建/删除安全组、增删规则、绑定实例）均走现有 `/provision/custom/{id}` 链路自动转发到上游。

## 适用范围

- 产品类型：`api_type = zjmf_api` 的代理产品（从上游魔方财务同步的产品）
- 上游要求：上游需为 **DCIM Cloud 类型**（dcimcloud），且已配置安全组功能
- 前端主题：兼容默认前台主题 (`clientarea/default`) 及其衍生主题

> 非代理产品安装此插件后不会产生副作用——点击安全组 Tab 会显示友好的加载失败提示。

## 安装部署

### 手动安装

1. 将 `ruinexus_security_group/` 目录上传至服务器：
   ```
   public/plugins/addons/ruinexus_security_group/
   └── RuinexusSecurityGroupPlugin.php
   ```

2. 登录魔方财务后台 → **插件管理**

3. 找到「安全组显示插件」→ 点击 **安装** → **启用**

4. 访问任意 zjmf_api 代理产品的详情页，验证"安全组"Tab 是否显示

然后按方法一的步骤 2-4 操作。

## 文件结构

```
ruinexus_security_group/
└── RuinexusSecurityGroupPlugin.php    # 插件入口（唯一文件）
    ├── $info                          # 插件元信息（名称/版本/作者）
    ├── install() / uninstall()        # 生命周期钩子
    └── templateAfterServicedetailSuspended()  # 核心：输出注入 JS
```

## License

MIT
