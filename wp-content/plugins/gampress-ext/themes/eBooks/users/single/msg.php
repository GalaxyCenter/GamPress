<script src="<?php echo get_template_directory_uri(); ?>/dist/js/vue/vue.min.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/dist/js/vue/plugins.min.js"></script>
<div class="content" id="app">
    <div class="item-list" id="list_notifications">
        <div class="item" v-for="(item, index) in msgs">
            <div class="msg-text" :class="item.read ? 'track' : ''" @click="read(item.thread_id, item.read, index)" v-html="item.message"></div>
            <p>{{item.date_sent}}</p>
        </div>
        <a class="loading" href="javascript:;">{{loading_txt}}</a>

    </div>
</div>
<script>
    var app = new Vue({
        el: '#app',
        data: {
            msgs:[],
            loading_txt:'正在加载中...',
        },
        created() {
            this.getMessages();
        },
        methods: {
            getMessages() {
                axios.get('/wp-admin/admin-ajax.php?action=get_messages&page_index=1').then((res) => {
                    this.msgs = res.data.data;
                    this.loading_txt = '没有更多内容了';
                });
            },
            read(threadId, isRead, index) {
                if(!isRead){
                    axios.get('/wp-admin/admin-ajax.php?action=mark_thread_read&thread_id=' + threadId).then((res) => {
                        this.msgs[index].read = true;
                    });
                }
            }
        },
    });
</script>