var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap','googlechart',"checklist-model",'datetimepicker']);



var urls = ["http://localhost:8080/","https://mammalweb.herokuapp.com/"];

// Ajax Service
adminApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function(query) {
    	//$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
        // Delete the Requested With Header
        //delete $http.defaults.headers.common['X-Requested-With'];
			 
      return $http.post(urls[0]+'photo',query).success(function() {
      });
    },
     getOptions: function() {
      return $http.get(urls[0]+'options').success(function() {
      });
    }
  };

}]);

adminApp.controller('MainController', ['$scope','ajax', function($scope,serverComm) {
	$scope.results = []; //contains the results from the server
	$scope.options = {};
	
	
	//PAGE functions
	$scope.currentPage = 0;
	$scope.pageSize = 10;
	
	$scope.numberOfPages = function() {
		return Math.ceil($scope.results.length/$scope.pageSize);
	}
	$scope.rowsShown = function() {
		if (($scope.currentPage * $scope.pageSize) + $scope.pageSize < $scope.results.length) {
			return Number(($scope.currentPage * $scope.pageSize) + $scope.pageSize);
		} else {
			return $scope.results.length;
		}
	}
	$scope.range = function(num) {
		return Array.apply(null, {length: num}).map(Number.call, Number)
	}
	$scope.setPage = function(num) {
		$scope.currentPage = num;
	}
  
  //MAIN functions

	$scope.getResults = function(){
		console.log("get results");
		$("#loader").fadeTo("fast", 0.7);
		serverComm.getPhotos($scope.filters).success(function(data) {
				//console.log("Data:",data);
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
		//console.log("get options");
		$("#loader").fadeTo("fast", 0.7);
		serverComm.getOptions().success(function(data) {
				//console.log(data);
				$scope.options = {};
				for (var i = 0; i < data.length; i++) {
					//console.log(data[i]["struc"])
					if (!$scope.options.hasOwnProperty(data[i]["struc"]) ){
						$scope.options[data[i]["struc"]] = {}
					}
					//Check for and remove "Unknown"
					if (!(data[i]["option_name"] === "Unknown")){
						$scope.options[data[i]["struc"]][data[i]["option_id"]] = data[i]["option_name"];
					}
				}
				//console.log($scope.options)
				
		});
	};
	
	 $scope.readable = function(string) {
		string = string.replace(/_id/,"");
		string = string.replace(/_/g, " ");
		string = string.replace(/([A-Z])/g, ' $1');
		string = string.replace(/^./, function(str){ return str.toUpperCase(); });
		return string
  	}

    $scope.filters = {
      Classification:{
		   species:{
		         type:"checkboxes",
		         value:[],
		         struc:"mammal"
		   },
		   gender:{
		       type:"checkboxes",
		       value:[],
		       struc: "gender"
		   },
		   age:{
		       type:"checkboxes",
		       value:[],
		       struc:"age"
		   },
       /*blankImages:{
           type:"checkboxes",
           value:[],
           ids:[62,64]
        },*/
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
		     }/*,
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
		     }*/
		     /*numAnimals:{
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
        },*/
		  },
        Site:{
		     habitat_id:{
		       type:"checkboxes",
		       value:[],
		       struc:"habitat"
		    }
       },
       Photo:{
		    contains_human:{
		       type:"boolean",
		       value:[]
		    },
		    taken:{
		       type:"dateTime",
		       icon: "glyphicon-calendar",
		       minValue: "",
		       maxValue: "",
		       options:{
		       	format:"DD/MM/YYYY"
		       }
		    }/*,
		    time:{
		       type:"dateTime",
		       icon: "glyphicon-time",
		       minValue: "",
		       maxValue: "",
		       options:{
		       	format:"LT"
		       }
		    }*/
		 }
        
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



        $scope.makeData = function(){
          console.log($scope.xName);
          xNameSplit = $scope.xName.split(".");
          yNameSplit = $scope.yName.split(".");

          $scope.chartObject.options.vAxis.title = yNameSplit[1];
          $scope.chartObject.options.hAxis.title = xNameSplit[1];

          //console.log($scope.results)
          $scope.chartObject.data.cols = [{id: "x", label: xNameSplit[1], type: "string"},{id: "y", label: yNameSplit[1], type: "string"}];

          $scope.chartObject.data.rows = [];
          console.log($scope.results[0])


          for (var i = 0; i < $scope.results.length; i++) {

          			//if the data is an array (and not just JSON data like in the site field)
          				//loop though array
          					//append the whole row

                   $scope.chartObject.data.rows.push({c: [{v: $scope.results[i][xNameSplit[0]][xNameSplit[1]]}, {v: $scope.results[i][yNameSplit[0]][yNameSplit[1]]}]});
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
	    "title": "Chart title",
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



//Utilities for treating objects like arrays
adminApp.filter('keylength', function(){
  return function(input){
    if(!angular.isObject(input)){
      throw Error("Usage of non-objects with keylength filter!!")
    }
    return Object.keys(input).length;
  }
});

adminApp.filter('objectLimitTo', [function(){
    return function(obj, limit){
        var keys = Object.keys(obj);
        if(keys.length < 1){
            return [];
        }

        var ret = new Object,
        count = 0;
        angular.forEach(keys, function(key, arrayIndex){
           if(count >= limit){
                return false;
            }
            ret[key] = obj[key];
            count++;
        });
        return ret;
    };
}]);