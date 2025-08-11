<?php

namespace Javaabu\Generators\IconProviders;

class FontAwesomeProvider extends BaseIconProvider
{
    public function getDefaultIcon(): string
    {
        return 'file';
    }

    public function getPrefix(): string
    {
        return 'fa-regular fa-';
    }

    protected static array $preset_icons = [
        'users' => 'users',
        'posts' => 'file-alt',
        'comments' => 'comment',
        'categories' => 'folder',
        'tags' => 'tags',
        'roles' => 'user-shield',
        'permissions' => 'lock',
        'profiles' => 'id-badge',
        'likes' => 'thumbs-up',
        'followers' => 'user-friends',
        'favorites' => 'star',
        'messages' => 'envelope',
        'notifications' => 'bell',
        'tasks' => 'tasks',
        'projects' => 'project-diagram',
        'teams' => 'users-cog',
        'members' => 'user',
        'invoices' => 'file-invoice-dollar',
        'payments' => 'credit-card',
        'orders' => 'shopping-basket',
        'products' => 'shopping-cart',
        'customers' => 'user-tag',
        'suppliers' => 'truck',
        'contacts' => 'address-book',
        'addresses' => 'map-marker-alt',
        'countries' => 'globe',
        'cities' => 'city',
        'states' => 'map',
        'employees' => 'user-tie',
        'departments' => 'building',
        'positions' => 'briefcase',
        'assets' => 'boxes',
        'loans' => 'hand-holding-usd',
        'expenses' => 'money-bill-wave',
        'transactions' => 'exchange-alt',
        'subcategories' => 'folder-open',
        'services' => 'concierge-bell',
        'appointments' => 'calendar-check',
        'bookings' => 'ticket-alt',
        'events' => 'calendar-alt',
        'places' => 'map-pin',
        'reviews' => 'comments',
        'ratings' => 'star-half-alt',
        'images' => 'image',
        'videos' => 'video',
        'documents' => 'file-alt',
        'audios' => 'volume-up',
        'articles' => 'newspaper',
        'books' => 'book',
        'authors' => 'user-edit',
        'genres' => 'bookmark',
        'chapters' => 'book-open',
        'sections' => 'list-alt',
        'questions' => 'question-circle',
        'answers' => 'comment-dots',
        'exams' => 'file-signature',
        'results' => 'poll',
        'grades' => 'clipboard-check',
        'students' => 'user-graduate',
        'teachers' => 'chalkboard-teacher',
        'courses' => 'graduation-cap',
        'lessons' => 'file-alt',
        'assignments' => 'clipboard-list',
        'submissions' => 'file-upload',
        'quizzes' => 'question',
        'options' => 'list',
        'responses' => 'file-alt',
        'surveys' => 'poll-h',
        'polls' => 'poll',
        'votes' => 'vote-yea',
        'candidates' => 'user-tie',
        'parties' => 'users',
        'constituencies' => 'map-marker',
        'elections' => 'calendar-day',
        'winners' => 'trophy',
        'losers' => 'sad-tear',
        'contests' => 'flag-checkered',
        'entries' => 'pen',
        'judges' => 'gavel',
        'verdicts' => 'balance-scale',
        'claims' => 'file-contract',
        'policies' => 'file-alt',
        'contracts' => 'file-signature',
        'clauses' => 'file-alt',
        'warranties' => 'shield-alt',
        'agreements' => 'handshake',
        'proposals' => 'lightbulb',
        'bids' => 'file-invoice',
        'tenders' => 'file-contract',
        'awards' => 'award',
        'offers' => 'tags',
        'negotiations' => 'handshake',
        'deals' => 'hand-holding-usd',
    ];

    protected static array $icons = [];

    public static function getIcons(): array
    {
        if (! static::$icons) {
            static::loadIcons();
        }

        return static::$icons;
    }

    public static function loadIcons(): void
    {
        static::$icons = file(__DIR__ . '/resources/fontawesome-v7.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
}
