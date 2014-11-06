<table class="default">
    <caption>
        <?= _('Aktivitäts Rangliste')?>
    </caption>
    <thead>
        <tr>
            <th><?= _('Platz') ?></th>
            <th><?= _('Name') ?></th>
            <th><?= _('Punkte') ?></th>
            <th><?= _('Titel') ?></th>
        </tr>
    </thead>
    <tbody>
    <? foreach ($scores as $score): ?>
        <tr>
            <td style="text-align: right; width: 0px">
                <?= $offset + ++$index ?>.
            </td>
            <td>
                <?= ObjectdisplayHelper::avatarlink($score->user); ?>
                </td>
                <td><?= number_format($score->score , 0, ',', '.')?></td>
            <td><?= $score->title ?></td>
        </tr>
    <? endforeach ?>
    </tbody>
<? if (ceil($numberOfPersons / $max_per_page) > 1): ?>
    <tfoot>
        <tr>
            <td colspan="7" style="text-align: right">
                <?= $GLOBALS['template_factory']->render('shared/pagechooser', array(
                        'perPage'      => $max_per_page,
                        'num_postings' => $numberOfPersons,
                        'page'         => $page,
                        'pagelink'     => 'plugins.php/activityrankingplugin/show/index/%u')) ?>
            </td>
        </tr>
    </tfoot>
<? endif ?>
</table>
