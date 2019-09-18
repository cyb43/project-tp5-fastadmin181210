define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    // ^2_3^
    var Controller = {
        index: function () {
            // 代码区域
            $("#cxselect-example .col-xs-12").each(function () {
                $("textarea", this).val($(this).prev().prev().html().replace(/[ ]{2}/g, ''));
            });
            
            //这里需要手动为Form绑定上元素事件
            Form.api.bindevent($("form#cxselectform"));
        }
    };
    return Controller;
});