var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap','googlechart',"checklist-model",'datetimepicker']);



var urls = ["http://localhost:8080/","https://mammalweb.herokuapp.com/"];

// Ajax Service
adminApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function(query,pageNum,pageSize) {
    	//$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";
        // Delete the Requested With Header
        //delete $http.defaults.headers.common['X-Requested-With'];
      return $http.post(urls[1]+'photo?pageNum='+pageNum+'&pageSize='+pageSize,query).success(function() {
      });
    },
     getOptions: function() {
      return $http.get(urls[1]+'options').success(function() {
      });
    }
  };

}]);

adminApp.controller('MainController', ['$scope','ajax', function($scope,serverComm) {
	$scope.results = []; //contains the results from the server
	$scope.options = {};
	
	
	//PAGE functions
	$scope.currentPage = 0;
	$scope.pageSize = 5;
	
	$scope.numberOfPages = function() {
		return Math.ceil($scope.numResults/$scope.pageSize);
	}
	$scope.rowsShown = function() {
		if (($scope.currentPage * $scope.pageSize) + $scope.pageSize < $scope.numResults) {
			return Number(($scope.currentPage * $scope.pageSize) + $scope.pageSize);
		} else {
			return $scope.numResults;
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
		console.log($scope.currentPage,$scope.pageSize)
		serverComm.getPhotos($scope.filters,$scope.currentPage,$scope.pageSize).success(function(data) {
				//console.log("Data:",data);
				$scope.results = data.rows;
				$scope.numResults = data.count;
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
					//if (!(data[i]["option_name"] === "Unknown")){
						$scope.options[data[i]["struc"]][data[i]["option_id"]] = data[i]["option_name"];
					//}*/ //Removed as breaks things
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

        $scope.chartTypes = ["Table","PieChart","BarChart","ColumnChart","LineChart","ScatterChart","AreaChart"];

        getValue = function(val,field){
        	if (field.type == "checkboxes"){
        		console.log(field.struc)
        		console.log($scope.options[field.struc])
        		return $scope.options[field.struc][val];
        	}
        	else{
        		return val;
        	}
        }

        $scope.makeData = function(){
        	console.log($scope.results);

		var typeMap = {"checkboxes":"string","slider":"number","boolean":"String"};

          if ($scope.yName == "countOfXaxis"){
          	xNameSplit = $scope.xName.split(".");
          	$scope.chartObject.options.hAxis.title = $scope.readable(xNameSplit[1]);
          	$scope.chartObject.options.vAxis.title = "Count of "+$scope.readable(xNameSplit[1]);
          	var xField = $scope.filters[xNameSplit[0]][xNameSplit[1]];
          	$scope.chartObject.data.cols = [{id: "x", label: $scope.readable(xNameSplit[1]), type: typeMap[xField.type]},
          	{id: "y", label: "Count of "+$scope.readable(xNameSplit[1]), type: "number"}];
          	$scope.chartObject.data.rows = [];


          	//build dictionary
          	dataDict = {}
          	for (i in $scope.results) {
          		console.log("loop")
          		if(Array.isArray($scope.results[i][xNameSplit[0]])){
          			for (var j = 0; j < $scope.results[i][xNameSplit[0]].length; j++)
	          			{
	          				xValue = getValue($scope.results[i][xNameSplit[0]][j][xNameSplit[1]],xField);
	          				if(dataDict.hasOwnProperty(xValue))
	          				{
	          					dataDict[xValue]+=1;
	          				}
	          				else{
	          					dataDict[xValue]=1;
	          				}
	          			
	          			}
          		}
          		else{
          			xValue = getValue($scope.results[i][xNameSplit[0]][xNameSplit[1]],xField);
      				if(dataDict.hasOwnProperty(xValue))
      				{
      					dataDict[xValue]+=1;
      				}
      				else{
      					dataDict[xValue]=1;
      				}

          		}

          	}
          	console.log(dataDict)

          	//Convert to rows
          	for (var key in dataDict){
          		$scope.chartObject.data.rows.push({c: [{v: key}, {v: dataDict[key]}]});
          	}



          }
          else{

          xNameSplit = $scope.xName.split(".");
          yNameSplit = $scope.yName.split(".");

          $scope.chartObject.options.vAxis.title = $scope.readable(yNameSplit[1]);
          $scope.chartObject.options.hAxis.title = $scope.readable(xNameSplit[1]);

          //check types of variables
          var xField = $scope.filters[xNameSplit[0]][xNameSplit[1]];
          var yField = $scope.filters[yNameSplit[0]][yNameSplit[1]];

          $scope.chartObject.data.cols = [{id: "x", label: $scope.readable(xNameSplit[1]), type: typeMap[xField.type]},{id: "y", label: $scope.readable(yNameSplit[1]), type: typeMap[yField.type]}];

          $scope.chartObject.data.rows = [];

          for (var i = 0; i < $scope.results.length; i++) {

          			//Deal with data containing arrays
          			loopX = 1;
          			loopY = 1;

          			xChanged = false;
          			yChanged = false;

          			//Set loop conditions
          			if (Array.isArray($scope.results[i][xNameSplit[0]])){
          				loopX = $scope.results[i][xNameSplit[0]].length;
          			}
          			else{
          				$scope.results[i][xNameSplit[0]] = [$scope.results[i][xNameSplit[0]]];
          				xChanged = true;
          			}
          			if (Array.isArray($scope.results[i][yNameSplit[0]])){
          				loopY = $scope.results[i][yNameSplit[0]].length;
          			}else{
          				$scope.results[i][yNameSplit[0]] = [$scope.results[i][yNameSplit[0]]]
          				yChanged = true;
          			}

          			//Add rows
          			for (var j = 0; j < loopX; j++)
          			{
          				for (var k = 0; k < loopY; k++)
						{
							xValue = getValue($scope.results[i][xNameSplit[0]][j][xNameSplit[1]],xField);
							yValue = getValue($scope.results[i][yNameSplit[0]][k][yNameSplit[1]],yField);
							$scope.chartObject.data.rows.push({c: [{v: xValue}, {v: yValue}]});
						}
          			}

          			//Restore data structure
          			if (xChanged){
          				$scope.results[i][xNameSplit[0]] = $scope.results[i][xNameSplit[0]][0];
          			}
          			if(yChanged){
          				$scope.results[i][yNameSplit[0]] = $scope.results[i][yNameSplit[0]][0];
          			}
          }

      }
          console.log($scope.chartObject.data);


        };


    $scope.chartStyle = "height:300px;width:300px";

    $scope.chartObject = {
	  "type": "Table",
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
	      "title": "Xaxis",
	      "gridlines": {
	        "count": 10
	      }
	    },
	    "hAxis": {
	      "title": "Yaxis"
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