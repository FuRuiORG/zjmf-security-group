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
        'version'     => '1.0.3',
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
     * 触发点: template_after_servicedetailSuspended（所有产品类型的详情页都会触发）
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

        $js = '<script>' . "\n";
        $js .= '(function(){' . "\n";
        $js .= "    var _hostId = '" . $hostId . "';\n";
        $js .= "    var _tabKey = '" . $tabKey . "';\n";
        $js .= "    var _tabName = '" . $tabName . "';\n";
        $js .= "    var _tabId = 'module_client_area_' + _tabKey;\n";
        $js .= "    var _loadUrl = '/provision/custom/content?id=' + _hostId + '&key=' + _tabKey;\n";
        $js .= "\n";
        $js .= "    function _findNavTabs() {\n";
        $js .= "        var _cands = ['.nav-tabs-custom', '.nav-tabs', '[role=\"tablist\"]', 'ul.nav'];\n";
        $js .= "        for (var _i = 0; _i < _cands.length; _i++) {\n";
        $js .= "            var _el = jQuery(_cands[_i]);\n";
        $js .= "            if (_el.length > 0 && _el.find('li.nav-item, li').length > 0) { return _el.first(); }\n";
        $js .= "        }\n";
        $js .= "        return jQuery();\n";
        $js .= "    }\n";
        $js .= "\n";
        $js .= "    function _findFinanceLi(_nav) {\n";
        $js .= "        var _anchors = ['a[href=\"#finance\"]', 'a[href=\"#billing\"]', 'a[href=\"#finance_tab\"]'];\n";
        $js .= "        for (var _j = 0; _j < _anchors.length; _j++) {\n";
        $js .= "            var _li = _nav.find(_anchors[_j]).closest('li');\n";
        $js .= "            if (_li.length > 0) { return _li; }\n";
        $js .= "        }\n";
        $js .= "        var _items = _nav.find('li a');\n";
        $js .= "        for (var _k = 0; _k < _items.length; _k++) {\n";
        $js .= "            var _txt = jQuery.trim(jQuery(_items[_k]).text());\n";
        $js .= "            if (_txt === '\\u8d22\\u52a1' || _txt.toLowerCase() === 'finance' || _txt.toLowerCase() === 'billing') {\n";
        $js .= "                return jQuery(_items[_k]).closest('li');\n";
        $js .= "            }\n";
        $js .= "        }\n";
        $js .= "        return jQuery();\n";
        $js .= "    }\n";
        $js .= "\n";
        $js .= "    function _findTabContent(_nav) {\n";
        $js .= "        var _c1 = _nav.closest('.card-body').find('.tab-content');\n";
        $js .= "        if (_c1.length > 0) { return _c1; }\n";
        $js .= "        var _c2 = _nav.parent().siblings('.tab-content');\n";
        $js .= "        if (_c2.length > 0) { return _c2; }\n";
        $js .= "        var _c3 = _nav.siblings('.tab-content');\n";
        $js .= "        if (_c3.length > 0) { return _c3; }\n";
        $js .= "        var _c4 = _nav.closest('.card, .panel, [class*=\"detail\"], [class*=\"service\"]').find('.tab-content');\n";
        $js .= "        if (_c4.length > 0) { return _c4.first(); }\n";
        $js .= "        var _c5 = jQuery('.tab-content');\n";
        $js .= "        if (_c5.length > 0) { return _c5.last(); }\n";
        $js .= "        return jQuery();\n";
        $js .= "    }\n";
        $js .= "\n";
        $js .= "    function injectSecurityGroupTab() {\n";
        $js .= "        if (jQuery('#' + _tabId).length > 0) { return; }\n";
        $js .= "        var _navTabs = _findNavTabs();\n";
        $js .= "        if (_navTabs.length === 0) {\n";
        $js .= "            console.warn('[SecurityGroup] Cannot find tab container on this theme.');\n";
        $js .= "            return;\n";
        $js .= "        }\n";
        $js .= "        var _financeLi = _findFinanceLi(_navTabs);\n";
        $js .= "        var _newLi = jQuery('<li class=\"nav-item\"><a class=\"nav-link\" data-toggle=\"tab\" href=\"#' + _tabId + '\" role=\"tab\"><span>' + _tabName + '</span></a></li>');\n";
        $js .= "        if (_financeLi.length > 0) {\n";
        $js .= "            _newLi.insertBefore(_financeLi);\n";
        $js .= "        } else {\n";
        $js .= "            console.warn('[SecurityGroup] Finance tab not found, appending to end.');\n";
        $js .= "            _navTabs.append(_newLi);\n";
        $js .= "        }\n";
        $js .= "        var _tabContent = _findTabContent(_navTabs);\n";
        $js .= "        if (_tabContent.length === 0) {\n";
        $js .= "            console.error('[SecurityGroup] Cannot find tab content area. Tab will show but content may not load.');\n";
        $js .= "            return;\n";
        $js .= "        }\n";
        $js .= "        var _newPane = jQuery('<div class=\"tab-pane\" role=\"tabpanel\" id=\"' + _tabId + '\"><div style=\"width:100%;min-height:550px;\"><div class=\"text-center py-5\"><i class=\"bx bx-loader bx-spin font-size-24 text-primary\"></i> <span class=\"ml-2 text-muted\">\\u6b63\\u5728\\u52a0\\u8f7d\\u5b89\\u5168\\u7ec4\\u6570\\u636e...</span></div></div></div>');\n";
        $js .= "        _tabContent.append(_newPane);\n";
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
