var adminApp = angular.module('adminDash', []);

adminApp.controller('MainController', ['$scope', function($scope) {
	$scope.results = [];
}]);

adminApp.controller('FilterController', ['$scope', function($scope) {
	$scope.query = "in filter";
}]);

$scope.getResults = function(query){
	for (var i = 0; i < 10; i++) {
		$scope.results.push({"photo_id": "1337", "filename":"test", "site":{"site_id"}, "upload":{"upload_id":"420", "person_id":"example"}, "taken":"01/02/16", "other crap here": "crap"});
	};
};


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
