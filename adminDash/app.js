var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap','googlechart',"checklist-model"]);






var url = "http://community.dur.ac.uk/g.t.hudson/GP/adminDash/";

// Ajax Service
adminApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function() {
      return $http.get('api/photo.php').success(function() {
      });
    },
    getSequence: function(id) {
      return $http.patch('/api/volunteer/approve/' + id).success(function() {
        console.log("approved");
      });
    }
  };

}]);

adminApp.controller('MainController', ['$scope','ajax', function($scope,serverComm) {
	$scope.results = []; //contains the results from the server
	$scope.options = {10:"Badger",11:"Blackbird",12:"Domestic Cat",3:"Female",4:"Male",5:"Adult",6:"Juvenille"};
    $scope.speciesIDs = [10,11,12];
    $scope.genderIDs = [3,4];
    $scope.ageIDs = [5,6];

    $scope.filters = {
      species:{
            type:"checkboxes",
            value:[],
            ids:[10,11,12]
      },
      gender:{
          type:"checkboxes",
          value:[],
          ids:[3,4]
      },
      age:{
          type:"checkboxes",
          value:[],
          ids:[5,6]
      },
      evenness:{
          type:"slider",
          minValue: 0,
          maxValue: 100,
          options: {
              floor: 0,
              ceil: 100,
              step: 1,
              precision: 1,
              onEnd: $scope.getFilterValues
          }
        },
        numClassifications:{
            type:"slider",
            minValue: 0,
            maxValue: 30,
            options: {
                floor: 0,
                ceil: 30,
                step: 1,
                precision: 1,
                onEnd: $scope.getFilterValues
            }
        },
        numAnimals:{
            type:"slider",
            minValue: 0,
            maxValue: 20,
            options: {
                floor: 0,
                ceil: 20,
                step: 1,
                precision: 1,
                onEnd: $scope.getFilterValues
            }
        }
    }

	$scope.getResults = function(){
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
