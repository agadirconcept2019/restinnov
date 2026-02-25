<?php

return [
    'roles' => [
        'super_admin',
        'admin',
        'editor',
        'content_manager',
        'support',
        'owner',
    ],

    'permissions' => [
        'cms.pages.view', 'cms.pages.create', 'cms.pages.update', 'cms.pages.delete', 'cms.pages.publish',
        'blog.posts.view', 'blog.posts.create', 'blog.posts.update', 'blog.posts.delete', 'blog.posts.publish',
        'blog.categories.manage',
        'realestate.properties.view', 'realestate.properties.create', 'realestate.properties.update', 'realestate.properties.delete',
        'realestate.availability.manage', 'realestate.bookings.manage', 'realestate.ical.manage',
        'forms.submissions.view', 'forms.submissions.update_status',
        'communications.templates.manage', 'communications.logs.view', 'communications.logs.retry',
        'seo.manage',
        'menus.manage',
        'media.manage',
        'users.manage',
        'redirects.manage',
        'ops.view', 'ops.jobs.retry',
        'audit_logs.view',
        'migration.profiles.manage', 'migration.runs.execute', 'migration.runs.rollback',
        'admin.exports.run',
    ],

    'role_permissions' => [
        'super_admin' => ['*'],
        'admin' => ['*'],
        'editor' => ['cms.pages.view', 'cms.pages.create', 'cms.pages.update', 'blog.posts.view', 'blog.posts.create', 'blog.posts.update', 'blog.categories.manage', 'media.manage'],
        'content_manager' => ['cms.pages.view', 'cms.pages.create', 'cms.pages.update', 'cms.pages.publish', 'blog.posts.view', 'blog.posts.create', 'blog.posts.update', 'blog.posts.publish', 'blog.categories.manage', 'seo.manage', 'menus.manage', 'media.manage', 'admin.exports.run'],
        'support' => ['forms.submissions.view', 'communications.logs.view', 'realestate.bookings.manage', 'ops.view', 'admin.exports.run'],
        'owner' => [],
    ],
];
