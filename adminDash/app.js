var adminApp = angular.module('adminDash', []);



adminApp.controller('MainController', ['$scope', function($scope) {
  $scope.var1 = "in main";
}]);



adminApp.controller('FilterController', ['$scope', function($scope) {
  $scope.var1 = "in filter";
}]);




adminApp.controller('SearchResultsController', ['$scope', function($scope) {
  $scope.var1 = "in search results";
}]);
