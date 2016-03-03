var adminApp = angular.module('adminDash', []);

adminApp.controller('MainController', ['$scope', function($scope) {
  $scope.results = [];
}]);

adminApp.controller('FilterController', ['$scope', function($scope) {
  $scope.query = "in filter";

  $scope.funFunction = function(a){
	alert(a);
	}
}]);


adminApp.controller('SearchResultsController', ['$scope', function($scope) {
  $scope.var1 = "in search results";
}]);

adminApp.controller('SummaryController', ['$scope', function($scope) {
  $scope.var1 = "in search results";
}]);

adminApp.controller('GraphsController', ['$scope', function($scope) {
  $scope.var1 = "in search results";
}]);

adminApp.controller('CSVController', ['$scope', function($scope) {
  $scope.var1 = "in search results";
}]);
