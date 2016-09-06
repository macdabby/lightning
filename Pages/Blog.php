<?php
/**
 * @file
 * Contains Lightning\Pages\Blog
 */

namespace Lightning\Pages;

use Lightning\Model\Blog as BlogModel;
use Lightning\Model\URL;
use Lightning\Tools\Output;
use Lightning\Tools\Request;
use Lightning\Tools\Template;
use Lightning\View\Page;
use Lightning\Model\BlogPost;

/**
 * A page handler for viewing and editing the blog.
 *
 * @package Lightning\Pages
 */
class Blog extends Page {

    protected $nav = 'blog';
    protected $page = 'blog';

    protected function hasAccess() {
        return true;
    }

    public function get() {
        $blog_id = Request::get('id', 'int') | Request::get('blog_id', 'int');
        $path = explode('/', Request::getLocation());

        $blog = BlogModel::getInstance();

        if ($blog_id) {
            $blog->loadContentById($blog_id);
            $this->setBlogMetadata(new BlogPost($blog->posts[0]));
        }
        elseif (!empty($path[0]) || count($path) > 2) {
            // This page num can be in index 2 (blog/page/#) or index 3 (blog/category/a-z/#).
            $blog->page = is_numeric($path[count($path) - 1]) ? $path[count($path) - 1] : 1;
            if (preg_match('/.htm$/', $path[0])) {
                // Load single blog by URL.
                $blog->loadContentByURL(preg_replace('/.htm$/', '', $path[0]));
                if (empty($blog->id)) {
                    Output::http(404);
                }
                $this->setBlogMetadata(new BlogPost($blog->posts[0]));
            } elseif (count($path) == 3 && $path[1] == 'category') {
                // Load category roll.
                $category = preg_replace('/\.htm$/', '', $path[2]);
                $c_parts = explode('-', $category);
                if (is_numeric(end($c_parts))) {
                    $blog->page = array_pop($c_parts);
                }
                $blog->category = implode('-', $c_parts);
                if ($cat = BlogPost::getCategory($blog->category)) {
                    $blog->category_url = $cat['cat_url'];
                    $blog->loadList('category', $cat['cat_id']);
                } else {
                    Output::http(404);
                }
            } elseif (count($path) == 3 && $path[1] == 'author') {
                // Load an author roll.
                if ($author_id = $blog->getAuthorID(preg_replace('/\.htm$/', '', $path[2]))) {
                    $blog->loadList('author', $author_id);
                } else {
                    Output::http(404);
                }
            } elseif (!empty($blog->page)) {
                $blog->loadList();
            }
        }
        else {
            // Fall back, load blogroll
            // TODO: This should only happen on /blog, otherwise it should return a 404
            $blog->loadList();
        }

        $template = Template::getInstance();
        if (count($blog->posts) == 1) {
            $template->set('page_section','blog');
        } else {
            // If there is more than one, we show a list with short bodies.
            $blog->shorten_body = true;
        }
    }

    /**
     * @param BlogPost $post
     */
    protected function setBlogMetaData($post) {
        $this->setMeta('title', $post->title);
        $this->setMeta('keywords', $post->keywords);
        $this->setMeta('description', $post->getShortBody(250, false));
        if ($image = $post->getHeaderImage()) {
            $this->setMeta('image', URL::getAbsolute($image));
        }
    }
}
