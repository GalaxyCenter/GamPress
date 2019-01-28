<div class="content" id="box_user_book">
    <div id="list_user_book" class="uc-box">
        <div class="pic-list list">
            <a :href="'import?id=' + item.id" class="item" v-for="(item, index) in books">
                <img :src="item.cover"  class="cover"/>
                <p>{{item.title}}</p>
            </a>
        </div>

        <a class="loading" href="javascript:;">{{loading_txt}}</a>
    </div>
</div>
<script>
    var app = new Vue({
        el: '#box_user_book',
        data: {
            books:[],
            loading_txt:'正在加载中...',
        },
        created() {
            this.getMessages();
        },
        methods: {
            getMessages() {
                var params = new FormData();
                params.append('action', 'get_books');
                params.append('author_id', <?php echo gp_loggedin_user_id()?>);

                axios.post('/wp-admin/admin-ajax.php', params).then((res) => {
                    this.books = res.data.data.items;
                    console.info(this.books);
                this.loading_txt = '没有更多内容了';
            });
            }
        },
    });
</script>