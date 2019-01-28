(function($) {
    // el 存在，则执行函数
    $.fn.existE = function(callback) {
        if (this.length) {
            callback();
        }
    };

    function getData(url, param, callback) {
        $.getJSON(url, param, function(json) {
            if (typeof json === 'object') {
                callback && callback(json);
            }
        })
    }

    /*------------ 百度统计 事件上报 -----------*/
    function clickStat(name) {
        if (typeof(_hmt) != "undefined" && name) {
            _hmt.push(["_trackEvent", name, "click"]);
        }
    }

    // 首页
    $('.banner').on('click', function() {
        clickStat('首页 banner');
    });

    $('#zhongbang').on('click', '.media .item', function() {
        clickStat('首页 重磅推荐 详情列表');
    }).
    on('click', '.pic-list .item', function() {
        clickStat('首页 重磅推荐 图文列表');
    });

    $('#remen').on('click', '.pic-list .item', function() {
        clickStat('首页 热门精选');
    });

    $('#mianfei').on('click', '.pic-list .item', function() {
        clickStat('首页 免费推荐 图文列表');
    }).
    on('click', '.txt-list .item', function() {
        clickStat('首页 免费推荐 文字列表');
    });

    $('#dashen').on('click', '.pic-list .item', function() {
        clickStat('首页 大神佳作');
    });

    $('#jinpai').on('click', '.media .item', function() {
        clickStat('首页 金牌完本');
    });

    $('#read_lately_btn').on('click', function() {
        clickStat('首页 最近阅读 按钮');

        // 记录 ad 出现次数
        $('#ad_bottom').existE(function() {
            clickStat('双旦 最近位 出现');
        });
    });

    $('#lately_read').on('click', '.item', function() {
        clickStat('首页 最近阅读 列表');
    });

    $('#lately_tuijian').on('click', '.item', function() {
        clickStat('首页 最近阅读 推荐');
    });

    $('#lately_login').on('click', '.item', function() {
        clickStat('首页 最近阅读 登录');
    });

    // 排行页
    $('#book_rank').on('click', '.nav .item', function() {
        var txt = $(this).text();
        clickStat('排行 ' + txt);
    }).
    on('click', '.tab-ranking > .hd > .item', function() {
        clickStat('排行 ' + $(this).html());
    });

    // 免费页
    $('#free_mianfei').on('click', '.pic-list .item', function() {
        clickStat('免费 免费书单');
    });

    $('#free_tuijian').on('click', '.pic-list .item', function() {
        clickStat('免费 精彩推荐 图文列表');
    }).
    on('click', '.txt-list .item', function() {
        clickStat('免费 精彩推荐 文字列表');
    });

    // 作品页
    $('#btn_add_bookmark').on('click', function() {
        clickStat('作品页 追书');
    });

    // 目录页
    $('#sort_btn').on('click', function() {
        clickStat('目录页 ' + $(this).html());
    });

    // 评论页
    $('#comment_submit').on('click', function() {
        clickStat('评论页 评论');
    });

    // 帮助
    $('#help_btn').on('click', function() {
        clickStat('底部 帮助 点击');
    });

    // 搜索
    $('#quick_search').on('click', '.item', function() {
        clickStat('大家都在搜');
    });

    // 阅读页
    $('#guess_books').existE(function() {
        clickStat('阅读页 95%的人都爱看 出现');
        $('#guess_books').on('click', '.pic-list .item', function() {
            clickStat('阅读页 95%的人都爱看 点击');
        });
    });

    $('#read_login_btn').existE(function() {
        clickStat('阅读页 登录按钮 出现');
        $('#read_login_btn').on('click', function() {
            clickStat('阅读页 登录按钮 点击');
        })
    });

    $('#read_recharge_btn').existE(function() {
        clickStat('阅读页 充值按钮 出现');
        $('#read_recharge_btn').on('click', function() {
            clickStat('阅读页 充值按钮 点击');
        })
    });

    $('.recommend-txt').existE(function() {
        clickStat('阅读页 文字链推荐 出现');
        $('.recommend-txt').on('click', 'a', function() {
            clickStat('阅读页 文字链推荐 点击');
        });
    });

    // 个人中心
    $('#recharge_btn').on('click', function() {
        clickStat('个人中心 充值');
    });

    $('#pay_content').existE(function() {
        clickStat('充值页 访问');
        $('#pay_content').on('click', '.money-list li', function() {
            clickStat('充值页 ' + $(this).attr('_price') + '元 点击');
        })
    });

    $('#pay_success').existE(function() {
        clickStat('充值成功页');
    });

    $('#pay_fail').existE(function() {
        clickStat('充值失败页');
    });

    // ad owe
    $('.ny-18-list').on('click', '.item', function() {
        clickStat('新年书单 ' + $(this).find('h3').html());
    });

    $('#ny_male_1').on('click', function() {
        clickStat('新年 首页 男频');
    });

    $('#ny_female_1').on('click', function() {
        clickStat('新年 首页 女频');
    });

    $('#ny_male_2').on('click', function() {
        clickStat('新年 最近 男频');
    });

    $('#ny_female_2').on('click', function() {
        clickStat('新年 最近 女频');
    });

    $('#ny_male_3').on('click', function() {
        clickStat('新年 个人 男频');
    });

    $('#ny_female_3').on('click', function() {
        clickStat('新年 个人 女频');
    });

    $('#ny_male_4').on('click', function() {
        clickStat('新年 阅读 男频');
    });

    $('#ny_female_4').on('click', function() {
        clickStat('新年 阅读 女频');
    });

    $('#act_reda').on('click', function() {
        clickStat('广告位 热大');
    });

    /*------------ 图片懒加载 && 倒计时-----------*/
    tools.lazyLoadImg('img');
    $('#timer').existE(function() {
        tools.timer();
    });

    /*------------ 返回顶部 -----------*/
    $('#go_top').on('tap', function() {
        $(window).scrollTop(0);
    });

    /*------------ 最近阅读弹层 -----------*/
    var readLately = function() {
        $('#read_lately_btn').on('tap', function() {
            var $sideMenu = $('.side-menu');

            $('.shade').remove();
            $('body').append('<div class="shade"></div>');
            $sideMenu.removeClass('back-left').addClass('slide-left').show();
        });

        $('body').on('tap', '.shade', function() {
            $('.side-menu').removeClass('slide-left').addClass('back-left');
            setTimeout(function() {
                $('.shade').remove();
                $('.side-menu').hide();
            }, 500);
        });
    };

    $('#read_lately_btn').existE(function() {
        readLately();
    });

    /*------------ 切换 -----------*/
    var tab = function() {
        $('#tab').on('tap', '#tab > .hd > .item', function() {
            var id = $(this).data('id');

            $(this).addClass('active').siblings().removeClass('active');

            $('#' + id).addClass('active').siblings().removeClass('active');
        });
    };

    $('#tab').existE(function() {
        tab();
    });

    /*----------- 支付 -----------*/
    // var pay = function() {
    //     $('.pay').on('tap', '.list a.item', function() {
    //         $(this).addClass('active').siblings().removeClass('active');

    //         $('#pay_module').val($(this).attr('_type'));
    //     });

    //     $('.money-list').on('tap', 'li', function() {
    //         var price = $(this).attr('_price');

    //         $(this).addClass('active').siblings().removeClass('active');

    //         $('#total_fee').val(price);
    //         $('#pay_btn').removeClass('btn-disable').find('span').html('¥' + price);
    //     });

    //     $('#money_txt').on('focus', function() {
    //         var reg = /^[1-9]\d+$/, // 大于等于10
    //             price = $(this).val();

    //         $('.money').find('.item.active').removeClass('active');

    //         if (reg.test(price)) {
    //             $('#money_em').html(price);
    //             $('#total_fee').val(price);
    //             $('#pay_btn').removeClass('btn-disable').find('span').html('¥' + price);
    //         } else {
    //             $('#total_fee').val(0);
    //             $('#pay_btn').addClass('btn-disable').find('span').html('¥' + 0);
    //         }
    //     }).
    //     on('keyup', function() {
    //         var $tip = $('.money-tip'),
    //             reg = /^[1-9]\d+$/, // 大于等于10
    //             price = $(this).val();

    //         if (reg.test(price)) {
    //             $('#money_em').html(+price * 100);
    //             $('#total_fee').val(price);
    //             $('#pay_btn').removeClass('btn-disable').find('span').html('¥' + price);
    //             $tip.hide();
    //         } else {
    //             $('#money_em').html(0);
    //             $('#total_fee').val(0);
    //             $('#pay_btn').addClass('btn-disable').find('span').html('¥' + 0);
    //             $tip.show();
    //         }
    //     });
    // };

    // $('#pay_content').existE(function() {
    //     pay();
    // });

    /*----------- 评分 -----------*/
    var commentStar = function() {
        $('#star').on('tap', 'a', function() {
            var index = $(this).index() + 1,
                $star = $('#star').find('a'),
                i;

            $star.removeClass('fa-star').addClass('fa-star-o');

            for (i = 0; i < index; i++) {
                $($star[i]).removeClass('fa-star-o').addClass('fa-star');
            }
        });
    };

    $('#star').existE(function() {
        commentStar();
    });

    /*----------- 搜索书单 -----------*/
    var searchBooks = function() {
        $('#search_txt').on('focus', function() {
            var $del = $('#search_del');

            if ($(this).val()) {
                $del.show();
            } else {
                $del.hide();
            }
        }).
        on('keyup', function() {
            var $del = $('#search_del');

            if ($(this).val()) {
                $del.show();
            } else {
                $del.hide();
            }
        });

        $('#search_del').on('tap', function() {
            $(this).hide();
            $('#search_txt').val('');
        });
    };

    $('#search_close').existE(function() {
        searchBooks();
    });


    /*----------- 作品详情 -----------*/
    var bookContent = function() {
        $('body').on('tap', '.summary', function() {
            $(this).css('height', 'auto');
            $('#txt_down').hide();
        });
    };

    $('#book').existE(function() {
        bookContent();
    });

    /*----------- 作品目录 -----------*/
    var catalogList = function() {
        var id = tools.getParam('idx'),
            $el = $('#' + id);

        if (id === null) {
            return;
        }

        $el.addClass('active');

        // document位置延时
        setTimeout(function() {
            $(window).scrollTop($el.offset().top);
        }, 100)
    }
    $('.catalog-list').existE(function() {
        catalogList();
    })

    /*----------- 作品阅读 -----------*/
    var readSetting = function() {
        var isLoding = false,
            replaceTxt = function() {
                var $content = $('.chapter').find('p');

                $content.each(function() {
                    var txt = $(this).html();

                    $(this).html(txt.replace(/本书首发阿呆熊文学网站，请关注公众号【adaixiongread】支持正版！/g, ''));
                })
            },
            bindEvent = function() {
                var hammer = new Hammer(document.getElementById('read')),
                    fontSizeRange = +($.fn.cookie('_size') || 2);

                hammer.on('tap', function(event) {
                    var $read = $('#read'),
                        y = event.center.y,
                        winH = window.innerHeight,
                        scope = winH / 3,
                        winTop = $(window).scrollTop(),
                        $bkRead = $('.bk-read'),
                        lineH = Math.round(parseFloat($bkRead.css('font-size')) * 1.9); // 行高

                    if (event.target.getAttribute('_bubbling') == 1) {
                        return false;
                    }

                    // 判断当前是否打开弹层
                    if ($read.attr('_type') === 'off') {
                        $read.attr('_type', 'on');
                        $('.read-head').removeClass('slide-down').addClass('back-down');
                        $('.read-foot').removeClass('slide-up').addClass('back-up');
                        $('.read-menu, .read-setting, .read-mark').hide();
                        setTimeout(function() {
                            $('.read-head, .read-foot').removeClass('active');
                        }, 500);
                        return false;
                    }

                    if (y < scope) {
                        // 上滚动
                        $(window).scrollTop(winTop - winH + lineH);
                    } else if (y > scope * 2) {
                        // 下滚动
                        $(window).scrollTop(winTop + winH - lineH);
                    } else {
                        // 显示弹层
                        $read.attr('_type', 'off');
                        $('.read-head').removeClass('back-down').addClass('slide-down active');
                        $('.read-foot').removeClass('back-up').addClass('slide-up active');
                        $('.read-mark').show();
                    }
                });

                $('body').on('tap', '#collapse', function(event) {
                    event.stopPropagation();
                    event.preventDefault();

                    $('.read-setting').hide();
                    $('.read-menu, .read-mark').toggle();
                }).
                // 自动订购下一章
                // on('tap', '#auto_pay', function() {
                //     if ($(this).attr('_auto') === 'no') {
                //         $(this).attr('_auto', 'yes');
                //         $(this).find('.toggle').removeClass('fa-toggle-off').addClass('fa-toggle-on');
                //     } else {
                //         $(this).attr('_auto', 'no');
                //         $(this).find('.toggle').removeClass('fa-toggle-on').addClass('fa-toggle-off');
                //     }
                // }).
                // 设置
                on('tap', '#setting', function() {
                    $('.read-menu').hide();
                    $('.read-setting').toggle();
                }).
                // 颜色
                on('tap', '#color .item', function() {
                    var $night = $('#night'),
                        color = $(this).attr('_color'),
                        id = $(this).attr('_id');

                    $.fn.cookie('_color', color, {
                        expires: 30,
                        path: '/',
                        domain: 'www.adaixiong.com'
                    });

                    $(this).addClass('active').siblings().removeClass('active');
                    $('#color').attr('_color', color);
                    $('body').attr('id', id);

                    // 重置夜间模式
                    $.fn.cookie('_mode', 'day', {
                        expires: 30,
                        path: '/',
                        domain: 'www.adaixiong.com'
                    });
                    $night.attr('_mode', 'day');
                    $night.find('.fa').removeClass('fa-sun-o').addClass('fa-moon-o');
                    $night.find('p').html('夜间');
                }).
                // 夜间
                on('tap', '#night', function() {
                    var $body = $('body'),
                        $color = $('#color'),
                        mode = $(this).attr('_mode'),
                        id = $(this).attr('_bdid'),
                        sc = $(this).attr('_sc');

                    if (mode === 'day') {
                        $(this).attr('_mode', 'night');
                        $(this).attr('_bdid', $body.attr('id'));
                        $(this).attr('_sc', $color.find('.active a').attr('class'));
                        $(this).find('.fa').removeClass('fa-moon-o').addClass('fa-sun-o');
                        $(this).find('p').html('日间');
                        $body.attr('id', 'skin_night');

                        // 重置颜色选择
                        $color.find('.item').removeClass('active');
                    } else {
                        $(this).attr('_mode', 'day');
                        $(this).find('.fa').removeClass('fa-sun-o').addClass('fa-moon-o');
                        $(this).find('p').html('夜间');
                        $body.attr('id', id);

                        // 恢复颜色选择
                        $('.' + sc).parent().addClass('active');
                    }

                    $.fn.cookie('_mode', $(this).attr('_mode'), {
                        expires: 30,
                        path: '/',
                        domain: 'www.adaixiong.com'
                    });

                }).
                // 字体大小
                on('tap', '#font_plus', function() {
                    var $bkRead = $('.bk-read'),
                        fontSize = parseFloat($bkRead.css('font-size'));


                    if (fontSizeRange < 4) {
                        fontSizeRange++;
                        $('#font_size').attr('_size', fontSizeRange);
                        $bkRead.css('font-size', fontSize + 2 + 'px');
                    }

                    $.fn.cookie('_size', fontSizeRange, {
                        expires: 30,
                        path: '/',
                        domain: 'www.adaixiong.com'
                    });
                }).
                on('tap', '#font_minus', function() {
                    var $bkRead = $('.bk-read'),
                        fontSize = parseFloat($bkRead.css('font-size'));

                    if (fontSizeRange) {
                        fontSizeRange--;
                        $('#font_size').attr('_size', fontSizeRange);
                        $bkRead.css('font-size', fontSize - 2 + 'px');
                    }

                    $.fn.cookie('_size', fontSizeRange, {
                        expires: 30,
                        path: '/',
                        domain: 'www.adaixiong.com'
                    });
                }).
                // 禁止右键打开菜单
                on('contextmenu', function() {
                    return false;
                }).
                // 禁止选中文字 不兼容firefox，firefox可用css属性
                on('selectstart', function() {
                    return false;
                }).
                // 禁止复制
                on('copy', function() {
                    return false;
                }).
                // 禁止粘贴
                on('paste', function() {
                    return false;
                });
            },
            init = function() {
                // 初始化设置
                var $body = $('body'),
                    $font = $('.bk-read'),
                    $color = $('#color'),
                    $night = $('#night'),
                    size = $.fn.cookie('_size') || 2,
                    color = $.fn.cookie('_color') || 1,
                    mode = $.fn.cookie('_mode') || 'day',
                    id;

                // 颜色
                $color.find('.item').each(function() {
                    if ($(this).attr('_color') === color) {
                        $(this).addClass('active').siblings().removeClass('active');
                        id = $(this).attr('_id');
                    }
                });

                // 夜间模式
                if (mode === 'night') {
                    $night.attr('_bdid', id);
                    $night.attr('_mode', 'night');
                    $night.attr('_sc', $color.find('.active a').attr('class'));
                    $night.find('.fa').removeClass('fa-moon-o').addClass('fa-sun-o');
                    $night.find('p').html('日间');

                    // 重置颜色选择
                    $color.find('.item').removeClass('active');
                }

                replaceTxt();
                bindEvent();
            };

        init();
    };

    /*----------- 猜你喜欢作品名处理 -----------*/
    var setBookName = function() {
        var $item = $('#guess_books').find('.pic-list p');

        $item.each(function() {
            if ($(this).html().length < 8) {
                $(this).css('text-align', 'center');
            }
        });
    }

    $('#read').existE(function() {
        readSetting();
        setBookName();
    });

    /*----------- 消息提示 -----------*/
    $('#user_tip').existE(function() {
        getData('/wp-admin/admin-ajax.php', { action: 'unread_messages_count' }, function(json) {
            if (json.status == 0 && json.data) {
                $('#user_tip').append('<span>' + ($('.head-m').length ? '' : json.data) + '</span>');
            }
        });
    });

    // add activity icon
    var addActivityIcon = function() {
        var timer,
            show = function() {
                // 排除 充值页面和VIP章节订购窗口页面
                if ($('#zhongbang').length || $('#list_user_bookmark').length) {
                    var str = ['<a href="http://www.adaixiong.com/login?redirect=user&tab=recharge" class="ad-pop-up" id="ad_pop_up">',
                        '<i class="close"></i>',
                        '<img src="/wp-content/plugins/gampress-ext/themes/eBooks/dist/images/act-owe-big.jpg?ver=20171225">',
                        '</a>'
                    ].join('');

                    $('body').append(str);
                    clickStat('双旦 大图 出现');
                }
            },
            bindEvent = function() {
                $('body').on('click', '#ad_pop_up .close', function(e) {
                    var day = 24 * 60 * 60 * 1000;
                    clearTimeout(timer);
                    $('#ad_pop_up').remove();
                    // 默认一天
                    tools.setCookie('ad_time', +new Date() + day);
                }).
                on('click', '#ad_pop_up', function(e) {
                    if (e.target.className === 'close') {
                        e.preventDefault();
                    }
                })
            },
            init = function() {
                var adTime = tools.getCookie('ad_time');

                if (+new Date() > +adTime) {
                    show();
                    timer = setTimeout(function() {
                        var h = 60 * 60 * 1000;
                        // 一个小时
                        tools.setCookie('ad_time', +new Date() + h, h);
                        $('#ad_pop_up').remove();
                    }, 8000)
                }
                bindEvent()
            };

        if (location.pathname != '/')
            init();

    }
    // addActivityIcon();

    // 虚拟键盘 保存按钮处理
    $('.wrap-btn.absolute').existE(function() {
        $('.txt').on('focus', function() {
            $('.wrap-btn').removeClass('absolute');
        }).
        on('blur', function() {
            $('.wrap-btn').addClass('absolute');
        });
    });

    // 公告通知
    var notice = function() {
        // 排除充值页和登录页
        if (!$('#pay_content').length && !$('#box_login').length && showMsg == 1) {
            msgAlert({ // $el 弹层选择器
                title: '网站公告', // 标题
                txt: '各位小主，为了给您提供更好的服务，本站将于本月18日凌晨2:00-5:00进行升级，期间暂不能使用。敬请期待！', // 内容
                btnTxt: '我知道了', // 按钮文本 默认：确定
                isShade: true, // 是否添加遮罩层：true, 添加， false, 不添加； 默认 true
                closeType: 'remove', // 关闭类型： 'remove', 移除， 'hide'， 隐藏； 默认 'remove',
                onlyClose: false, // 是否只关闭弹层: true, 只关闭， false: 同时关闭遮罩层； 默认 false
                callback: function($el) { // 点击确认按钮， 参数：$el,弹层选择器
                    getData('/wp-admin/admin-ajax.php', {action:'close_tips'}, function(){
                        $el.msgRemove(true);
                        clickStat('升级公告 关闭');
                    });
                },
                closeCallback: function($el) { // 关闭弹层后回调，参数：$el, 弹层选择器
                    getData('/wp-admin/admin-ajax.php', {action:'close_tips'}, function(){
                        clickStat('升级公告 关闭');
                    })
                },
                showCallback: function($el) { // 显示弹层后回调，参数：$el, 弹层选择器
                    clickStat('升级公告 出现');
                }
            });
        }
    }
    notice();











})(Zepto);