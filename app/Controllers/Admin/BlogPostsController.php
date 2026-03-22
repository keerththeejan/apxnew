<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Models\ActivityLog;
use App\Models\BlogPost;

final class BlogPostsController extends AdminBaseController
{
    /** Alias for legacy URLs: News admin lives at /admin/blog */
    public function newsAlias(): void
    {
        $this->redirect('/admin/blog');
    }

    public function index(): void
    {
        $this->requireAuth();

        $q = trim((string) Request::get('q', ''));
        $page = (int) Request::get('page', 1);
        $perPage = (int) Request::get('perPage', 12);
        $result = BlogPost::paginate($q, $page, $perPage);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.blog_index', [
            'title' => 'APX Admin - Blog',
            'pageKey' => 'blog',
            'pageTitle' => 'Blog / News',
            'crumb' => 'APX / Blog',
            'q' => $q,
            'posts' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'pageCount' => $result['pageCount'],
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function create(): void
    {
        $this->requireAuth();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.blog_form', [
            'title' => 'APX Admin - New post',
            'pageKey' => 'blog',
            'pageTitle' => 'New post',
            'crumb' => 'APX / Blog / New',
            'post' => null,
            'mode' => 'create',
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function store(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $title = trim((string) Request::post('title', ''));
        $slug = trim((string) Request::post('slug', ''));
        if ($slug === '') {
            $slug = $this->slugify($title);
        }
        $pub = trim((string) Request::post('published_at', ''));
        if ($pub !== '') {
            $pub = str_replace('T', ' ', $pub);
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $pub) === 1) {
                $pub .= ':00';
            }
        }
        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => trim((string) Request::post('excerpt', '')),
            'content' => (string) Request::post('content', ''),
            'cover_image_path' => trim((string) Request::post('cover_image_path', '')),
            'status' => trim((string) Request::post('status', 'draft')),
            'published_at' => $pub,
        ];
        if ($data['title'] === '' || $data['slug'] === '') {
            $_SESSION['flash_error'] = 'Title and slug are required.';
            $this->redirect('/admin/blog/new');
            return;
        }

        $aid = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
        $id = BlogPost::create($data, $aid);
        try {
            ActivityLog::record($aid, 'blog.create', 'blog_post', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Post created.';
        $this->redirect('/admin/blog/edit/' . $id);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();

        $post = BlogPost::findById((int) $id);
        if ($post === null) {
            $_SESSION['flash_error'] = 'Post not found.';
            $this->redirect('/admin/blog');
            return;
        }

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        view('admin.blog_form', [
            'title' => 'APX Admin - Edit post',
            'pageKey' => 'blog',
            'pageTitle' => 'Edit post',
            'crumb' => 'APX / Blog / Edit',
            'post' => $post,
            'mode' => 'edit',
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid id.';
            $this->redirect('/admin/blog');
            return;
        }

        $title = trim((string) Request::post('title', ''));
        $slug = trim((string) Request::post('slug', ''));
        if ($slug === '') {
            $slug = $this->slugify($title);
        }
        $pub = trim((string) Request::post('published_at', ''));
        if ($pub !== '') {
            $pub = str_replace('T', ' ', $pub);
            if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $pub) === 1) {
                $pub .= ':00';
            }
        }
        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => trim((string) Request::post('excerpt', '')),
            'content' => (string) Request::post('content', ''),
            'cover_image_path' => trim((string) Request::post('cover_image_path', '')),
            'status' => trim((string) Request::post('status', 'draft')),
            'published_at' => $pub,
        ];
        if ($data['title'] === '' || $data['slug'] === '') {
            $_SESSION['flash_error'] = 'Title and slug are required.';
            $this->redirect('/admin/blog/edit/' . $id);
            return;
        }

        BlogPost::update($id, $data);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'blog.update', 'blog_post', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Post saved.';
        $this->redirect('/admin/blog/edit/' . $id);
    }

    public function destroy(): void
    {
        $this->requireAuth();
        if (!Csrf::verify((string) Request::post('_token', ''))) {
            http_response_code(419);
            echo 'CSRF token mismatch';
            return;
        }

        $id = (int) Request::post('id', 0);
        if ($id < 1) {
            $_SESSION['flash_error'] = 'Invalid id.';
            $this->redirect('/admin/blog');
            return;
        }

        BlogPost::delete($id);
        try {
            ActivityLog::record(isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null, 'blog.delete', 'blog_post', $id, null);
        } catch (\Throwable $e) {
        }
        $_SESSION['flash_success'] = 'Post deleted.';
        $this->redirect('/admin/blog');
    }

    private function slugify(string $s): string
    {
        $s = strtolower(trim($s));
        $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? '';
        $s = trim($s, '-');

        return $s !== '' ? $s : 'post';
    }
}
