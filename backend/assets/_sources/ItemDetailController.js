"use strict";

angular.module('BackendApp')

  .controller('ItemDetailController', [
    '$scope', '$timeout', 'QueryScope', 'ItemResource',
    function ($scope, $timeout, QueryScope, Item) {
      var defaultValues = {sections: []};

      $scope.data = {};

      $scope.getItem = function () {
        return QueryScope.get('id');
      };

      $scope.isNewItem = $scope.getItem() === null;

      $scope.$on('reloadItemData', function (e) {
        $scope.reload();
      });

      $scope.$watch('data.title', function (val) {
        if (typeof val !== 'undefined' && $scope.isNewItem) {
          $scope.data.slug = getSlug(val);
        }
      });

      if ($scope.isNewItem && QueryScope.get('section') !== null) {
        defaultValues.sections = [parseInt(QueryScope.get('section'))];
      }

      $scope.isItemDisabled = function (item) {
        return $scope.getItem() === item;
      };

      $scope.reload = function () {
        $scope.isNewItem = $scope.getItem() === null;

        $scope.updatedWarning = false;
        $scope.editedProperty = null;

        if ($scope.getItem() === null) {
          $scope.data = angular.copy(defaultValues);
        } else {
          Item.detail({id: $scope.getItem()}, function (response) {
            $scope.data = response;

            $scope.$broadcast('itemDataReloaded', response);
          });
        }
      };

      $timeout($scope.reload);
      $timeout(checkItemUpdate, 5000);

      $scope.updatedWarning = false;

      function checkItemUpdate() {
        if ($scope.getItem() !== null) {
          Item.detail({id: $scope.getItem()}, function (item) {
            if ($scope.data.hash !== item.hash) {
              $scope.updatedWarning = true;
            }
          });

          $timeout(checkItemUpdate, 5000);
        }
      }
    }
  ]);