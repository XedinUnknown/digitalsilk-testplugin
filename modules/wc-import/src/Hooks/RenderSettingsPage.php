<?php

declare(strict_types=1);

namespace DigitalSilk\WcImport\Hooks;

/**
 * Renders the page that allows management of module settings.
 */
class RenderSettingsPage
{
    protected string $username;
    protected string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function __invoke()
    {
        $handlerUrl = admin_url('admin-post.php');
        $actionPrefix = 'digitalsilk-testplugin';
        $saveActionName = "$actionPrefix-settings-save";
        $importActionName = "$actionPrefix-schedule-import";
        ?>
        <div class="wrap">
            <h1><?= __('Settings', 'digitalsilk-testplugin') ?></h1>
        </div>
        <form action="<?= esc_url($handlerUrl) ?>" method="POST">
            <?php wp_nonce_field($saveActionName) ?>
            <input type="hidden" name="action" value="<?= $saveActionName ?>" />

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="username"><?= __('Username', 'digitalsilk-testplugin') ?></label>
                    </th>
                    <td>
                        <input type="text" name="username" id="username" value="<?= $this->username ?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="password"><?= __('Password', 'digitalsilk-testplugin') ?></label>
                    </th>
                    <td>
                        <input type="password" name="password" id="password" value="<?= $this->password ?>"/>
                    </td>
                </tr>
            </table>

            <input class="button button-primary" type="submit" name="submit" value="<?= __('Save', 'digitalsilk-testplugin') ?>" />
        </form>

        <form action="<?= esc_url($handlerUrl) ?>" method="POST">
            <?php wp_nonce_field($importActionName) ?>
            <input type="hidden" name="action" value="<?= $importActionName ?>" />

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <input class="button button-primary" type="submit" name="submit" value="<?= __('Import Now!', 'digitalsilk-testplugin') ?>" />
                    </th>
                </tr>
            </table>
        </form>
        <?php
    }
}
