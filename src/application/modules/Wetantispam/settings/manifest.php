<?php return array(
    'package' =>
        array(
            'type' => 'module',
            'name' => 'wetantispam',
            'version' => '1.1.0',
            'path' => 'application/modules/Wetantispam',
            'title' => 'Wet AntiSpam',
            'description' => 'Spam filter for forum posts, messages, blog posts and blog comments.',
            'author' => 'Robert Wetzlmayr',
            'callback' =>
                array(
                    'class' => 'Engine_Package_Installer_Module',
                ),
            'actions' =>
                array(
                    0 => 'install',
                    1 => 'upgrade',
                    2 => 'refresh',
                    3 => 'enable',
                    4 => 'disable',
                ),
            'directories' =>
                array(
                    0 => 'application/modules/Wetantispam',
                ),
            'files' =>
                array(
                    0 => 'application/languages/en/wetantispam.csv',
                    1 => 'application/languages/de/wetantispam.csv',
                ),
        ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onForumTopicCreateBefore',
            'resource' => 'Wetantispam_Plugin_Antispam',
        ),
        array(
            'event' => 'onForumPostCreateBefore',
            'resource' => 'Wetantispam_Plugin_Antispam',
        ),
        array(
            'event' => 'onForumPostUpdateBefore',
            'resource' => 'Wetantispam_Plugin_Antispam',
        ),
        array(
            'event' => 'onMessagesMessageCreateBefore',
            'resource' => 'Wetantispam_Plugin_Antispam',
        ),
        array(
            'event' => 'onBlogCreateBefore',
            'resource' => 'Wetantispam_Plugin_Antispam',
        ),
        array(
            'event' => 'onBlogUpdateBefore',
            'resource' => 'Wetantispam_Plugin_Antispam',
        ),
        array(
            'event' => 'onCoreCommentCreateBefore',
            'resource' => 'Wetantispam_Plugin_Antispam',
        ),
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(
        'wetantispam_admin_settings' => array(
            'route' => "admin/wetantispam",
            'defaults' => array(
                'module' => 'wetantispam',
                'controller' => 'admin-index',
                'action' => 'index'
            ),
        ),
    )
);
