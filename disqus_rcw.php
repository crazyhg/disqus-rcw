<?php
/*
 * Plugin Name: Disqus Recent Comments (async)
 * Description: Widget to show recent comments from Disqus API.
 * Author: hg
 * Version: 1.0.0
 */

class Disqus_RCW extends WP_Widget
{
    public function __construct()
    {
        $widget_ops =
        parent::__construct(
            'disqus-rcw',
            'Disqus Recent Comments (async)',
            array('classname' => 'dsq-widget', 'description' => 'Recent comments from Disqus')
        );

        add_action('wp_ajax_nopriv_load_recent_comments', array($this, 'load_recent_comments_callback'));
        add_action('wp_ajax_load_recent_comments', array($this, 'load_recent_comments_callback'));
        wp_enqueue_style('disqus-rcw', plugins_url('disqus_rcw.css', __FILE__));
    }

    public function widget($args, $instance)
    {
        set_default_values($instance);

        if ($instance['livestamp_timeout'] > 0) {
            wp_register_script('moment.js', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js', array(), '2.11.2');
            wp_register_script('moment.js-it', '//cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/locale/it.js', array('moment.js'), '2.11.2');
            wp_register_script('livestamp', '//cdnjs.cloudflare.com/ajax/libs/livestamp/1.1.2/livestamp.min.js', array('jquery', 'moment.js-it'), '1.1.2');
            wp_enqueue_script('livestamp');
        }

        echo $args['before_widget'];

        echo $args['before_title'];
        echo esc_html($instance['title']);
        echo $args['after_title'];

        echo '<ul class="disqus-rcw-list dsq-widget-list" data-widget-idbase="'.$this->id_base.'" data-widget-number="'.$this->number.'">';

        if ($instance['ajax_enabled']) {
            wp_enqueue_script('disqus-rcw', plugins_url('disqus_rcw.js', __FILE__), array('jquery'));
            wp_localize_script('disqus-rcw', 'localizedData', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'id_base' => $this->id_base,
                'livestamp_timeout' => $instance['livestamp_timeout'],
            ));
        } else {
            $this->output_widget_content($instance);
        }

        echo '</ul>';

        echo $args['after_widget'];
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;

        $instance['api_key'] = strip_tags($new_instance['api_key']);
        $instance['forum_name'] = strip_tags($new_instance['forum_name']);
        $instance['comment_limit'] = (int) $new_instance['comment_limit'];
        $instance['comment_length'] = (int) $new_instance['comment_length'];
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['request_comment_limit'] = (int) $new_instance['request_comment_limit'];
        $instance['comments_per_thread'] = (int) $new_instance['comments_per_thread'];
        $instance['cache_timeout'] = (int) $new_instance['cache_timeout'];
        $instance['livestamp_timeout'] = (int) $new_instance['livestamp_timeout'];
        $instance['ajax_enabled'] = (bool) $new_instance['ajax_enabled'];

        return $instance;
    }

    public function form($instance)
    {
        set_default_values($instance);
        extract($instance);

        ?>

        <p>
        <label for="<?= $this->get_field_id('title') ?>"><?='Title' ?></label>
        <input id="<?= $this->get_field_id('title') ?>"
               name="<?= $this->get_field_name('title') ?>"
               value="<?= esc_attr($title) ?>"
               type="text" class="widefat" />
        </p>

        <p>
        <label for="<?= $this->get_field_id('comment_limit') ?>"><?= 'Comments shown in the widget' ?></label>
        <input id="<?= $this->get_field_id('comment_limit') ?>"
               name="<?= $this->get_field_name('comment_limit') ?>"
               value="<?= esc_attr($comment_limit) ?>"
               type="number" min="1" max="100" class="widefat"/>
        </p>

        <p>
        <label for="<?= $this->get_field_id('comment_length') ?>"><?= 'Maximum comment length <small>0 to show the entire comment</small>' ?></label>
        <input id="<?= $this->get_field_id('comment_length') ?>"
               name="<?= $this->get_field_name('comment_length') ?>"
               value="<?= esc_attr($comment_length) ?>"
               type="number" min="1" class="widefat" />
        </p>
        <p>
        <label for="<?= $this->get_field_id('comments_per_thread') ?>"><?= 'Comments from the same thread <small>0 to disable the restriction</small>' ?></label>
        <input id="<?= $this->get_field_id('comments_per_thread') ?>"
               name="<?= $this->get_field_name('comments_per_thread') ?>"
               value="<?= esc_attr($comments_per_thread) ?>"
               type="number" min="0" class="widefat" />
        </p>

        <p>
        <label for="<?= $this->get_field_id('cache_timeout') ?>"><?= 'Cache timeout <small>in seconds, 0 to disable caching</small>' ?></label>
        <input id="<?= $this->get_field_id('cache_timeout') ?>"
               name="<?= $this->get_field_name('cache_timeout') ?>"
               value="<?= esc_attr($cache_timeout) ?>"
               type="number" min="0" class="widefat" />
        </p>

        <p>
        <label for="<?= $this->get_field_id('livestamp_timeout') ?>"><?= 'Livestamp timeout <small>in seconds, 0 to disable livestamp</small>' ?></label>
        <input id="<?= $this->get_field_id('livestamp_timeout') ?>"
               name="<?= $this->get_field_name('livestamp_timeout') ?>"
               value="<?= esc_attr($livestamp_timeout) ?>"
               type="number" min="0" class="widefat" />
        </p>

        <p>
        <input id="<?= $this->get_field_id('ajax_enabled') ?>"
               name="<?= $this->get_field_name('ajax_enabled') ?>"
                <?php checked($instance[ 'ajax_enabled' ]) ?>
               value="true" type="checkbox" />
        <label for="<?= $this->get_field_id('ajax_enabled') ?>"><?= 'Asyncronous loading' ?></label>
        </p>
        
        <p>
        <label for="<?= $this->get_field_id('api_key') ?>"><?='Api key:' ?></label>
        <input id="<?= $this->get_field_id('api_key') ?>"
               name="<?= $this->get_field_name('api_key') ?>"
               value="<?= esc_attr($api_key) ?>"
               type="text" class="widefat" />
        </p>
        <p>
        <label for="<?= $this->get_field_id('forum_name') ?>"><?='Forum name:' ?></label>
        <input id="<?= $this->get_field_id('forum_name') ?>"
               name="<?= $this->get_field_name('forum_name') ?>"
               value="<?= esc_attr($forum_name) ?>"
               type="text" class="widefat" />
        </p>
        <p>
        <label for="<?= $this->get_field_id('request_comment_limit') ?>"><?= 'Comments retrieved from disqus <small>[1, 100]</small>' ?></label>
        <input id="<?= $this->get_field_id('request_comment_limit') ?>"
               name="<?= $this->get_field_name('request_comment_limit') ?>"
               value="<?= esc_attr($request_comment_limit) ?>"
               type="number" min="1" max="100" class="widefat" />
        </p>

    <?php

    }

    protected function output_widget_content($instance)
    {
        $disqus_params = array(
            'api_key' => $instance['api_key'],
            'forum' => $instance['forum_name'],
            'limit' => $instance['request_comment_limit'],
            'related' => 'thread',
            'include' => 'approved',
        );
        $comments = get_last_comments(
            $disqus_params,
            $instance['comments_per_thread'],
            $instance['comment_limit'],
            $instance['comment_length'],
            $instance['cache_timeout'],
            $this->number
        );

        foreach ($comments as $comment) {
            output_comment($comment);
        }
    }

    public function load_recent_comments_callback()
    {
        $number = intval($_POST['number']);

        $widgets = get_option('widget_'.$this->id_base);

        if ($widgets !== false) {
            $instance = $widgets[$number];
            $this->output_widget_content($instance);
        }

        wp_die();
    }
}

function get_last_comments($disqus_params, $comments_per_thread, $comment_limit, $comment_length, $cache_timeout, $widget_id)
{
    if ($cache_timeout > 1) {
        $response = get_transient('disqus_rcw_cache_'.$widget_id);
        if ($response !== false) {
            $response = maybe_unserialize($response);

            return $response;
        }
    }

    $last_comments = array();
    $thread_counters = array();

    $response = query_disqus_api($disqus_params);
    if (!is_array($response)) {
        echo $response;

        return array();
    }

    foreach ($response as $comment) {
        if ($comments_per_thread > 0) {
            $thread_id = $comment['thread']['id'];
            if (isset($thread_counters[$thread_id])) {
                if ($thread_counters[$thread_id] >= $comments_per_thread) {
                    continue;
                } else {
                    $thread_counters[$thread_id] += 1;
                }
            } else {
                $thread_counters[$thread_id] = 1;
            }
        }

        $timestamp = strtotime($comment['createdAt']);

        $newcomment = array(
            'author_name' => $comment['author']['name'],
            'author_url' => $comment['author']['profileUrl'],
            'author_avatar' => $comment['author']['avatar']['large']['permalink'],
            'thread_name' => $comment['thread']['title'],
            'thread_url' => $comment['thread']['link'],
            'message' => truncate($comment['raw_message'], $comment_length),
            'timestamp' => $timestamp,
            'time' => date('H:i', $timestamp),
            'datetime' => date('d/m/Y H:i:s', $timestamp),
            'comment_url' => $comment['thread']['link'].'#comment-'.$comment['id'],
        );

        $last_comments[] = $newcomment;

        ++$comment_counter;
        if ($comment_counter === $comment_limit) {
            break;
        }
    }

    if ($cache_timeout > 1) {
        set_transient('disqus_rcw_cache_'.$widget_id, serialize($last_comments), apply_filters('disqus_rcw_cache_time', $cache_timeout));
    }

    return $last_comments;
}

function query_disqus_api($disqus_params)
{
    $url = add_query_arg($disqus_params, 'http://disqus.com/api/3.0/posts/list.json');

    $response = wp_remote_get($url);

    if (is_wp_error($response) || !isset($response['body'])) {
        return $response;
    }

    $body_array = json_decode($response['body'], true);

    if (!isset($body_array['response'])) {
        return 'No response field in JSON';
    }

    return $body_array['response'];
}

function output_comment($comment)
{
    extract($comment);

    echo '<li class="dsq-widget-item">';
    echo '<a href="'.esc_url($author_url).'">';
    echo '<img class="dsq-widget-avatar" src="'.esc_url($author_avatar).'">';
    echo '</a>';
    echo '<a class="dsq-widget-user" href="'.esc_url($author_url).'">'.esc_html($author_name).'</a> ';
    echo '<span class="dsq-widget-comment"><p>'.esc_html($message).'</p></span>';
    echo '<p class="dsq-widget-meta">';
    echo '<a href="'.esc_url($thread_url).'">'.esc_html($thread_name).'</a> ·&nbsp;';
    echo '<a href="'.esc_url($comment_url).'" data-livestamp="'.esc_attr($timestamp).'" title="'.esc_attr($datetime).'">'.esc_html($time).'</a>';
    echo '</p>';
    echo '</li>';
}

function set_default_values(&$instance)
{
    $defaults = array(
        'title' => 'Recent comments',
        'comment_limit' => 10,
        'comment_length' => 300,
        'comments_per_thread' => 1,
        'cache_timeout' => 30,
        'livestamp_timeout' => 30,
        'ajax_enabled' => true,
        'api_key' => '',
        'forum_name' => '',
        'request_comment_limit' => 100,
    );

    foreach ($defaults as $field => $value) {
        if (!isset($instance[$field])) {
            $instance[$field] = $value;
        }
    }
}

function truncate($string, $length = 100, $append = '...')
{
    if ($length > 0 && strlen($string) > $length) {
        $string = substr($string, 0, $length).$append;
    }

    return $string;
}

/**
 * time_elapsed_string().
 *
 * Zachary Johnson
 * http://www.zachstronaut.com/posts/2009/01/20/php-relative-date-time-string.html
 */
function time_elapsed_string($timestring)
{
    if (($timestamp = strtotime($timestring)) === -1) {
        return '';
    }

    $etime = time() - $timestamp;

    if ($etime < 1) {
        return 'adesso';
    }

    $a = array(12 * 30 * 24 * 60 * 60 => array('anno', 'anni'),
                30 * 24 * 60 * 60 => array('mese', 'mesi'),
                24 * 60 * 60 => array('giorno', 'giorni'),
                60 * 60 => array('ora', 'ore'),
                60 => array('minuto', 'minuti'),
                1 => array('secondo', 'secondi'),
                );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);

            return $r.' '.($r > 1 ? $str[1] : $str[0]).' fa';
        }
    }
}

add_action('widgets_init', function () {
    register_widget('Disqus_RCW');
});

?>
