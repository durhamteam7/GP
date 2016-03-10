var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap']);

adminApp.controller('MainController', ['$scope', function($scope) {
  $scope.results = [];
}]);

adminApp.controller('FilterController', ['$scope', function($scope) {
  $scope.query = "in filter";

   //Range slider config
    $scope.minRangeSlider = {
        minValue: 0,
        maxValue: 100,
        options: {
            floor: 0,
            ceil: 100,
            step: 1,
            precision: 1
        }
    };


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
