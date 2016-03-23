var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap','googlechart']);

var url = "http://community.dur.ac.uk/g.t.hudson/GP/adminDash/";

// Ajax Service
adminApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function() {
      return $http.get('api/photo.php').success(function() {
      });
    },
    getSequence: function(id) {
      return $http.patch(url+'/api/volunteer/approve/' + id).success(function() {
        console.log("approved");
      });
    }
  };

}]);

adminApp.controller('MainController', ['$scope','ajax', function($scope,serverComm) {
	$scope.results = []; //contains the results from the server
	$scope.species = [{"option_id":10,"option_name":"Badger"},{"option_id":11,"option_name":"Blackbird"},{"option_id":12,"option_name":"Domestic Cat"}];


	$scope.getResults = function(query){
		
		serverComm.getPhotos().success(function(data) {
				//console.log(data);
				$scope.results = data;
				for (var i = 0; i < $scope.results.length; i++) {
          var result = $scope.results[i];
					var parts = result.dirname.split("/");

					$scope.results[i].URL = parts[parts.length - 2]+"/"+parts[parts.length - 1]+"/"+result.filename;
					//console.log($scope.results[i].URL);
				}
			});
		
	};
	$scope.getResults();
}]);

adminApp.controller('FilterController', ['$scope', function($scope) {
	$scope.query = "in filter";

    $scope.sendQ = function(){
        alert("hi");
    }


   //Range slider config
    $scope.evennessSlider = {
        minValue: 0,
        maxValue: 100,
        options: {
            floor: 0,
            ceil: 100,
            step: 1,
            precision: 1,
            onEnd: $scope.sendQ
        }
    };

    $scope.numAnimalsSlider = {
        minValue: 0,
        maxValue: 20,
        options: {
            floor: 0,
            ceil: 20,
            step: 1,
            precision: 1,
            onEnd: $scope.sendQ
        }
    };

    $scope.numClassificationsSlider = {
        minValue: 0,
        maxValue: 30,
        options: {
            floor: 0,
            ceil: 30,
            step: 1,
            precision: 1,
            onEnd: $scope.sendQ
        }
    };
}]);


adminApp.controller('SearchResultsController', ['$scope', function($scope) {
	$scope.var1 = "in search results";
}]);

adminApp.controller('SummaryController', ['$scope', function($scope) {
	$scope.var1 = "in search results";
}]);

adminApp.controller('GraphsController', ['$scope', function($scope) {
	$scope.var1 = "in search results";

        $scope.chartTypes = ["AreaChart","PieChart","BarChart","ColumnChart","LineChart","Table"];
        $scope.variables = [{displayName:"Species",value:"species"},{displayName:"Evenness",value:"evenness"},{displayName:"Number of Classifications",value:"numberOfClassifications"},{displayName:"Number of Animals",value:"numberOfAnimals"},{displayName:"Gender",value:"gender"},{displayName:"Age",value:"age"},{displayName:"Date",value:"taken"},{displayName:"Time",value:"taken"},{displayName:"Postcode",value:"site_id"},{displayName:"Habitat Type",value:"habitatType"},{displayName:"Human Prescence",value:"contains_human"},{displayName:"Blank Images",value:"blank"}];




        $scope.makeData = function(){
          console.log($scope.xName);
          //console.log($scope.results)
          $scope.chartObject.data.cols = [{id: "x", label: $scope.xName, type: "string"},{id: "y", label: $scope.yName, type: "number"}];

          $scope.chartObject.data.rows = [];
          for (var i = 0; i < $scope.results.length; i++) {
            $scope.chartObject.data.rows.push({c: [{v: $scope.results[i][$scope.xName]}, {v: $scope.results[i][$scope.yName]}]});
          }

          console.log($scope.chartObject.data);
        };




    $scope.chartObject = {
  "type": "AreaChart",
  "displayed": true,
  "data": {
    "cols": [
      
    ],
    "rows": [
      
    ]
  },
  "options": {
    "title": "Sales per month",
    "isStacked": "true",
    "fill": 20,
    "displayExactValues": true,
    "vAxis": {
      "title": "Sales unit",
      "gridlines": {
        "count": 10
      }
    },
    "hAxis": {
      "title": "Date"
    }
  },
  "formatters": {}
}
}]);

adminApp.controller('CSVController', ['$scope', function($scope) {
	$scope.var1 = "in search results";
}]);
