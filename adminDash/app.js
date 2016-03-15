var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap']);

adminApp.controller('MainController', ['$scope', function($scope) {
	$scope.results = [	];
	$scope.getResults = function(query){
		for (var i = 0; i < 10; i++) {
			$scope.results.push({"photo_id": "1337", "upload_filename":"IMG_0003.JPG", "site":{"site_id":"1234"}, "upload":{"upload_id":"420", "person_id":"example"}, "taken":"01/02/16", "filename":"9ae715f0d99b9682a7978494ff43def6.jpg","dirname":"person_182/site_2"});
		};
	};
	$scope.getResults();
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
