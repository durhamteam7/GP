var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap']);


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
	$scope.species = [{"option_id":10,"option_name":"Badger"},{"option_id":11,"option_name":"Blackbird"},{"option_id":12,"option_name":"Domestic Cat"}]

	$scope.getResults = function(query){
		
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
   //Range slider config
    $scope.evennessSlider = {
        minValue: 0,
        maxValue: 100,
        options: {
            floor: 0,
            ceil: 100,
            step: 1,
            precision: 1
        }
    };

    $scope.numAnimalsSlider = {
        minValue: 0,
        maxValue: 20,
        options: {
            floor: 0,
            ceil: 20,
            step: 1,
            precision: 1
        }
    };

    $scope.numClassificationsSlider = {
        minValue: 0,
        maxValue: 30,
        options: {
            floor: 0,
            ceil: 30,
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
