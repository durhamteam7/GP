var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap','googlechart',"checklist-model"]);






var url = "http://community.dur.ac.uk/g.t.hudson/GP/adminDash/";

// Ajax Service
adminApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function(query) {
      return $http.post('api/photo.php',query).success(function() {
      });
    },
     getOptions: function() {
      return $http.get('api/option.php').success(function() {
      });
    }
  };

}]);

adminApp.controller('MainController', ['$scope','ajax', function($scope,serverComm) {
	$scope.results = []; //contains the results from the server
	$scope.options = {};


	$scope.getResults = function(){
		console.log("get results");
		$("#loader").fadeTo("fast", 0.7);
		serverComm.getPhotos($scope.filters).success(function(data) {
				//console.log(data);
				$scope.results = data;
				for (var i = 0; i < $scope.results.length; i++) {
                    var result = $scope.results[i];
					var parts = result.dirname.split("/");

					$scope.results[i].URL = parts[parts.length - 2]+"/"+parts[parts.length - 1]+"/"+result.filename;
				}
				$("#loader").fadeOut("slow");
		});
	};

	$scope.getOptions = function(){
		console.log("get options");
		$("#loader").fadeTo("fast", 0.7);
		serverComm.getOptions().success(function(data) {
				console.log(data);
				for (var i = 0; i < data.length; i++) {
					$scope.options[data[i]["option_id"]] = data[i]["option_name"]
				}
				console.log($scope.options)
				
		});
	};
	
	 $scope.readable = function(string) {
      string = string.replace(/_/g, " ");
      string = string.replace(/([A-Z])/g, ' $1');
      string = string.replace(/^./, function(str){ return str.toUpperCase(); });
      return string
  	}

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
              onEnd: $scope.getResults
          }
        },
        numClassifications:{
            type:"slider",
            minValue: 0,
            maxValue: 100,
            options: {
                floor: 0,
                ceil: 100,
                step: 1,
                precision: 1,
                onEnd: $scope.getResults
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
                onEnd: $scope.getResults
            }
        },
        habitatType:{
          type:"checkboxes",
          value:[],
          ids:[62,64]
       },
       humanPresence:{
          type:"checkboxes",
          value:[],
          ids:[62,64]
       },
       blankImages:{
          type:"checkboxes",
          value:[],
          ids:[62,64]
       },
        
    }


	$scope.getResults();
	$scope.getOptions();
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
