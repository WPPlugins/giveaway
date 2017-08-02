<?php
@include_once dirname(__FILE__) . '/controls.php';

$plugin = $giveaway;

// The comments (cleaned of duplicates) of the selected post (if one).
function giveaway_get_comments($post_id) {
    global $wpdb;

    // get the number of direct replies too
    //$comments = $wpdb->get_results("select comment_id id, lower(comment_author_email) email, comment_author name, comment_post_id post_id, coalesce(wc.replies, 0) replies from " . $wpdb->prefix . "comments
    //    left outer join (select comment_parent cp, count(*) replies from " . $wpdb->prefix . "comments where comment_post_id=" . $post_id . " and comment_type='' group by comment_parent) wc
    //    on comment_id=wc.cp where comment_parent=0 and comment_post_id=" . $post_id . " and comment_type='' and comment_author_email<>''");

    $comments = $wpdb->get_results("select comment_id id, lower(comment_author_email) email, comment_author name, comment_post_id post_id from " . $wpdb->prefix .
        "comments where comment_parent=0 and comment_post_id=" . $post_id . " and comment_type='' and comment_author_email<>''");
    if (empty($comments)) return null;
    $results = array();
    $duplicates = array();
    foreach($comments as $comment) {
        // Cleans email like xxx+yyy@aaa.bbb to xxx@aaa.bbb
        // TODO
        if (isset($duplicates[$comment->email])) continue;
        $duplicates[$comment->email] = 1;
        $results[] = $comment;
    }

    return $results;
}

// If there is no action requested...
if (!$controls->is_action()) {
    // Nothing
}
else {
    // Process known actions. Some actions need to process the options, do something
    // without storing them on the database but showing them to the user a second time.
    if ($controls->is_action('save')) {
        $plugin->set_options(stripslashes_deep($_POST['options']));
    }

    if ($controls->is_action('reset')) {
        $plugin->set_options($plugin->get_default_options());
    }

    if ($controls->is_action('extract')) {
        $plugin->set_options(stripslashes_deep($_POST['options']));
        $comments = giveaway_get_comments($plugin->get_option('post'));
        $idx = rand(1, count($comments));
        $controls->errors = 'The winner is: ' . $idx . ' ' . $comments[$idx-1]->name . '.';
    }

}

if ($controls->options == null) $controls->options = $plugin->get_options();

?>
<style type="text/css">
.form-table {
    background-color: #fff;
}
.form-table th {
    text-align: right;
    font-weight: bold;
}
p.submit {
    margin-top: 5px;
    padding: 5px;
}
.hints {
    border: 1px solid #aaf;
    background-color: #fafaff;
    padding: 5px;
    margin-top: 10px;
    border-bottom-left-radius: 4px 4px;
    border-bottom-right-radius: 4px 4px;
    border-top-left-radius: 4px 4px;
    border-top-right-radius: 4px 4px;
}
.widefat th {
    text-align: left;
}
</style>

<div class="wrap metabox-holder">
    <h2><?php echo $plugin->title; ?></h2>

    <?php if (!empty($controls->errors)) { ?>
    <div class="updated">
        <p><?php echo $controls->errors; ?></p>
    </div>
    <?php } ?>

    <p>
        Giveaways managed by this plugin is when you create a post and ask the readers to comment on it (may be with a specific theme).
        When the giveaway ends, you close the comment and extract randomly a subscriber. Giveaways posts need to be tagged to be recognized
        usually with "giveaway" word, but you can change it below.
    </p>

    <form method="post" action="">
        <?php $controls->init(); ?>

        <div class="postbox">
        <h3 class="hndle"><span>Configuration</span></h3>

        <table class="form-table">
            <tr valign="top">
                <th>Current giveaway post</th>
                <td>
                    <?php
                    $posts = get_posts(array('tag'=>$controls->options['tag']));
                    $opts = array(0=>'Select a giveaway post to work on');
                    foreach($posts as $post) {
                        $opts[$post->ID] = $post->post_title;
                    }
                    ?>
                    <?php $controls->select('post', $opts); ?>
                    <?php $controls->button('save', 'Select'); ?>
                    <?php $controls->button('extract', 'Extract a winner'); ?>

                </td>
            </tr>
        </table>
        </div>

        <div class="postbox">
        <h3 class="hndle"><span>Participants</span></h3>
        <table class="form-table">
            <tr valign="top">
                <th>Participants</th>
                <td>
                    <?php
                    $comments = giveaway_get_comments($controls->options['post']);
                    if (!empty($comments)) {
                    ?>

                        <div style="height: 400px; overflow: auto; border: 1px solid #999; padding: 10px;" id="giveaway_pro_table">
                        <table class="widefat" >
                            <thead><tr><th>Number</th><th>Name</th><th>Email</th><th>Comment ID</th></tr></thead>
                        <?php
                        $i = 0;
                        foreach($comments as $comment) {
                            $i++;
                        ?>
                        <tr><td><?php echo $i; ?></td><td><?php echo $comment->name; ?></td><td><?php echo $comment->email; ?></td><td><?php echo $comment->id; ?></td></tr>
                        <?php
                        }
                        ?>
                        </table>
                        </div>

                    <?php } else { ?>

                        Select a giveaway post above (or wait for some participants)

                    <?php } ?>
                </td>
            </tr>

           
        </table>
        </div>

        <div class="postbox">
        <h3 class="hndle"><span>Common options</span></h3>

        <table class="form-table">

            <tr valign="top">
                <th>Tag that identify giveaway posts</th>
                <td>
                    <?php $controls->text('tag'); ?>
                </td>
            </tr>
        </table>
        <p class="submit">
            <?php $controls->button('save', 'Save'); ?>
        </p>
        </div>
    </form>

</div>