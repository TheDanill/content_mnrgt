<?
if (!defined('_INITIALIZED')) {
    header('HTTP/1.1 404 Not Found');
    header('Status: 404 Not Found');
    exit;
}
?>
        
            </div>
            <!--<div class="col-lg-2">
                <div class="panel panel-success">
                    <div class="panel-heading">
                        Меню
                    </div>
                    <div class="list-group" id="blocks">
                        <a href="#block-users" data-toggle="collapse" data-parent="#blocks" class="clearfix list-group-item<?= (in_array($this->template, ['users','groups','statsusers','statsgroups','statsuser','statsgroup','historyuser','user','group']) ? ' active set-first' : ' list-group-item-warning'); ?>">Пользователи<span class="caret pull-right"></span></a>
                        <div id="block-users" class="collapse<?= (in_array($this->template, ['users','groups','statsusers','statsgroups','statsuser','statsgroup','historyuser','user','group']) ? ' in set-first' : ''); ?>">
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/users/" class="clearfix list-group-item<? if ($this->template == 'users' || $this->template == 'user') { echo ' list-group-item-success'; } ?>"><span class="icon-user"></span> <strong>Все пользователи</strong></a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/users/stats/" class="clearfix list-group-item small<? if (in_array($this->template, ['statsusers','statsuser','historyuser'])) { echo ' list-group-item-success'; } ?>"><span class="icon-stats"></span>&nbsp;&nbsp;&nbsp;Статистика пользователей</a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/groups/" class="clearfix list-group-item<? if ($this->template == 'groups' || $this->template == 'group') { echo ' list-group-item-success'; } ?>"><span class="icon-users"></span> <strong>Группы</strong></a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/groups/stats/" class="clearfix list-group-item small<? if ($this->template == 'statsgroups' || $this->template == 'statsgroup') { echo ' list-group-item-success'; } ?>"><span class="icon-stats"></span>&nbsp;&nbsp;&nbsp;Статистика групп</a>
                        </div>
                        <a href="#block-exercises" data-toggle="collapse" data-parent="#blocks" class="clearfix list-group-item<?= (in_array($this->template, ['exercises','variables','editexercise','addexercise','teacherexercise','variable','addvariable','blocks','block','addblock','statsexercises','statsblocks','statsexercise','statsblock']) ? ' active set-first' : ' list-group-item-warning'); ?>">Задания<span class="caret pull-right"></span></a>
                        <div id="block-exercises" class="collapse<?= (in_array($this->template, ['exercises','variables','editexercise','addexercise','teacherexercise','variable','addvariable','blocks','block','addblock','statsexercises','statsblocks']) ? ' in set-first' : ''); ?>">
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/exercises/" class="clearfix list-group-item <? if ($this->template == 'exercises' || $this->template == 'editexercise' || $this->template == 'teacherexercise') { echo ' list-group-item-success'; } ?>"><span class="icon-file"></span> <strong>Все задания</strong></a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/exercises/stats/" class="clearfix list-group-item small<? if ($this->template == 'statsexercises' || $this->template == 'statsexercise') { echo ' list-group-item-success'; } ?>"><span class="icon-stats"></span>&nbsp;&nbsp;&nbsp;Статистика по заданиям</a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/exercise/add/" class="clearfix list-group-item small<? if ($this->template == 'addexercise') { echo ' list-group-item-success'; } ?>"><span class="icon-plus"></span>&nbsp;&nbsp;&nbsp;Добавить задание</a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/variables/" class="clearfix list-group-item<? if ($this->template == 'variables' || $this->template == 'variable') { echo ' list-group-item-success'; } ?>"><span class="icon-dice"></span> <strong>Переменные</strong></a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/variable/add/" class="clearfix list-group-item small<? if ($this->template == 'addvariable') { echo ' list-group-item-success'; } ?>"><span class="icon-plus"></span>&nbsp;&nbsp;&nbsp;Добавить переменную</a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/blocks/" class="clearfix list-group-item<? if ($this->template == 'blocks' || $this->template == 'block') { echo ' list-group-item-success'; } ?>"><span class="icon-stack"></span> <strong>Блоки заданий</strong></a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/blocks/stats/" class="clearfix list-group-item small<? if ($this->template == 'statsblocks' || $this->template == 'statsblock') { echo ' list-group-item-success'; } ?>"><span class="icon-stats"></span>&nbsp;&nbsp;&nbsp;Статистика по блокам</a>
                            <a href="http://<?= $GLOBALS['config']['domain']; ?>/block/add/" class="clearfix list-group-item small<? if ($this->template == 'addblock') { echo ' list-group-item-success'; } ?>"><span class="icon-plus"></span>&nbsp;&nbsp;&nbsp;Добавить блок заданий</a>
                        </div>
                    </div>
                </div>
            </div>-->
        </div>
        <footer class="text-center well-sm">
            <small>&copy; <?= date('Y'); ?> Kim Jong-un team</small>
        </footer>
    </body>
</html>