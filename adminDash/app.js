var adminApp = angular.module('adminDash', []);

adminApp.controller('MainController', ['$scope', function($scope) {
	$scope.results = [	];
	$scope.species = [{"option_id":10,"option_name":"Badger"},{"option_id":11,"option_name":"Blackbird"},{"option_id":12,"option_name":"Domestic Cat"}]
	$scope.getResults = function(query){
		for (var i = 0; i < 10; i++) {
			$scope.results.push({"photo_id": "1337", "upload_filename":"IMG_0003.JPG", "site":{"site_id":"1234"}, "upload":{"upload_id":"420", "person_id":"example"}, "taken":"01/02/16", "filename":"9ae715f0d99b9682a7978494ff43def6.jpg","dirname":"person_182/site_2"});
		};
	};
	$scope.getResults();
}]);

adminApp.controller('FilterController', ['$scope', function($scope) {
	$scope.query = "in filter";

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
