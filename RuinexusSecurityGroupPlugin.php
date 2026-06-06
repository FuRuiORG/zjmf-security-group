<?php
namespace addons\ruinexus_security_group;

use app\admin\lib\Plugin;

/**
 * 安全组显示插件
 * 为zjmf_api代理类型产品的前台详情页注入安全组Tab
 * 原系统在HostController中过滤掉了security_groups key，本插件通过JS动态注入恢复该功能
 */
class RuinexusSecurityGroupPlugin extends Plugin
{
    public $info = array(
        'name'        => 'RuinexusSecurityGroup',
        'title'       => '安全组显示插件',
        'description' => '为zjmf_api代理产品的前台产品详情页注入安全组管理Tab，通过上游API链路实现安全组的查看和管理',
        'status'      => 1,
        'author'      => 'RuiNexus',
        'version'     => '1.0.2',
        'module'      => 'addons',
        'lang'        => [
            'chinese'    => '安全组显示插件',
            'chinese_tw' => '安全組顯示插件',
            'english'    => 'Security Group Display Plugin',
        ]
    );

    public function install()
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }

    /**
     * Hook: 在产品详情页注入安全组Tab的JS代码
     * 触发点: template_after_servicedetail_suspended（所有产品类型的详情页都会触发）
     * 通过JS动态向nav-tabs注入安全组Tab和对应的内容区域
     *
     * @param array $param 包含 hostid 键，值为当前产品ID
     * @return string 注入到页面的HTML（script标签）
     */
    public function templateAfterServicedetailSuspended($param)
    {
        $hostId = intval($param['hostid']);
        if ($hostId <= 0) {
            return '';
        }
        $tabName = '\u5b89\u5168\u7ec4';
        $tabKey = 'security_groups';
        $tabId = 'module_client_area_' . $tabKey;
        $loadUrl = '/provision/custom/content?id=' . $hostId . '&key=' . $tabKey;

        $js = '<script>' . "\n";
        $js .= '(function(){' . "\n";
        $js .= "    var _hostId = '" . $hostId . "';\n";
        $js .= "    var _tabKey = '" . $tabKey . "';\n";
        $js .= "    var _tabName = '" . $tabName . "';\n";
        $js .= "    var _tabId = 'module_client_area_' + _tabKey;\n";
        $js .= "    var _loadUrl = '/provision/custom/content?id=' + _hostId + '&key=' + _tabKey;\n";
        $js .= "\n";
        $js .= "    function injectSecurityGroupTab() {\n";
        $js .= "        var _navTabs = jQuery('.nav-tabs-custom');\n";
        $js .= "        if (_navTabs.length === 0) { return; }\n";
        $js .= "        if (jQuery('#' + _tabId).length > 0) { return; }\n";
        $js .= "        var _financeLi = _navTabs.find('li.nav-item a[href=\"#finance\"]').closest('li.nav-item');\n";
        $js .= "        var _newLi = jQuery('<li class=\"nav-item\"><a class=\"nav-link\" data-toggle=\"tab\" href=\"#' + _tabId + '\" role=\"tab\"><span>' + _tabName + '</span></a></li>');\n";
        $js .= "        if (_financeLi.length > 0) {\n";
        $js .= "            _newLi.insertBefore(_financeLi);\n";
        $js .= "        } else {\n";
        $js .= "            _navTabs.append(_newLi);\n";
        $js .= "        }\n";
        $js .= "        var _tabContent = _navTabs.closest('.card-body').find('.tab-content');\n";
        $js .= "        if (_tabContent.length === 0) {\n";
        $js .= "            _tabContent = _navTabs.parent().siblings('.tab-content');\n";
        $js .= "        }\n";
        $js .= "        if (_tabContent.length > 0) {\n";
        $js .= "            var _newPane = jQuery('<div class=\"tab-pane\" role=\"tabpanel\" id=\"' + _tabId + '\"><div style=\"width:100%;min-height:550px;\"><div class=\"text-center py-5\"><i class=\"bx bx-loader bx-spin font-size-24 text-primary\"></i> <span class=\"ml-2 text-muted\">\\u6b63\\u5728\\u52a0\\u8f7d\\u5b89\\u5168\\u7ec4\\u6570\\u636e...</span></div></div></div>');\n";
        $js .= "            _tabContent.append(_newPane);\n";
        $js .= "        }\n";
        $js .= "    }\n";
        $js .= "\n";
        $js .= "    function loadSecurityGroupContent() {\n";
        $js .= "        var _pane = jQuery('#' + _tabId);\n";
        $js .= "        if (_pane.length === 0) { return; }\n";
        $js .= "        var _container = _pane.find('> div');\n";
        $js .= "        if (_container.length === 0) { return; }\n";
        $js .= "        if (_container.data('loaded') === 'yes') { return; }\n";
        $js .= "        _container.data('loaded', 'yes');\n";
        $js .= "        var _req = new Object();\n";
        $js .= "        _req.url = _loadUrl;\n";
        $js .= "        _req.type = 'get';\n";
        $js .= "        _req.success = (function(_cont) { return function(res) { _cont.html(res); }; })(_container);\n";
        $js .= "        _req.error = (function(_cont) { return function() { _cont.html('<div class=\"text-center py-5 text-danger\"><i class=\"bx bx-error-circle font-size-24\"></i><p class=\"mt-2\">\\u52a0\\u8f7d\\u5b89\\u5168\\u7ec4\\u6570\\u636e\\u5931\\u8d25\\uff0c\\u8bf7\\u5237\\u65b0\\u9875\\u9762\\u91cd\\u8bd5</p></div>'); }; })(_container);\n";
        $js .= "        jQuery.ajax(_req);\n";
        $js .= "    }\n";
        $js .= "\n";
        $js .= "    jQuery(document).ready(function(){\n";
        $js .= "        injectSecurityGroupTab();\n";
        $js .= "        jQuery(document).on('shown.bs.tab', 'a[href=\"#' + _tabId + '\"]', function(){ loadSecurityGroupContent(); });\n";
        $js .= "    });\n";
        $js .= '})();' . "\n";
        $js .= '</script>';

        return $js;
    }
}
