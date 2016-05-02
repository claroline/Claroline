<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Activity\ActivityRuleAction;
use Claroline\InstallationBundle\Updater\Updater;

class Updater030000 extends Updater
{
    private $container;
    private $icons;
    private $om;

    public function __construct($container)
    {
        $this->container = $container;
        $this->icons = $this->setIcons();
        $this->om = $container->get('doctrine.orm.entity_manager');
    }

    public function preUpdate()
    {
        $this->removeActivities();
    }

    public function postUpdate()
    {
        $this->updateActivityRuleAction();
        $this->updateActivityIcon();
        $this->removePublicProfilePreference();
        $this->updateAdminPluginTool();
        $this->cleanWeb();
    }

    private function removeActivities()
    {
        //First we need to check we're still using the old activity definition. If yes, then we remove them.
        $conn = $this->om->getConnection();
        $sm = $conn->getSchemaManager();
        $columns = $sm->listTableColumns('claro_activity');

        //if there is no primary resource, then we are using the old activities
        if (!array_key_exists('primaryresource_id', $columns)) {
            $resourceType = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findOneByName('activity');
            $this->log('removing old activities...');
            //find old nodes
            $rows = $conn->query("SELECT id from claro_resource_type rt WHERE rt.name = 'activity'");

            foreach ($rows as $row) {
                $id = $row['id'];
            }

            $conn->query("DELETE FROM claro_resource_node WHERE resource_type_id = {$id}");
            $conn->query('DELETE FROM claro_activity');
            $this->om->flush();
        }
    }

    private function updateActivityRuleAction()
    {
        $this->log('Updating list of action that can be mapped to an activity rule...');

        $fileType = $this->om
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName('file');
        $textType = $this->om
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneByName('text');

        $fileAction = $this->om
            ->getRepository('ClarolineCoreBundle:Activity\ActivityRuleAction')
            ->findRuleActionByActionAndResourceType('resource-read', $fileType);
        $textAction = $this->om
            ->getRepository('ClarolineCoreBundle:Activity\ActivityRuleAction')
            ->findRuleActionByActionAndResourceType('resource-read', $textType);
        $badgeAwardAction = $this->om
            ->getRepository('ClarolineCoreBundle:Activity\ActivityRuleAction')
            ->findRuleActionByActionWithNoResourceType('badge-awarding');

        if (is_null($fileAction)) {
            $fileAction = new ActivityRuleAction();
            $fileAction->setAction('resource-read');
            $fileAction->setResourceType($fileType);
            $this->om->persist($fileAction);
        }

        if (is_null($textAction)) {
            $textAction = new ActivityRuleAction();
            $textAction->setAction('resource-read');
            $textAction->setResourceType($textType);
            $this->om->persist($textAction);
        }

        if (is_null($badgeAwardAction)) {
            $badgeAwardAction = new ActivityRuleAction();
            $badgeAwardAction->setAction('badge-awarding');
            $this->om->persist($badgeAwardAction);
        }

        $this->om->flush();
    }

    public function updateActivityIcon()
    {
        $this->log('updating activity icon...');
        $path = 'bundles/clarolinecore/images/resources/icons/';

        $icon = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
                ->findOneBy(array('mimeType' => 'custom/activity'));
        $icon->setRelativeUrl($path.'res_activity.png');
        $this->om->persist($icon);

        $this->container->get('claroline.manager.icon_manager')->createShortcutIcon($icon);
    }

    private function updateAdminPluginTool()
    {
        $this->log('updating admin plugin tool...');

        $pluginTool = $this->om
            ->getRepository('Claroline\CoreBundle\Entity\Tool\AdminTool')
            ->findOneByName('platform_plugins');

        if ($pluginTool) {
            $pluginTool->setName('platform_packages');
            $this->om->persist($pluginTool);
            $this->om->flush();
        }
    }

    private function cleanWeb()
    {
        $webDir = $this->container->getParameter('claroline.param.web_dir');

        //remove the old maintenance file
        if (file_exists($webDir.DIRECTORY_SEPARATOR.'maintenance.html')) {
            unlink($webDir.DIRECTORY_SEPARATOR.'maintenance.html');
        }
    }

    private function removePublicProfilePreference()
    {
        $conn = $this->om->getConnection();
        $sm = $conn->getSchemaManager();

        if ($sm->tablesExist(array('claro_user_public_profile_preferences')) == true) {
            $fromSchema = $sm->createSchema();
            $toSchema = clone $fromSchema;
            $toSchema->dropTable('claro_user_public_profile_preferences');
            $sql = $fromSchema->getMigrateToSql($toSchema, $conn->getDatabasePlatform());
            $stmt = $conn->prepare($sql[0]);
            $stmt->execute();
            $stmt->closeCursor();
        }
    }

    private function setIcons()
    {
        return array(
            'icon-adjust' => 'adjust',
            'icon-adn' => 'adn',
            'icon-align-center' => 'align-center',
            'icon-align-justify' => 'align-justify',
            'icon-align-left' => 'align-left',
            'icon-align-right' => 'align-right',
            'icon-ambulance' => 'ambulance',
            'icon-anchor' => 'anchor',
            'icon-android' => 'android',
            'icon-angle-down' => 'angle-down',
            'icon-angle-left' => 'angle-left',
            'icon-angle-right' => 'angle-right',
            'icon-angle-up' => 'angle-up',
            'icon-apple' => 'apple',
            'icon-archive' => 'archive',
            'icon-arrow-down' => 'arrow-down',
            'icon-arrow-left' => 'arrow-left',
            'icon-arrow-right' => 'arrow-right',
            'icon-arrow-up' => 'arrow-up',
            'icon-asterisk' => 'asterisk',
            'icon-backward' => 'backward',
            'icon-ban-circle' => 'ban',
            'icon-bar-chart' => 'bar-chart-o',
            'icon-barcode' => 'barcode',
            'icon-beaker' => 'flask',
            'icon-beer' => 'beer',
            'icon-bell-alt' => 'bell',
            'icon-bell' => 'bell-o',
            'icon-bitbucket-sign' => 'bitbucket-square',
            'icon-bitbucket' => 'bitbucket',
            'icon-bitcoin' => 'bitcoin',
            'icon-bold' => 'bold',
            'icon-bolt' => 'bolt',
            'icon-book' => 'book',
            'icon-bookmark-empty' => 'bookmark-o',
            'icon-bookmark' => 'bookmark',
            'icon-briefcase' => 'briefcase',
            'icon-btc' => 'btc',
            'icon-bug' => 'bug',
            'icon-building' => 'building-o',
            'icon-bullhorn' => 'bullhorn',
            'icon-bullseye' => 'bullseye',
            'icon-calendar-empty' => 'calendar-o',
            'icon-calendar' => 'calendar',
            'icon-camera-retro' => 'camera-retro',
            'icon-camera' => 'camera',
            'icon-caret-down' => 'caret-down',
            'icon-caret-left' => 'caret-left',
            'icon-caret-right' => 'caret-right',
            'icon-caret-up' => 'caret-up',
            'icon-certificate' => 'certificate',
            'icon-check-empty' => 'square-o',
            'icon-check-minus' => 'minus-square-o',
            'icon-check-sign' => 'check-square',
            'icon-check' => 'check-square-o',
            'icon-chevron-down' => 'chevron-down',
            'icon-chevron-left' => 'chevron-left',
            'icon-chevron-right' => 'chevron-right',
            'icon-chevron-sign-down' => 'chevron-circle-down',
            'icon-chevron-sign-left' => 'chevron-circle-left',
            'icon-chevron-sign-right' => 'chevron-circle-right',
            'icon-chevron-sign-up' => 'chevron-circle-up',
            'icon-chevron-up' => 'chevron-up',
            'icon-circle-arrow-down' => 'arrow-circle-down',
            'icon-circle-arrow-left' => 'arrow-circle-left',
            'icon-circle-arrow-right' => 'arrow-circle-right',
            'icon-circle-arrow-up' => 'arrow-circle-up',
            'icon-circle-blank' => 'circle-o',
            'icon-circle' => 'circle',
            'icon-cloud-download' => 'cloud-download',
            'icon-cloud-upload' => 'cloud-upload',
            'icon-cloud' => 'cloud',
            'icon-cny' => 'rub',
            'icon-code-fork' => 'code-fork',
            'icon-code' => 'code',
            'icon-coffee' => 'coffee',
            'icon-cog' => 'cog',
            'icon-cogs' => 'cogs',
            'icon-collapse-alt' => 'minus-square-o',
            'icon-collapse-top' => 'caret-square-o-up',
            'icon-collapse' => 'caret-square-o-down',
            'icon-columns' => 'columns',
            'icon-comment-alt' => 'comment-o',
            'icon-comment' => 'comment',
            'icon-comments-alt' => 'comments-o',
            'icon-comments' => 'comments',
            'icon-compass' => 'compass',
            'icon-copy' => 'files-o',
            'icon-credit-card' => 'credit-card',
            'icon-crop' => 'crop',
            'icon-css3' => 'css3',
            'icon-cut' => 'scissors',
            'icon-dashboard' => 'tachometer',
            'icon-desktop' => 'desktop',
            'icon-dollar' => 'dollar',
            'icon-double-angle-down' => 'angle-double-down',
            'icon-double-angle-left' => 'angle-double-left',
            'icon-double-angle-right' => 'angle-double-right',
            'icon-double-angle-up' => 'angle-double-up',
            'icon-download-alt' => 'download',
            'icon-download' => 'arrow-circle-o-down',
            'icon-dribbble' => 'dribbble',
            'icon-dropbox' => 'dropbox',
            'icon-edit-sign' => 'pencil-square',
            'icon-edit' => 'pencil-square-o',
            'icon-eject' => 'eject',
            'icon-ellipsis-horizontal' => 'ellipsis-h',
            'icon-ellipsis-vertical' => 'ellipsis-v',
            'icon-envelope-alt' => 'envelope-o',
            'icon-envelope' => 'envelope',
            'icon-eraser' => 'eraser',
            'icon-eur' => 'eur',
            'icon-euro' => 'euro',
            'icon-exchange' => 'exchange',
            'icon-exclamation-sign' => 'exclamation-circle',
            'icon-exclamation' => 'exclamation',
            'icon-expand-alt' => 'plus-square-o',
            'icon-expand' => 'caret-square-o-right',
            'icon-external-link-sign' => 'external-link-square',
            'icon-external-link' => 'external-link',
            'icon-eye-close' => 'eye-slash',
            'icon-eye-open' => 'eye',
            'icon-facebook-sign' => 'facebook-square',
            'icon-facebook' => 'facebook',
            'icon-facetime-video' => 'video-camera',
            'icon-fast-backward' => 'fast-backward',
            'icon-fast-forward' => 'fast-forward',
            'icon-female' => 'female',
            'icon-fighter-jet' => 'fighter-jet',
            'icon-file-alt' => 'file-o',
            'icon-file-text-alt' => 'file-text-o',
            'icon-file-text' => 'file-text',
            'icon-file' => 'file',
            'icon-film' => 'film',
            'icon-filter' => 'filter',
            'icon-fire-extinguisher' => 'fire-extinguisher',
            'icon-fire' => 'fire',
            'icon-fixed-width' => 'fixed-width',
            'icon-fixed-width' => 'fw',
            'icon-flag-alt' => 'flag-o',
            'icon-flag-checkered' => 'flag-checkered',
            'icon-flag' => 'flag',
            'icon-flickr' => 'flickr',
            'icon-folder-close-alt' => 'folder-o',
            'icon-folder-close' => 'folder',
            'icon-folder-open-alt' => 'folder-open-o',
            'icon-folder-open' => 'folder-open',
            'icon-font' => 'font',
            'icon-food' => 'cutlery',
            'icon-forward' => 'forward',
            'icon-foursquare' => 'foursquare',
            'icon-frown' => 'frown-o',
            'icon-fullscreen' => 'arrows-alt',
            'icon-gamepad' => 'gamepad',
            'icon-gbp' => 'gbp',
            'icon-gear' => 'gear',
            'icon-gears' => 'gears',
            'icon-gift' => 'gift',
            'icon-github-alt' => 'github-alt',
            'icon-github-sign' => 'github-square',
            'icon-github' => 'github',
            'icon-gittip' => 'gittip',
            'icon-glass' => 'glass',
            'icon-globe' => 'globe',
            'icon-google-plus-sign' => 'google-plus-square',
            'icon-google-plus' => 'google-plus',
            'icon-group' => 'users',
            'icon-h-sign' => 'h-square',
            'icon-hand-down' => 'hand-o-down',
            'icon-hand-left' => 'hand-o-left',
            'icon-hand-right' => 'hand-o-right',
            'icon-hand-up' => 'hand-o-up',
            'icon-hdd' => 'hdd-o',
            'icon-headphones' => 'headphones',
            'icon-heart-empty' => 'heart-o',
            'icon-heart' => 'heart',
            'icon-home' => 'home',
            'icon-hospital' => 'hospital-o',
            'icon-html5' => 'html5',
            'icon-inbox' => 'inbox',
            'icon-indent-left' => 'outdent',
            'icon-indent-right' => 'indent',
            'icon-info-sign' => 'info-circle',
            'icon-info' => 'info',
            'icon-inr' => 'inr',
            'icon-instagram' => 'instagram',
            'icon-italic' => 'italic',
            'icon-jpy' => 'jpy',
            'icon-key' => 'key',
            'icon-keyboard' => 'keyboard-o',
            'icon-krw' => 'krw',
            'icon-laptop' => 'laptop',
            'icon-large' => 'large',
            'icon-large' => 'lg',
            'icon-leaf' => 'leaf',
            'icon-legal' => 'gavel',
            'icon-lemon' => 'lemon-o',
            'icon-level-down' => 'level-down',
            'icon-level-up' => 'level-up',
            'icon-li' => 'li',
            'icon-lightbulb' => 'lightbulb-o',
            'icon-link' => 'link',
            'icon-linkedin-sign' => 'linkedin-square',
            'icon-linkedin' => 'linkedin',
            'icon-linux' => 'linux',
            'icon-list-alt' => 'list-alt',
            'icon-list-ol' => 'list-ol',
            'icon-list-ul' => 'list-ul',
            'icon-list' => 'list',
            'icon-location-arrow' => 'location-arrow',
            'icon-lock' => 'lock',
            'icon-long-arrow-down' => 'long-arrow-down',
            'icon-long-arrow-left' => 'long-arrow-left',
            'icon-long-arrow-right' => 'long-arrow-right',
            'icon-long-arrow-up' => 'long-arrow-up',
            'icon-magic' => 'magic',
            'icon-magnet' => 'magnet',
            'icon-mail-forward' => 'mail-forward',
            'icon-mail-reply-all' => 'mail-reply-all',
            'icon-mail-reply' => 'mail-reply',
            'icon-male' => 'male',
            'icon-map-marker' => 'map-marker',
            'icon-maxcdn' => 'maxcdn',
            'icon-medkit' => 'medkit',
            'icon-meh' => 'meh-o',
            'icon-microphone-off' => 'microphone-slash',
            'icon-microphone' => 'microphone',
            'icon-minus-sign-alt' => 'minus-square',
            'icon-minus-sign' => 'minus-circle',
            'icon-minus' => 'minus',
            'icon-mobile-phone' => 'mobile',
            'icon-money' => 'money',
            'icon-moon' => 'moon-o',
            'icon-move' => 'arrows',
            'icon-music' => 'music',
            'icon-off' => 'power-off',
            'icon-ok-circle' => 'check-circle-o',
            'icon-ok-sign' => 'check-circle',
            'icon-ok' => 'check',
            'icon-paper-clip' => 'paperclip',
            'icon-paperclip' => 'paperclip',
            'icon-paste' => 'clipboard',
            'icon-pause' => 'pause',
            'icon-pencil' => 'pencil',
            'icon-phone-sign' => 'phone-square',
            'icon-phone' => 'phone',
            'icon-picture' => 'picture-o',
            'icon-pinterest-sign' => 'pinterest-square',
            'icon-pinterest' => 'pinterest',
            'icon-plane' => 'plane',
            'icon-play-circle' => 'play-circle-o',
            'icon-play-sign' => 'play-circle',
            'icon-play' => 'play',
            'icon-plus-sign-alt' => 'plus-square',
            'icon-plus-sign' => 'plus-circle',
            'icon-plus' => 'plus',
            'icon-power-off' => 'power-off',
            'icon-print' => 'print',
            'icon-pushpin' => 'thumb-tack',
            'icon-puzzle-piece' => 'puzzle-piece',
            'icon-qrcode' => 'qrcode',
            'icon-question-sign' => 'question-circle',
            'icon-question' => 'question',
            'icon-quote-left' => 'quote-left',
            'icon-quote-right' => 'quote-right',
            'icon-random' => 'random',
            'icon-refresh' => 'refresh',
            'icon-remove-circle' => 'times-circle-o',
            'icon-remove-sign' => 'times-circle',
            'icon-remove' => 'times',
            'icon-renminbi' => 'renminbi',
            'icon-renren' => 'renren',
            'icon-reorder' => 'bars',
            'icon-repeat' => 'repeat',
            'icon-reply-all' => 'reply-all',
            'icon-reply' => 'reply',
            'icon-resize-full' => 'expand',
            'icon-resize-horizontal' => 'arrows-h',
            'icon-resize-small' => 'compress',
            'icon-resize-vertical' => 'arrows-v',
            'icon-retweet' => 'retweet',
            'icon-road' => 'road',
            'icon-rocket' => 'rocket',
            'icon-rotate-left' => 'rotate-left',
            'icon-rotate-right' => 'rotate-right',
            'icon-rss-sign' => 'rss-square',
            'icon-rss' => 'rss',
            'icon-rupee' => 'rupee',
            'icon-save' => 'floppy-o',
            'icon-screenshot' => 'crosshairs',
            'icon-search' => 'search',
            'icon-share-alt' => 'share',
            'icon-share-sign' => 'share-square',
            'icon-share' => 'share-square-o',
            'icon-shield' => 'shield',
            'icon-shopping-cart' => 'shopping-cart',
            'icon-sign-blank' => 'square',
            'icon-signal' => 'signal',
            'icon-signin' => 'sign-in',
            'icon-signout' => 'sign-out',
            'icon-sitemap' => 'sitemap',
            'icon-skype' => 'skype',
            'icon-smile' => 'smile-o',
            'icon-sort-by-alphabet-alt' => 'sort-alpha-desc',
            'icon-sort-by-alphabet' => 'sort-alpha-asc',
            'icon-sort-by-attributes-alt' => 'sort-amount-desc',
            'icon-sort-by-attributes' => 'sort-amount-asc',
            'icon-sort-by-order-alt' => 'sort-numeric-desc',
            'icon-sort-by-order' => 'sort-numeric-asc',
            'icon-sort-down' => 'sort-asc',
            'icon-sort-down' => 'sort-desc',
            'icon-sort-up' => 'sort-asc',
            'icon-sort-up' => 'sort-desc',
            'icon-sort' => 'sort',
            'icon-spin' => 'spin',
            'icon-spinner' => 'spinner',
            'icon-stackexchange' => 'stack-overflow',
            'icon-star-empty' => 'star-o',
            'icon-star-half-empty' => 'star-half-o',
            'icon-star-half-full' => 'star-half-full',
            'icon-star-half' => 'star-half',
            'icon-star' => 'star',
            'icon-step-backward' => 'step-backward',
            'icon-step-forward' => 'step-forward',
            'icon-stethoscope' => 'stethoscope',
            'icon-stop' => 'stop',
            'icon-strikethrough' => 'strikethrough',
            'icon-subscript' => 'subscript',
            'icon-suitcase' => 'suitcase',
            'icon-sun' => 'sun-o',
            'icon-superscript' => 'superscript',
            'icon-table' => 'table',
            'icon-tablet' => 'tablet',
            'icon-tag' => 'tag',
            'icon-tags' => 'tags',
            'icon-tasks' => 'tasks',
            'icon-terminal' => 'terminal',
            'icon-text-height' => 'text-height',
            'icon-text-width' => 'text-width',
            'icon-th-large' => 'th-large',
            'icon-th-list' => 'th-list',
            'icon-th' => 'th',
            'icon-thumbs-down-alt' => 'thumbs-o-down',
            'icon-thumbs-down' => 'thumbs-down',
            'icon-thumbs-up-alt' => 'thumbs-o-up',
            'icon-thumbs-up' => 'thumbs-up',
            'icon-ticket' => 'ticket',
            'icon-time' => 'clock-o',
            'icon-tint' => 'tint',
            'icon-trash' => 'trash-o',
            'icon-trello' => 'trello',
            'icon-trophy' => 'trophy',
            'icon-truck' => 'truck',
            'icon-tumblr-sign' => 'tumblr-square',
            'icon-tumblr' => 'tumblr',
            'icon-twitter-sign' => 'twitter-square',
            'icon-twitter' => 'twitter',
            'icon-umbrella' => 'umbrella',
            'icon-unchecked' => 'unchecked',
            'icon-underline' => 'underline',
            'icon-undo' => 'undo',
            'icon-unlink' => 'chain-broken',
            'icon-unlock-alt' => 'unlock-alt',
            'icon-unlock' => 'unlock',
            'icon-upload-alt' => 'upload',
            'icon-upload' => 'arrow-circle-o-up',
            'icon-usd' => 'usd',
            'icon-user-md' => 'user-md',
            'icon-user' => 'user',
            'icon-vk' => 'vk',
            'icon-volume-down' => 'volume-down',
            'icon-volume-off' => 'volume-off',
            'icon-volume-up' => 'volume-up',
            'icon-warning-sign' => 'exclamation-triangle',
            'icon-weibo' => 'weibo',
            'icon-windows' => 'windows',
            'icon-won' => 'won',
            'icon-wrench' => 'wrench',
            'icon-xing-sign' => 'xing-square',
            'icon-xing' => 'xing',
            'icon-yen' => 'yen',
            'icon-youtube-play' => 'youtube-play',
            'icon-youtube-sign' => 'youtube-square',
            'icon-youtube' => 'youtube',
            'icon-zoom-in' => 'search-plus',
            'icon-zoom-out' => 'search-minus',
        );
    }
}
