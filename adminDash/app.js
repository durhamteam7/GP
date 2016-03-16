var adminApp = angular.module('adminDash', []);



// Ajax Service
adminApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function() {
      return $http.get('api/photo.php').success(function() {
      });
    },
    getSequence: function(id) {
      return $http.patch('/api/volunteer/approve/' + id).success(function() {
        console.log("approved")
      });
    }
  };

}]);

adminApp.controller('MainController', ['$scope','ajax', function($scope,serverComm) {
	$scope.results = [];
	$scope.getResults = function(query){
		for (var i = 0; i < 0; i++) {
			$scope.results.push({"photo_id": "1337", "upload_filename":"IMG_0003.JPG", "site":{"site_id":"1234"}, "upload":{"upload_id":"420", "person_id":"example"}, "taken":"01/02/16", "filename":"9ae715f0d99b9682a7978494ff43def6.jpg","dirname":"person_182/site_2"});
			
		};
		
		serverComm.getPhotos().success(function(data) {
				console.log(data);
				$scope.results = data;
				for (var i = 0; i < $scope.results.length; i++) {
				 	result = $scope.results[i];
					var parts = result.dirname.split("/");

					$scope.results[i].URL = parts[parts.length - 2]+"/"+parts[parts.length - 1]+"/"+result.filename;
					console.log($scope.results[i].URL);
				}
			});
		
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
