<?php

class Model_SQ_Frontend {

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var array */
    private $keywords;

    /** @var integer */
    private $min_title_length = 10;

    /** @var integer */
    private $max_title_length = 100;

    /** @var integer */
    private $max_description_length = 170;

    /** @var integer */
    private $min_description_length = 70;

    /** @var integer */
    private $max_keywrods = 4;

    /** @var object Current post */
    private $post;
    private $post_type;

    /** @var array Meta custom content */
    private $meta = array();

    public function __construct() {
        SQ_ObjController::getController('SQ_Tools', false);
        $this->post_type = array('post', 'page', 'movie', 'product', 'download', 'shopp_page_shopp-products');
    }

    /** @var meta from other plugins */
    // private $op_meta = array();
    /**
     * Write the signature
     * @return string
     */
    public function setStart() {
        return "\n\n<!-- Squirrly Wordpress SEO Plugin " . SQ_VERSION . ", visit: http://www.squirrly.co/ -->\n";
    }

    /**
     * End the signature
     * @return string
     */
    public function setEnd() {
        return "<!-- /Squirrly Wordpress SEO Plugin -->\n\n";
    }

    /*     * *****USE BUFFER****** */

    /**
     * Start the buffer record
     * @return type
     */
    public function startBuffer() {
        ob_start(array($this, 'getBuffer'));
    }

    /**
     * Get the loaded buffer and change it
     *
     * @param buffer $buffer
     * @return buffer
     */
    public function getBuffer($buffer) {
        $buffer = $this->setMetaInBuffer($buffer);
        return $buffer;
    }

    /**
     * Flush the header from wordpress
     *
     * @return string
     *
     */
    public function flushHeader() {
        $buffers = array();
        if (function_exists('ob_list_handlers')) {
            $buffers = @ob_list_handlers();
        }

        if (sizeof($buffers) > 0 && strtolower($buffers[sizeof($buffers) - 1]) == strtolower('Model_SQ_Frontend::getBuffer')) {
            @ob_end_flush();
        }
    }

    /**
     * Change the title, description and keywords in site's buffer
     *
     * @return string
     */
    private function setMetaInBuffer($buffer) {
        global $wp_query, $post;
        if (!isset($wp_query)) {
            return $buffer;
        }

        //get the post from shop if woocommerce is installed
        if (function_exists('is_shop') && is_shop()) {
            $this->post = get_post(woocommerce_get_page_id('shop'));
        } else {
            $this->post = get_post($post->ID);
        }

        if (is_home() || (isset($wp_query->query) && empty($wp_query->query)) || is_single() || is_preview() || is_page() || is_archive() || is_author() || is_category() || is_tag() || is_search() || in_array(get_post_type(), $this->post_type)) {
            preg_match("/<head[^>]*>/i", $buffer, $out);
            if (!empty($out)) {
                $title = $this->getCustomTitle();
                if (isset($title) && !empty($title) && $title <> '') {
                    $buffer = @preg_replace('/<title[^<>]*>([^<>]*)<\/title>/si', sprintf("<title>%s</title>", $title), $buffer, 1, $count);
                    if ($count == 0) { //if no title found
                        $buffer .= sprintf("<title>%s</title>", $title) . "\n";
                    } //add the title
                }


                $description = $this->getCustomDescription();
                if (isset($description) && !empty($description) && $description <> '') {
                    $buffer = @preg_replace('/<meta[^>]*name=\"description\"[^>]*content=[\"|\'][^>]*[\"|\'][^>]*>/si', $description, $buffer, 1, $count);
                }

                $keyword = $this->getCustomKeyword();
                if (isset($keyword) && !empty($keyword) && $keyword <> '') {
                    $buffer = @preg_replace('/<meta[^>]*name=\"keywords"[^>]*content=[\"|\'][^>]*[\"|\'][^>]*>/si', $keyword, $buffer, 1, $count);
                }
            }
        }
        return $buffer;
    }

    /*     * ********************** */

    /**
     * Overwrite the header with the correct parameters
     *
     * @return string
     */
    public function setHeader() {
        global $wp_query, $post;
        $ret = '';

        //get the post from shop if woocommerce is installed
        if (function_exists('is_shop') && is_shop()) {
            $this->post = get_post(woocommerce_get_page_id('shop'));
        } else {
            $this->post = get_post($post->ID);
        }

        $this->meta['blogname'] = get_bloginfo('name');

        if (!function_exists('preg_replace')) {
            return $ret;
        }

        if ($this->isHomePage() || is_single() || is_page() || is_singular() || is_preview() || is_archive() || is_category() || is_author() || is_tag() || is_search() || in_array(get_post_type(), $this->post_type)) {

            /* Meta setting */
            $this->title = $this->clearTitle($this->getCustomTitle());

            //Add description in homepage if is set or add description in other pages if is not home page
            if ((SQ_Tools::$options['sq_auto_description'] == 1 && $this->isHomePage()) || !$this->isHomePage()) {
                $ret .= $this->getCustomDescription() . "\n";
                $ret .= $this->getCustomKeyword() . "\n";
            }

            if (SQ_Tools::$options['sq_auto_canonical'] == 1) {
                $ret .= $this->setCanonical();
                $ret .= $this->setRelPrevNext();
            }

            if (SQ_Tools::$options['sq_auto_sitemap'] == 1) {
                $ret .= $this->getXMLSitemap();
            }
            /* Auto setting */

            if (SQ_Tools::$options['sq_auto_favicon'] == 1) {
                $ret .= $this->getFavicon();
            }

            if (SQ_Tools::$options['sq_auto_meta'] == 1) {
                $ret .= $this->getCopyright();
                $ret .= $this->getGooglePlusMeta();
                $ret .= $this->getLanguage();
                $ret .= $this->getDublinCore();
            }
            if (SQ_Tools::$options['sq_auto_facebook'] == 1) {
                $ret .= $this->getOpenGraph() . "\n";
            }

            if (SQ_Tools::$options['sq_auto_twitter'] == 1) {
                $ret .= $this->getTwitterCard(SQ_Tools::$options) . "\n";
            }
            /* SEO optimizer tool */
            $ret .= $this->getGoogleWT();
            $ret .= $this->getGoogleAnalytics();
            $ret .= $this->getFacebookIns();
            $ret .= $this->getBingWT();
            $ret .= $this->getPinterest();

            $ret .= $this->getAlexaT();

            $ret .= $this->setEnd();
        }
        return $ret;
    }

    private function getTwitterCard($options) {
        $meta = "\n";

        //Title and Description is required
        if ($this->title == '' || $this->description == '') {
            return;
        }

        //if ($options['sq_twitter_creator'] == '' && $options['sq_twitter_site'] == '') return;
        $sq_twitter_creator = $options['sq_twitter_account'];
        $sq_twitter_site = $options['sq_twitter_account'];

        if (!isset($this->thumb_image) || $this->thumb_image == '') {
            $this->thumb_image = $this->getImageFromContent();
        }

        $meta .= '<meta name="twitter:card" content="summary" />' . "\n";

        $meta .= (($sq_twitter_creator <> '') ? sprintf('<meta name="twitter:creator" content="%s" />', $sq_twitter_creator) . "\n" : '');
        $meta .= (($sq_twitter_site <> '') ? sprintf('<meta name="twitter:site" content="%s" />', $sq_twitter_site) . "\n" : '');

        $meta .= sprintf('<meta name="twitter:title" content="%s">', $this->title) . "\n";
        $meta .= (($this->title == $this->description) ? sprintf('<meta name="twitter:description" content="%s">', $this->description . ' | ' . $this->meta['blogname']) . "\n" : '');
        $meta .= ((isset($this->thumb_image) && $this->thumb_image <> '') ? sprintf('<meta name="twitter:image:src" content="%s">', $this->thumb_image) . "\n" : '');
        $meta .= (($this->meta['blogname'] <> '') ? sprintf('<meta name="twitter:domain" content="%s">', $this->meta['blogname']) . "\n" :
                        '');

        return $meta;
    }

    /**
     * Get the Open Graph Protocol
     * @return string
     */
    private function getOpenGraph() {
        $meta = "\n";
        $image = '';
        $ogimage = null;

        $url = $this->getCanonicalUrl();
        if (!isset($this->thumb_image) || $this->thumb_image == '') {
            if (isset($this->post) && isset($this->post->ID) && $ogimage = $this->getAdvancedMeta($this->post->ID, 'ogimage')) {
                $this->thumb_image = $ogimage;
            } else {
                $this->thumb_image = $this->getImageFromContent();
            }
        }

        if (!isset($this->thumb_video) || $this->thumb_video == '') {
            $this->thumb_video = $this->getVideoFromContent();
        }

        if ($image == '' && $url == '') {
            return;
        }
        //GET THE URL
        $meta .= sprintf('<meta property="og:url" content="%s" />', $url) . "\n";
        if (( isset($ogimage) && $this->isHomePage() || !$this->isHomePage()) && (isset($this->thumb_image) && $this->thumb_image <> '')) {
            $meta .= sprintf('<meta property="og:image" content="%s" />', $this->thumb_image) . "\n";
            $meta .= sprintf('<meta property="og:image:width" content="%s" />', '500') . "\n";
        }

        if ((isset($this->thumb_video) && $this->thumb_video <> '')) {
            $meta .= sprintf('<meta property="og:video" content="%s" />', $this->thumb_video) . "\n";
        }

        $meta .= sprintf('<meta property="og:title" content="%s" />', $this->title) . "\n";
        $meta .= sprintf('<meta property="og:description" content="%s" />', $this->description) . "\n";
        $meta .= (($this->meta['blogname'] <> '') ? sprintf('<meta property="og:site_name" content="%s" />', $this->meta['blogname']) . "\n" : '');

        if (is_author()) {
            $author = get_queried_object();

            $meta .= sprintf('<meta property="og:type" content="%s" />', 'profile') . "\n";
            $meta .= sprintf('<meta property="profile:first_name" content="%s" />', get_the_author_meta('first_name', $author->ID)) . "\n";
            $meta .= sprintf('<meta property="profile:last_name" content="%s" />', get_the_author_meta('last_name', $author->ID)) . "\n";
        } elseif (!$this->isHomePage() && (is_single() || is_page())) {
            $meta .= sprintf('<meta property="og:type" content="%s" />', ((isset($this->thumb_video) && $this->thumb_video <> '') ? 'video' : 'article')) . "\n";
            if ((isset($this->thumb_video) && $this->thumb_video <> '')) {

            } else {
                $meta .= sprintf('<meta property="article:published_time" content="%s" />', get_the_time('c', $this->post->ID)) . "\n";
                if ($this->keywords <> '') {
                    $keywords = preg_split('/[,]+/', $this->keywords);
                    if (is_array($keywords) && !empty($keywords)) {
                        foreach ($keywords as $keyword) {
                            $meta .= sprintf('<meta property="article:tag" content="%s" />', $keyword) . "\n";
                        }
                    }
                }
            }
        } else {
            $meta .= sprintf('<meta property="og:type" content="%s" />', 'website') . "\n";
        }

        return $meta;
    }

    /**
     * Get the canonical link for the current page
     *
     * @return string
     */
    private function setCanonical() {
        $url = $this->getCanonicalUrl();
        if ($url) {
            remove_action('wp_head', 'rel_canonical');

            return sprintf("<link rel=\"canonical\" href=\"%s\" />", $url) . "\n";
        }

        return '';
    }

    public function setRelPrevNext() {
        global $paged;
        $meta = "";
        if (!$this->isHomePage()) {
            if (get_previous_posts_link()) {
                $meta .= sprintf('<link rel="prev" href="%s" />', get_pagenum_link($paged - 1)) . "\n";
            }
            if (get_next_posts_link()) {
                $meta .= sprintf('<link rel="next" href="%s" />', get_pagenum_link($paged + 1)) . "\n";
            }
        }

        return (($meta <> '') ?
                        $meta . "\n" : '');
    }

    /**
     * Get the correct title of the article
     *
     * @return string
     */
    public function getCustomTitle() {
        $title = '';
        $sep = '|';
        //If its a home page and home page auto title is activated
        if ($this->isHomePage() && SQ_Tools::$options['sq_auto_title'] == 1) { //for homepage
            $title = $this->clearTitle($this->grabTitleFromPost());
            if ($title <> "" && $this->meta['blogname'] <> '') {
                $title .= " " . $sep . " " . $this->meta ['blogname'];
            }
        }
        //If its a post/page
        if (!$this->isHomePage() && (is_single() || is_page() || is_singular() || in_array(get_post_type(), $this->post_type))) {
            $title = $this->clearTitle($this->grabTitleFromPost($this->post->ID));
        }

        //If is category
        if (is_category()) { //for category
            $category = get_category(get_query_var('cat'), false);
            $title = SQ_Tools::i18n($category->cat_name);
            if ($title == '') {
                $title = $this->clearTitle($this->grabTitleFromPost());
            }
            if (is_paged()) {
                $title .= " " . $sep . " " . __('Page', _SQ_PLUGIN_NAME_) . " " . get_query_var('paged');
            }
        }

        if (is_author()) { //for author
            $author = SQ_Tools::i18n(get_the_author_meta('nicename', get_query_var('author')));

            if ($title == '') {
                $title = $this->clearTitle($this->grabTitleFromPost()) . " " . $sep . " " . ucfirst($author);
            }
            if ($title == '') {
                $title = __('About') . " " . ucfirst($author);
            }
            if (is_paged()) {
                $title .= " " . $sep . " " . __('Page', _SQ_PLUGIN_NAME_) . " " . get_query_var('paged');
            }
        }

        if (is_tag()) { //for tags
            if (is_paged()) {
                $tag = get_query_var('tag');
                $title = ucfirst(str_replace('-', ' ', SQ_Tools::i18n($tag))) . " " . $sep . " " . __('Page', _SQ_PLUGIN_NAME_) . " " . get_query_var('paged');
            }
        }

        //If title then clear it

        if ($title <> '') {
            $title = $this->truncate($this->clearTitle($title), $this->min_title_length, $this->max_title_length);
        }

        /* Check if is a predefined Title */
        if ($this->isHomePage() &&
                SQ_Tools::$options ['sq_auto_title'] == 1 &&
                SQ_Tools:: $options['sq_fp_title'] <> '') {

            if (isset($this->post) && isset($this->post->ID) && $this->getAdvancedMeta($this->post->ID, 'title') <> '') {
                $title = SQ_Tools::i18n($this->getAdvancedMeta($this->post->ID, 'title'));
            } else {
                $title = $this->clearTitle(SQ_Tools::$options['sq_fp_title']);
            }
        }

        return

                $title;
    }

    /**
     * Get the image from content
     * @global type $wp_query
     * @return type
     */
    public function getImageFromContent() {
        global $wp_query;
        $match = array();
        $post = $this->post;

        if (!$post) {
            if (!empty($wp_query->posts))
                foreach ($wp_query->posts as $post) {
                    if (isset($post->ID) && get_post_status($post->ID) == 'publish') {
                        $post = get_post($post->ID);
                        break;
                    }
                }
        }

        if ($post && isset($post->ID)) {
            if (has_post_thumbnail($post->ID)) {
                $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'large');
                if (is_array($image) && !empty($image)) {
                    return $image[0];
                }
            } elseif (isset($post->post_content)) {
                preg_match('/<img[^>]*src="([^"]*)"[^>]*>/i', $post->post_content, $match);
                if (empty($match)) {
                    return;
                }

                if (strpos($match[1], '//') === false) {
                    $match[1] = get_bloginfo('url') . $match[1];
                }

                return $match[1];
            }
        }
    }

    /**
     * Get the video from content
     * @global type $wp_query
     * @return type
     */
    public function getVideoFromContent() {
        if (!$this->post) {
            return false;
        }

        if (isset($this->post->post_content)) {
            preg_match('/(https?:)?\/\/(?:[0-9A-Z-]+\.)?(?:(youtube|youtu)(?:-nocookie)?\.(com|be)\/(?:[^"\']+))/si', $this->post->post_content, $match);

            if (isset($match[0])) {
                if (strpos($match[0], '//') !== false && strpos($match[0], 'http') === false) {
                    $match[0] = 'http:' . $match[0];
                }
            }

            if (empty(
                            $match)) {

            } return;
        } else {
            return;
        }

        return $match[0];
    }

    private function clearTitle($title) {
        $title = str_replace(array('"', "&nbsp;", "  "), array('', ' ', ' '), $title);
        return trim(strip_tags(html_entity_decode(
                                $title)));
    }

    /**
     * Get the description from last/current article
     *
     * @return string
     */
    private function getCustomDescription() {
        $sep = '|';
        $description = '';

        //Is home page, has posts and autodescription is on
        if ($this->isHomePage() && SQ_Tools::$options['sq_auto_description'] == 1) { //for homepage
            $description = $this->grabDescriptionFromPost();
        }

        //If not homepage and its a post/page
        if (!$this->isHomePage() && (is_single() || is_page() || is_singular() || $this->checkPostsPage() || in_array(get_post_type(), $this->post_type))) {
            if (isset($this->post) && isset($this->post->ID)) {
                $description = $this->grabDescriptionFromPost($this->post->ID);
            }
        }

        //If is a category
        if (is_category()) { //for categories
            $category = get_category(get_query_var('cat'), false);
            $description = SQ_Tools::i18n($category->category_description);
            if ($description == '') {
                $description = SQ_Tools::i18n($category->cat_name);
            }
            if ($description == '') {
                $description = $this->grabDescriptionFromPost();
            }

            if (is_paged()) {
                $description .= " " . $sep . " " . __('Page', _SQ_PLUGIN_NAME_) . " " . get_query_var('paged');
            }

            if ($this->isHomePage() && $description <> '') {
                if ($this->meta['blogname'] <> '') {
                    $description .= " " . $sep . " " . $this->meta['blogname'];
                }
            }
        }

        //If is the author page
        if (is_author()) { //for author
            $description = SQ_Tools::i18n(get_the_author_meta('description', get_query_var('author')));
            if ($description == '') {
                $author = SQ_Tools::i18n(get_the_author_meta('nicename', get_query_var('author')));
                $description = $this->grabDescriptionFromPost() . " " . $sep . " " . $author;
            }
            if (is_paged()) {
                $description .= " " . $sep . " " . __('Page', _SQ_PLUGIN_NAME_) . " " . get_query_var('paged');
            }
        }

        //If is tag
        if (is_tag()) { //for tags
            $description = SQ_Tools::i18n(tag_description());
            if ($description == '') {
                $tag = SQ_Tools::i18n(single_tag_title('', false));
                $description = ucfirst($tag) . " " . $sep . " " . $this->grabDescriptionFromPost();
            }
            if (is_paged()) {
                $description .= " " . $sep . " " . __('Page', _SQ_PLUGIN_NAME_) . " " . get_query_var('paged');
            }
        }

        /* Check if is a predefined TitleIn Snippet */
        if ($this->isHomePage() &&
                SQ_Tools::$options['sq_auto_description'] == 1 &&
                SQ_Tools::$options ['sq_fp_description'] <> '') {

            if (isset($this->post) && isset($this->post->ID) && $this->getAdvancedMeta($this->post->ID, 'description') <> '') {
                $description = SQ_Tools::i18n($this->getAdvancedMeta($this->post->ID, 'description'));
            } else {
                $description = strip_tags(SQ_Tools::$options['sq_fp_description']);
            }
        }

        $description = (($description <> '') ? $description : $this->title);
        if ($description <> '') {
            $this->description = $this->clearDescription($description);

            if ($this->description <> '') { //prevent blank description
                $description = sprintf("<meta name=\"description\" content=\"%s\" />", $this->description);
            } else {
                $description = '';
            }
        }

        return $description;
    }

    private function clearDescription($description) {
        $description = str_replace(array("&nbsp;", "  ", "\r", "\n"), array(' ', '', '', ' '), $description);
        $search = array("'<script[^>]*?>.*?<\/script>'si", // strip out javascript
            "/<form.*?<\/form>/si",
            "/<iframe.*?<\/iframe>/si");

        if (function_exists('preg_replace')) {
            $description = preg_replace($search, '', $description);
        }

        $description = html_entity_decode($description);
        $description = strip_tags($description);
        $description = str_replace('"', '\'', $description);

        return trim($description);
    }

    /**
     * Get the keywords from articles
     *
     * @return string
     */
    private function getCustomKeyword() {
        global $wp_query;
        $keywords = '';

        if ($this->checkPostsPage() && SQ_Tools::$options['sq_auto_description'] == 1) {
            $keywords = stripcslashes(SQ_Tools::i18n($this->grabKeywordsFromPost($this->post->ID)));
        } elseif (is_single() || is_page()) {
            $keywords = stripcslashes(SQ_Tools::i18n($this->grabKeywordsFromPost($this->post->ID)));
        } elseif (SQ_Tools::$options['sq_auto_description'] == 1) {
            $keywords = trim(SQ_Tools::i18n($this->grabKeywordsFromPost()));
        }

        /* Check if is a predefined Keyword */
        if (SQ_Tools::$options['sq_auto_description'] == 1) { //
            if (($this->isHomePage() &&
                    SQ_Tools::$options['sq_fp_keywords'] <> '')) {
                $keywords = strip_tags(SQ_Tools::$options ['sq_fp_keywords']);
            }
        }

        if (isset($keywords) && !empty($keywords) && !(is_home() && is_paged())) {
            $this->keywords = str_replace('"', '', $keywords);

            return sprintf("<meta name=\"keywords\" content=\"%s\" />", $this->keywords);
        }

        return false;
    }

    /**
     * Get the copyright meta
     *
     * @return string
     */
    private function getCopyright() {
        $name = $this->getAuthorLinkFromBlog();
        if (!$name) {
            $name = $this->meta['blogname'];
        }

        if ($name <> '') {
            return sprintf("<meta name=\"copyright\" content=\"%s\" />" . "\n", $name) . "\n";
        }

        return false;
    }

    /**
     * Get the Google Plus Author meta
     *
     * @return string
     */
    private function getGooglePlusMeta() {
        $author = SQ_Tools::$options['sq_google_plus'];
        if ($author == '') {
            $author = $this->getAuthorLinkFromBlog();
        } elseif (!$author) {
            $author = $this->meta['blogname'];
        } elseif (strpos($author, 'plus.google.com') === false && is_numeric($author)) {
            $author = 'https://plus.google.com/' . $author . '/posts';
        }

        if ($this->isHomePage() && $author <> '') {
            return '<link rel="author" href="' . $author . '" />' . "\n";
        } else {
            if (is_single() && !class_exists('ABH_Classes_ObjController')) {
                return '<link rel="author" href="' . $author . '" />' . "\n";
            }
        }

        return false;
    }

    /**
     * Get the icons for serachengines
     *
     * @return string
     */
    private function getFavicon() {

        $str = '';
        $rnd = '';

        if (function_exists('base64_encode')) {
            if (isset(SQ_Tools::$options['favicon_tmp']) && current_user_can('edit_plugins')) {
                $rnd = '?ver=' . base64_encode(SQ_Tools::$options['favicon_tmp']);
            }
        }

        $favicon = get_bloginfo('wpurl') . '/favicon.ico' . $rnd;

        if (file_exists(ABSPATH . '/favicon.ico')) {
            $str .= sprintf("<link rel=\"shortcut icon\" type=\"image/png\"  href=\"%s\" />" . "\n", $favicon);
            $str .= sprintf("<link rel=\"apple-touch-icon\" type=\"image/png\"  href=\"%s\" />" . "\n", $favicon);
            $str .= sprintf("<link rel=\"apple-touch-icon-precomposed\" type=\"image/png\"  href=\"%s\" />" . "\n", $favicon);
        }
        return $str;
    }

    /**
     * Get the language meta
     *
     * @return string
     */
    private function getLanguage() {
        $meta = '';
        if ($this->isHomePage()) {
            $hreflang = SQ_ObjController::getController('SQ_Ranking', false)->getLanguage();

            if ($hreflang <> '') {
                $url = get_bloginfo('url');
                $meta .= sprintf("<link rel=\"alternate\" hreflang=\"%s\" href=\"$url\" />", $hreflang) . "\n";
            }
        }

        $language = get_bloginfo('language');

        if ($language <> '') {
            $meta .= sprintf("<meta name=\"language\" content=\"%s\" />", $language) . "\n";
        }

        return $meta;
    }

    /**
     * Get the DC.publisher meta
     *
     * @return string
     */
    private function getDublinCore() {
        global $wp_query;
        $date = null;
        $meta = '';

        $name = $this->getAuthorLinkFromBlog();
        if (!$name) {
            $name = $this->meta['blogname'];
        }

        if ($name <> '') {
            $meta .= sprintf("<meta name=\"DC.Publisher\" content=\"%s\" />", $name) . "\n";
        }

        $meta .= sprintf('<meta name="DC.Title" content="%s" />', $this->title) . "\n";
        $meta .= sprintf('<meta name="DC.Description" content="%s" />', $this->description) . "\n";

        if ($this->isHomePage()) {
            $date = date('Y-m-d', strtotime(get_lastpostmodified()));
        } elseif (is_single()) {
            $date = date('Y-m-d', strtotime($this->post->post_date));
        }

        if ($date) {
            $meta .= sprintf("<meta name=\"DC.Date\" content=\"%s\" />", $date) . "\n";

            $meta .= sprintf("<meta name=\"DC.date.issued\" content=\"%s\" />", $date) . "\n";
        }

        return $meta;
    }

    /**
     * Get the XML Sitemap meta
     *
     * @return string
     */
    private function getXMLSitemap() {
        $xml_url = SQ_ObjController::getController('SQ_Sitemap', false)->getXmlUrl();

        if ($xml_url <> '') {
            return sprintf("<link rel=\"alternate\" type=\"application/rss+xml\" " . (($this->title <> '') ? "title=\"%s\"" : "") . " href=\"%s\" />", $this->title, $xml_url) . "\n";
        }

        return false;
    }

    /**
     * Get the google Webmaster Tool code
     *
     * @return string
     */
    private function getGoogleWT() {
        $sq_google_wt = SQ_Tools::$options['sq_google_wt'];

        if (($this->checkHomePosts() || $this->checkFrontPage()) && $sq_google_wt <> '') {
            return sprintf("<meta name=\"google-site-verification\" content=\"%s\" />", $sq_google_wt) . "\n";
        }

        return false;
    }

    /**
     * Get the google Analytics code
     *
     * @return string
     */
    private function getGoogleAnalytics() {
        $sq_google_analytics = SQ_Tools::$options['sq_google_analytics'];

        if ($sq_google_analytics <> '') {
            SQ_ObjController::getController('SQ_DisplayController', false)
                    ->loadMedia('https://www.google-analytics.com/analytics.js');
            return sprintf("
<script>
    //<![CDATA[
   window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
   ga('create', '%s', 'auto');
   ga('send', 'pageview');
    //]]>
</script>", $sq_google_analytics) . "\n";
        }

        return false;
    }

    /**
     * Get the Facebook Insights code
     *
     * @return string
     */
    private function getFacebookIns() {
        $sq_facebook_insights = SQ_Tools::$options ['sq_facebook_insights'];

        if (($this->checkHomePosts() || $this->checkFrontPage()) && $sq_facebook_insights <> '') {
            return sprintf("<meta property=\"fb:admins\" content=\"%s\" />", $sq_facebook_insights) . "\n";
        }

        return false;
    }

    /**
     * Get the Pinterest code
     *
     * @return string
     */
    private function getPinterest() {
        $sq_pinterest = SQ_Tools::$options['sq_pinterest'];

        if (($this->checkHomePosts() || $this->checkFrontPage()) && $sq_pinterest <> '') {
            return sprintf("<meta name=\"p:domain_verify\" content=\"%s\" />", $sq_pinterest) . "\n";
        }

        return false;
    }

    /**
     * Get the Alexa Tool code
     *
     * @return string
     */
    private function getAlexaT() {
        $sq_alexa = SQ_Tools::$options['sq_alexa'];

        if (($this->checkHomePosts() || $this->checkFrontPage()) && $sq_alexa <> '') {
            return sprintf("<meta name=\"alexaVerifyID\" content=\"%s\" />", $sq_alexa) . "\n";
        }

        return false;
    }

    /**
     * Get the bing Webmaster Tool code
     *
     * @return string
     */
    private function getBingWT() {
        $sq_bing_wt = SQ_Tools::$options['sq_bing_wt'];

        if (($this->checkHomePosts() || $this->checkFrontPage()) && $sq_bing_wt <> '') {
            return sprintf("<meta name=\"msvalidate.01\" content=\"%s\" />", $sq_bing_wt) . "\n";
        }

        return false;
    }

    /*     * *******************************************************************
     * ******************************************************************** */

    /**
     * Get the title from the curent/last post
     *
     * @return string
     */
    private function grabTitleFromPost($id = null) {
        global $wp_query;
        $post = null;
        $title = '';
        $advtitle = '';

        if (isset($id)) {
            $post = get_post($id);
        }

        if (!$post) {
            if (!empty($wp_query->posts))
                foreach ($wp_query->posts as $post) {
                    $id = (is_attachment()) ? ($post->post_parent) : ($post->ID);
                    $post = get_post($id);

                    break;
                }
        }

        if ($post) {
            if (!$this->isHomePage())
                $title = SQ_Tools::i18n($post->post_title);

            //If there is title saved in database
            if ($advtitle = $this->getAdvancedMeta($post->ID, 'title')) {
                $title = SQ_Tools::i18n($advtitle);
            } elseif ($advtitle = $this->getOtherPluginsMeta($post->ID, 'title')) {
                $title = SQ_Tools::i18n($advtitle);
            }
        }

        return

                $title;
    }

    /**
     * Get the description from the curent/last post
     *
     * @return string
     */
    public function grabDescriptionFromPost($id = null) {
        global $wp_query;
        $post = null;

        if (isset($id)) {
            $post = get_post($id);
        }

        $description = '';
        $advdescription = '';
        //echo 'post: ' . get_the_ID();
        if (!$post) {
            if (!empty($wp_query->posts))
                foreach ($wp_query->posts as $post) {
                    $id = (is_attachment()) ? ($post->post_parent) : ($post->ID);
                    $post = get_post($id);
                    break;
                }
        }


        if ($post) {
            if (!$this->isHomePage()) {
                $description = $this->_truncate(SQ_Tools::i18n($post->post_excerpt), $this->min_description_length, $this->max_description_length);
                if (!$description) {
                    $description = $this->truncate(SQ_Tools::i18n($post->post_content), $this->min_description_length, $this->max_description_length);
                }
            }

            //If there is description saved in database
            if ($advdescription = $this->getAdvancedMeta($post->ID, 'description')) {
                $description = SQ_Tools::i18n($advdescription);
            } elseif ($advdescription = $this->getOtherPluginsMeta($post->ID, 'description')) {
                $description = SQ_Tools::i18n($advdescription);
            }
        }
        // "internal whitespace trim"

        $description = preg_replace("/\s\s+/u", " ", $description);

        return $description;
    }

    /**
     * Get the keywords from the curent/last post and from density
     *
     * @return array
     */
    private function grabKeywordsFromPost($id = null) {
        global $wp_query;

        $this->max_keywrods = ($this->max_keywrods > 0 ? ($this->max_keywrods - 1) : 0);
        if ($this->max_keywrods == 0) {
            return;
        }

        $keywords = array();
        $advkeywords = '';


        if (isset($id) && $post = get_post($id)) {
            $density = array();

            if (SQ_Tools::$options['sq_keywordtag'] == 1) {
                foreach (wp_get_post_tags($id) as $keyword) {
                    $keywords[] = SQ_Tools::i18n($keyword->name);
                }
            } else {
                if ($json = SQ_ObjController::getModel('SQ_Post')->getKeyword($post->ID)) {
                    if (isset($json->keyword)) {
                        $keywords[] = SQ_Tools::i18n($json->keyword);
                    }
                }
            }

            if (count($keywords) <= $this->max_keywrods) {
                if ($advkeywords = $this->getAdvancedMeta($post->ID, 'keywords')) {
                    $keywords[] = SQ_Tools::i18n($advkeywords);
                } else {

                    $density = $this->calcDensity(strip_tags($post->post_content), $post->post_title, $this->description);
                    if (is_array($density)) {
                        if (is_array($keywords) && is_array($density)) {
                            $keywords = array_merge($keywords, $density);
                        } else {
                            if (is_array($density)) {
                                $keywords = $density;
                            }
                        }
                    }
                }
            }
            if (sizeof($keywords) > $this->max_keywrods) {
                $keywords = array_slice($keywords, 0, $this->max_keywrods);
            }
        } else {
            if (is_404()) {
                return null;
            }

            if (!is_home() && !is_page() && !is_single() && !$this->checkFrontPage() && !$this->checkPostsPage()) {
                return null;
            }

            if (is_home()) {
                if (SQ_Tools::$options['sq_keywordtag'] == 1) {
                    foreach ($wp_query->posts as $post) {
                        foreach (wp_get_post_tags($post->ID) as $keyword) {
                            $keywords[] = SQ_Tools::i18n($keyword->name);
                        }
                    }
                }

                if (count($keywords) <= $this->max_keywrods) {
                    foreach ($wp_query->posts as $post) {
                        $more_keywords = $this->calcDensity(strip_tags($post->post_content), $post->post_title, $this->description);

                        if (is_array($more_keywords) && is_array($more_keywords)) {
                            $keywords = array_merge($keywords, $more_keywords);
                        }
                    }
                } if (sizeof($keywords) > $this->max_keywrods) {
                    $keywords = array_slice($keywords, 0, $this->max_keywrods);
                }
            }
            if (count($keywords) <= $this->max_keywrods) {
                foreach ($wp_query->posts as $post) {
                    $id = (is_attachment()) ? ($post->post_parent) : ($post->ID);

                    if (SQ_Tools::$options['sq_keywordtag'] == 1) {
                        foreach (wp_get_post_tags($id) as $keyword) {
                            $keywords[] = SQ_Tools::i18n($keyword->name);
                        }
                    }
// autometa
                    $autometa = stripcslashes(get_post_meta($id, 'autometa', true));
                    //$autometa = stripcslashes(get_post_meta($post->ID, "autometa", true));
                    if (isset($autometa) && !empty($autometa)) {

                        $autometa_array = explode(' ', $autometa);
                        foreach ($autometa_array as $e) {
                            $keywords[] = SQ_Tools::i18n($e);
                        }
                    }
                }
            }
        }

        //If there are keywords saved in database
        if ($advkeywords = $this->getAdvancedMeta($post->ID, 'keyword')) {
            $keywords[] = SQ_Tools::i18n($advkeywords);
        }

        //If there are keywords in other plugins
        if ($advkeywords = $this->getOtherPluginsMeta($post->ID, 'keyword')) {
            $keywords[] = SQ_Tools::i18n($advkeywords);
        }

        return $this->getUniqueKeywords(
                        $keywords);
    }

    /**
     * Calculate the keyword density from blog content
     *
     * @return array
     */
    private function calcDensity($text, $title = '', $description = '') {
        $keywords = array();
        $text = $title . '. ' . $text;
        if (function_exists('preg_replace')) {
            $text = preg_replace('/[^a-zA-Z0-9-.]/', ' ', $text);
        }

        $title = explode(" ", $title);
        $description = explode(" ", $description);
        $words = explode(" ", strtolower($text));
        $phrases = $this->searchPhrase(strtolower($text));

        $common_words = "a,i,he,she,it,and,me,my,you,the,tags,hash,that,this,they,their";
        $common_words = strtolower($common_words);
        $common_words = explode(",", $common_words);
// Get keywords
        $words_sum = 0;
        foreach ($words as $value) {
            $common = false;
            $value = $this->trimReplace($value);
            if (strlen($value) >= 3) {
                foreach ($common_words as $common_word) {
                    if ($common_word == $value) {
                        $common = true;
                    }
                } if ($common != true) {
                    if (!preg_match("/http/i", $value) && !preg_match("/mailto:/i", $value)) {
                        $keywords[] = SQ_Tools::i18n($value);
                        $words_sum++;
                    }
                }
            }
        }

        $results = $results1 = $results2 = array();
        if (is_array($keywords) && count($keywords) > 0) {
            // Do some maths and write array
            $keywords = array_count_values($keywords);
            arsort($keywords);

            if (sizeof($keywords) > 10) {
                $keywords = array_slice($keywords, 0, 10);
            }

            $phraseId = 0;
            foreach ($keywords as $key => $value) {
                $percent = 100 / $words_sum * $value;
                if ($percent > 1 && in_array($key, $title)) {
                    foreach ($phrases as $phrase => $count) {
                        if (strpos($phrase, $key) !== false) {
                            $results1[] = trim($key);
                            $results2[] = $phrase;
                        }
                    }
                }

                $results = array_merge($results2, $results1);
            }
            if (sizeof($keywords) > 4) {
                $results = array_slice($results, 0, 4);
            }
        }
        // Return array
        return $results;
    }

    public function searchPhrase($text) {
        $words = explode(".", strtolower($text));
        //print_r($words);
        $frequencies = array();
        foreach ($words as $str) {
            $phrases = $this->twoWordPhrases($str);

            foreach ($phrases as $phrase) {
                $key = join(' ', $phrase);
                if (!isset($frequencies[$key])) {
                    $frequencies[$key] = 0;
                }
                $frequencies[$key] ++;
            }
        } arsort($frequencies);
        if (sizeof($frequencies) >
                10) {
            $frequencies = array_slice($frequencies, 0, 10);
        } return $frequencies;
    }

    public function twoWordPhrases($str) {
        $words = preg_split('#\s+#', $str, -1, PREG_SPLIT_NO_EMPTY);

        $phrases = array();
        if (count($words) > 2) {
            foreach (range(0, count($words) - 2) as $offset) {
                $phrases[] = array_slice($words, $offset, 2);
            }
        }
        return $phrases;
    }

    /**
     * Get the newlines out
     *
     * @return string
     */
    private function trimReplace($string) {
        $string = trim($string);
        return (string) str_replace(array("\r", "\r\n",
                    "\n"), '', $string);
    }

    /**
     * Find the correct canonical url
     *
     * @return string
     */
    public function getCanonicalUrl() {
        global $wp_query;

        if (!isset($wp_query) || $wp_query->is_404 || $wp_query->is_search) {
            return false;
        }

        $haspost = count($wp_query->posts) > 0;
        $has_ut = function_exists('user_trailingslashit');

        if (get_query_var('m') <> '') {
            $m = preg_replace('/[^0-9]/', '', get_query_var('m'));
            switch (strlen($m)) {
                case 4:
                    $link = get_year_link($m);
                    break;
                case 6:
                    $link = get_month_link(substr($m, 0, 4), substr($m, 4, 2));
                    break;
                case 8:
                    $link = get_day_link(substr($m, 0, 4), substr($m, 4, 2), substr($m, 6, 2));
                    break;
                default:
                    return false;
            }
        } elseif ((is_single() || is_page()) && $haspost) {
            $post = $wp_query->posts[0];
            $link = get_permalink($post->ID);
            $link = $this->getPaged($link);
        } elseif ((is_single() || is_page()) && $haspost) {
            $post = $wp_query->posts[0];
            $link = get_permalink($post->ID);
            $link = $this->getPaged($link);
        } elseif (is_author() && $haspost) {
            global $wp_version;
            if ($wp_version >= '2') {
                $author = get_userdata(get_query_var('author'));
                if ($author === false) {
                    return false;
                }
                $link = get_author_posts_url($author->ID, $author->user_nicename);
            } else {
                global $cache_userdata;
                $userid = get_query_var('author');
                $link = get_author_posts_url($userid, $cache_userdata[$userid]->user_nicename);
            }
        } elseif (is_category() && $haspost) {
            $link = $this->getPaged(get_category_link(get_query_var('cat')));
        } else if (is_tag() && $haspost) {
            $tag = get_term_by('slug', get_query_var('tag'), 'post_tag');
            if (!empty($tag->term_id)) {
                $link = get_tag_link($tag->term_id);
            }
            $link = $this->getPaged($link);
        } elseif (is_day() && $haspost) {
            $link = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
        } elseif (is_month() && $haspost) {
            $link = get_month_link(get_query_var('year'), get_query_var('monthnum'));
        } elseif (is_year() && $haspost) {
            $link = get_year_link(get_query_var('year'));
        } elseif (is_home()) {
            if ((get_option('show_on_front') == 'page') && ($pageid = get_option('page_for_posts'))) {
                $link = trailingslashit($this->getPaged(get_permalink($pageid)));
            } else {
                if (function_exists('icl_get_home_url')) {
                    $link = icl_get_home_url();
                } else {
                    $link = get_option('home');
                }
                $link = trailingslashit($this->getPaged($link));
            }
        } elseif (is_tax() && $haspost) {
            $taxonomy = get_query_var('taxonomy');
            $term = get_query_var('term');
            $link = $this->getPaged(
                    get_term_link($term, $taxonomy));
        } else {
            return false;
        }

        return $link;
    }

    public function getAuthorLinkFromBlog() {
        global $wp_query, $wp_version;

        if ($wp_query->is_author && count($wp_query->posts) > 0) {
            if ($wp_version >= '2') {
                $author = get_userdata(get_query_var('author'));
                if ($author === false) {
                    return false;
                }
                return get_author_posts_url($author->ID, $author->user_nicename);
            } else {
                global $cache_userdata;
                $userid = get_query_var('author');
                return

                        get_author_posts_url($userid, $cache_userdata[$userid]->user_nicename);
            }
        }
        return false;
    }

    public function getPaged($link) {
        $page = get_query_var('paged');
        if ($page && $page > 1) {
            $link = trailingslashit($link) . "page/" . "$page/";
        }
        return $link;
    }

    /**
     * Check if is the homepage
     *
     * @return bool
     */
    private function isHomePage() {
        global $wp_query;
        return (is_home() || (isset($wp_query->query) && empty($wp_query->query
                ) && !is_preview()));
    }

    /**
     * Check if page is shown in front
     *
     * @return bool
     */
    private function checkFrontPage() {
        return is_page() && get_option('show_on_front') == 'page' && $this->post->ID ==
                get_option('page_on_front');
    }

    /**
     * Check if page is shown in home
     *
     * @return bool
     */
    private function checkPostsPage() {
        return is_home() && get_option('show_on_front') == 'page' && $this->post->ID ==
                get_option('page_for_posts');
    }

    /**
     * Check if posts in home page
     *
     * @return bool
     */
    private function checkHomePosts() {
        global $wp_query;

        if (!$this->checkPostsPage()) {
            return is_home() && (int) $wp_query->post_count > 0 &&
                    isset($wp_query->posts) && is_array($wp_query->posts);
        } else {
            return false;
        }
    }

    public function truncate($text, $min, $max) {
        if (function_exists('strip_tags')) {
            $text = strip_tags($text);
        } $text = str_replace(']]>', ']]&gt;', $text);
        $text = @preg_replace('|\[(.+?)\](.+?\[/\\1\])?|s', '', $text);
        $text = strip_tags($text);

        if ($max < strlen($text)) {
            while ($text[$max] != ' ' && $max > $min) {
                $max--;
            }
        }
        $text = substr($text, 0, $max);
        return trim(
                stripcslashes($text));
    }

    public function _truncate($text) {
        if (function_exists('strip_tags'))
            $text = strip_tags($text);
        $text = str_replace(']]>', ']]&gt;', $text);
        $text = @preg_replace('|\[(.+?)\](.+?\[/\\1\])?|s', '', $text);
        $text = strip_tags($text);
        return

                trim(stripcslashes($text));
    }

    /**
     * Show just distinct keywords
     *
     * @return string
     */
    public function getUniqueKeywords($keywords) {
        $all = array();
        if (is_array($keywords)) {
            foreach ($keywords as $word) {
                if (function_exists('mb_strtolower')) {
                    $all[] = mb_strtolower($word, get_bloginfo('charset'));
                } else {
                    $all[] = strtolower($word);
                }
            }
        }

        if (is_array($all) && count($all) > 0) {
            $all = array_unique($all);
            if (sizeof($all) > 5) {
                $all = array_slice($all, 0, 5);
            }

            return implode(',', $all);
        }

        return '';
    }

    /**
     * Check if other plugin are/were installed and don't change the SEO
     *
     * @param type $post_id
     * @return boolean
     */
    public function getAdvancedMeta($post_id, $meta = 'title') {
        global $wpdb;

        $field = '';
        $cond = '';

        if (!isset($post_id) || (int) $post_id == 0) {
            return '';
        }

//check yoast
        switch ($meta) {
            case 'title':
                $field = 'sq_fp_title';
                break;
            case 'description':
                $field = 'sq_fp_description';
                break;
            case 'keyword':
                $field = 'sq_fp_keywords';
                break;
            case 'ogimage':
                $field = 'sq_fp_ogimage';
                break;
            default:
                $field = 'sq_fp_title';
        }

        if ($field <> '' && isset($this->meta[$post_id][$field])) {
            return $this->meta[$post_id][$field];
        }

// Get the custom Squirrly meta
//////////////////////////////////////////
        $fields = array('sq_fp_title' => '', 'sq_fp_description' => '', 'sq_fp_keywords' => '', 'sq_fp_ogimage' => '');

        $sql = "SELECT `meta_key`, `meta_value`
                       FROM `" . $wpdb->postmeta . "`
                        WHERE `post_id`=" . (int) $post_id;

        if ($rows = $wpdb->get_results($sql)) {
            foreach ($rows as $row) {
                if (array_key_exists($row->meta_key, $fields)) {
                    $this->meta[$post_id][$row->meta_key] = $row->meta_value;
                }
            }
        }
        if (isset($this->meta[$post_id]) && is_array($this->meta[$post_id])) {
            $this->meta[$post_id] = array_merge($fields, $this->meta[$post_id]);
        } else {
            $this->meta[$post_id] = $fields;
        }
//////////////////////////////////////////

        if ($field <> '') {
            return $this->meta[$post_id][$field];
        }
/////////////
        return false;
    }

    /**
     * Check if other plugin are/were installed and don't change the SEO
     *
     * @param type $post_id
     * @return boolean
     */
    public function getOtherPluginsMeta($post_id, $meta = 'title') {
        global $wpdb;

        $field = '';
        $cond = '';

        if (!isset($post_id) || (int) $post_id == 0) {
            return '';
        }

//check yoast
        switch ($meta) {
            case 'title':
                $field = '_yoast_wpseo_title';
                break;
            case 'description':
                $field = '_yoast_wpseo_metadesc';
                break;
            case 'keyword':
                $field = '_yoast_wpseo_focuskw';
                break;
            default:
                $field = '_yoast_wpseo_title';
        }

        if ($field <> '' && isset($this->meta[$post_id][$field])) {
            return $this->meta[$post_id][$field];
        }

// Get the custom Squirrly meta
//////////////////////////////////////////
        $fields = array('_yoast_wpseo_title' => '', '_yoast_wpseo_metadesc' => '', '_yoast_wpseo_focuskw' => '');

        $sql = "SELECT `meta_key`, `meta_value`
                       FROM `" . $wpdb->postmeta . "`
                       WHERE `post_id`=" . (int) $post_id;

        $rows = $wpdb->get_results($sql);
        if ($rows) {
            foreach ($rows as $row) {
                if (array_key_exists($row->meta_key, $fields)) {
                    $this->meta[$post_id][$row->meta_key] = $row->meta_value;
                }
            }
        }
        if (isset($this->meta[$post_id]) && is_array($this->meta[$post_id])) {
            $this->meta[$post_id] = array_merge($fields, $this->meta[$post_id]);
        } else {
            $this->meta[$post_id] = $fields;
        }
//////////////////////////////////////////
        if ($field <> '') {
            return $this->meta[$post_id][$field];
        }
        /////////////
        return false;
    }

}
