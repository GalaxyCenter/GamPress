/**
 * Created by f on 2015/4/30.
 */
(function(win, $) {
    win.shine = function(obj) {
        var el = obj.el, //考虑到可有会有多个 el， 所有不用 querySelector
            imgSrc = obj.imgSrc,
            clipRange = obj.clipRange || 30, //涂抹范围
            lasting = obj.lasting || 50, //涂抹长时间后 canvas 移除
            isClip = false, //判断是否涂抹
            flag = 0, //计数， 120 后隐藏 canvas
            //手指坐标
            startX = 0,
            startY = 0,
            moveX = 0,
            moveY = 0,
            //获取 el在 body 上的位置
            getPosition = function(option) {
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
            //手指触摸
            eventDown = function(event) {
                var canvas = event.target,
                    ctx = canvas.getContext('2d'),
                    offsetX = parseInt(canvas.style.left, 10),
                    offsetY = parseInt(canvas.style.top, 10),
                    hasTouch = 'ontouchstart' in window ? true : false;

                startX = (hasTouch ? event.targetTouches[0].pageX : event.pageX) - offsetX;
                startY = (hasTouch ? event.targetTouches[0].pageY : event.pageY) - offsetY;
                isClip = true;
                flag += 2;
                event.preventDefault();

                ctx.save();
                ctx.beginPath();
                ctx.arc(startX, startY, clipRange / 2, 0, 2 * Math.PI);
                ctx.fill();
                ctx.restore();
            },
            //手指滑动
            eventMove = function(event) {
                var canvas = event.target,
                    ctx = canvas.getContext('2d'),
                    offsetX = parseInt(canvas.style.left, 10),
                    offsetY = parseInt(canvas.style.top, 10),
                    hasTouch = 'ontouchstart' in window ? true : false;

                event.preventDefault();
                if (isClip) {
                    moveX = (hasTouch ? event.targetTouches[0].pageX : event.pageX) - offsetX;
                    moveY = (hasTouch ? event.targetTouches[0].pageY : event.pageY) - offsetY;
                    flag++;

                    ctx.save();
                    ctx.beginPath();
                    ctx.lineWidth = clipRange;
                    ctx.lineCap = 'round';
                    ctx.moveTo(startX, startY);
                    ctx.lineTo(moveX, moveY);
                    ctx.stroke();
                    ctx.restore();

                    startX = moveX;
                    startY = moveY;
                }
            },
            //手指离开
            eventUp = function(event) {
                var canvas = event.target;

                event.preventDefault();
                isClip = false;
                if (flag > lasting) {
                    document.body.removeChild(canvas);
                    obj.callback && obj.callback();
                }
            },
            init = function(option) {
                var el = option,
                    w = el.offsetWidth, //offsetWidth 为 width + padding + border
                    h = el.offsetHeight,
                    canvas = document.createElement('canvas'),
                    ctx = canvas.getContext('2d'),
                    img = new Image(),
                    position = getPosition(el),
                    x, y, //el 的位置
                    //判断浏览器在PC端或移动端
                    hasTouch = 'ontouchstart' in window ? true : false,
                    tapStart = hasTouch ? "touchstart" : "mousedown",
                    tapMove = hasTouch ? "touchmove" : "mousemove",
                    tapEnd = hasTouch ? "touchend" : "mouseup";

                x = position.x;
                y = position.y;
                canvas.width = w;
                canvas.height = h;
                canvas.style.cssText = 'position: absolute; left: ' + x + 'px; top: ' + y + 'px; z-index: 999;';

                ctx.fillStyle = '#e8e8e8';
                ctx.fillRect(0, 0, w, h);
                document.body.appendChild(canvas);
                el.style.visibility = 'visible';
                ctx.globalCompositeOperation = "destination-out"; //抹去模糊图片
                canvas.addEventListener(tapStart, eventDown);
                canvas.addEventListener(tapMove, eventMove);
                canvas.addEventListener(tapEnd, eventUp);

                //窗口大小变化时， 模糊图片位置改变
                window.onresize = function() {
                    position = getPosition(el);
                    canvas.style.left = position.x + 'px';
                    canvas.style.top = position.y + 'px';
                };
            };

        init(el);
    };

    function getJSON(opts) {
        var type = opts.type || 'GET',
            data = opts.data || {};

        $.ajax({
            type: type,
            dataType: 'json',
            url: opts.url,
            data: data,
            success: function(json) {
                if (typeof json === 'object') {
                    opts.callback && opts.callback(json);
                }
            }
        });
    }

    $(function() {
        // 提交信息
        function submitInfo(title, lotteryId, type, itemId) {
            var txt = '',
                isLock = false;

            // 提交姓名
            if ((type & 0x0100) == 0x0100) {
                txt += '<input type="text" class="txt" id="user_name" placeholder="请输入姓名">';
            }

            // 手机
            if ((type & 0x0200) == 0x0200) {
                txt += '<input type="tel" class="txt" id="phone" placeholder="请输入电话">'
            }

            // 地址
            if ((type & 0x0400) == 0x0400) {
                txt += '<input type="text" class="txt" id="address" placeholder="请输入地址">'
            }

            // qq
            if ((type & 0x0800) == 0x0800) {
                txt += '<input type="tel" class="txt" id="qq" placeholder="请输入QQ">';
            }

            if (txt) {
                msgConfirm({
                    className: 'scratch-confirm',
                    title: title,
                    txt: txt,
                    btnTxt: '提交',
                    callback: function($el) {
                        if (isLock) {
                            return;
                        }

                        var obj = {
                                lottery_id: lotteryId,
                                item_id: itemId
                            },
                            flag = false;

                        $el.find('.txt').each(function() {
                            if ($(this).val().length === 0) {
                                $(this).css('border-color', '#ee4444');
                                flag = false;
                                return;
                            } else {
                                $(this).css('border-color', '#e1e1e1');
                                obj[this.id] = $(this).val();
                                flag = true;
                            }
                        });

                        if (flag) {
                            isLock = true;
                            getJSON({
                                url: '/games/lottery/contact/1',
                                data: obj,
                                type: 'POST',
                                callback: function(json) {
                                    if (json.status == 0) {
                                        msg(json.msg, 'success', function() {
                                            location.href = location.pathname + '?time=' + ((new Date()).getTime());
                                        });
                                    } else {
                                        msg(json.msg, 'error', function() {
                                            location.href = location.pathname + '?time=' + ((new Date()).getTime());
                                        });
                                    }
                                }
                            });
                        }
                    },
                    closeCallback: function() {
                        location.href = location.pathname + '?time=' + ((new Date()).getTime());
                    }
                })
            } else {
                msgAlert({
                    txt: title,
                    callback: function() {
                        location.href = location.pathname + '?time=' + ((new Date()).getTime());
                    }
                })
            }
        }

        // 抽奖次数
        getJSON({
            url: '/games/lottery/check/1',
            callback: function(json) {
                if (json.status == 0) {
                    $('#num').html(json.data)
                } else if (json.status == 1) {
                    // 未登录
                    msg(json.msg, 'error', function() {
                        location.href = 'http://www.adaixiong.com/login?redirect=' + location.href;
                    });
                }
            }
        });

        // 刮奖
        $('.cover').on('click', function() {
            var that = this,
                lotteryId;

            if (typeof(_hmt) != "undefined") {
                _hmt.push(["_trackEvent", '刮刮卡 点击刮奖', "click"]);
            }
            
            getJSON({
                url: '/games/lottery/get/1',
                callback: function(json) {
                    if (json.status == 0) {
                        var data = json.data;

                        $('#scratch_box').html(data.name);
                        $(that).hide();

                        lotteryId = data.id;

                        shine({
                            el: document.getElementById('scratch_box'),
                            callback: function() {
                                submitInfo(data.message, lotteryId, data.type, data.item_id);
                            }
                        });
                    } else if (json.status == 1) {
                        // 未登录
                        msg(json.msg, 'error', function() {
                            location.href = 'http://www.adaixiong.com/login?redirect=' + location.href;
                        });
                    } else if (json.status == 2) {
                        // 没有抽奖次数
                        msgAlert({
                            txt: json.msg,
                            btnTxt: '去充值获取刮刮卡',
                            callback: function() {
                                location.href = 'http://www.adaixiong.com/login?redirect=user&tab=recharge';
                            }
                        })
                    }
                }
            });
        });

        // 我的中奖记录
        var isLoadList = false;
        $('#my_scratch').on('click', function() {
            if (!isLoadList) {
                getJSON({
                    url: 'http://www.adaixiong.com/games/lottery/my/1',
                    callback: function(json) {
                        if (json.status == 0) {
                            isLoadList = true;
                            var $el = $('#scratch_list'),
                                data = json.data,
                                str = '',
                                i, len;

                            for (i = 0, len = data.length; i < len; i++) {
                                str += ['<div class="item">',
                                    data[i].contact === 0 ? '<a href="javascript:;" class="font-red r set-info" _title="' + data[i].lottery.name + '" _lottery="' + data[i].lottery_id + '" _type="' + data[i].lottery.type + '" _id="' + data[i].id + '">填写信息</a>' : '',
                                    data[i].lottery.name,
                                    '</div>'
                                ].join('');
                            }

                            if (len == 0) {
                                str = '您暂时还没有中奖哦~';
                            }

                            $el.find('.bd').html(str);
                            $el.show();
                        }
                    }
                });
            } else {
                $('#scratch_list').toggle()
            }
        });

        $('body').on('click', '.set-info', function() {
            submitInfo($(this).attr('_title'), $(this).attr('_lottery'), $(this).attr('_type'), $(this).attr('_id'))
        })
    })
})(window, Zepto);