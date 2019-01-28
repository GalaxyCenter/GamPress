(function($) {
    $.fn.loadMore = function(options) {
        var defaults = {
            'id': '',
            'url': '/wp-admin/admin-ajax.php',
            'data': '',
            'handle_complete': function() {}
        };
        var opts = $.extend(defaults, options);
        return this.each(function() {
            var $this = $(this);

            function getData() {
                var data = opts.data;
                $('.loading').html('努力加载中...').show();
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: opts.url,
                    data: data,
                    success: function(json) {
                        //$this.find('.load_status').remove();
                        //$this.find('.btn-more').show().removeClass('loading-img').html('Load More');
                        if (json && json.status == 0 && json.data != '' && json.data.items.length != 0) {
                            var dataHtml = template(opts.id, json.data);
                            $this.find('.list').append(dataHtml);
                            $this.find('.loading').hide();
                            eBook.isLoading = false;
                            tools.lazyLoadImg('img');
                        } else {
                            $('.loading').html('没有更多数据');
                        }
                        opts.handle_complete(opts);
                    },
                    error: function() {}
                });
            }
            getData();
        });
    };

})($);

var eBook = {
    'isLoading': false,
    'page_index': 1,
    'page_size': 10
}

$(document).ready(function() {
    //--------------------- global funciton -------------------------
    $('#btn_add_bookmark').click(function() {
        var $this = $(this);
        var book_id = $this.data('book-id');

        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                'action': 'add_bookmark',
                'book_id': book_id
            },
            success: function(data) {
                if (data['status'] == 0) {
                    msg(data['msg'], 'success');
                    $this.html('已追书');
                    $this.unbind();

                    var count = parseInt($('#book p.type em.r span').text());
                    count++;
                    $('#book p.type em.r span').text(count);
                    $this.removeAttr('id');
                } else {
                    msg(data['msg'], 'error');
                }
            }
        });
    });

    //$('.login-msg').click(function(){
    $(document).delegate('.login-msg', 'click', function() {
        var $this = $(this);

        msg('您还没登录', 'error', function() {
            location.href = $this.attr('_rel');
        });
    });

    // like
    $('#list_comments').delegate('.gp-icon-like', 'click', function() {
        var item_id = $(this).closest('li').data('id');
        var $this = $(this);
        $this.addClass('load');
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                'action': 'add_vote',
                'item_id': item_id,
                'type': 'like'
            },
            success: function(data) {
                if (data['status'] == 0) {
                    msg(data['msg'], 'success');
                    var likes = parseInt($this.find('em').html()) + 1;
                    $this.addClass('active amplify').removeClass('gp-icon-like').find('em').html(likes);
                } else {
                    msg(data['msg'], 'error');
                }
                $this.removeClass('load');
            }
        });
    });
    // end

    //---------------------book marker-------------------------------
    if ($('#book #book_marks')[0]) {
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                'action': 'get_bookmark_count',
                'book_id': book_id
            },
            success: function(data) {
                if (data['status'] == 0) {
                    $('#book_marks').html(data.data);
                }
            }
        });
    }

    //---------------------books ------------------------------------
    $('#box_pub_activity button.btn-submit').bind('click', function() {
        var data = $('.form-box').serializeJSON();
        if (data.content == '') {
            msg('评论内容不能为空', 'error');
            return;
        } else if (data.length > 500) {
            msg('评论不能超过500字', 'error');
            return;
        }
        $(this).unbind('click');
        $.ajax({
            url: data.url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data) {
                msg(data['msg'], 'success', function(){
                    if (data['status'] == 0) {
                        window.location = window.location.href.replace('pub-activity', '');
                    } else if ( data['status'] == 3 ) {
                        window.location = 'http://www.adaixiong.com/login?auto_login=false&redirect=' + window.location.href;
                    }
                });
            }
        });
    });

    // end

    //---------------------book activities --------------------------
    var load_activities = function(book_id, order_by) {
        $('#list_comments').loadMore({
            id: 'tpl_activity_list',
            data: {
                'action': 'get_activities',
                'item_id': book_id,
                'order_by': order_by,
                'page_size': 6,
                'page_index': eBook.page_index
            }
        });
    };

    if ($('#box_activities')[0]) {
        var order_by = $('#list_comments').data('orderby');
        eBook.isLoading = false;
        eBook.order_by = order_by;
        load_activities(book_id, eBook.order_by);
        if ($('#list_comments').data('auto-load') == true) {
            $(window).on('scroll', function() {
                if ($(document).height() - $(window).scrollTop() - window.innerHeight < 50 && !eBook.isLoading) {
                    eBook.isLoading = true;
                    eBook.page_index++;
                    load_activities(book_id, eBook.order_by);
                }
            });
        }
    }

    $('#box_activities .hd a').click(function() {
        $('#box_activities .hd a').removeClass('active');
        $(this).addClass('active');
        var order_by = $(this).data('orderby');
        eBook.page_index = 1;
        eBook.order_by = order_by;
        load_activities(book_id, eBook.order_by);

        $('#list_comments .list').empty();
    });
    // end

    //---------------------books ------------------------------------
    var stacks = function() {
        var load_books = function(term_name) {
                $('#list_books').loadMore({
                    id: 'tpl_books_list',
                    data: {
                        'action': 'get_books',
                        'search_terms': '',
                        'order_by': 'id',
                        'term_name': eBook.term_name,
                        'words_query': eBook.words_query,
                        'charge_type': eBook.charge_type,
                        'page_size': eBook.page_size,
                        'page_index': eBook.page_index
                    },
                    handle_complete: function(opts) {
                        eBook.page_index++;
                    }
                });
            },
            bindEvent = function() {
                $('body').on('tap', '#classify', function() {
                    var $sideMenu = $('.side-menu');

                    // 关闭排序弹窗
                    $('.shade').remove();
                    $('.second-menu').hide();

                    $('body').append('<div class="shade z2001"></div>');
                    $sideMenu.removeClass('back-right').addClass('slide-right').show();

                }).
                on('tap', '#sort', function() {
                    var $secondMenu = $('.second-menu'),
                        type = $(this).attr('type');

                    // 关闭分类弹窗
                    $('.shade').remove();
                    $('.side-menu').hide();

                    $('body').append('<div class="shade"></div>');
                    $secondMenu.removeClass('back-down').addClass('slide-down').show();
                }).
                on('tap', '#close_btn, .shade', function() {
                    $('.side-menu').removeClass('slide-right').addClass('back-right');
                    $('.second-menu').removeClass('slide-down').addClass('back-down');
                    setTimeout(function() {
                        $('.shade').remove();
                        $('.side-menu').hide();
                        $('.second-menu').hide();
                    }, 500);
                }).
                on('tap', '.sort-list .item', function() {
                    $(this).addClass('active').siblings().removeClass('active');
                    $('#sort').html($(this).html() + ' <i class="fa fa-sort"></i>');

                    // 初始化
                    eBook.page_index = 1;
                    $('#list_books').find('.list').html('');
                    eBook.order_by = $(this).attr('_type');

                    load_books();
                }).
                on('tap', '#side_menu_btn', function() {
                    $('#category').find('a.active').each(function() {
                        var key = $(this).parent().attr('_type'),
                            value = $(this).attr('_type');

                        key && (eBook[key] = value);
                    });
                    // 初始化
                    eBook.page_index = 1;
                    $('#list_books').find('.list').html('');

                    load_books();

                    $('#classify').html(eBook.term_name + ' <i class="fa fa-filter"></i>');
                    // 关闭弹窗
                    $('.side-menu').removeClass('slide-right').addClass('back-right');
                    setTimeout(function() {
                        $('.shade').remove();
                        $('.second-menu').hide();
                    }, 500);
                });

                $('#category').on('tap', 'a.item', function() {
                    var $options = $('#category').find('.options'),
                        id = $(this).data('id');

                    if ($(this).hasClass('active')) {
                        return false;
                    }
                    $(this).addClass('active').siblings().removeClass('active');

                    if (id) {
                        $options.removeClass('active').find('a.item').removeClass('active');
                        $('#' + id).addClass('active').find('a.item:first-child').addClass('active');
                    }
                });

                $(window).on('scroll', function() {
                    if ($(document).height() - $(window).scrollTop() - window.innerHeight < 50 && !eBook.isLoading) {
                        eBook.isLoading = true;
                        load_books();
                    }
                });
            },
            info = function() {
                eBook.page_index = 2;

                bindEvent();
            };

        info()
    };

    if ($('#box_books')[0]) {
        stacks();
    }
    // end

    //--------------------- search ----------------------------------
    var search_books = function(search_terms) {
        $('#list_books').loadMore({
            id: 'tpl_books_list',
            data: {
                'action': 'get_books',
                'search_terms': search_terms,
                'order_by': 'id',
                'term_name': eBook.term_name,
                'page_size': eBook.page_size,
                'page_index': eBook.page_index
            },
            handle_complete: function(opts) {
                eBook.page_index++;
            }
        });
    };

    if ($('.head-search')[0]) {
        $('#search_del').click(function() {
            $('#search_txt').val('');
        });

        $('#btn_search').click(function() {
            if ($('#search_txt').val() == '') {
                return false;
            }
            eBook.page_index = 1;
            eBook.search_terms = $('#search_txt').val();
            $('#list_books .list').empty();
            search_books(eBook.search_terms);
        });

        $('#btn_search_form').click(function() {
            if ($('#search_txt').val() == '') {
                return false;
            }

            $('#form_search').submit();
        });
    }

    //---------------------user bookmark ----------------------------
    var load_user_bookmark = function() {
        var type = $('#list_user_bookmark').data('type');

        $('#list_user_bookmark').loadMore({
            id: 'tpl_user_bookmark_list',
            data: {
                'action': 'get_user_bookmark',
                'type': type,
                'order_by': 'post_time',
                'page_size': 8,
                'page_index': eBook.page_index
            }
        });
    };

    if ($('#box_user_bookmark')[0]) {
        load_user_bookmark();
        eBook.isLoading = false;
        $(window).on('scroll', function() {
            if ($(document).height() - $(window).scrollTop() - window.innerHeight < 50 && !eBook.isLoading) {
                eBook.isLoading = true;
                eBook.page_index++;
                load_user_bookmark();
            }
        });
    }
    // end

    //---------------------user record------------------------------
    var load_user_coin_bill = function() {
        var type = $('#list_user_coin_bill').data('type');

        $('#list_user_coin_bill').loadMore({
            id: 'tpl_user_coin_bill_list',
            data: {
                'action': 'get_coin_bills',
                'type': type,
                'order_by': 'order_by',
                'page_size': 6,
                'page_index': eBook.page_index
            }
        });
    };

    if ($('#box_user_record')[0]) {
        load_user_coin_bill();

        $(window).on('scroll', function() {
            if ($(document).height() - $(window).scrollTop() - window.innerHeight < 50 && !eBook.isLoading) {
                eBook.isLoading = true;
                eBook.page_index++;
                load_user_coin_bill();
            }
        });
    }
    // end

    //---------------------user notifiction -----------------------
    var load_user_notification = function() {
        var type = $('#list_user_coin_bill').data('type');

        $('#list_user_coin_bill').loadMore({
            id: 'tpl_notification_list',
            data: {
                'action': 'get_notifications',
                'type': type,
                'order_by': 'order_by',
                'page_size': 6,
                'page_index': 1
            }
        });
    }

    if ($('#list_notifications')[0]) {
        load_user_notification();
    }

    // end

    //---------------------book - catalog --------------------------
    $('#book #box_chapters .catalog-select').change(function(i) {
        var page = $(this).val();
        var sorder = $(this).data('order');

        window.location = book_chapters_permalink + '/' + page + '?order=' + sorder;
    });

    if ($('#book #box_chapters')[0]) {
        //$('#nav_left').attr('href', book_permalink);
    }
    // end

    //---------------------user profile edit ------------------------
    $('#btn_user_profile_save').click(function() {
        var data = $('.form-box').serializeJSON();
        $.ajax({
            url: data.url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data) {
                if (data['status'] == 0) {
                    msg(data['msg'], 'success');
                    window.history.back();
                } else {
                    msg(data['msg'], 'error');
                }
            }
        });

    });
    // end

    //---------------------user book import--------------------------
    $('#btn_user_book_import').click(function() {
        var data = $('.form-box').serializeJSON();
        $.ajax({
            url: data.url,
            type: 'post',
            dataType: 'json',
            data: data,
            success: function(data) {
                if (data['status'] == 0) {
                    msg(data['msg'], 'success', function(){
                        window.history.back();
                    });
                } else {
                    msg(data['msg'], 'error');
                }
            }
        });
        return false;
    });
    // end

    //---------------------recharge-----------------------------------
    $('#pay_content ul li').click(function() {
        $('#pay_content ul li').removeClass('active');
        $(this).addClass('active');

        var price = $(this).attr('_price');
        var item_id = $(this).data('item_id');

        $(this).addClass('active').siblings().removeClass('active');

        $('#item_id').val(item_id);
        $('#price').val(price);
        $('#total_fee').val(price);
        $('#product_name').val($('#product_name').data('text') + price);
        $('#product_description').val($('#product_description').data('text') + price);

        msgAlert({
            title: '',
            txt: '正在唤醒支付平台',
            btnTxt: '我知道啦',
            isShade: true,
            closeType: 'remove',
            onlyClose: false,
            callback: function($el) {
                $el.remove();
                $('.shade').remove();
            }
        });

        $('.form-box').submit();
    });
    $('#pay_content #pay_btn').click(function() {
        var fee = parseInt($('#money_txt').val());
        if (fee < 10)
            return false;

        $('.form-box').submit();
    });
    // end

    //---------------------chapter------------------------------------
    $('#auto_pay').click(function() {
        var auto_pay = $(this).attr('_auto') != 'yes';
        var book_id = $(this).data('book_id');
        var $this = $(this);
        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                action: 'update_auto_create_order',
                book_id: book_id,
                auto_pay: auto_pay
            },
            success: function(data) {
                if (auto_pay == true) {
                    $this.attr('_auto', 'no');
                    $this.find('.toggle').removeClass('fa-toggle-off').addClass('fa-toggle-on');
                } else {
                    // 取消订阅
                    $this.attr('_auto', 'off');
                    $this.find('.toggle').removeClass('fa-toggle-on').addClass('fa-toggle-off');
                }
            }
        });
    });
    // end
    //---------------------login ------------------------
    $('#box_login input[name="phone"]').bind('input propertychange', function() {
        if ($(this).val().length == 0) {
            $('#box_login #btn_get_sms_captcha').addClass('btn-disable').attr('disabled', 'disabled');
        } else {
            $('#box_login #btn_get_sms_captcha').removeClass('btn-disable').removeAttr('disabled');
        }
    });

    $('#box_login #btn_get_sms_captcha').click(function() {
        $.ajax({
            url: '/sms/request_code',
            type: 'post',
            dataType: 'json',
            data: {
                phone: $('#box_login input[name="phone"]').val(),
            },
            success: function(data) {
                if (data['status'] == 0) {
                    $('#box_login #btn_get_sms_captcha').addClass('btn-disable').attr('disabled', 'disabled');
                    msg(data['msg'], 'success');
                }
                msg(data['msg'], 'error');
            }
        });
    });
    $('#box_login input[name="sms_code"]').bind('input propertychange', function() {
        if ($(this).val().length == 0) {
            $('#box_login #btn_login').addClass('btn-disable').attr('disabled', 'disabled');
        } else {
            $('#box_login #btn_login').removeClass('btn-disable').removeAttr('disabled');
        }
    });

    $('#box_login #btn_login').click(function() {
        $.ajax({
            url: '/sms/login',
            type: 'post',
            dataType: 'json',
            data: {
                'phone': $('#box_login input[name="phone"]').val(),
                'code': $('#box_login input[name="sms_code"]').val(),
                'redirect': $('#box_login input[name="redirect"]').val(),
                'tab': $('#box_login input[name="tab"]').val()
            },
            success: function(data) {
                if (data['status'] == 0) {
                    msg(data['msg'], 'success');
                    window.location.href = data['data'];
                } else {
                    msg(data['msg'], 'error');
                }
            }
        });
    });

    function siwp_genid() {
        var cid = '',
            chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        for (var c = 0; c < 40; ++c) { cid += chars.charAt(Math.floor(Math.random() * chars.length)); }
        return cid;
    };
    // end
});