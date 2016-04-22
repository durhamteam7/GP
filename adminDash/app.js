
var adminApp = angular.module('adminDash', ['rzModule', 'ui.bootstrap','googlechart',"checklist-model",'datetimepicker','toggle-switch','ngAutocomplete','bw.paging']);

var mammalwebBaseURL = "http://www.mammalweb.org/biodivimages/"

var urls = ["http://localhost:8080/","https://mammalweb.herokuapp.com/"];

// Ajax Service
adminApp.factory('ajax', ['$http', function($http) {
	return {
    getPhotos: function(query,pageNum,pageSize,isSequence) {
      return $http.post(urls[0]+'photo?pageNum='+pageNum+'&pageSize='+pageSize+'&sequence='+isSequence,query).success(function() {
      });
    },
    getPhotosCSV: function(query,isSequence){
    	return $http.post(urls[0]+'photo?output=csv&sequence='+isSequence,query).success(function() {
      });
    },
    getOptions: function() {
      return $http.get(urls[0]+'options').success(function() {
      });
    },
    getPersons: function() {
      return $http.get(urls[0]+'persons').success(function() {
      });
    },
    getFilters: function() {
      return $http.get('filters.json').success(function() {
      });
    }
  };

}]);

adminApp.controller('MainController', ['$scope','ajax', function($scope,serverComm) {
	$scope.results = []; //contains the results from the server
	$scope.options = {};
	

	$scope.result = '';
    $scope.options = {country:''};
    $scope.details = '';
	
	//PAGE functions
	$scope.currentPage = 1;
	$scope.pageSize = 15;

	$scope.persons = [];

	$scope.getPersons = function() {
		serverComm.getPersons().success(function(data) {
			$scope.persons = data
		})
	}
		
	$scope.getPersons();

	$scope.rowsShown = function() {
		if ((($scope.currentPage-1) * $scope.pageSize) + $scope.pageSize < $scope.numResults) {
			return Number((($scope.currentPage-1) * $scope.pageSize) + $scope.pageSize);
		} else {
			return $scope.numResults;
		}
	}
  
  //MAIN functions

	$scope.getResults = function(page){
		$("#loader").fadeTo("fast", 0.7);
		if (page){
			$scope.currentPage = page
		}
		serverComm.getPhotos($scope.filters,$scope.currentPage,$scope.pageSize,$scope.isSequence).success(function(data) {
				console.log("Data:",data);
				$scope.results = data.rows;
				$scope.numResults = data.count;
				for (var i = 0; i < $scope.results.length; i++) {
					var result = $scope.results[i];
					var parts = result.Photo.dirname.split("/");
					$scope.results[i].Photo.URL = mammalwebBaseURL + parts[parts.length - 2]+"/"+parts[parts.length - 1]+"/"+result.Photo.filename;
				}
				$("#loader").fadeOut("slow");
		});
	};

	
	
	
	
	
	$scope.downloadCSV = function(){
		$("#loader").fadeTo("fast", 0.7);
		serverComm.getPhotosCSV($scope.filters,$scope.isSequence).success(function(data) {
				console.log("Data:",data);
				$("#loader").fadeOut("slow");
				//console.log(data)
				dataLines  = data.split("\n");
				//dataLines[0] = dataLines[0].substring(0,dataLines[0].length-1)
				for (i in dataLines){
					lineSplit = dataLines[i].split(",")
					for (j in lineSplit){
						//lineSplit[j].replace(/"/g, '"');
					}
					dataLines[i] = lineSplit.join(',')

				}
				console.log(dataLines);
				data = dataLines.join('\r\n')
				console.log(data)
				$scope.url = "data:application/csv;charset=utf-8,"+encodeURIComponent(data);

				$('body').append('<a class="b" href="'+$scope.url+'" target="_blank" download="mammal.csv">HIIII</a>');
				$('.b').click(function() {
				    window.location = $(this).attr('href');
				}).click();
				$( ".b" ).remove();
		});
	}
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

	$scope.getOptionName = function(optionNum){
        //Function to convert an option into human readable string
        for (key in $scope.options){
            if($scope.options[key].hasOwnProperty(optionNum)){
                return $scope.options[key][optionNum]
            }
        }
        return "";
    }

	$scope.getFilters = function(){
		serverComm.getFilters().success(function(data) {
			console.log("FITLERS",data)
				//console.log(data);
				$scope.filters = data
				
		});
	}
	
	 $scope.readable = function(string) {
	 	if (typeof string === "undefined")
	 	{
	 		return "";
	 	}
		string = string.replace(/_id/,"");
		string = string.replace(/_/g, " ");
		string = string.replace(/([A-Z])/g, ' $1');
		string = string.replace(/^./, function(str){ return str.toUpperCase(); });
		return string
  	}

    $scope.filters = {}

    $scope.$watch('filters', function(newVal, oldVal){
    	$scope.currentPage = 1;
    	$scope.getResults();
	}, true);
	$scope.$watch('isSequence', function(newVal, oldVal){
		$scope.currentPage = 1;
    	$scope.getResults();
	}, true);

	$scope.getResults();
	$scope.getOptions();
	$scope.getFilters();

}]);

adminApp.controller('GraphsController', ['$scope', function($scope) {
	$scope.var1 = "in search results";

        $scope.chartTypes = ["Table","PieChart","BarChart","ColumnChart","LineChart","ScatterChart","AreaChart"];

        getValue = function(val,field){
        	if (field.type == "checkboxes"){
        		return $scope.getOptionName(val);
        	}
        	else if(field.type == "dateTime"){
        		return new Date(val);
        	}
        	else{
        		return val;
        	}
        }

        $scope.makeData = function(){
        	console.log($scope.results);

        	
        	//Stop if a variable is not defined
        	if (typeof $scope.xName == "undefined" || typeof $scope.yName == "undefined")	{
        		return;
        	}

		var typeMap = {"checkboxes":"string","slider":"number","boolean":"number","dateTime":"datetime","coord":"number"};

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


    $scope.chartStyle = "height:300px;width:100%";

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