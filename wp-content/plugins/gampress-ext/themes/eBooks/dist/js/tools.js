var tools = {
        // 随机生成唯一ID
        randomId: function(prefix) {
            var n = Math.floor(Math.random() * 1000000),
                id = prefix + n;

            if ($('#' + id).length) {
                arguments.callee(prefix);
            } else {
                return id;
            }
        },
        // 判断数组
        isArray: function(obj) {
            return Object.prototype.toString.call(obj) === '[object Array]';
        },
        //获取 el在 body 上的位置
        getPosition: function(option) {
            var el = option,
                x = 0,
                y = 0;

            do {
                x += el.offsetLeft;
                y += el.offsetTop;
            } while (el = el.offsetParent); //若 el 的上层元素为绝对定位，则 el.offsetParent 为该元素；如没定位，追溯到上层定位元素，直至到 body
            return {
                x: x,
                y: y
            }
        },
        // 判断图片是否在当前屏
        isScreen: function(el) {
            var elTop = this.getPosition(el).y,
                scaleH = window.innerHeight,
                scrollTop = document.documentElement.scrollTop || document.body.scrollTop;

            if (elTop < scrollTop + scaleH && elTop > scrollTop - el.height) {
                return true;
            }
            return false;
        },
        // 懒加载图片
        lazyLoadImg: function(el) {
            var _this = this,
                imgs = [],
                bindEvent = function() {
                    $(window).bind('scroll', load);
                    $(window).bind('resize', load);
                },
                unBindEvent = function() {
                    $(window).unbind('scroll', load);
                    $(window).unbind('resize', load);
                },
                load = function() {
                    setTimeout(function() {
                        var len = imgs.length;
                        if (len) {
                            for (var i = 0; i < imgs.length; i++) {
                                if (_this.isScreen(imgs[i])) {
                                    var src = $(imgs[i]).data('src');
                                    if (src) {
                                        imgs[i].src = src;
                                    }
                                    imgs[i].removeAttribute('data-src');
                                    imgs.splice(i--, 1);
                                }
                            }
                        } else {
                            unBindEvent();
                        }
                    }, 200);
                },
                init = function() {
                    $(el).each(function() {
                        if ($(this).attr('data-src')) {
                            imgs.push(this);
                        }
                    });

                    load();
                    bindEvent();
                };

            init();
        },
        // 倒计时
        timer: function() {
            var loop, time_obj = $('#timer'),
                init = function() {
                    var distance = time_obj.attr('_time'),
                        h, m, s, distance_mod;

                    if (distance > 0) {
                        h = Math.floor(distance / 3600);
                        distance_mod = distance % 3600;
                        m = Math.floor(distance_mod / 60);
                        s = distance_mod % 60;
                    } else {
                        h = 0;
                        m = 0;
                        s = 0;
                        clearInterval(loop);
                    }

                    h = h < 10 ? '0' + h : h;
                    m = m < 10 ? '0' + m : m;
                    s = s < 10 ? '0' + s : s;

                    time_obj.html('<i>' + h + '</i>:' + '<i>' + m + '</i>:' + '<i>' + s + '</i>');

                    time_obj.attr('_time', distance - 1);
                    //if (distance < 2) location.reload();
                };

            loop = setInterval(init, 1000);
        },
        // 是否支持 sticky 属性
        supportSticky: function(str) {
            var t,
                n = str,
                e = document.createElement("i");
            e.style.position = n;
            t = e.style.position;
            e = null;
            return t === n;
        },
        // 获取url 参数
        getParam: function(name) {
            var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)"),
                r = window.location.search.substr(1).match(reg);

            if (r != null) {
                return unescape(r[2]);
            }
            return null;
        },
        // 禁止滚动条
        stopScrollBar: function(el) {

        },
        // 读取 cookie 默认 一天
        setCookie: function(name, value, ms) {
            var exp = new Date(),
            day = typeof ms === 'number' ? ms : 24 * 60 * 60 * 1000;

            exp.setTime(exp.getTime() + day);
            document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
        },
        getCookie: function(name) {
            var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
            if (arr = document.cookie.match(reg))
                return unescape(arr[2]);
            else {
                return null;
            }
        }
    },

    /*********** 选择弹窗 *********
     *
     * @param text       // 标题
     * @param type       // 类型： success 成功， error 失败
     * @param callback   // 回调函数
     * @param delay      // 延时
     */
    msg = function(text, type, callback, delay) {
        var baseHTML = ['<div class="msg">',
            '<i class="icon-',
            type,
            '"></i>',
            '<h3>',
            text,
            '</h3>',
            '</div>'
        ].join('');

        delay = delay || 2000;
        $('body').append(baseHTML);

        setTimeout(function() {
            $('.msg').remove();
            callback && callback();
        }, delay);
    },
    /*********** 消息弹窗 *********
     * @param:  object
     * @example:
     * var $el = msgAlert({                 // $el 弹层选择器
            title: '请选择小区',            // 标题
            txt: '快选择小区哦！！！',      // 内容
            btnTxt: '确定',               // 按钮文本 默认：确定
            isShade: true,                  // 是否添加遮罩层：true, 添加， false, 不添加； 默认 true
            closeType: 'remove',            // 关闭类型： 'remove', 移除， 'hide'， 隐藏； 默认 'remove',
            onlyClose: false,               // 是否只关闭弹层: true, 只关闭， false: 同时关闭遮罩层； 默认 false
            callback: function($el) {       // 点击确认按钮， 参数：$el,弹层选择器
                console.log('确认');
            },
            closeCallback: function ($el) { // 关闭弹层后回调，参数：$el, 弹层选择器
                console.log('关闭弹层');
             },
            showCallback: function ($el) {  // 显示弹层后回调，参数：$el, 弹层选择器
                console.log('生成弹层');
            }
        });
        */
    msgAlert = function(options) {
        var $el,
            config = {
                btnTxt: '确定',
                isShade: true, // 是否添加遮罩层
                onlyClose: false, // 是否只关闭弹层
                closeType: 'remove' // 关闭类型
            },
            param = $.extend({}, config, options),
            id = tools.randomId('msg_alert_'),
            btnId = tools.randomId('msg_alert_btn_'),
            baseHTML = function(title, txt, btnTxt) {
                return ['<div class="confirm" id="',
                    id,
                    '">',
                    '<div class="hd">',
                    '<a href="javascript:;" class="close"><i class="icon-close"></i></a>',
                    '</div>',
                    '<div class="bd">',
                    '<h2>',
                    title,
                    '</h2>',
                    txt ? '<p>' + txt + '</p>' : '',
                    '</div>',
                    '<div class="fd">',
                    '<a href="javascript:;" class="btn-primary btn-block" id="',
                    btnId,
                    '">',
                    btnTxt,
                    '</a>',
                    '</div>',
                    '</div>'
                ].join('');
            },
            show = function(flag, callback) {
                var str = flag ? '<div class="confirm-shade"></div>' : '';

                $('body').append(str + baseHTML(param.title, param.txt, param.btnTxt));

                $el = $('#' + id);
                callback && callback($el);
            },
            bindEvent = function() {
                $('body').on('click', '#' + id + ' .close', function(event) {
                    if (!param.onlyClose) {
                        if (param.closeType === 'hide') {
                            $('.confirm-shade').hide();
                        } else {
                            $('.confirm-shade').remove();
                        }
                    }
                    if (param.closeType === 'hide') {
                        $el.hide();
                    } else {
                        $el.remove();
                        $(this).off(event);
                    }
                    param.closeCallback && param.closeCallback($el);
                }).
                on('click', '#' + btnId, function() {
                    param.callback && param.callback($el);
                });
            },
            init = function() {
                show(param.isShade, param.showCallback);
                bindEvent();
            };

        init();
        return $el;
    },
    /*********** 选择弹窗 *********
     * @param:  object
     * @example:
     * var $el = msgConfirm({                 // $el 弹层选择器
            title: '您尚未进行实名认证',                // 标题
            txt: '请先完成实名认证后再进行业主认证',  // 内容
            btnTxt: '实名认证',                // 按钮文字
            isShade: true,                    // 是否添加遮罩层：true, 添加， false, 不添加； 默认 true
            closeType: 'remove',              // 关闭类型： 'remove', 移除， 'hide'， 隐藏； 默认 'remove',
            onlyClose: false,                 // 是否只关闭弹层: true, 只关闭， false: 同时关闭遮罩层； 默认 false
            closeCallback: function ($el) {   // 关闭弹层后回调，参数：$el, 弹层选择器
                console.log('关闭弹层');
             },
            showCallback: function ($el) {    // 显示弹层后回调，参数：$el, 弹层选择器
                console.log('生成弹层');
            },
            callback: function ($el){          // 确认按钮回调，参数：$el, 弹层选择器
                console.log('点击确认按钮');
            }
        });
        */
    msgConfirm = function(options) {
        var $el,
            config = {
                isShade: true, // 是否添加遮罩层
                onlyClose: false, // 是否只关闭弹层
                closeType: 'remove' // 关闭类型
            },
            param = $.extend({}, config, options),
            id = tools.randomId('msg_confirm_'),
            btnId = tools.randomId('msg_confirm_btn_'),
            baseHTML = function(title, txt, btnTxt) {
                return ['<div class="confirm" id="',
                    id,
                    '">',
                    '<div class="hd">',
                    '<a href="javascript:;" class="close"><i class="icon-close"></i></a>',
                    '</div>',
                    '<div class="bd">',
                    '<h2>',
                    title,
                    '</h2>',
                    '<p>',
                    txt,
                    '</p>',
                    '</div>',
                    '<div class="fd">',
                    '<div class="item">',
                    '<a href="javascript:;" class="btn-default btn-block close">取消</a>',
                    '</div>',
                    '<div class="item">',
                    '<a href="javascript:;" class="btn-primary btn-block" id="',
                    btnId,
                    '">',
                    btnTxt,
                    '</a>',
                    '</div>',
                    '</div>',
                    '</div>'
                ].join('');
            },
            show = function(flag, callback) {
                var str = flag ? '<div class="confirm-shade"></div>' : '';

                $('body').append(str + baseHTML(param.title, param.txt, param.btnTxt));

                $el = $('#' + id);
                callback && callback($el);
            },
            bindEvent = function() {
                $('body').on('click', '#' + id + ' .close', function(event) {
                    if (!param.onlyClose) {
                        if (param.closeType === 'hide') {
                            $('.confirm-shade').hide();
                        } else {
                            $('.confirm-shade').remove();
                        }
                    }
                    if (param.closeType === 'hide') {
                        $el.hide();
                    } else {
                        $el.remove();
                        $(this).off(event)
                    }
                    param.closeCallback && param.closeCallback($el);
                }).
                on('click', '#' + btnId, function() {
                    param.callback && param.callback($el);
                });
            },
            init = function() {
                show(param.isShade, param.showCallback);
                bindEvent();
            };

        init();
        return $el;
    },
    /*********** 滑动窗口 *********
     * @param:  object
     * @example:
     * var $el = msgSlide({                 // $el 弹层选择器
            className: string,                    // 样式： css样式
            type: 'up',                       // 类型： 'up'， 'down', 'left', 'right'
            title: string,                    // 标题
            phone: array,                     // 手机号码
            isShade: true,                    // 是否添加遮罩层：true, 添加， false, 不添加； 默认 true
            closeType: 'remove',              // 关闭类型： 'remove', 移除， 'hide'， 隐藏； 默认 'remove',
            onlyClose: false,                 // 是否只关闭弹层: true, 只关闭， false: 同时关闭遮罩层； 默认 false
            closeCallback: function ($el) {   // 关闭弹层后回调，参数：$el, 弹层选择器
                console.log('关闭弹层');
             },
            showCallback: function ($el) {    // 显示弹层后回调，参数：$el, 弹层选择器
                console.log('生成弹层');
            }
        });
        */
    msgSlide = function(options) {
        var $el,
            config = {
                isShade: true, // 是否添加遮罩层
                onlyClose: false, // 是否只关闭弹层
                closeType: 'hide', // 关闭类型
                cancel_btn: '取消' // 关闭按钮内容
            },
            param = $.extend({}, config, options),
            id = tools.randomId('msg_slide_'),
            baseHTML = function(className, type, title, phone, cancel_btn) {
                var str = '',
                    i, len;

                if (tools.isArray(phone)) {
                    for (i = 0, len = phone.length; i < len; i++) {
                        str += ['<a href="tel:',
                            phone[i],
                            '" class="item">',
                            '<div class="varied pr0">',
                            '<i class="r icon-telephone"></i>',
                            '<h3>',
                            phone[i],
                            '</h3>',
                            '</div>',
                            '</a>'
                        ].join('')
                    }
                }

                return ['<div id="',
                    id,
                    '" class="',
                    className,
                    ' slide-',
                    type,
                    '">',
                    '<div class="hd">',
                    '<h3 class="text-center">',
                    title,
                    '</h3>',
                    '</div>',
                    '<div class="bd">',
                    '<div class="global-list">',
                    str,
                    '</div>',
                    '</div>',
                    '<div class="fd">',
                    '<a href="javascript:;" class="close">' + cancel_btn + '</a>',
                    '</div>',
                    '</div>'
                ].join('');
            },
            show = function(flag, callback) {
                var str = flag ? '<div class="confirm-shade"></div>' : '';

                $('body').append(str + baseHTML(param.className, param.type, param.title, param.phone, param.cancel_btn));

                $el = $('#' + id);
                callback && callback($el);
            },
            bindEvent = function() {
                $('body').on('click', '#' + id + ' .close', function(event) {
                    var type = options.type;

                    $el.removeClass('slide-' + type).addClass('back-' + type);

                    setTimeout(function() {
                        if (!param.onlyClose) {
                            if (param.closeType === 'hide') {
                                $('.confirm-shade').hide();
                            } else {
                                $('.confirm-shade').remove();
                            }
                        }
                        if (param.closeType === 'hide') {
                            $el.hide();
                        } else {
                            $el.remove();
                            $(this).off(event)
                        }
                        param.closeCallback && param.closeCallback($el);
                    }, 500);
                });
            },
            init = function() {
                show(param.isShade, param.showCallback);
                bindEvent();
            };

        init();
        return $el;
    };

// Zepto 扩展
// 弹层显示
// @param: boolean; true, 同时显示遮罩层
$.fn.msgShow = function(flag) {
    flag && $('.confirm-shade').show();
    this.show();
};

// 弹层隐藏
// @param: boolean; true, 同时隐藏遮罩层
$.fn.msgHide = function(flag) {
    flag && $('.confirm-shade').hide();
    this.hide();
};

// 弹层添加
// @param: boolean; true, 同时移除遮罩层
$.fn.msgRemove = function(flag) {
    flag && $('.confirm-shade').remove();
    this.remove();
};

// 弹层重新添加
// @param: boolean; true, 同时添加遮罩层
$.fn.msgAdd = function(flag) {
    var $body = $('body');

    flag && $body.append('<div class="confirm-shade"></div>');
    $body.append(this);
};

// 滑动弹层重新添加
// @param: string; 'up', 'down', 'left', 'right'
// @param: boolean; true, 同时添加遮罩层
$.fn.msgAddSlide = function(type, flag) {
    var $body = $('body');

    flag && $body.append('<div class="confirm-shade"></div>');
    this.removeClass('back-' + type).addClass('slide-' + type);
    $body.append(this);
};


// 滚动animate效果
// @param: object;
// key: toT, 滚动目标位置
// key: durTime, 过度动画时间
// key: delay, 定时器时间
// key: callback, 回调函数
$.fn.scrollTo = function(options) {
    var defaults = {
            toT: 0, //滚动目标位置
            durTime: 500, //过渡动画时间
            delay: 30, //定时器时间
            callback: null //回调函数
        },
        opts = $.extend(defaults, options),
        timer = null,
        _this = this,
        curTop = _this.scrollTop(), //滚动条当前的位置
        subTop = opts.toT - curTop, //滚动条目标位置和当前位置的差值
        index = 0,
        dur = Math.round(opts.durTime / opts.delay),
        smoothScroll = function(t) {
            index++;
            var per = Math.round(subTop / dur);
            if (index >= dur) {
                _this.scrollTop(t);
                window.clearInterval(timer);
                if (opts.callback && typeof opts.callback == 'function') {
                    opts.callback();
                }
                return;
            } else {
                _this.scrollTop(curTop + index * per);
            }
        };

    timer = window.setInterval(function() {
        smoothScroll(opts.toT);
    }, opts.delay);

    return _this;
};