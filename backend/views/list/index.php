<?php
/**
 * index.php
 * @author Revin Roman
 * @link https://rmrevin.com
 *
 * @var yii\web\View $this
 */

use cookyii\modules\Feed;
use rmrevin\yii\fontawesome\FA;
use yii\helpers\Html;
use yii\helpers\Json;

$this->title = Yii::t('cookyii.feed', 'Sections management');

Feed\backend\assets\ListAssetBundle::register($this);

/**
 * @param string $type
 * @param string $label
 * @return string
 */
function sortLink($type, $label)
{
    $label .= ' ' . FA::icon('sort-numeric-desc', ['ng-show' => 'items.sort.order === "-' . $type . '"']);
    $label .= ' ' . FA::icon('sort-numeric-asc', ['ng-show' => 'items.sort.order === "' . $type . '"']);

    return Html::a($label, null, [
        'ng-click' => 'items.sort.setOrder("' . $type . '")',
    ]);
}

?>

<section <?= Html::renderTagAttributes([
    'class' => 'content',
    'ng-controller' => 'feed.ListController',
]) ?>>
    <div class="row">
        <div class="col-xs-3 com-sm-3 col-md-3 col-lg-2">
            <div class="box-filter">
                <h3><?= Yii::t('cookyii', 'Filter') ?></h3>

                <hr>

                <?= Html::tag('a', FA::icon('check') . ' ' . Yii::t('cookyii.feed', 'Removed items'), [
                    'class' => 'checker',
                    'ng-click' => 'items.filter.toggleDeleted()',
                    'ng-class' => Json::encode(['checked' => new \yii\web\JsExpression('items.filter.deleted')]),
                ]) ?>

                <hr>

                <script type="text/ng-template" id="section.html">
                    <?= Html::a('{{ items.filter.section.get(sect.slug).title }}', null, [
                        'ng-click' => 'items.filter.section.select(sect)',
                    ]) ?>

                    <span class="dash">&ndash;</span>

                    <ul class="sub" ng-if="sect.sections">
                        <li ng-repeat="sect in sect.sections track by sect.slug"
                            ng-class="{
                            'active': items.filter.section.isActive(sect),
                            'deleted': items.filter.section.get(sect.slug).deleted
                            }"
                            ng-include="'section.html'"></li>
                    </ul>
                </script>

                <ul class="sections opened">
                    <li ng-repeat="sect in items.filter.section.tree track by sect.slug"
                        ng-class="{
                        'active': items.filter.section.isActive(sect),
                        'deleted': items.filter.section.get(sect.slug).deleted
                        }"
                        ng-include="'section.html'"></li>
                </ul>
            </div>
        </div>
        <div class="col-xs-9 com-sm-9 col-md-9 col-lg-10">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">
                        <?= Yii::t('cookyii.feed', 'Items list') ?>

                        <a ng-click="items.reload()" ng-if="items.filter.section.selected"
                           class="btn-reload pull-right">
                            <?= FA::icon('refresh', ['ng-class' => '{"fa-spin": accounts.inProgress}']) ?>
                        </a>
                    </h3>

                    <div class="box-section" ng-if="items.filter.section.selected">
                        Edit section:
                        <?= Html::a('{{ items.filter.section.getSelected().title }}', null, [
                            'ng-click' => 'items.filter.section.edit(items.filter.section.selected)',
                            'title' => Yii::t('cookyii.feed', 'Edit section'),
                        ]) ?>

                        <?php
                        echo Html::tag('a', FA::icon('times'), [
                            'class' => 'text-red',
                            'title' => Yii::t('cookyii.feed', 'Remove section'),
                            'ng-click' => 'items.filter.section.remove(items.filter.section.selected, $event)',
                            'ng-show' => 'items.filter.section.getSelected().deleted === "0"',
                        ]);
                        echo Html::tag('a', FA::icon('undo'), [
                            'class' => 'text-light-blue',
                            'title' => Yii::t('cookyii.feed', 'Restore section'),
                            'ng-click' => 'items.filter.section.restore(items.filter.section.selected)',
                            'ng-show' => 'items.filter.section.getSelected().deleted === "1"',
                        ]);
                        ?>
                    </div>

                    <div class="box-tools">
                        <?= Html::tag('pagination', null, [
                            'class' => 'pagination pagination-sm no-margin pull-right',
                            'ng-model' => 'pagination.currentPage',
                            'total-items' => 'pagination.totalCount',
                            'items-per-page' => 'pagination.perPage',
                            'ng-change' => 'doPageChanged()',
                            'max-size' => '10',
                            'previous-text' => '‹',
                            'next-text' => '›',
                        ]) ?>

                        <form ng-submit="items.filter.search.do()" class="pull-right">
                            <div class="input-group search" ng-class="{'wide':items.filter.search.query.length>0}">
                                <?= Html::textInput(null, null, [
                                    'class' => 'form-control input-sm pull-right',
                                    'placeholder' => Yii::t('cookyii', 'Search'),
                                    'maxlength' => 100,
                                    'ng-model' => 'items.filter.search.query',
                                    'ng-blur' => 'items.filter.search.do()',
                                ]) ?>
                                <a ng-click="items.filter.search.clear()" ng-show="items.filter.search.query"
                                   class="clear-search">
                                    <?= FA::icon('times') ?>
                                </a>

                                <div class="input-group-btn">
                                    <button type="submit" class="btn btn-sm btn-default">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="box-body no-padding">
                    <div class="text-center text-italic text-light" ng-show="items.list.length === 0">
                        <?= Yii::t('cookyii.feed', 'Items not found') ?>
                    </div>
                    <?php
                    $options = [
                        'title' => Yii::t('cookyii.feed', 'Edit item'),
                        'class' => 'box-item',
                        'ng-class' => '{deactivated:!item.activated,deleted:item.deleted}',
                    ];
                    ?>
                    <div ng-repeat="item in items.list track by item.id"
                        <?= Html::renderTagAttributes($options) ?>>
                        <div class="row">
                            <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
                                <div class="preview-picture clickable" ng-click="items.edit(item)">
                                    <div ng-if="item.picture_300"><img ng-src="{{ item.picture_300 }}"></div>
                                    <div ng-if="!item.picture_300">Preview<br>picture</div>
                                </div>
                            </div>
                            <div class="col-xs-2 col-sm-2 col-md-1 col-lg-1 activated">
                                <md-switch ng-model="item.activated"
                                           ng-change="items.toggleActivation(item)"
                                           ng-disabled="item.deleted"
                                           title="Item {{ item.activated ? 'activated' : 'deactivated' }}"
                                           aria-label="Item {{ item.activated ? 'activated' : 'deactivated' }}">
                                </md-switch>

                                <br>

                                <?= Html::tag('div', '{{ item.sort }}', [
                                    'class' => 'sort',
                                    'title' => Yii::t('cookyii.feed', 'Sort'),
                                ]) ?>
                            </div>
                            <div class="col-xs-8 col-sm-4 col-md-7 col-lg-7 contain">
                                <h3 class="clickable" ng-click="items.edit(item)">
                                    {{ item.title }}
                                    <br>
                                    <small>{{ item.slug }}</small>
                                </h3>

                                <small>{{ item.updated_at.format }}</small>

                                <p ng-bind-html="item.content_preview" class="clickable"
                                   ng-click="items.edit(item)"></p>
                            </div>
                            <div class="col-xs-2 col-sm-2 col-md-1 col-lg-1 actions">
                                <?php
                                echo Html::tag('a', FA::icon('times'), [
                                    'class' => 'text-red',
                                    'title' => Yii::t('cookyii.feed', 'Remove item'),
                                    'ng-click' => 'items.remove(item, $event)',
                                    'ng-show' => '!item.deleted',
                                ]);
                                echo Html::tag('a', FA::icon('undo'), [
                                    'class' => 'text-light-blue',
                                    'title' => Yii::t('cookyii.feed', 'Restore item'),
                                    'ng-click' => 'items.restore(item)',
                                    'ng-show' => 'item.deleted',
                                ]);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer clearfix">
                    <?= Html::tag('pagination', null, [
                        'class' => 'pagination pagination-sm no-margin pull-right',
                        'ng-model' => 'items.pagination.currentPage',
                        'total-items' => 'items.pagination.totalCount',
                        'items-per-page' => 'items.pagination.perPage',
                        'ng-change' => 'items.doPageChanged()',
                        'max-size' => '10',
                        'previous-text' => '‹',
                        'next-text' => '›',
                    ]) ?>
                </div>
            </div>

            <div class="box-actions pull-right">
                <?php

                echo Html::button(FA::icon('folder-o')->fixedWidth() . Yii::t('cookyii.feed', 'Create new section'), [
                    'class' => 'btn btn-primary',
                    'ng-click' => 'items.filter.section.add()',
                    'aria-label' => 'Add section',
                ]);

                echo Html::button(FA::icon('file-o')->fixedWidth() . Yii::t('cookyii.feed', 'Create new item'), [
                    'class' => 'btn btn-primary',
                    'ng-click' => 'items.add()',
                    'aria-label' => 'Add item',
                ]);

                ?>
            </div>
        </div>
    </div>
</section>